<?php

namespace OMT\External\Contract;

// Use external class dependencies
use \Psr\Log\LoggerInterface;

/**
 * Defines logging module functionality.
 *
 * @author Mateusz Tokarski
 * @creaed Apr 16, 2016
 */
interface ILoggingModule {

	/**
	 * Returns a logger with given name from configuration.
	 *
	 * If logger with given name is not defined in the configuration,
	 * a new instance with default values will be created.
	 *
	 * @param string $name Logger name.
	 * @return LoggerInterface Logger with given name.
	 */
	public function getLogger($name);

}
