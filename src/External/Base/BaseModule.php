<?php

namespace OMT\External\Base;

// Use external class dependencies
use \OMT\External\Contract\ILoggingModule;
use \Psr\Log\LoggerInterface;

/**
 * Base module with default logging behaviour.
 * 
 * @author Mateusz Tokarski
 * @created Sep 21, 2015
 */
abstract class BaseModule {

	/**
	 * Module logger instance.
	 *
	 * @var LoggerInterface
	 */
	protected $module_logger;

	/**
	 * Creates module with default logging behaviour.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 */
	public function __construct(ILoggingModule $logging_module) {
		// Get module logger from logging module
		$this->module_logger = $logging_module->getLogger($this->getModuleId());
	}

	/**
	 * Returns module id.
	 *
	 * @return string Module id.
	 */
	public abstract function getModuleId();

	/**
	 * Returns module logger instance.
	 *
	 * @return LoggerInterface Module logger instance.
	 */
	public function getModuleLogger() {
		return $this->module_logger;
	}

	/**
	 * Logs module exception.
	 *
	 * @param \Exception $exception Exception object.
	 * @param string $message General message,
	 * @param array $additional_context Context to be merged with exception context.
	 */
	protected function logException(\Exception $exception, $message, $additional_context) {
		$context = array_merge([
			'message' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		], $additional_context);
		$this->module_logger->critical($message, $context);
	}

}
