<?php

class LogLevel {
	const TRACE   = 'TRACE';
	const INFO    = 'INFO';
	const WARNING = 'WARNING';
	const ERROR   = 'ERROR';
}

interface ILogger {
	const LOG_ITEM_SEPARATOR = ' | ';

	public function trace($msg);
	public function info($msg);
	public function warning($msg);
	public function error($msg);

	public function log($level, $message);
}

class SilentLogger implements ILogger {
	public function trace($msg) { }
	public function info($msg) { }
	public function warning($msg) { }
	public function error($msg) { }
	public function log($level, $message) { }
}
