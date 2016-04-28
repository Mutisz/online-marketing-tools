<?php

namespace OMT\Internal\Base;

/**
 * Base class for singleton implementations.
 *
 * @package Core\Base
 * @author Mateusz Tokarski
 * @created Dec 6, 2014
 */
abstract class BaseSingleton {

	/**
	 * Singleton instance.
	 *
	 * @var OMT\Internal\Base\BaseSingleton
	 */
	protected static $instance = array();

	/**
	 * Abstract constructor.
	 *
	 * This function is protected to prevent external initialization. If overriding class passes any
	 * parameters to this constructor, they will be ignored, since instance accessor ignores them.
	 */
	protected abstract function __construct();

	/**
	 * Returns cached singleton instance or creates new one.
	 * 
	 * @return OMT\Internal\Base\BaseSingleton Singleton implementation of
	 * child class being called.
	 */
	public static function get() {
		$instance = null;
		$called_class = get_called_class();
		if (isset(self::$instance[$called_class])) {
			// Get existing singleton instance
			$instance = self::$instance[$called_class];
		} else {
			// Create new instance and save it as singleton
			$instance = new $called_class;
			static::$instance[$called_class] = $instance;
		}

		return $instance;
	}

}
