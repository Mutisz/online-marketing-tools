<?php

namespace OMT\Internal;

/**
 * An autoloader implementation based on PSR-4 coding standard example.
 *
 * It maps namespaces to corresponding directories, more than one directory can
 * be mapped to a single namespace.
 *
 * @link http://www.php-fig.org/psr/psr-4/ PSR-4 Coding Standard.
 * @package Core
 * @author Mateusz Tokarski
 */
class InternalAutoloader {

	/**
	 * An associative array where the key is a namespace prefix and the value
	 * is an array of base directories for classes in that namespace.
	 *
	 * @var array
	 */
	private $namespaces = [];

	/**
	 * Autoloader cache, will prevent looping through all the saved namespaces
	 * for the classes that are already loaded.
	 *
	 * @var array
	 */
	private $loaded = [];

	/**
	 * Register loader with SPL autoloader stack.
	 *
	 * @return boolean True if autoloader was registered successfully,
	 * false otherwise.
	 */
	public function register() {
		return spl_autoload_register([$this, 'loadClass']);
	}

	/**
	 * Adds a base directory for a given namespace, if it's not set already.
	 *
	 * @param string $namespace The namespace prefix.
	 * @param string $baseDir A base directory for class files in the
	 * namespace.
	 * @return void
	 */
	public function addNamespace($namespace, $directory) {
		// Normalize namespace and base directory
		$namespace = trim($namespace, '\\') . '\\';
		$directory = rtrim($directory, DIRECTORY_SEPARATOR) . '/';

		// Check if given namespace - directory pair exists
		if (!isset($this->namespaces[$namespace]) ||
			!in_array($directory, $this->namespaces[$namespace], true)) {
			// Initialize the namespace array if it doesn't exist
			if (!isset($this->namespaces[$namespace])) {
				$this->namespaces[$namespace] = [];
			}

			// Push new directory for given namespace prefix
			array_push($this->namespaces[$namespace], $directory);
		}
	}

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully qualified class name.
	 * @return boolean The mapped file name on success, false on failure.
	 */
	public function loadClass($class) {
		$result = false;
		if (!in_array($class, $this->loaded)) {
			$namespace = $class;
			while (false !== $pos = strrpos($namespace, '\\')) {
				// Get relative class and namespace
				$namespace = substr($class, 0, $pos + 1);
				$relative_class = substr($class, $pos + 1);

				// Try to load mapped file for this iteration
				if ($this->loadMappedFile($namespace, $relative_class)) {
					array_push($this->loaded, $class);
					$result = true;
					break;
				}

				// Remove the trailing namespace separator for the next iteration
				$namespace = rtrim($namespace, '\\');
			}
		}

		return $result;
	}

	/**
	 * Clears the loaded classes cache, so every new class will be searched for.
	 */
	public function clearLoaded() {
		$this->loaded = [];
	}

	/**
	 * Load the mapped file for a namespace prefix and relative class name.
	 *
	 * @param string $namespace The namespace prefix.
	 * @param string $relative_class The relative class name.
	 * @return boolean True if mapped file was loaded successfully,
	 * false otherwise
	 */
	private function loadMappedFile($namespace, $relative_class) {
		$result = false;
		if (isset($this->namespaces[$namespace])) {
			foreach ($this->namespaces[$namespace] as $directory) {
				// Replace namespace with saved directory
				$file = $directory . str_replace('\\', '/', $relative_class) . '.php';

				// Require the resulting file name
				if ($this->requireFile($file)) {
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The name of the file to require.
	 * @return boolean True if the file exists, false if not.
	 */
	private function requireFile($file) {
		$result = false;
		if (file_exists($file)) {
			require($file);
			$result = true;
		}

		return $result;
	}

}
