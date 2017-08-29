<?php

namespace util;

require_once (__DIR__ . '/ILogger.class.php');
require_once (__DIR__.'/../scripts/ScriptUtil.class.php');

class Logger implements ILogger
{

	const LOG_LEVEL_TRACE = 'TRACE';
	const LOG_LEVEL_DEBUG = 'DEBUG';
	const LOG_LEVEL_INFO = 'INFO';
	const LOG_LEVEL_WARN = 'WARN';
	const LOG_LEVEL_ERROR = 'ERROR';
	const LOG_LEVEL_NONE = 'NONE';

	const LOG_MAX_FILE_SIZE_IN_MB = 'maxFileSizeInMB';
	const LOG_LEVEL = 'level';
	const LOG_STDOUT = 'stdout';
	const LOG_PATH = 'logPath';

	const DEFAULT_LOG_MAX_FILE_SIZE_IN_MB = 100;
	const DEFAULT_LOG_LEVEL = 'INFO';
	const DEFAULT_LOG_STDOUT = false;
	const DEFAULT_LOG_PATH = '/tmp/log/Php.log';
	const DEFAULT_LOG_CLASS_NAME = "Php";

	private $maxFileSizeInMB = self::DEFAULT_LOG_MAX_FILE_SIZE_IN_MB;
	private $logLevel = self::DEFAULT_LOG_LEVEL;
	private $stdout = self::DEFAULT_LOG_STDOUT;
	private $logPath = self::DEFAULT_LOG_PATH;
	private $className = self::DEFAULT_LOG_CLASS_NAME;
	private $scriptUtil;

	private $levelValues = array (
			self::LOG_LEVEL_TRACE => 0,
			self::LOG_LEVEL_DEBUG => 1,
			self::LOG_LEVEL_INFO => 2,
			self::LOG_LEVEL_WARN => 3,
			self::LOG_LEVEL_ERROR => 4,
			self::LOG_LEVEL_NONE => 5
	);

	public function __construct($config, $className)
	{

		$this->scriptUtil = new \scripts\ScriptUtil ( null );

		if (!is_null($config))
		{
			$this->maxFileSizeInMB = $config[self::LOG_MAX_FILE_SIZE_IN_MB];
			$this->logLevel = $config[self::LOG_LEVEL];
			$this->stdout = $config[self::LOG_STDOUT];
			$this->logPath = $config[self::LOG_PATH];
		}

		if (!is_null($className))
		{
			$this->className = $className;
		}
	}

	public static function createDefaultLogger()
	{
			return new Logger(parse_ini_file(null, null));
	}

	public static function createLogger($configPath, $className)
	{
			return new Logger(parse_ini_file($configPath), $className);
	}

	public function trace($msg)
	{
		$this->write ( self::LOG_LEVEL_TRACE, $msg );
	}

	public function debug($msg)
	{
		$this->write ( self::LOG_LEVEL_DEBUG, $msg );
	}

	public function info($msg)
	{
		$this->write ( self::LOG_LEVEL_INFO, $msg );
	}

	public function warn($msg)
	{
		$this->write ( self::LOG_LEVEL_WARN, $msg );
	}

	public function error($msg)
	{
		$this->write ( self::LOG_LEVEL_ERROR, $msg );
	}

	private function __print($type, $msg) {

		if ($this->levelValues [$type] < $this->levels [strtoupper ( $this->logLevel )])
		{
			return true;
		}

		if (! $this->stdout)
		{
			$this->_checkFile ( $this->logPath );
			$filehandler = fopen ( $filePath, 'a' );

			if (! $filehandler)
			{
				return false;
			}

			//date time to be able to print microseconds
			$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
			$logmessage = '[' . $now->format("m-d-Y H:i:s.u") . '] ' . $type . ' - ' . $this->className .  ' : ' . $msg . "\n";

			fputs ( $filehandler, $logmessage );
			fclose ( $filehandler );
		}
		else
		{
			$scriptUtil->println($msg);
		}
	}

	private function _checkFile($file)
	{
		if (! file_exists ( $file ))
		{
			return;
		}

		if (filesize ( $file ) / 1024 / 1024 > $this->maxFileSizeInMB)
		{
			//TODO logrotate on time too

			$appendDate = date ( 'Y-m-d_H', time () );

			$backupFile = dirname ( $file ) . '/' . basename ( $file, '.log' ) . '.' . $appendDate . '.log';

			$suffix = 0;
			while (file_exists ( $backupFile ))
			{
				$backupFile = dirname ( $file ) . '/' . basename ( $file, '.log' ) . '.' . $appendDate . "_" . $suffix . '.log';
				$suffix ++;
			}

			rename ( $file, $backupFile );
		}
	}
}
?>
