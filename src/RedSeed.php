<?php

namespace BenMajor\RedSeed;

use RedBeanPHP\R;

class RedSeed
{
	private $allowedHashingAlgos = [ 
		PASSWORD_DEFAULT,
		PASSWORD_BCRYPT,
		PASSWORD_ARGON2I,
		PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
		PASSWORD_ARGON2_DEFAULT_TIME_COST,
		PASSWORD_ARGON2_DEFAULT_THREADS
	];

	private $functionRegEx = '/([a-zA-Z]+)[(]\s?(.*)\s?[)]$/';

	private $functionHandler;
	private $hashingMethod;

	function __construct()
	{
		$this->hashingMethod = PASSWORD_DEFAULT;
		$this->functionHandler = new FunctionHandler();
	}

	public function seed( string $beanType, int $numToSeed, array $fields )
	{
		if( $numToSeed <= 0 )
		{
			throw new Exception\SeedException('Number of beans to generate must be greater than 0.');
		}

		if( empty($fields) )
		{
			throw new Exception\SeedException('Fields cannot be empty.');
		}

		$fieldMap = [ ];

		# Map the functions:
		foreach( $fields as $field => $function )
		{
			# Call the function:
			if( is_callable($function) || is_array($function) )
			{
				$fieldMap[$field] = $function;
			}
			else
			{
				preg_match($this->functionRegEx, $function, $matches);
					
				# Invalid function syntax!
				if( ! count($matches) )
				{
					throw new Exception\SyntaxException('Invalid function syntax specified for field \''.$field.'\'');
				}

				# Does the function exist?
				if( ! method_exists($this->functionHandler, $matches[1]) )
				{
					throw new Exception\FunctionException('Specified function does not exist: '.$matches[1].'().');
				}

				$fieldMap[$field] = [
					'method' => $matches[1],
					'args' => array_map('trim', explode(',', $matches[2])),
					'_func' => true
				];
			}
		}

		$beans = [ ];

		# Loop and create the beans:
		for( $i = 0; $i < $numToSeed; $i++ )
		{
			$bean = R::dispense($beanType);

			# Add params:
			foreach( $fieldMap as $field => $function )
			{
				# It's a user-defined anonymous function:
				if( is_callable($function) )
				{
					# If the function returns an array, it's probably an ownList:
					$value = call_user_func($function);

					if( is_array($value) )
					{
						$bean->{'own'.ucfirst($field).'List'} = $value;
					}
					else
					{
						$bean->{$field} = call_user_func($function);
					}
				}

				else
				{
					$bean->{$field} = $this->functionHandler->{$function['method']}($function['args']);
				}
			}

			# Identify the bean as having been seeded:
			$bean->_seeded = true;
			
			$id = R::store($bean);

			if( $id )
			{
				$beans[] = $bean;
			}
		}

		return $beans;
	}

	public function unseed( string $beanType )
	{
		$removed = [ ];

		foreach( R::find($beanType, '_seeded = 1') as $bean )
		{
			R::trash($bean);

			$removed = $bean->id;
		}

		# Remove the _seeded column:
		R::exec("ALTER TABLE `$beanType` DROP `_seeded`");

		return $removed;
	}

	# Set the password hashing algorithm to use by the password() function:
	public function setHashMethod( string $hash )
	{
		if( ! in_array($hash, $this->allowedHashingAlgos) )
		{
			throw new Exception\SeedException('Unsupported hashing algorithm specified; must be one of: '.implode(' ', $this->allowedHashingAlgos));
		}

		$this->hashingMethod = $hash;
	}

	# Get the current hash method:
	public function getHashMethod()
	{
		return $this->hashingMethod;
	}
}

$seeder = new RedSeed();

# Add the plugins to the Facade:
R::ext('seed', function($beanType, $number, $fields) use ($seeder) {  return $seeder->seed($beanType, $number, $fields); });
R::ext('unseed', function($beanType) use ($seeder) { return $seeder->unseed($beanType); });
R::ext('setSeederHashMethod', function($algorithm) use ($seeder) { return $seeder->setHashMethod($algorithm); });
