<?php

namespace OMT\Controller;

// Use implemented interfaces and base classes
use \OMT\Controller\Base\BaseController;

/**
 * Controller for main application page.
 * 
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */
class MainController extends BaseController {

	public function getControllerId() {
		return 'controller_main';
	}

	public function showDefault() {
		$this->view->display();
	}

}
