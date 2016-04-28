<?php

namespace OMT\Internal\Input\Base;

// Use implemented interfaces and base classes
use \OMT\Internal\Base\BaseSingleton;

/**
 * Base class for PHP input that can be filtered.
 *
 * This class is a singleton implementation and can have only one instance which
 * must be instantiated from <i>get</i> method.
 *
 * @package Core
 * @author Mateusz Tokarski
 * @created Jul 15, 2015
 */
abstract class BaseInput extends BaseSingleton {

	/**
	 * Filter type.
	 *
	 * @var int|array
	 */
	protected $filter = FILTER_DEFAULT;

	/**
	 * Automatically fills input.
	 */
	protected function __construct() {
		$this->fill();
	}

	/**
	 * Fetches input with given name.
	 *
	 * @param string $name Input variable name.
	 */
	public abstract function fetch($name);

	/**
	 * Checks if input with given name exists.
	 *
	 * @param string $name Input variable name.
	 */
	public abstract function has($name);

	/**
	 * Refreshes input.
	 *
	 * If filter type was changed before invoking this method,
	 * it will be automatically applied to new input values.
	 */
	public function refresh() {
		$this->clear();
		$this->fill();
	}

	/**
	 * Returns currently used filter.
	 *
	 * @return int|array Filter type to use for all variables or
	 * array of variables with filters to use for them.
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * Sets new filter.
	 *
	 * After setting a new filter, input falues will be refreshed.
	 * The new input will be used in further actions until its
	 * changed again via this method.
	 *
	 * @param int|array $filter Filter type to use for all variables or
	 * array of variables with filters to use for them.
	 */
	public function setFilter($filter) {
		// Save new filter and refresh input if valid
		if($this->validateFilter($filter)) {
			$this->filter = $filter;
			$this->refresh();
		}
	}

	/**
	 * Clears input variables.
	 */
	protected abstract function clear();

	/**
	 * Fills input variables.
	 */
	protected abstract function fill();

	/**
	 * Checks if all filter variables are filtered with valid filter.
	 *
	 * @param int|array $filter Filter type to use for all variables or
	 * array of variables with filters to use for them.
	 * @return boolean True if filter is correct, false otherwise.
	 */
	private function validateFilter($filter) {
		$valid = true;
		$available_filter_types = filter_list();
		if (is_array($filter)) {
			// Check all filter types in array
			foreach ($filter as $variable => $filter_type) {
				$valid &= is_string($variable);
				$valid &= in_array($filter_type, $available_filter_types);
			}
		} else {
			$valid &= in_array($filter, $available_filter_types);
		}

		return $valid;
	}

}
