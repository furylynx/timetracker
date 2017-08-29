<?php

use util\Logger;
/*
 * This script fetches the current price list for the given account.
 * The account id is given using the client GET parameter.
 * The from is given using the from GET parameter.
 * The until is given using the until GET parameter.
 * The script uses the old CUSTOMER_PRICES pricing.
 */
set_time_limit ( 0 );

//german locale
setlocale(LC_ALL, "de_DE.utf8");

require_once ( __DIR__ . '/../scripts/ScriptUtil.class.php');

$scriptUtil = new scripts\ScriptUtil ( $argv );
$args = $scriptUtil->getArgs ( true, true, "username", "password" );


if (! is_null ( $args ))
{

	$userName = $args["username"];
	$password = $args["password"];

	if (!is_null($userName) && !is_null($password))
	{

		require_once (__DIR__ . '/../util/Logger.class.php');
		require_once (__DIR__ . '/../util/PDOHelper.class.php');
		require_once (__DIR__ . '/../dao/TimesDaoSql.class.php');
		require_once (__DIR__ . '/../dao/AccountDaoSql.class.php');

		//initialize loggerconfig and database
		$loggerconfig = parse_ini_file(__DIR__."/logger.ini");
		$db = util\PDOHelper::createPDOUsingConfigFile(__DIR__."/database.ini");

		//initialize the DAO
		$accDao = new dao\AccountDaoSql ($loggerconfig, $db);
		$timesDao = new dao\TimesDaoSql ($loggerconfig, $db);

		$userId = $accDao->identify($userName, $password);

		if (!is_null($userId) && $userId > 0)
		{
			//request time for today
			$timefortoday = $timesDao->getTimeForDay($userId, date("Y-m-d"));
			print_r ( $timefortoday );

		}
		else
		{
			$scriptUtil->println("You are not authorized to access this site.");
			exit (1);
		}
	}
}
else
{
	$scriptUtil->println("Invalid arguments.");
	exit (1);
}
