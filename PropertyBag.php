<?

# Usable currently but work in progress...

abstract class PropertyBag implements ArrayAccess, Countable {
	protected $_store = null;
	protected $_readOnly = false;
	
	protected function __construct(&$source, $readOnly = false) {
		$this->_store = $source;
		$this->_readOnly = $readOnly;
	}
	
	public function isReadOnly() {
		return $this->_readOnly;
	}
	
	public static function fromArray(array &$source = null, $readOnly = false) {
		return new ArrayBasedPropertyBag($source, $readOnly);
	}
	
	public static function fromObject($source, $readOnly = false) {
		if ($source === null || !is_object($source)) {
			throw new Exception('Expected object type.');
		}
		
		return new ObjectBasedPropertyBag($source, $readOnly);
	}
	
	public function __get($name) {
		// NOTE: Called when property access ($pbag->property) during a
		// get could not be resolved.
		return $this->offsetGet($name);
	}
	
	public function __set($name, $value) {
		// NOTE: Called when property access ($pbag->property = value;)
		// during a set could not be resolved.
		$this->offsetSet($name, $value);
	}
}

class ArrayBasedPropertyBag extends PropertyBag {
	public function __construct(array &$source = null, $readOnly = false) {
		if ($source === null) {
			$source = [];
			$readOnly = false;
		}
		
		parent::__construct($source, $readOnly);
	}
	
	#region ArrayAccess Interface Implementation

	public function offsetExists($key) {
		// TODO: verifyKeyType($key);
		return isset($this->_store[$key]);
	}
	
	public function offsetGet($key) {
		if (!$this->offsetExists($key)) {
			throw new Exception('Key not found: ' . $key);
		}
		
		return $this->_store[$key];
	}

	public function offsetSet($key, $value) {
		if (!$this->offsetExists($key) && $this->isReadOnly()) {
			throw new Exception('PropertyBag is not writable. Key: ' . $key);
		}
		
		$this->_store[$key] = $value;
	}

	public function offsetUnset($key) {
		if ($this->offsetExists($key)) {
	 		unset($this->_store[key]);
			return true;
		}
		
		return false;
	}
	
	#endregion
	
	#region Countable Interface Implementation
	
	public function count() {
		return count($this->_store);
	}
	
	#endregion
}

class ObjectBasedPropertyBag extends PropertyBag {
	public function __construct(&$source = null, $readOnly = false) {
		if ($source === null) {
			$source = new stdClass();
			$readOnly = false;
		}
		
		if (!is_object($source)) {
			throw new Exception('Expected object type', 2000);
		}
		
		parent::__construct($source, $readOnly);
	}
	
	#region ArrayAccess Interface Implementation

	public function offsetExists($key) {
		// TODO: verifyKeyType($key);
		return property_exists($this->_store, $key);
	}
	
	public function offsetGet($key) {
		if (!$this->offsetExists($key)) {
			throw new Exception('Key not found: ' . $key);
		}
			
		return $this->_store->$key;
	}

	public function offsetSet($key, $value) {
		if (!$this->offsetExists($key) && $this->isReadOnly()) {
			throw new Exception('PropertyBag is not writable. Key: ' . $key);
		}
			
		$this->_store->$key = $value;
	}

	public function offsetUnset($key) {
		if ($this->offsetExists($key)) {
			unset($this->_store->key);
			return true;
		}
		
		return false;
	}
	
	#endregion
	
	#region Countable Interface Implementation
	
	public function count() {
		return count(get_object_vars($this->_store));
	}
	
	#endregion
}
