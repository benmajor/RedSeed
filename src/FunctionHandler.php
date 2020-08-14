<?php

namespace BenMajor\RedSeed;

use BenMajor\RedSeed\Exception\ArgumentException;
use RedBeanPHP\R;

class FunctionHandler
{
	private $chars = [ ];
	private $digits = [ ];

	private $_chars = 'abcdefghijklmnopqrstuvwxyz';
	private $_digits = '0123456789';

	function __construct()
	{
		$this->chars = str_split($this->_chars);
		$this->digits = str_split($this->_digits);
	}

	# Generate a string of random length:
	public function string( array $args )
	{
		if( count($args) != 2 )
		{
			throw new ArgumentException('string() expects exactly two arguments.');
		}

		$min = $args[0];
		$max = $args[1];

		if( !is_int($min) || !is_int($max) )
		{
			if( (!ctype_digit($min) || !ctype_digit($max)) )
			{
				throw new ArgumentException('Both arguments specified for string() must be integers.');
			}
		}

		if( $min < 0 ) 
		{
			throw new ArgumentException('Minimum length must be greater than 0.');
		}

		if( $max < 0 )
		{
			throw new ArgumentException('Maximum length must be greater than 0.');
		}

		if( $max < $min )
		{
			throw new ArgumentException('Maximum length must be greater than or equal to minimum length');
		}

		$string = '';
		$length = rand($min, $max);

		for($i = 0; $i < $length; $i++)
		{
			$string.= $this->chars[array_rand($this->chars)];
		}

		return $string;
	}

	# Generate a word of random length:
	public function word( array $args )
	{
		return ucfirst($this->string($args));
	}

	# Generate an integer of random length:
	public function integer( array $args )
	{
		if( count($args) != 2 )
		{
			throw new ArgumentException('string() expects exactly two arguments.');
		}

		$min = $args[0];
		$max = $args[1];

		if( !is_int($min) || !is_int($max) )
		{
			if( (!ctype_digit($min) || !ctype_digit($max)) )
			{
				throw new ArgumentException('Both arguments specified for string() must be integers.');
			}
		}

		if( $min < 0 ) 
		{
			throw new ArgumentException('Minimum length must be greater than 0.');
		}

		if( $max < 0 )
		{
			throw new ArgumentException('Maximum length must be greater than 0.');
		}

		if( $max < $min )
		{
			throw new ArgumentException('Maximum length must be greater than or equal to minimum length');
		}

		return mt_rand($min, $max);
	}

	# Return the current time:
	public function time( array $args )
	{
		return date('H:i:s');
	}

	# Return the current date:
	public function date( array $args )
	{
		return date('Y-m-d');
	}

	# Return the current datetime:
	public function datetime( array $args )
	{
		return R::isoDateTime();
	}

	# Generate a valid email address:
	public function email( array $args )
	{
		$tlds = [ 'com', 'co.uk', 'nl', 'info', 'io' ];

		$domains = [
			'gmail',
			'live',
			'me',
			'domain',
			'aol',
			'mail',
			'email'
		];

		# Build the email:
		return sprintf('%s.%s@%s.%s',
			$this->string([ 3, 10 ]),
			$this->string([ 5, 10 ]),
			$domains[array_rand($domains)],
			$tlds[array_rand($tlds)]
		);
	}

	# Generate a random IP address:
	public function ipaddress( array $args )
	{
		return sprintf(
			'%d.%d.%d.%d',
			$this->integer([ 1, 255 ]),
			$this->integer([ 1, 255 ]),
			$this->integer([ 1, 255 ]),
			$this->integer([ 1, 255 ])
		);
	}
}