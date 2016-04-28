<?php

namespace OMT\Internal\Input;

// Use implemented interfaces and base classes
use \OMT\Internal\Input\Base\BaseInput;

/**
 * Wrapper class for application input parameters.
 *
 * Parameters are read-only but their values can be refreshed if needed. 
 * Values contained in this class can be filtered automatically
 * and shouldn't require additional processing.
 *
 * This class is a singleton implementation and can have only one instance which
 * must be instantiated from <i>get</i> method.
 *
 * @package Core
 * @author Mateusz Tokarski
 * @created Dec 6, 2014
 */
class Parameters extends BaseInput {

	/**
	 * Copy of GET array after filtering.
	 *
	 * @var array
	 */
	protected $get = [];

	/**
	 * Copy of POST array after filtering.
	 *
	 * @var array
	 */
	protected $post = [];

	/**
	 * Fetches GET or POST with given name.
	 *
	 * If variable with given name doesn't exist, false is returned.
	 *
	 * @param string|boolean $name Input variable name, false
	 * if no such variable is set.
	 */
	public function fetch($name) {
		// Merge GET ad POST arrays
		$search_array = array_merge($this->get, $this->post);

		// Search for value in merged arrays
		$result = false;
		if (isset($search_array[$name])) {
			$result = $search_array[$name];
		}

		// Return search result
		return $result;
	}

	/**
	 * Checks if variable with given name exists in GET or POST.
	 *
	 * @param string $name Input variable name.
	 * @return boolean True if variable with given name exists,
	 * false otherwise.
	 */
	public function has($name) {
		// Merge GET ad POST arrays
		$search_array = array_merge($this->get, $this->post);
		return isset($search_array[$name]);
	}

	/**
	 * Checks if variable with given name exists in GET.
	 *
	 * @param string $name Input variable name.
	 * @return boolean True if variable with given name exists,
	 * false otherwise.
	 */
	public function hasGet($name) {
		return isset($this->get[$name]);
	}

	/**
	 * Returns array of parameters sent by in website URL.
	 *
	 * @return array Values from GET array.
	 */
	public function getGet() {
		return $this->get;
	}

	/**
	 * Checks if variable with given name exists in POST.
	 *
	 * @param string $name Input variable name.
	 * @return boolean True if variable with given name exists,
	 * false otherwise.
	 */
	public function hasPost($name) {
		return isset($this->post[$name]);
	}

	/**
	 * Returns array of parameters sent in website header.
	 *
	 * @return array Values from POST array.
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * Clears cached POST and GET values.
	 */
	protected function clear() {
		// Reset the arrays with parameters
		$this->get = [];
		$this->post = [];
	}

	/**
	 * Copies values from GET and POST arrays to this class.
	 */
	protected function fill() {
		// Fill GET values
		$get = filter_input_array(INPUT_GET, $this->filter) ?: array();
		foreach ($get as $index => $value) {
			$this->get[$index] = $value;
		}

		// Fill POST values
		$post = filter_input_array(INPUT_POST, $this->filter) ?: array();
		foreach ($post as $index => $value) {
			$this->post[$index] = $value;
		}
	}

}
