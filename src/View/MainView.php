<?php

namespace OMT\View;

// Use implemented interfaces and base classes
use \OMT\View\Base\BaseView;

/**
 * Represents view for application main page.
 *
 * @author Mateusz Tokarski
 * @created Apr 4, 2016
 */
class MainView extends BaseView {
	
	/**
	 * Template name for main page.
	 * 
	 * @var string
	 */
	const MAIN_TEMPLATE = 'main';

	public function getViewId() {
		return 'view_main';
	}

	public function getDefaultTemplate() {
		return self::MAIN_TEMPLATE;
	}

}
