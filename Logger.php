<?php

require_once 'ILogger.php';
require_once 'LogMessageFormatter.php';

class Logger implements ILogger {
	use LogMessageFormatter;

	private $logFileHandle;

	public function __construct($logFileName) {
		$this->logFileHandle = fopen($logFileName, 'a+');
	}

	public function __destruct() {
		fclose($this->logFileHandle);
	}

	#region ILogger Interface Implementation

	public function trace($msg) {
		$funcArgs = func_get_args();
		$args     = Logger::getMessageArguments($funcArgs);
		$logText  = $this->formatTrace($msg, $args);
		$this->writeLog($logText);
	}

	public function info($msg) {
		$funcArgs = func_get_args();
		$args     = Logger::getMessageArguments($funcArgs);
		$logText  = $this->formatInfo($msg, $args);
		$this->writeLog($logText);
	}

	public function warning($msgTextFmtSpec) {
		$funcArgs = func_get_args();
		$args     = Logger::getMessageArguments($funcArgs);
		$logText  = $this->formatWarning($msgTextFmtSpec, $args);
		$this->writeLog($logText);
	}

	public function error($msgTextFmtSpec) {
		$funcArgs = func_get_args();
		$args     = Logger::getMessageArguments($funcArgs);
		$logText  = $this->formatError($msgTextFmtSpec, $args);
		$this->writeLog($logText);
	}

	#endregion

	#region Private Methods

	private static function getMessageArguments(array &$funcArgs) {
		return count($funcArgs) == 2 && (!isset($funcArgs[1]) || is_array($funcArgs[1]) || is_null($funcArgs[1]))
			? $funcArgs[1]
			: array_slice($funcArgs, 1);
	}

	private function writeLog($logMsg) {
		fwrite($this->logFileHandle, $logMsg, strlen($logMsg));
	}

	#endregion
}
