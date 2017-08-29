<?php

namespace dao;

interface IAccountDao
{

	public function identify($userName, $password);

}
