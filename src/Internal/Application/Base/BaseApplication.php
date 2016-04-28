<?php

namespace OMT\Internal\Application\Base;

// Use implemented interfaces and base classes
use \OMT\Internal\Base\BaseSingleton;

// Use external class dependencies
use \Interop\Container\ContainerInterface;
use \Doctrine\Common\Cache\ApcuCache;
use \DI\ContainerBuilder;

/**
 * Class representing application facade.
 *
 * It serves as initialization object and provides a method used to run entire application.
 *
 * Summary list of application responsibilities:
 * <ul>
 * <li>Initializes PHP callback functions and globals (kept to a minimum);</li>
 * <li>Initializes logging routine;</li>
 * <li>Initializes dependency injection container.</li>
 * </ul>
 *
 * Effects of invoking <i>run</i> method are implementation specific.
 *
 * This class is a singleton implementation and can have only one instance which
 * must be instantiated from <i>get</i> method.
 *
 * @see \Internal\Base\BaseSingleton Singleton functionality abstract class.
 * @package Core
 * @author Mateusz Tokarski
 * @created Apr 3, 2014
 */
abstract class BaseApplication extends BaseSingleton {

	/**
	 * Application DI container.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Custom autoloader implementation.
	 *
	 * @var \Internal\InternalAutoloader
	 */
	protected $autoloader;

	/**
	 * Custom error handler implementation.
	 *
	 * @var \Internal\InternalErrorHandler
	 */
	protected $error_handler;

	/**
	 * Initializes application.
	 *
	 * Performs required initializations for new application instance, those include (in order of execution):
	 * <ol>
	 * <li>Global state (kept to a minimum);</li>
	 * <li>Dependency injection container.</li>
	 * <li>Custom autoloader following PSR-4 standard;</li>
	 * <li>Custom error and exception handler;</li>
	 * </ol>
	 */
	protected function __construct() {
		// Define globals (for path variables)
		$this->initializeGlobalPaths();
		$this->initializeGlobalClasses();
		$this->initializeGlobalObjectNames();

		// Initialize DI container
		$this->initializeContainer();

		// Initializers for special callback functions
		$this->initializeAutoloader();
		$this->initializeErrorHandler();
	}

	public static function get() {
		$result = null;
		if (defined('PATHS_LOADED')) {
			$result = parent::get();
		} else {
			// This is entry point, so no pretty error handling is set up
			exit("Fatal initialization error occured!");
		}

		return $result;
	}

	/**
	 * Returns application type identification.
	 */
	public abstract function getApplicationId();

	/**
	 * Runs the initialized application.
	 */
	public abstract function run();

	/**
	 * Initializes global state paths.
	 */
	protected function initializeGlobalPaths() {
		if (!defined('GLOBAL_PATHS_LOADED')) {
			define('EXTERNAL_PATH', SRC_PATH . '/External');
			define('INTERNAL_PATH', SRC_PATH . '/Internal');
			define('MODEL_PATH', SRC_PATH . '/Model');
			define('VIEW_PATH', SRC_PATH . '/View');
			define('CONTROLLER_PATH', SRC_PATH . '/Controller');
			define('GLOBAL_PATHS_LOADED', true);
		}
	}

	/**
	 * Initializes global state variables and functions.
	 */
	protected function initializeGlobalClasses() {
		if (!defined('GLOBAL_CLASSES_LOADED')) {
			define('AUTOLOADER_NAMESPACE', '\\OMT\\Internal\\');
			define('AUTOLOADER_CLASS', 'InternalAutoloader');
			define('AUTOLOADER_PATH', INTERNAL_PATH . '/' . AUTOLOADER_CLASS . '.php');
			define('ERROR_HANDLER_NAMESPACE', '\\OMT\\Internal\\');
			define('ERROR_HANDLER_CLASS', 'InternalErrorHandler');
			define('ERROR_HANDLER_PATH', INTERNAL_PATH . '/' . ERROR_HANDLER_CLASS . '.php');
			define('GLOBAL_CLASSES_LOADED', true);
		}
	}

	/**
	 * Initializes global state object names.
	 *
	 * Objects with these names will be accessible from globals
	 * array.
	 */
	protected function initializeGlobalObjectNames() {
		if (!defined('GLOBAL_OBJECT_NAMES_LOADED')) {
			define('DI_CONTAINER_OBJECT', 'di_container');
			define('AUTOLOADER_OBJECT', 'autoloader');
			define('ERROR_HANDLER_OBJECT', 'error_handler');
			define('GLOBAL_OBJECT_NAMES_LOADED', true);
		}
	}

	/**
	 * Configures DI container used by application.
	 *
	 * This container is stored in global constant <i>DI_CONTAINER</i>
	 * and can be accessed accross the application.
	 */
	protected function initializeContainer() {
		// Create cache
		$cache = new ApcuCache();
		$cache->setNamespace($this->getApplicationId());

		// Create container
		$builder = new ContainerBuilder();
		$builder->useAnnotations(true);
		$builder->setDefinitionCache($cache);
		$builder->writeProxiesToFile(true, SITE_PATH . '/var/proxies');
		$builder->addDefinitions(CONFIGURATION_PATH . '/config.php');
		$builder->addDefinitions(CONFIGURATION_PATH . '/dependencies.php');

		// Save container to be used in application and globally
		$this->container = $GLOBALS[DI_CONTAINER_OBJECT] = $builder->build();
	}

	/**
	 * Registers instance of custom autoloader.
	 *
	 * It's important to note, that this function should be invoked
	 * at the beginning of initialization, otherwise classes won't be loaded.
	 * The autoloader implementation itself is required manually inside
	 * of this method.
	 *
	 * @see \Internal\InternalAutoloader Current custom autoloader
	 * implementation, it follows PSR-4 convention.
	 */
	protected function initializeAutoloader() {
		// Require file with autoloader class implementation
		require(AUTOLOADER_PATH);

		// Create and register new autoloader
		$autoloader_class = AUTOLOADER_NAMESPACE . AUTOLOADER_CLASS;
		$autoloader = $this->container->get($autoloader_class);
		$autoloader->register();

		// Add namespaces that will be always required
		$autoloader->addNamespace('OMT', SRC_PATH);

		// Save autoloader instance
		$this->autoloader = $GLOBALS[AUTOLOADER_OBJECT] = $autoloader;
	}

	/**
	 * Registers instance of custom error handler.
	 *
	 * @see \Internal\InternalErrorHandler Current custom error handler, it
	 * serves only as a mechanism for formatting errors for display.
	 */
	protected function initializeErrorHandler() {
		// Create and register new error handler
		$error_handler_class = ERROR_HANDLER_NAMESPACE . ERROR_HANDLER_CLASS;
		$error_handler = $this->container->get($error_handler_class);
		$error_handler->register();

		// Save error handler instance
		$this->error_handler = $GLOBALS[ERROR_HANDLER_OBJECT] = $error_handler;
	}

}
