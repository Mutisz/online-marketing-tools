<?php

namespace OMT\Internal\Router\Contract;

/**
 * 
 * @author Mateusz Tokarski
 * @created Apr 3, 2016
 */
final class Constants {

	/**
	 * Name of controller GET parameter.
	 * @var string
	 */
	const CONTROLLER_INDEX = 'controller';

	/**
	 * Name of action GET parameter.
	 * @var string
	 */
	const ACTION_INDEX = 'action';

	/**
	 * Controller namespace for fully qualified name.
	 * @var string
	 */
	const CONTROLLER_CLASS_NAMESPACE = '\\OMT\\Controller\\';

	/**
	 * String appended to controller name.
	 * @var string
	 */
	const CONTROLLER_CLASS_SUFFIX = 'Controller';

	/**
	 * Controler used when no parameters are set.
	 * @var string
	 */
	const DEFAULT_CONTROLLER = 'Main';

	/**
	 * Action used when no parameters are set.
	 * @var string
	 */
	const DEFAULT_ACTION = 'showDefault';

}
