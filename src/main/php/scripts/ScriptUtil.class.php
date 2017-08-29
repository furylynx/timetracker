<?php

namespace scripts;

class ScriptUtil
{
	const LINEBREAK_HTML = "</br>";
	const LINEBREAK_TERMINAL = "\n";

	private static $terminal = null;

	var $argv = array ();

	public function __construct($argv=array())
	{
		$this->argv = $argv;
	}

	/**
	 * Returns the cli/request parameters as a hashmap using the params as keys for the arguments.
	 *
	 * @return multitype:unknown
	 */
	public function getArgs($verbose, $nullOnError)
	{
		$argumentOffset = 2;
		$argvOffset = 1;
		$countFuncArgs=count ( func_get_args () );
		$countFuncArgsPurged=$countFuncArgs-$argumentOffset;
		$countArgv=count ( $this->argv );
		$countArgvPurged=$countArgv-$argvOffset;

		$result = array ();

		if (ScriptUtil::isCli () && $countFuncArgsPurged != $countArgvPurged )
		{
			if ($verbose)
			{
				if ( $countFuncArgsPurged > $countArgvPurged) {
					$this->println ( "Missing input arguments. Expected " . $countFuncArgsPurged . " but found only ". $countArgvPurged ."." );
				} else {
					$this->println ( "Too many input arguments. Expected " .$countArgvPurged. " but found ". $countArgvPurged .". The unnecessary arguments will be ignored." );
				}
			}

			if ($nullOnError)
			{
				return null;
			}
		}

		$funcParams = func_get_args ();
		$c = $argvOffset;

		for($i = $argumentOffset; $i < $countFuncArgs; $i++)
		{
			if (ScriptUtil::isCli ())
			{
				if ($c < $countArgv)
				{
					$result [$funcParams [$i]] = $this->argv [$c];
				}
				$c++;
			}
			else
			{
				if (! isset ( $_REQUEST [$funcParams [$i]] ))
				{
					if ($verbose)
					{
						$this->println ( "Argument '".($funcParams [$i]) . "' is not set!" );
					}

					if ($nullOnError)
					{
						return null;
					}
				}
				else
				{
					$result [$funcParams [$i]] = $_REQUEST [$funcParams [$i]];
				}
			}
		}

		return $result;
	}

	/**
	 * Prints a new message line to the output.
	 *
	 * @param string $msg
	 *        	The message to be printed.
	 */
	public function println($msg = "")
	{
		print_r ( $msg );
		print_r ( $this->getLineBreakString () );
	}

	/**
	 * Prints a table to the output.
	 *
	 * @param  multitype:unknown  $header The header array.
	 * @param  multitype:unknown  $rows The rows array.
	 * @param  boolean $borders   True for borders.
	 * @param  boolean $printKeys True to print the keys.
	 */
	public function printTable($header, $rows, $borders = false, $printKeys = false)
	{
		if (is_null ( $header ))
		{
			// get the header, using the first row as header
			$header = array ();
			if (count ( $rows ) > 0)
			{
				foreach ( $rows [0] as $key => $value )
				{
					$header [] = $key;
				}
			}
		}

		if (ScriptUtil::isCli ())
		{
			$length = 10;
			$lengths = array ();

			foreach ( $rows as $items )
			{
				foreach ( $items as $key => $value )
				{
					if (! isset ( $lengths [$key] ) || strlen ( $key ) > $lengths [$key])
					{
						$lengths [$key] = strlen ( $key );
					}

					if (! isset ( $lengths [$key] ) || strlen ( $value ) > $lengths [$key])
					{
						$lengths [$key] = strlen ( $value );
					}
				}
			}

			$i = 0;
			foreach ( $rows as $items )
			{
				if ($i ++ == 0)
				{
					foreach ( $items as $key => $value )
					{
						$mask = "%{$length}s|";
						if (isset ( $lengths [$key] ))
						{
							$mask = "%{$lengths[$key]}s|";
						}

						printf ( $mask, $key );
					}
					print_r ( self::LINEBREAK_TERMINAL );
				}

				foreach ( $items as $key => $value )
				{
					$mask = "%{$length}s|";
					if (isset ( $lengths [$key] ))
					{
						$mask = "%{$lengths[$key]}s|";
					}

					printf ( $mask, $value );
				}
				print_r ( self::LINEBREAK_TERMINAL );
			}
		}
		else
		{
			if ($borders)
			{
				print_r ( "<table border=\"1\" >" );
			}
			else
			{
				print_r ( "<table>" );
			}

			print_r ( "<tr>" );
			foreach ( $header as $head )
			{
				print_r ( "<th>" );
				print_r ( $head );
				print_r ( "</th>" );
			}
			print_r ( "</tr>" );

			foreach ( $rows as $row )
			{
				print_r ( "<tr>" );
				foreach ( $row as $key => $entry )
				{
					print_r ( "<td>" );

					if ( $printKeys )
					{
						print_r ( $key . " : " );
					}

					print_r ( $entry );
					print_r ( "</td>" );
				}
				print_r ( "</tr>" );
			}
			print_r ( "</table>" );
		}
	}

	public function printPreformatted($msg)
	{
		if (! ScriptUtil::isCli ())
		{
			print_r ("<pre>");
		}

		print_r ( $msg );

		if (! ScriptUtil::isCli ())
		{
			print_r ("</pre>");
		}

		print_r ( $this->getLineBreakString () );
	}

	public static function getLineBreakString()
	{
		if (!ScriptUtil::isCli ())
		{
			return self::LINEBREAK_HTML;
		}
		else
		{
			return self::LINEBREAK_TERMINAL;
		}
	}

	public static function isCli()
	{
		if (is_null ( ScriptUtil::$terminal ))
		{
			ScriptUtil::$terminal = (php_sapi_name () == 'cli');
		}

		return ScriptUtil::$terminal;
	}
}
