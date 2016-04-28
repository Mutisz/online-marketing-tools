<?php

namespace OMT\External;

// Use implemented interfaces and base classes
use \OMT\External\Base\BaseModule;
use \OMT\External\Contract\ITemplateModule;

// Use external module class dependencies
use \OMT\External\Contract\ILoggingModule;

/**
 * Module wrapper for templating engine.
 *
 * This implementation uses Twig engine with
 * filesystem template loader.
 *
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */
class TwigFileTemplateModule extends BaseModule implements ITemplateModule {

	/**
	 * Extension each template must have.
	 *
	 * @var string
	 */
	const TEMPLATE_EXTENSION = '.twig';

	/**
	 * Twig environment instance.
	 *
	 * @var \Twig_Environment
	 */
	protected $environment;

	/**
	 * Creates Twig based templating module.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 * @param \Twig_Environment $environment Twig environment instance.
	 */
	public function __construct(ILoggingModule $logging_module, \Twig_Environment $environment) {
		parent::__construct($logging_module);
		$this->environment = $environment;
	}

	public function getModuleId() {
		return 'module_template';
	}

	public function display($template, $variables) {
		try {
			$this->appendExtension($template);
			$this->environment->display($template, $variables);
		} catch (\Exception $exception) {
			$message = 'Failed to display template';
			$this->logException($exception, $message, [
				'template' => $template,
				'variables' => \json_encode($variables)
			]);
		}
	}

	/**
	 * Appends extension to pased template name.
	 *
	 * @param string $template Template name.
	 */
	protected function appendExtension(&$template) {
		if (strpos($template, self::TEMPLATE_EXTENSION) === FALSE) {
			// Append extension to template name
			$template .= self::TEMPLATE_EXTENSION;
		}
	}

}
