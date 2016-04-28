<?php

namespace OMT\Internal\Storage;

/**
 * Storage class for holding application errors.
 *
 * @author Mateusz Tokarski
 * @created Apr 17, 2016
 */
final class ApplicationErrors {

	/**
	 * Array of error messages registered.
	 *
	 * @var string[]
	 */
	private static $errors = [];

	/**
	 * Returns an array of registered errro messages.
	 *
	 * @param boolean $for_html If true new lines in errors will be converted for HTML.
	 * @return string[] Array of registered errro messages.
	 */
	public static function getErrors($for_html = false) {
		$errors = self::$errors;
		if ($for_html) {
			array_walk($errors, function(&$error) {
				nl2br($error);
			});
		}

		return $errors;
	}

	/**
	 * Registers an error message.
	 *
	 * @param string $error Error message.
	 */
	public static function addError($error) {
		self::$errors[] = $error;
	}

	/**
	 * Displays all error messages and cleans the storage.
	 */
	public static function flush() {
		// Display plaintext errors
		foreach (self::$errors as $error) {
			echo("$error");
		}

		//Clear errors array
		self::$errors = [];
	}

}
