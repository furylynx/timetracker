<?php

namespace dao;

use domain;
use util;

require_once (__DIR__ . '/IAccountDao.class.php');
require_once (__DIR__ . '/../util/Logger.class.php');

class AccountDaoSql implements IAccountDao {

	private $db = null;
	private $logger = null;

	/**
	 * Initializes the times DAO.
	 *
	 * @param string $loggerconfig
	 *        	The logger's configuration.
	 * @param string $db
	 *        	The db controller instance.
	 */
	public function __construct($loggerconfig, \PDO $db)
	{
			$this->logger = new util\Logger($loggerconfig, __CLASS__);
			$this->db = $db;
	}

	public function identify($userName, $password)
	{

		$hashedPassword = hash("sha256", $password);

		$sql = "SELECT id
				FROM accounts
				WHERE
					user_name = ?
		 			AND password = ?
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($userName, $hashedPassword));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows))
		{
			return null;
		}
		else
		{
			return $rows[0]["id"];
		}

	}

	public function getTargetTimePerDay($userId)
	{
		$sql = "SELECT target_time_per_day
				FROM accounts
				WHERE
					id = ?
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows))
		{
			return null;
		}
		else
		{
			return $rows[0]["target_time_per_day"];
		}

	}


}
