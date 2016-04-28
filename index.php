<?php

/**
 * This is a main index file.
 * 
 * It's responsible for loading OMT application facade object 
 * and running it. Widely used global variables SITE_PATH and SRC_PATH 
 * are also defined in this file, they point to application root directory and 
 * application source directory respectively.
 * 
 * Please note that autoloader is only registered in application's
 * constructor, so this file <b>must</b> load all additional 
 * requirements manually.
 *
 * This file can exit with an error when one or more of the files
 * required cannot be loaded properly.
 * 
 * @see OMT\Internal\Application/DefaultApplication Main application class.
 * @package Core
 * @author Mateusz Tokarski
 */

use \OMT\Internal\Application\DefaultApplication;

// Define application paths (those depend on this file location)
if (!defined('PATHS_LOADED')) {
	/**
	 * An absolute path to site's root directory.
	 *
	 * @var string
	 */
	define('SITE_PATH', realpath(dirname(__FILE__)));

	/**
	 * An absolute path to site's configuration files.
	 *
	 * @var string
	 */
	define('CONFIGURATION_PATH', SITE_PATH . '/config');

	/**
	 * An absolute path to site's source files.
	 *
	 * @var string
	 */
	define('SRC_PATH', SITE_PATH . '/src');

	/**
	 * An absolute path to site's vendor files.
	 *
	 * @var string
	 */
	define('VENDOR_PATH', SITE_PATH . '/vendor');

	/**
	 * Indicates that site paths has been loaded.
	 * 
	 * @var boolean
	 */
	define('PATHS_LOADED', true);
}

// Define prerequisite files
$required = [
	VENDOR_PATH . '/autoload.php',
	CONFIGURATION_PATH . '/logging.php',
	CONFIGURATION_PATH . '/config.php',
	CONFIGURATION_PATH . '/dependencies.php',
	SRC_PATH . '/Internal/Base/BaseSingleton.php',
	SRC_PATH . '/Internal/Application/Base/BaseApplication.php',
	SRC_PATH . '/Internal/Application/DefaultApplication.php'
];

// Check whether prerequisite files exist and are readable
foreach ($required as $filename) {
	if (file_exists($filename) && is_readable($filename)) {
		require($filename);
	} else {
		exit("Fatal initialization error occured!");
	}
}

// Run application
$app = DefaultApplication::get();
$app->run();
