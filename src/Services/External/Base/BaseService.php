<?php

namespace OMT\Services\External\Base;

// Use internal class dependencies
use \OMT\Internal\Utility\ContainerAccess;

// Use external class dependencies
use \OMT\External\Contract\IHTTPModule;

/**
 * Class representing an external webservice.
 *
 * This is a webservice consumer.
 *
 * @author Mateusz Tokarski
 * @created Apr 4, 2016
 */
abstract class BaseService {
	use ContainerAccess;

	/**
	 * Module for creating HTTP requests to called service.
	 *
	 * @var HTTPModule
	 */
	protected $http_module;

	/**
	 * Creates external webservice proxy.
	 *
	 * @param HTTPModule $http_module Module for creating HTTP requests to called service.
	 */
	public function __construct(IHTTPModule $http_module) {
		$this->http_module = $http_module;
	}

}
