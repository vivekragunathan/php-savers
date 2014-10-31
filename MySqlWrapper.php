<?

require_once 'ErrorCodes.php';

trait MySqlWrapper {
	/* @var PDO */
	private $_handle;

	public function __construct($host, $user, $password, $schema = null) {
		$this->_handle = $this->connect($host, $user, $password, $schema);
	}

	public function __destruct() {
		$this->_handle = null;
	}

	public function getColumnValues($table, $column) {
		self::ensureString($table, false);
		self::ensureString($column, false);

		$stmt = $this->_handle->query('SELECT DISTINCT ' . $column . ' FROM ' . $table);
		$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		return $result;
	}

	private function connect($host, $user, $password, $schema = null) {
		self::ensureString($host, false);
		self::ensureString($user);
		self::ensureString($password);
		self::ensureString($schema);

		$connectionString = sprintf("mysql:host=%s;%s", $host, empty($schema) ? '' : ('dbname=' . $schema));
		$handle = new PDO($connectionString, $user, $password, [ PDO::ATTR_PERSISTENT => false ]);
		$this->throwOnError();
		return $handle;
	}

	private function throwOnError($msg = null) {
		$error = $this->createLastError();
		if ($error !== false) {
			throw $error;
		}
	}

	private function createLastError($msg = null) {
		if (!empty($this->_handle)) {
			$code = $this->_handle->lastErrorCode();
			if ($code != 0) {
				return new Exception($msg ?: $this->_handle->lastErrorMsg(), $code);
			}
		}

		return false;
	}

	private static function ensureString(&$value, $allowNull = true, $msg = null) {
		if ($value === null && !$allowNull) {
			throw new Exception($msg ?: 'Encountered unexpected data type! Expected string.',
				ErrorCodes::INVALID_PARAMETER,
				new Exception('[' . __FILE__ . ', #' . __LINE__ . '] ' . gettype($value), ErrorCodes::INVALID_PARAMETER));
		}
	}
}
