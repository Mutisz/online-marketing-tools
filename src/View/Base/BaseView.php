<?php

namespace OMT\View\Base;

// Use internal class dependencise
use \OMT\Internal\Storage\ApplicationErrors;

// Use external class dependencies
use \OMT\External\Contract\ILoggingModule;
use \OMT\External\Contract\ITemplateModule;
use \Psr\Log\LoggerInterface;

/**
 * Represents base class for all view objects.
 *
 * Views act as a proxy between controller and templating engine.
 *
 * @author Mateusz Tokarski
 * @created Apr 4, 2016
 */
abstract class BaseView {

	/**
	 * View logger instance.
	 *
	 * @var LoggerInterface
	 */
	protected $view_logger;

	/**
	 * Templating engine module.
	 *
	 * @var TemplateModule
	 */
	protected $template_module;
	
	/**
	 * Template name if set.
	 *
	 * @var string|boolean
	 */
	protected $template = false;

	/**
	 * Template variables.
	 *
	 * @var array
	 */
	protected $variables = array();

	/**
	 * Creates an instance of called view.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 * @param ITemplateModule $template_module Templating engine module.
	 */
	public function __construct(ILoggingModule $logging_module, ITemplateModule $template_module) {
		$this->module_logger = $logging_module->getLogger($this->getViewId());
		$this->template_module = $template_module;
	}

	/**
	 * Returns view id.
	 *
	 * @return string View id.
	 */
	public abstract function getViewId();

	/**
	 * Returns default template name used by called view.
	 *
	 * @return string Default template name.
	 */
	public abstract function getDefaultTemplate();

	/**
	 * Returns a template variable with given name.
	 *
	 * @param string $name Variable name.
	 * @return mixed Value of variable with given name or null if variable is not set.
	 */
	public function get($name) {
		$result = null;
		if (isset($this->variables[$name])) {
			$result = $this->variables[$name];
		} else {
			// Accessing unset variable should not happen
			$message = 'Accessing undefined view variable';
			$this->view_logger->warning($message, [
				'variable' => $name
			]);
		}

		return $result;
	}

	/**
	 * Sets a variable with given name.
	 *
	 * @param string $name Variable name.
	 * @param mixed $value Variable value.
	 */
	public function set($name, $value) {
		$this->variables[$name] = $value;
	}

	/**
	 * Returns template name.
	 *
	 * @return string|boolean Template name or false if no template was set.
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Sets template name wityh or without extension.
	 *
	 * @param string $template Template name.
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}

	/**
	 * Outputs template from templating module.
	 *
	 * If no template name was set, a default template will be displayed.
	 */
	public function display() {
		if (!$this->getTemplate()) {
			// Use default template if none was set externally
			$default_template = $this->getDefaultTemplate();
			$this->setTemplate($default_template);
		}

		// Add potential errors to be displayed within template
		$errors = ApplicationErrors::getErrors(true);
		$this->set('errors', $errors);

		// Display template
		$this->template_module->display(
			$this->template,
			$this->variables
		);
	}

}
