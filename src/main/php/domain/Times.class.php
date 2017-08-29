<?php

namespace domain;

class Times {
	
	private $id;
	private $userId;
	private $timeFrom;
	private $timeUntil;
	private $comment;
	
	public function setId($id) {
	 	$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	
	public function getUserId() {
		return $this->userId;
	}
	
	public function setTimeFrom($timeFrom) {
		$this->timeFrom = $timeFrom;
	}
	
	public function getTimeFrom() {
		return $this->timeFrom;
	}
	
	public function setTimeUntil($timeUntil) {
		$this->timeUntil = $timeUntil;
	}
	
	public function getTimeUntil() {
		return $this->timeUntil;
	}
	
	public function setComment($comment) {
		$this->comment = $comment;
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	public static function fromArray($array) {
		$t = new Times();
		$t->setId($array["id"]);
		$t->setUserId($array["user_id"]);
		$t->setTimeFrom($array["time_from"]);
		$t->setTimeUntil($array["time_until"]);
		$t->setComment($array["comment"]);
		
		return $t;
	}
	
}