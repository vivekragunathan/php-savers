<?

class Strictness {
	const NO_STRICT		= 0;
	const STRICT_GET	= 1;
	const STRICT_SET	= 2;
	const STRICT_ALL	= 3;	// STRICT_GET | STRICT_SET
}

abstract class ArrayAccessImpl implements ArrayAccess {
    protected $_array;
	private $_strictness = Strictness::NO_STRICT;

    public function __construct(array &$array, $strictness = Strictness::NO_STRICT) {
		if (is_null($array)) {
			throw new Exception('The specified array cannot be null.');
		}
		
        $this->_array = $array;
		$this->_strictness = $strictness;
    }

    public function offsetGet($key) {
        self::verifyKeyType($key);
		
		$keyExists = isset($this->_array[$key]);
		
		if (!$keyExists && !$this->canSafeGet()) {
			throw new Exception('Property \'' . $key . '\' does not exist.');
		}
		
        return $keyExists ? $this->_array[$key] : null;
    }

    public function offsetSet($key, $value) {
        self::verifyKeyType($key);

        if (is_null($key)) {
            $this->_array[] = $value;
			return;
        }
		
		$keyExists = isset($this->_array[$key]);
		
		if (!$keyExists && !$this->canSafeSet()) {
			throw new Exception('Property \'' . $key . '\' does not exist.');
		}
        
		$this->_array[$key] = $value;
    }

    public function offsetExists($key) {
        return isset($this->_array[$key]);
    }

    public function offsetUnset($key) {
        unset($this->_array[$key]);
    }
	
	protected function canSafeGet() {
		return (($this->_strictness & Strictness::STRICT_GET) == 0 ? true : false);
	}
	
	protected function canSafeSet() {
		return (($this->_strictness & Strictness::STRICT_SET) == 0 ? true : false);
	}

    protected static function verifyKeyType($key) {
        if (!is_integer($key) && !is_string($key)) {
            throw new Exception('The specified key is not integer or string');
        }
    }
}
