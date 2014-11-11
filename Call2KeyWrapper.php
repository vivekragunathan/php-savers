<?

require_once 'Hurl.php';

class Call2KeyMapper {

	const METHOD_SIGNATURE_GET = 'get';
	const METHOD_SIGNATURE_SET = 'set';

	private static $_SecretKey         = '@Ny S3cRe+ |0hr@Se';
	private static $_mapperConfigCache = [];

	private $_srcMap;
	private $_mapperCfgKey;
	private $_readOnly = false;

	public function __construct($secretKey, $mccKey/*, ILogger $logger*/) {
		Hurl::ifFalse($secretKey === self::$_SecretKey, __CLASS__ . ': Use of constructor prohibited');
		Hurl::ifFalse(array_key_exists($mccKey, self::$_mapperConfigCache), __CLASS__ . ': Invalid mapper config key');
		$this->_mapperCfgKey = $mccKey;
	}

	public static function createUsing(array &$source, $jsonCfgPath) {
		$mapper          = self::createCallMapperObject($jsonCfgPath);
		$mapper->_srcMap =& $source;
		return $mapper;
	}

	public static function copyFrom(array $source, $jsonCfgPath) {
		$mapper          = self::createCallMapperObject($jsonCfgPath);
		$mapper->_srcMap = $source;
		return $mapper;
	}

	public function __call($name, $arguments) {
		$signature             = self::detectMethodSignature($name);
		$requestedPropertyName = substr($name, strlen($signature));

		switch ($signature) {
			case self::METHOD_SIGNATURE_GET:
				return $this->getPropertyValue($requestedPropertyName, $arguments);

			case self::METHOD_SIGNATURE_SET:
				return $this->setPropertyValue($requestedPropertyName, count($arguments) > 0 ? $arguments[0] : $arguments);

			default:
				return $this->handleMethodCall($requestedPropertyName, $arguments);
		}
	}

	public function isReadOnly() {
		return $this->_readOnly;
	}

	public function setReadOnly($readOnly = true) {
		$this->_readOnly = is_bool($readOnly) ? $readOnly : true;
		return $this->_readOnly;
	}

	#region Protected Methods

	protected function handleMethodCall($name, $arguments) {
		throw new CdiExceptionBase(__CLASS__ . '.' . $name . ': Unrecognized method call', GlobalStatusCodes::UNEXPECTED_ERROR);
		return null;
	}

	protected function handlePropertyGet($name, $arguments) {
		return null;
	}

	protected function handlePropertySet($name, $arguments) {
		throw new CdiExceptionBase(__CLASS__ . ': Unhandled property update - ' . $name, GlobalStatusCodes::UNEXPECTED_ERROR);
		return null;
	}

	#endregion

	#region Private Methods

	private function getPropertyValue($name, $arguments) {
		$mapperConfig =& self::$_mapperConfigCache[$this->_mapperCfgKey];

		$propertyConfig = null;

		if (array_key_exists($name, $mapperConfig)) {
			$propertyConfig =& $mapperConfig[$name];
		}

		if (empty($propertyConfig)) {
			return $this->handlePropertyGet($name, $arguments);
		}

		if (is_string($propertyConfig)) {
			$nameInSource =& $propertyConfig;

			return array_key_exists($nameInSource, $this->_srcMap)
				? $this->_srcMap[$nameInSource]
				: $this->handlePropertyGet($propertyConfig, $arguments);
		}

		if (@$propertyConfig->cooked === true) {
			return $this->handlePropertyGet($propertyConfig, $arguments);
		}

		$memberName = @$propertyConfig->memberName ?: $name;

		if (empty($propertyConfig->parent)) {
			return array_key_exists($memberName, $this->_srcMap)
				? $this->_srcMap[$memberName]
				: $this->handlePropertyGet($name, $arguments);
		}

		$parent =& $this->_srcMap[$propertyConfig->parent];

		if (is_array($parent)) { return @$parent[$memberName] ?: null; }
		if (is_object($parent)) { return @$parent->$memberName ?: null; }

		return null;
	}

	private function setPropertyValue($name, $value) {
		## TODO: Need a better error code !!!
		Hurl::ifTrue($this->isReadOnly(), __CLASS__ . ': Attempt to modify readonly store', GlobalStatusCodes::VALIDATION_ERROR);

		$mapperConfig =& self::$_mapperConfigCache[$this->_mapperCfgKey];

		$propertyConfig = null;

		if (array_key_exists($name, $mapperConfig)) {
			$propertyConfig =& $mapperConfig[$name];
		}

		if (empty($propertyConfig)) {
			return $this->handlePropertySet($name, $value);
		}

		if (is_string($propertyConfig)) {
			$this->_srcMap[$propertyConfig] = $value;
			return true;
		}

		if (@$propertyConfig->cooked === true) {
			return $this->handlePropertySet($propertyConfig, $value);
		}

		$memberName = @$propertyConfig->memberName ?: $name;

		if (empty($propertyConfig->parent)) {
			$this->_srcMap[$memberName] = $value;
			return true;
		}

		if (!array_key_exists($propertyConfig->parent, $this->_srcMap)) {
			$this->_srcMap[$propertyConfig->parent] = [];
		}

		$parent =& $this->_srcMap[$propertyConfig->parent];

		if (is_array($parent)) {
			$parent[$memberName] = $value;
			return true;
		}

		if (is_object($parent)) {
			$parent->$memberName = $value;
			return true;
		}

		return false;
	}

	private static function detectMethodSignature($name) {
		require_once 'platform/stdlib/StringUtils.php';

		if (StringUtils::startsWith($name, self::METHOD_SIGNATURE_GET)) {
			return self::METHOD_SIGNATURE_GET;
		}

		if (StringUtils::startsWith($name, self::METHOD_SIGNATURE_SET)) {
			return self::METHOD_SIGNATURE_SET;
		}

		return null;
	}

	#endregion

	#region Intricate Private Methods - DO NOT TAMPER !!!

	private static function createCallMapperObject($jsonCfgPath) {
		$mccKey = self::loadCallMapperJson($jsonCfgPath);
		return new Call2KeyMapper(self::$_SecretKey, $mccKey);
	}

	private static function loadCallMapperJson($filePath) {
		$mccKey = self::generateMapperConfigKey($filePath);

		#var_dump(json_decode(file_get_contents($filePath)));

		if (!array_key_exists($mccKey, self::$_mapperConfigCache)) {
			$json = json_decode(file_get_contents($filePath));
			Hurl::ifTrue(empty($json), __CLASS__ . ': Invalid or corrupt configuration');

			$json = get_object_vars($json);

			$mapping = [];
			foreach ($json as $key => &$value) {
				$mapping[$key] = $value;
			}

			self::$_mapperConfigCache[$mccKey] =& $mapping;
		}

		return $mccKey;
	}

	private static function generateMapperConfigKey($filePath) {
		return $filePath;
	}

	#endregion
}
