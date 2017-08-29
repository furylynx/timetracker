<?php

namespace util;

interface ILogger {

	public function trace($msg);

	public function debug($msg);

	public function info($msg);

	public function warn($msg);

	public function error($msg);

}
?>
