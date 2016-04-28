<?php

namespace OMT\Internal;

// Use internal class dependencies
use \OMT\Internal\Storage\ApplicationErrors;

// Use external class dependencies
use \OMT\External\Contract\ILoggingModule;
use \Psr\Log\LoggerInterface;

/**
 * Class defining application error handling routines.
 *
 * @author Mateusz Tokarski
 * @created 26-Oct-2014
 */
class InternalErrorHandler {

	/**
	 * Module providing logging functionality.
	 *
	 * @var LoggerInterface
	 */
	protected $internal_error_logger;

	/**
	 * Flag indicating whether or not caught errors should be displayed.
	 *
	 * @var boolean
	 */
	protected $show_errors;

	/**
	 * Creates internal error handler.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 * @param boolean $show_errors Flag indicating whether or not caught errors should be displayed.
	 * @Inject({"show_errors" = "logging.show_errors"})
	 */
	public function __construct(ILoggingModule $logging_module, $show_errors) {
		$this->internal_error_logger = $logging_module->getLogger('application');
		$this->show_errors = $show_errors;
	}

	/**
	 * Registers error handling routines.
	 */
	public function register() {
		set_exception_handler([$this, 'handleException']);
		set_error_handler([$this, 'handleError']);
		register_shutdown_function([$this, 'handleFatalError']);
	}

	/**
	 * Handles uncaught exception.
	 *
	 * Execution is halted afterwards.
	 *
	 * @param \Exception $exception Uncaught exception.
	 */
	public function handleException(\Exception $exception) {
		// Log error
		$user_message = 'Uncaught exception occured';
		$this->internal_error_logger->critical($user_message, [
			'message' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		]);

		// Format error for display
		if ($this->show_errors) {
			$exception_message = $exception->getMessage();
			$user_message .= "\nException message:\n$exception_message";
		}

		// Add error to display storage
		ApplicationErrors::addError($user_message);

		// Flush stored errors
		ApplicationErrors::flush();
		exit();
	}

	/**
	 * Handles PHP not fatal errors and user triggered errors.
	 *
	 * @param int $type Error type.
	 * @param string $message Error message.
	 * @param string $file Error file.
	 * @param int $line Error line.
	 * @return boolean True if error was handled correctly, false otherwise.
	 */
	public function handleError($type, $message, $file, $line) {
		// Log error
		$user_message = 'Application error occured';
		$error = compact('type', 'message', 'file', 'line');
		$this->internal_error_logger->error($user_message, $error);

		// Format error for display
		if ($this->show_errors) {
			$encoded_error = \json_encode($error);
			$user_message .= "\nJSON encoded error values:\n$encoded_error";
		}

		// Add error to display storage
		ApplicationErrors::addError($user_message);

		return true;
	}

	/**
	 * Handles PHP fatal errors.
	 *
	 * Execution is halted afterwards.
	 */
	public function handleFatalError() {
		$error = error_get_last();
		if ($error) {
			// Log error
			$user_message = 'Application fatal error occured';
			$this->internal_error_logger->critical($user_message, $error);

			// Flush stored errors and die
			ApplicationErrors::flush();
			exit();
		}
	}

}
