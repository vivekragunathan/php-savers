<?

require_once 'ILogger.php';

const LOG_TIMESTAMP_FORMAT = 'Y-m-d G:i:s';

trait LogMessageFormatter {

	public function __construct() {
	}

	public function formatTrace($msgFmtSpec, array $args = null) {
		return $this->formatMessage(date(LOG_TIMESTAMP_FORMAT),
			LogLevel::TRACE,
			$msgFmtSpec,
			$args);
	}

	public function formatInfo($msgFmtSpec, array $args = null) {
		return $this->formatMessage(date(LOG_TIMESTAMP_FORMAT),
			LogLevel::INFO,
			$msgFmtSpec,
			$args);
	}

	public function formatWarning($msgFmtSpec, array $args = null) {
		return $this->formatMessage(date(LOG_TIMESTAMP_FORMAT),
			LogLevel::WARNING,
			$msgFmtSpec,
			$args);
	}

	public function formatError($msgFmtSpec, array $args = null) {
		return $this->formatMessage(date(LOG_TIMESTAMP_FORMAT),
			LogLevel::ERROR,
			$msgFmtSpec,
			$args);
	}

	protected function formatMessage($timeStamp, $logLevel, $msgTextFmtSpec, array $args = null) {
		$coreMsg = $msgTextFmtSpec;

		if (!empty($args)) {
			foreach ($args as $name => $value) {
				if (is_array($value)) {
					$args[$name] = '<<Array>>';
				} else {
					if (is_object($value)) {
						$args[$name] = '<<Object>>';
					}
				}
			}

			$coreMsg = vsprintf($msgTextFmtSpec, $args);
		}

		return $timeStamp
			. ILogger::LOG_ITEM_SEPARATOR
			. $logLevel
			. ILogger::LOG_ITEM_SEPARATOR
			. $coreMsg
			. "\n";
	}
}
