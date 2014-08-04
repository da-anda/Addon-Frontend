<?php
class MarkerManager {

	/**
	 * @var array
	 */
	protected $cache = array();

	/**
	 * @var string
	 */
	protected $emptyValue = '';

	/**
	 * @var array
	 */
	protected $markerHandlers = array();

	/**
	 * Constructur
	 * 
	 * @return void
	 */
	public function __construct() {
		global $configuration;
		if (is_array($configuration['markerHandler'])) {
			foreach ($configuration['markerHandler'] as $handler => $classPath) {
				require_once($classPath);
				$this->registerHandler($handler);
			}
		}
	}

	/**
	 * Check is a given marker can be resolved
	 * 
	 * @param string $markerName
	 * @param string $wrapper
	 * @return boolean
	 */
	public function hasMarker($markerName, $wrapper = '') {
		$marker = $wrapper . $markerName . $wrapper;
		if (isset($this->cache[$marker])) return TRUE;
		$methodName = $this->buildMarkerFunction($markerName);
		foreach ($this->markerHandlers as $handler) {
			if (method_exists($handler, $methodName)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Returns the replacement value for a marker
	 * 
	 * @param string $markerName
	 * @param string $wrapper 
	 * @return string
	 */
	public function getMarker($markerName, $wrapper = '') {
		if (!$this->hasMarker($markerName, $wrapper)) return $this->emptyValue;

		$marker = $wrapper . $markerName . $wrapper;
		if (isset($this->cache[$marker])) return $this->cache[$marker];
		
		$methodName = $this->buildMarkerFunction($markerName);
		foreach ($this->markerHandlers as $handler) {
			if (method_exists($handler, $methodName)) {
				$value = $handler->$methodName();
				$this->setMarker($marker, $value);
				return $value;
			}
		}
		return $this->emptyValue;
	}

	/**
	 * Registers a additional marker handler to resolve missing markers
	 *
	 * @param mixed $handler	Either Classname or Instance
	 * @return void
	 */
	public function registerHandler(&$handler) {
		$key = NULL;
		$object = NULL;
		if (is_object($handler)) {
			$key = spl_object_hash($handler);
			$object = $handler;
		} else if (is_string($handler) && class_exists($handler)) {
			$key = $handler;
			$object = new $handler();
		}
		if ($key && $object && !isset($this->markerHandlers[$key])) {
			$this->markerHandlers[$key] = $object;
		}
	}

	/**
	 * Sets the value for a marker
	 * 
	 * @param string $markerName
	 * @param string $content
	 * @return void
	 */
	public function setMarker($markerName, $content) {
		$this->cache[$markerName] = $content;
	}

	/**
	 * Flushes the internal cache
	 *
	 * @return void
	 */
	public function flush() {
		$this->cache = array();
	}

	/**
	 * Builds the correct getter method name for the given markerName
	 * 
	 * @param string $markerName
	 * @return string The method name
	 */
	protected function buildMarkerFunction($markerName) {
		return $funcFromMarker = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($markerName)))) . 'Marker';
	}
}
?>