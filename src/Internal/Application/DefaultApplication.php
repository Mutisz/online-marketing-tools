<?php

namespace OMT\Internal\Application;

// Use implemented interfaces and base classes
use \OMT\Internal\Application\Base\BaseApplication;

// Use internal class dependencies
use \OMT\Internal\Router\Contract\IRouter;

/**
 * {@inheritdoc}
 *
 * Run method will invoke <i>IRouter</I> implementation to analyze user input and act accordingly.
 * As an outcome an appropriate controller's method will be invoked.
 *
 * @see \Internal\Application\Base\BaseSingleton Singleton functionality abstract class.
 * @package Core
 * @author Mateusz Tokarski
 * @created Dec 6, 2014
 */
class DefaultApplication extends BaseApplication {

	public function getApplicationId() {
		return 'html_application';
	}

	/**
	 * {@inheritdoc}
	 *
	 * This is handled by instance of <i>Router</i> class, that invokes
	 * appropriate controller method.
	 */
	public function run() {
		$router = $this->container->get(IRouter::class);
		$router->route();
	}

}
