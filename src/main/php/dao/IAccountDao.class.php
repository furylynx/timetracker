<?php

namespace dao;

interface IAccountDao
{

	public function identify($userName, $password);

	public function getTargetTimePerDay($userId);

}
