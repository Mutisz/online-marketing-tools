<?php

// Use internal file dependencies
use \OMT\Internal\Router\Contract\IRouter;
use \OMT\Internal\Router\DefaultRouter;

// Use external module file dependencies
use \OMT\External\Contract\ILoggingModule;
use \OMT\External\CascadeLoggingModule;
use \OMT\External\GuzzleHTTPModule;
use \OMT\External\Contract\ITemplateModule;
use \OMT\External\TwigFileTemplateModule;
use \OMT\External\Contract\IXMLModule;
use \OMT\External\SabreXMLModule;

// Use external library file dependencies
use \Interop\Container\ContainerInterface;
use \DI\Factory\RequestedEntry;
use \GuzzleHttp\Client;

/**
 * Defines dependencies in applciation.
 * 
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */

$external = [
	\Twig_LoaderInterface::class => function(ContainerInterface $container) {
		return new Twig_Loader_Filesystem($container->get('templates.path'));
	},
	\Twig_Environment::class => function(ContainerInterface $container, \Twig_LoaderInterface $loader) {
		return new Twig_Environment($loader, array(
			'cache' => $container->get('templates.cache'),
			'debug' => $container->get('developer.god_mode')
		));
	}
];

$modules = [
	ILoggingModule::class => DI\object(CascadeLoggingModule::class),
	ITemplateModule::class => DI\object(TwigFileTemplateModule::class),
	IXMLModule::class => DI\object(SabreXMLModule::class),
	"*.http_module" => function(ContainerInterface $container, RequestedEntry $requested) {
		// Get logging module
		$logging_module = $container->get(ILoggingModule::class);

		// Get module id to extract correct options
		$requested_parts = explode('.', $requested->getName());
		$http_options = $container->get(reset($requested_parts) . '.http_options');

		// Get HTTP configured client
		$http_client = new Client($http_options);

		return new GuzzleHTTPModule($logging_module, $http_client);
	}
];

$internal = [
	IRouter::class => DI\object(DefaultRouter::class),
	'OMT\Internal\Application\*Application' => function(RequestedEntry $requested) {
		$class_name = $requested->getName();
		$application = $class_name::get();

		return $application;
	},
	'OMT\Controller\*Controller' => function(ContainerInterface $container, RequestedEntry $requested) {
		// Get logging module
		$logging_module = $container->get(ILoggingModule::class);

		// Get class type
		$class_name = $requested->getName();
		$class_name_parts = explode('\\', $class_name);
		$class_type = str_replace('Controller', '', end($class_name_parts));
		
		// Create view class
		$view_class_name = "OMT\\View\\{$class_type}View";
		$view = $container->has($view_class_name) ? $container->get($view_class_name) : null;

		// Create model class
		$model_class_name = "OMT\\Model\\{$class_type}Model";
		$model = $container->has($model_class_name) ? $container->get($model_class_name) : null;

		return new $class_name($logging_module, $view, $model);
	},
];

return array_merge($external, $modules, $internal);
