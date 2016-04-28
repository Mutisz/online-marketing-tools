<?php

namespace OMT\Internal\Router;

// Use implemented interfaces and base classes
use \OMT\Internal\Router\Contract\IRouter;

// Use internal class dependencies
use \OMT\Internal\Router\Contract\Constants;
use \OMT\Internal\Input\Parameters;
use \OMT\Internal\Utility\ContainerAccess;

/**
 * 
 *
 * @author Mateusz Tokarski
 */
class DefaultRouter implements IRouter {
	use ContainerAccess;

	/**
	 * Name of class containing  action to be executed.
	 * @var string
	 */
	protected $controller_class;

	/**
	 * Name of method to be executed.
	 * @var string
	 */
	protected $controller_action;

	/**
	 * Router constructor.
	 *
	 * Class, action and/or service fields are all set based on user input
	 * via GET and POST parameters.
	 */
	public function __construct() {
		$this->setFromParameters();
	}

	public function route() {
		if ($this->validateController()) {
			$controller = $this->getFromContainer($this->controller_class);
			call_user_func(array($controller, $this->controller_action));
		}
	}

	/**
	 * Sets the correct fields (group, controller and action) from URL and
	 * assigns it to this router instance.
	 */
	protected function setFromParameters() {
		// Get application parameters
		$params = Parameters::get();

		if ($params->has(Constants::CONTROLLER_INDEX) && $params->has(Constants::ACTION_INDEX)) {
			// Assign controller and action from parameter
			$this->setController(
				$params->fetch(Constants::CONTROLLER_INDEX),
				$params->fetch(Constants::ACTION_INDEX)
			);
		} else {
			// Use default controller with default action
			$this->setController(
				Constants::DEFAULT_CONTROLLER,
				Constants::DEFAULT_ACTION
			);
		}
	}

	protected function setController($class, $action) {
		// Set fully qualified controller class
		$this->controller_class = Constants::CONTROLLER_CLASS_NAMESPACE . $class . Constants::CONTROLLER_CLASS_SUFFIX;
		$this->controller_action = $action;
	}

	protected function validateController() {
		$is_valid = isset($this->controller_class);
		$is_valid = $is_valid && is_subclass_of($this->controller_class, \OMT\Controller\Base\BaseController::class, TRUE);
		$is_valid = $is_valid && method_exists($this->controller_class, $this->controller_action);

		return $is_valid;
	}

}
