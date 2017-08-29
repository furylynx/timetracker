<?php

use util\Logger;
/*
 * This script inserts/updates an entry to the database using the
 * given credentials and the time element's fields
 */
set_time_limit ( 0 );

//german locale
setlocale(LC_ALL, "de_DE.utf8");

require_once ( __DIR__ . '/../scripts/ScriptUtil.class.php');

$scriptUtil = new scripts\ScriptUtil ( $argv );

$args = $scriptUtil->getArgs(false, true, "username", "password", "timeuntil", "comment");

if (! is_null ( $args ))
{
	//request params
	$userName = $args["username"];
	$password = $args["password"];

	$timeUntil = $args["timeuntil"];
	$comment = $args["comment"];

	if (!is_null($userName) && !is_null($password))
	{
		require_once (__DIR__ . '/../util/Logger.class.php');
		require_once (__DIR__ . '/../util/PDOHelper.class.php');
		require_once (__DIR__ . '/../dao/TimesDaoSql.class.php');
		require_once (__DIR__ . '/../dao/AccountDaoSql.class.php');
		require_once (__DIR__ . '/../domain/Times.class.php');

		//initialize loggerconfig and database
		$loggerconfig = parse_ini_file(__DIR__."/logger.ini");
		$db = util\PDOHelper::createPDOUsingConfigFile(__DIR__."/database.ini");

		//initialize the DAOs
		$accDao = new dao\AccountDaoSql ($loggerconfig, $db);
		$timesDao = new dao\TimesDaoSql ($loggerconfig, $db);

		$userId = $accDao->identify($userName, $password);

		if (!is_null($userId) && $userId > 0)
		{
			$timesObject = new domain\Times();
			$timesObject->setUserId($userId);
			$timesObject->setTimeUntil($timeUntil);
			$timesObject->setComment($comment);

			$createdRow = $timesDao->updateOpenInterval($timesObject);

			$output = "Entry processed.";

			if ($createdRow > 0)
			{
				$output .= " Closed $createdRow entr". ($createdRow > 1 ? "ies" : "y").".";
			}

			$scriptUtil->println($output);
			exit (1);
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
