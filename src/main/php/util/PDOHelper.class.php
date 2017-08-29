<?php

namespace util;

class PDOHelper
{

	const DATABASE_HOST = 'host';
	const DATABASE_DATABASE = 'database';
	const DATABASE_PORT = 'port';

	const DATABASE_USER = 'user';
	const DATABASE_PASSWORD = 'password';

	public function preparePDOConnectionStringUsingConfigFile($configPath)
	{
		return PDOHelper::preparePDOConnectionString(parse_ini_file ( $configPath ));
	}

	public function preparePDOConnectionString($config)
	{
		$host = $config[PDOHelper::DATABASE_HOST];
		$database = $config[PDOHelper::DATABASE_DATABASE];
		$port = $config[PDOHelper::DATABASE_PORT];

		return "mysql:host=$host;port=$port;dbname=$database";
	}

	public function createPDO($config)
	{
		$user = $config[PDOHelper::DATABASE_USER];
		$password = $config[PDOHelper::DATABASE_PASSWORD];

		$db = new \PDO(PDOHelper::preparePDOConnectionString($config), $user, $password);
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

		return $db;
	}

	public function createPDOUsingConfigFile($configPath)
	{
		return PDOHelper::createPDO(parse_ini_file ( $configPath ));
	}
}
?>
