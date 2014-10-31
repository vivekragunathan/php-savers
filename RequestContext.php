<?

class RequestContext {
	const REQUEST_METHOD   = 'REQUEST_METHOD';
	const SERVER_PROTOCOL  = 'SERVER_PROTOCOL';
	const SERVER_SIGNATURE = 'SERVER_SIGNATURE';
	const SERVER_PORT      = 'SERVER_PORT';
	const HTTPS            = 'HTTPS';

	const HTTP_METHOD_NOT_ALLOWED = 401;
	
	public static function isHttps() {
		return empty($_SERVER[self::HTTPS])
			? (@$_SERVER[self::SERVER_PORT] ?: 80) == 443
			: $_SERVER[self::HTTPS] != 'off';
	}

	public static function isPost() {
		return (@$_SERVER[self::REQUEST_METHOD] ?: null) == 'POST' && isset($_POST);
	}

	public static function isGet() {
		return (@$_SERVER[self::REQUEST_METHOD] ?: null) == 'GET' && isset($_GET);
	}
	
	public static function ensurePost() {
		if (!self::isPost()) {
			throw new \Exception('Request method not allowed.', self::HTTP_METHOD_NOT_ALLOWED);
		}
	}
	
	public static function ensureGet() {
		if (!self::isGet()) {
			throw new \Exception('Request method not allowed.', self::HTTP_METHOD_NOT_ALLOWED);
		}
	}
	
	public static function isWebContext() {
		return (@$_SERVER[self::SERVER_PROTOCOL] ?: false)      // HTTP/1.1
			&& (@$_SERVER[self::REQUEST_METHOD] ?: false)       // GET or POST ...
			&& (@$_SERVER[self::SERVER_SIGNATURE] ?: false);	// Apache/2.4.6 (Unix) Server at www.twinspires.com Port 80;
	}

	public static function newLine() {
		static $nl = null;

		if ($nl === null) {
			$nl = self::isWebContext() ? "<br />" : PHP_EOL;
		}

		return $nl;
	}
}
