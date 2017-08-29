<?php

namespace dao;

use domain;

require_once (__DIR__ . '/../domain/Times.class.php');

interface ITimesDao
{
	public function get($id);

	public function getAllForDay($userId, $date);

	public function getAllForMonth($userId, $date);

	public function insert(domain\Times $timesObject);

	public function getTimeForDay($userId, $date);

	public function getTimeForWeek($userId, $date, $onlyIfSameMonth, $ignoreToday);

	public function getTimeForMonth($userId, $date, $ignoreToday);
}
