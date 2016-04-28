<?php

namespace OMT\External;

// Use implemented interfaces and base classes
use \OMT\External\Contract\ILoggingModule;

// Use external library class dependencies
use \Cascade\Cascade;


/**
 * Module wrapper for logging.
 *
 * This implementation uses Cascade, which is
 * a configurable extension of Monolog logger.
 *
 * @author Mateusz Tokarski
 * @created Apr 16, 2016
 */
class CascadeLoggingModule implements ILoggingModule {

	/**
	 * Creates Cascade logging module.
	 *
	 * @param string $file Path to logging configuration file.
	 * @Inject({"logging.file"})
	 */
	public function __construct($file) {
		// One time cascade configuration
		$config_file = require($file);
		Cascade::fileConfig($config_file);
	}

	public function getLogger($name) {
		return Cascade::getLogger($name);
	}

}
