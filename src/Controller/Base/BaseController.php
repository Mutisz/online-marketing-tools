<?php

namespace OMT\Controller\Base;

// Use internal class dependencies
use \OMT\View\Base\BaseView;
use \OMT\Model\Base\BaseModel;

// Use external class dependencies
use \OMT\External\Contract\ILoggingModule;
use \Psr\Log\LoggerInterface;

/**
 * Represents base class for application controllers.
 *
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */
abstract class BaseController {

	/**
	 * Controller logger instance.
	 *
	 * @var LoggerInterface
	 */
	protected $controller_logger;

	/**
	 * View object for displaying templates.
	 *
	 * This can be null if there is no injectable view
	 * for called controller.
	 *
	 * @var BaseView
	 */
	protected $view;

	/**
	 * Model object for manipulating and extracting data.
	 *
	 * This can be null if there is no injectable model
	 * for called controller.
	 *
	 * @var Base Model
	 */
	protected $model;

	/**
	 * Creates an instance of called controller.
	 *
	 * @param BaseView $view View object for displaying templates.
	 * @param BaseModel $model Model object for manipulating and extracting data.
	 */
	public function __construct(ILoggingModule $logging_module, BaseView $view = null, BaseModel $model = null) {
		$this->controller_logger = $logging_module->getLogger($this->getControllerId());
		$this->view = $view;
		$this->model = $model;
	}

	/**
	 * Returns controller id.
	 *
	 * @return string Controller id.
	 */
	public abstract function getControllerId();

	/**
	 * Shows default page for this controller.
	 */
	public abstract function showDefault();

	public function showError() {
		$this->view->setTemplate('error');
		$this->view->display();
	}

}
