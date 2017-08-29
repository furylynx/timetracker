<?php

use util\Logger;
/*
 * This script fetches the given month's time overview using the
 * given credentials and the time element's fields.
 */
set_time_limit ( 0 );

//german locale
setlocale(LC_ALL, "de_DE.utf8");

require_once ( __DIR__ . '/../scripts/ScriptUtil.class.php');

$scriptUtil = new scripts\ScriptUtil ( $argv );
$args = $scriptUtil->getArgs ( true, true, "username", "password", "month", "ignoretoday" );


if (! is_null ( $args ))
{

	$userName = $args["username"];
	$password = $args["password"];
	if (!is_null($userName) && !is_null($password))
	{
		$month = date("Y-m-d");
		if (array_key_exists("month", $args))
		{
			$month = $args ["month"];
		}

		$ignoreToday = true;

		if (array_key_exists("ignoretoday", $args))
		{
			$ignoreToday = $args ["ignoretoday"] > "0";
		}

		require_once (__DIR__ . '/../util/Logger.class.php');
		require_once (__DIR__ . '/../util/PDOHelper.class.php');
		require_once (__DIR__ . '/../dao/TimesDaoSql.class.php');
		require_once (__DIR__ . '/../dao/AccountDaoSql.class.php');
		require_once (__DIR__ . '/../service/TimeService.class.php');

		//initialize loggerconfig and database
		$loggerconfig = parse_ini_file(__DIR__."/logger.ini");
		$db = util\PDOHelper::createPDOUsingConfigFile(__DIR__."/database.ini");

		//initialize the DAO
		$accDao = new dao\AccountDaoSql ($loggerconfig, $db);
		$timesDao = new dao\TimesDaoSql ($loggerconfig, $db);

		$userId = $accDao->identify($userName, $password);

		if (!is_null($userId) && $userId > 0)
		{
			//use accDao to fetch time
			$targetTimePerDay = $accDao->getTargetTimePerDay($userId);

			//initialize the time service
			$timeService = new service\TimeService ($timesDao);
			$list = $timeService->getMonthOverview($userId, $month, $scriptUtil->getLineBreakString(), $targetTimePerDay, $ignoreToday);

			$scriptUtil->println ( $list );

		}
		else
		{
			$scriptUtil->println("You are not authorized to access this site.");
		}
	}
}
else
{
	$scriptUtil->println("Invalid arguments.");
	exit (1);
}
