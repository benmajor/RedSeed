<?php

namespace BenMajor\RedSeed;

use RedBeanPHP\R;
use BenMajor\RedSeed\Exception\SeedException;
use BenMajor\RedSeed\Exception\SyntaxException;
use BenMajor\RedSeed\Exception\FunctionException;

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

	public function __construct()
	{
		$this->hashingMethod = PASSWORD_DEFAULT;
		$this->functionHandler = new FunctionHandler();
	}

	/**
	 * Seed the specified bean type
	 *
	 * @param string $beanType
	 * @param integer $numToSeed
	 * @param array $fields
	 * @return array
	 */
	public function seed(string $beanType, int $numToSeed, array $fields): array
	{
		if ($numToSeed <= 0) {
			throw new SeedException('Number of beans to generate must be greater than 0.');
		}

		if (empty($fields)) {
			throw new SeedException('Fields cannot be empty.');
		}

		$fieldMap = [ ];

		foreach ($fields as $field => $function) {
			// Call the callback:
			if (is_callable($function) || is_array($function)) {
				$fieldMap[$field] = $function;
			}
			else {
				preg_match($this->functionRegEx, $function, $matches);

				// Invalid function syntax!
				if (count($matches) === 0) {
					throw new SyntaxException('Invalid function syntax specified for field \''.$field.'\'');
				}

				// Does the function exist?
				if(method_exists($this->functionHandler, $matches[1]) === false) {
					throw new FunctionException('Specified function does not exist: '.$matches[1].'().');
				}

				$fieldMap[$field] = [
					'method' => $matches[1],
					'args' => array_map('trim', explode(',', $matches[2])),
					'_func' => true
				];
			}
		}

		$beans = [ ];

		// Loop and create the beans:
		for ($i = 0; $i < $numToSeed; $i++) {
			$bean = R::dispense($beanType);

			// Add params:
			foreach ($fieldMap as $field => $function) {
				// It's a user-defined anonymous function:
				if (is_callable($function) === true) {
					// If the function returns an array, it's probably an ownList:
					$value = call_user_func($function);

					if (is_array($value) === true) {
						$bean->{'own'.ucfirst($field).'List'} = $value;
					}
					else {
						$bean->{$field} = call_user_func($function);
					}
				}
				else {
					$bean->{$field} = $this->functionHandler->{$function['method']}($function['args']);
				}
			}

			// Identify the bean as having been seeded:
			$bean->_seeded = true;

			$id = R::store($bean);

			if ($id) {
				$beans[] = $bean;
			}
		}

		return $beans;
	}

	/**
	 * Unseed the specified bean type
	 *
	 * @param string $beanType
	 * @return array
	 */
	public function unseed(string $beanType): array
	{
		$removed = [ ];

		foreach (R::find($beanType, '_seeded = 1') as $bean) {
			R::trash($bean);

			$removed[] = $bean->id;
		}

		# Remove the _seeded column:
		R::exec("ALTER TABLE `$beanType` DROP `_seeded`");

		return $removed;
	}

	/**
	 * Set the password hashing algorithm to use by the password() function
	 *
	 * @param string $hash
	 * @return void
	 */
	public function setHashMethod(string $hash): void
	{
		if (in_array($hash, $this->allowedHashingAlgos) === false) {
			throw new Exception\SeedException('Unsupported hashing algorithm specified; must be one of: '.implode(' ', $this->allowedHashingAlgos));
		}

		$this->hashingMethod = $hash;
	}

	/**
	 * Get the current hash method
	 *
	 * @return int
	 */
	public function getHashMethod(): int
	{
		return $this->hashingMethod;
	}
}

$seeder = new RedSeed();

# Add the plugins to the Facade:
R::ext('seed', function($beanType, $number, $fields) use ($seeder) {  return $seeder->seed($beanType, $number, $fields); });
R::ext('unseed', function($beanType) use ($seeder) { return $seeder->unseed($beanType); });
R::ext('setSeederHashMethod', function($algorithm) use ($seeder) { return $seeder->setHashMethod($algorithm); });
