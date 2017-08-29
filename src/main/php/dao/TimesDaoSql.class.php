<?php

namespace dao;

use domain;
use util;

//includes
require_once (__DIR__ . '/../domain/Times.class.php');
require_once (__DIR__ . '/ITimesDao.class.php');
require_once (__DIR__ . '/../util/Logger.class.php');


class TimesDaoSql implements ITimesDao
{
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
		// initialize the logger
		$this->logger = new util\Logger($loggerconfig, __CLASS__);
		$this->db = $db;
	}

	public function get($id)
	{
		$sql = "SELECT *
				FROM times as c
				WHERE id = ?";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($id));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$result = array ();
		foreach ( $rows as $entry )
		{
			$result [] = domain\Times::fromArray ( $entry );
		}

		return $result;
	}

	public function getAllForDay($userId, $date)
	{
		$sql = "SELECT *
				FROM times
				WHERE
					DATE(time_from) = DATE(?)
					AND user_id = ?
				ORDER BY time_from
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($date, $userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$result = array ();
		foreach ( $rows as $entry )
		{
			$result [] = domain\Times::fromArray ( $entry );
		}

		return $result;
	}

	public function getAllForMonth($userId, $date)
	{
		$sql = "SELECT *
				FROM times
				WHERE
					DATE_FORMAT(time_from, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
					AND user_id = ?
				ORDER BY time_from
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($date, $userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$result = array ();
		foreach ( $rows as $entry )
		{
			$result [] = domain\Times::fromArray ( $entry );
		}

		return $result;
	}

	public function insert(domain\Times $timesObject)
	{

		$sql = "INSERT INTO times
				(id, user_id, time_from, time_until, comment)
				VALUES
				(?,?,?,?,?)
				ON DUPLICATE KEY UPDATE
				time_from=?,time_until=?,comment=?
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($timesObject->getId(), $timesObject->getUserId (),$timesObject->getTimeFrom (),$timesObject->getTimeUntil (),$timesObject->getComment (), $timesObject->getTimeFrom (), $timesObject->getTimeUntil (), $timesObject->getComment ()));
	}

	public function updateOpenInterval(domain\Times $timesObject)
	{
		$sql = "UPDATE times SET time_until = ?, comment = ?
						WHERE user_id = ? AND DATE(time_from) = DATE(?) AND time_until IS NULL
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($timesObject->getTimeUntil (),$timesObject->getComment (), $timesObject->getUserId (), $timesObject->getTimeUntil ()));

		return  $stmt->rowCount();
	}

	public function insertIfNoOpenInterval(domain\Times $timesObject)
	{

		$sql = " SELECT id FROM times
							WHERE user_id = ? AND DATE(time_from) = DATE(?) AND time_until IS NULL
							LIMIT 1
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($timesObject->getUserId (),$timesObject->getTimeFrom ()));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows))
		{
				//no open interval for user, create one
				$sql = "INSERT INTO times
						(user_id, time_from, comment)
						VALUES (?,?,?)
						";

				$stmt = $this->db->prepare($sql);
				$stmt->execute(array($timesObject->getUserId (),$timesObject->getTimeFrom (),$timesObject->getComment ()));

				return  $stmt->rowCount();
		}
	}

	public function getTimeForDay($userId, $date)
	{
		$sql = "SELECT SUM(t) as ct
				FROM (
					SELECT TIMESTAMPDIFF(MINUTE,time_from,time_until) as t
					FROM times
					WHERE
						DATE(time_from) = DATE(?)
						AND time_until IS NOT NULL
						AND user_id = ?
					UNION ALL
					SELECT TIMESTAMPDIFF(MINUTE, time_from, NOW()) as t
					FROM times
					WHERE
						DATE(time_from) = DATE(?)
						AND time_until IS NULL
						AND user_id = ?
				) as x
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($date, $userId,$date, $userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows) || is_null($rows[0]["ct"]))
		{
			return 0;
		}
		else
		{
			return $rows[0]["ct"];
		}
	}

	public function getTimeForWeek($userId, $date, $onlyIfSameMonth, $ignoreToday)
	{

		$dateFormat = "%Y %u";

		if ($onlyIfSameMonth)
		{
			$dateFormat = "%Y-%m %u";
		}

		$sql = "SELECT SUM(t) as ct
				FROM (
					SELECT TIMESTAMPDIFF(MINUTE,time_from,time_until) as t
					FROM times
					WHERE
						DATE_FORMAT(time_from, ?) = DATE_FORMAT(?, ?)
						AND time_until IS NOT NULL
						AND user_id = ?
						";

		if ($ignoreToday)
		{
			$sql .= " AND DATE(time_from) != DATE(NOW()) ";
		}

		$sql .= "
					UNION ALL
					SELECT TIMESTAMPDIFF(MINUTE, time_from, NOW()) as t
					FROM times
					WHERE
						DATE_FORMAT(time_from, ?) = DATE_FORMAT(?, ?)
						AND time_until IS NULL
						AND user_id = ?
						";

		if ($ignoreToday)
		{
			$sql .= " AND DATE(time_from) != DATE(NOW()) ";
		}

		$sql .= "
				) as x
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($dateFormat, $date, $dateFormat, $userId, $dateFormat, $date, $dateFormat, $userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows) || is_null($rows[0]["ct"]))
		{
			return 0;
		}
		else
		{
			return $rows[0]["ct"];
		}
	}

	public function getTimeForMonth($userId, $date, $ignoreToday)
	{

		$dateFormat = '%Y-%m';

		$sql = "SELECT SUM(t) as ct
				FROM (
					SELECT TIMESTAMPDIFF(MINUTE,time_from,time_until) as t
					FROM times
					WHERE
						DATE_FORMAT(time_from, ?) = DATE_FORMAT(?, ?)
						AND time_until IS NOT NULL
						AND user_id = ?
						";

		if ($ignoreToday)
		{
			$sql .= " AND DATE(time_from) != DATE(NOW()) ";
		}

		$sql .= "
					UNION ALL
					SELECT TIMESTAMPDIFF(MINUTE, time_from, NOW()) as t
					FROM times
					WHERE
						DATE_FORMAT(time_from, ?) = DATE_FORMAT(?, ?)
						AND time_until IS NULL
						AND user_id = ?
						";

		if ($ignoreToday)
		{
			$sql .= " AND DATE(time_from) != DATE(NOW()) ";
		}

		$sql .= "
				) as x
				";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($dateFormat, $date, $dateFormat, $userId, $dateFormat, $date, $dateFormat, $userId));
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		if (0 == count($rows) || is_null($rows[0]["ct"]))
		{
			return 0;
		}
		else
		{
			return $rows[0]["ct"];
		}
	}

}

