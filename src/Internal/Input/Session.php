<?php

namespace OMT\Internal\Input;

// Use implemented interfaces and base classes
use \OMT\Internal\Input\Base\BaseInput;

/**
 * Wrapper class for session variables. 
 * 
 * It caches session variables to be used across application. 
 * Values contained in this class can be filtered automatically
 * and shouldn't require additional processing.
 *
 * This class is a singleton implementation and can have only one instance which
 * must be instantiated from <i>get</i> method.
 *
 * @package Core
 * @author Mateusz Tokarski
 * @created Dec 13, 2014
 */
class Session extends BaseInput {

	/**
	 * Copy of session array after filtering.
	 *
	 * @var array
	 */
	protected $session = [];

	/**
	 * Fetches session variable with given name.
	 *
	 * If variable with given name doesn't exist, false is returned.
	 * This operates solely on this classes' session cache and actual session
	 * is not accessed.
	 *
	 * @param string|boolean $name Input variable name, false
	 * if no such variable is set.
	 */
	public function fetch($name) {
		// Return for value in cached session
		$result = false;
		if (isset($this->session[$name])) {
			$result = $this->session[$name];
		}

		return $result;
	}

	/**
	 * Stores the value of specific session variable.
	 *
	 * This operates solely on this classes' session cache and actual session
	 * is not accessed. To save session afterwards <i>commit</i> must be invoked.
	 *
	 * @param string $name Input variable name.
	 * @param mixed $value Value to save to this variable.
	 * @param boolean $overwrite If true variables with
	 * the same name will be overwritten, otherwise they will not be touched.
	 * Defaults to true.
	 */
	public function store($name, $value, $overwrite = true) {
		if ($overwrite || !isset($this->session[$name])) {
			$this->session[$name] = $value;
		}
	}

	/**
	 * Checks if variable with given name exists in session.
	 *
	 * This operates solely on this classes' session cache and actual session
	 * is not accessed.
	 *
	 * @param string $name Input variable name.
	 * @return boolean True if variable with given name exists,
	 * false otherwise.
	 */
	public function has($name) {
		return isset($this->session[$name]);
	}

	/**
	 * Starts session if it is not started yet.
	 * 
	 * @return boolean Current session status.
	 */
	public function start() {
		// Start session if it's not started already
		$is_active = true;
		if (!$this->status()) {
			$is_active &= session_start();
		}

		// Check if session activated properly
		$is_active &= $this->status();
		return $is_active;
	}

	/**
	 * Destroys session.
	 *
	 * This does not clear cached values from this class.
	 */
	public function destroy() {
		// Stop session if it's not stopped already
		if ($this->status()) {
			session_abort();
		}

		// Check if session stopped properly
		$is_stopped = !$this->status();
		return $is_stopped;
	}

	/**
	 * Saves data from cache array to actual session.
	 *
	 * Session must be started first. After commiting changes
	 * session will close.
	 *
	 * @return boolean True if changes commited successfully,
	 * false otherwise.
	 */
	public function commit() {
		// Save temporary values to actual session
		foreach ($this->session as $name => $value) {
			$_SESSION[$name] = $value;
		}

		// Commit and close session
		$is_commited = false;
		if ($this->status()) {
			$is_commited = session_commit();
		}

		return $is_commited;
	}
	
	/**
	 * Checks if session is active.
	 * 
	 * @return boolean True if session is active, false otherwise.
	 */
	public function status() {
		$is_started = session_status() == PHP_SESSION_ACTIVE;
		return $is_started;
	}

	/**
	 * Clears cached session values.
	 */
	protected function clear() {
		// Clear session cache and destroy session
		$this->session = [];
		$this->destroy();
	}

	/**
	 * Copies values from session to this class.
	 */
	protected function fill() {
		// Fill session cache if session started successfully
		if ($this->start()) {
			foreach (filter_input_array(INPUT_SESSION, $this->filter) as $name => $value) {
				$this->session[$name] = $value;
			}
		}
	}

}
