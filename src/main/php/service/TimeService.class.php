<?php

namespace service;

use dao;
use domain;

require_once (__DIR__ . '/../dao/ITimesDao.class.php');
require_once (__DIR__ . '/../domain/Times.class.php');
class TimeService {
	private $dao = null;

	public function __construct(dao\ITimesDao $dao) {
		$this->dao = $dao;
	}

	public function getMonthOverview($userId, $date, $linebreak, $targetTimePerDay, $ignoreToday) {
		$times = $this->dao->getAllForMonth ( $userId, $date );

		$dateTitle = strftime ( "%B %Y", strtotime ( $date ) );

		//ignore today if interval not closed, otherwise continue
		$ignoreToday = $ignoreToday && (is_null($times[count($times) -1]->getTimeUntil()));

		$result = $dateTitle;
		$result .= $linebreak;

		for($i = 0; $i < strlen ( $dateTitle ); $i ++) {
			$result .= "=";
		}

		$result .= $linebreak;
		$result .= $linebreak;

		$lastDay = null;
		$lastWeek = null;
		$weekTarget = 0;
		$monthTarget = 0;
		foreach ( $times as $time ) {
			$currentDay = date ( "Y-m-d", strtotime ( $time->getTimeFrom () ) );
			$currentWeek = date ( "W", strtotime ( $time->getTimeFrom () ) );

			if (is_null ( $lastDay )) {
				$lastDay = $currentDay;
				$lastWeek = $currentWeek;

				// increment target minutes
				if (! $ignoreToday || $currentDay != date ( "Y-m-d" )) {
					$weekTarget += $targetTimePerDay;
					$monthTarget += $targetTimePerDay;
				}

				$day = date ( "d", strtotime ( $time->getTimeFrom () ) );
				$result .= $day . ") ";
			} else if ($lastDay != $currentDay) {
				// next day, end the current one

				// get total time for the day
				$overallMinutes = $this->dao->getTimeForDay ( $userId, $lastDay );

				$hours = floor ( $overallMinutes / 60 );
				$minutes = $overallMinutes % 60;
				$result .= " = " . sprintf ( "%02d", $hours ) . "h " . sprintf ( "%02d", $minutes ) . "m";
				$result .= $linebreak;

				if ($lastWeek != $currentWeek) {

					// get total time for the week
					$result .= $this->printWeekTotal ( $userId, $lastDay, $weekTarget, $ignoreToday, $linebreak );

					$result .= $linebreak;

					$weekTarget = 0;
					$lastWeek = $currentWeek;
				}

				// increment target minutes
				if (! $ignoreToday || $currentDay != date ( "Y-m-d" )) {
					$weekTarget += $targetTimePerDay;
					$monthTarget += $targetTimePerDay;
				}

				$lastDay = $currentDay;
				$day = date ( "d", strtotime ( $time->getTimeFrom () ) );

				$result .= $day . ") ";
			} else {
				$result .= " // ";
			}

			$from = date ( "H:i", strtotime ( $time->getTimeFrom () ) );

			$until = "xx:xx";
			if (! is_null ( $time->getTimeUntil () )) {
				$until = date ( "H:i", strtotime ( $time->getTimeUntil () ) );
			} else {
				//add required minutes on top of current timestamp
				$until = "(ETA ".date("H:i", strtotime("+".($targetTimePerDay - $this->dao->getTimeForDay($userId, $time->getTimeFrom()))." minutes")).")";
			}

			$result .= $from . " -- " . $until;
		}

		$overallMinutes = $this->dao->getTimeForDay ( $userId, $lastDay );

		$hours = floor ( $overallMinutes / 60 );
		$minutes = $overallMinutes % 60;
		$result .= " = " . sprintf ( "%02d", $hours ) . "h " . sprintf ( "%02d", $minutes ) . "m";
		$result .= $linebreak;

		$result .= $this->printWeekTotal ( $userId, $lastDay, $weekTarget, $ignoreToday, $linebreak );

		$result .= $linebreak;

		$result .= $this->printMonthTotal ( $userId, $lastDay, $monthTarget, $ignoreToday, $linebreak );
		$result .= $linebreak;

		return $result;
	}

	private function printWeekTotal($userId, $date, $targetWeekTime, $ignoreToday, $linebreak) {
		$result = "";

		// get total time for the week
		$weekOverallMinutes = $this->dao->getTimeForWeek ( $userId, $date, true, $ignoreToday );
		$weekOvertime = $weekOverallMinutes - $targetWeekTime;

		$negativeOvertime = false;
		if ($weekOvertime < 0) {
			$negativeOvertime = true;
			$weekOvertime = - 1 * $weekOvertime;
		}

		$weekHours = floor ( $weekOvertime / 60 );
		$weekMinutes = $weekOvertime % 60;
		$result .= ":: ";
		if ($negativeOvertime) {
			$result .= "-";
		} else {
			$result .= "+";
		}
		$result .= sprintf ( "%02d", $weekHours ) . ":" . sprintf ( "%02d", $weekMinutes ) . "";
		$result .= $linebreak;

		return $result;
	}

	private function printMonthTotal($userId, $date, $targetMonthTime, $ignoreToday, $linebreak) {
		$result = "=========";
		$result .= $linebreak;

		// get total time for the week
		$monthOverallMinutes = $this->dao->getTimeForMonth ( $userId, $date, $ignoreToday );
		$monthOvertime = $monthOverallMinutes - $targetMonthTime;

		$negativeOvertime = false;
		if ($monthOvertime < 0) {
			$negativeOvertime = true;
			$monthOvertime = - 1 * $monthOvertime;
		}

		$monthHours = floor ( $monthOvertime / 60 );
		$monthMinutes = $monthOvertime % 60;
		$result .= ":: ";
		if ($negativeOvertime) {
			$result .= "-";
		} else {
			$result .= "+";
		}
		$result .= sprintf ( "%02d", $monthHours ) . ":" . sprintf ( "%02d", $monthMinutes ) . "";
		$result .= $linebreak;

		return $result;
	}
}

