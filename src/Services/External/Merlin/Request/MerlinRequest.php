<?php

namespace OMT\Services\External\Merlin\Request;

// Use implemented interfaces and base classes
use \OMT\Services\External\Base\Request\BaseRequest;

// Use external class dependencies
use \OMT\External\Contract\IXMLModule;

/**
 * Represents a request to Merlin web service.
 *
 * @author Mateusz Tokarski
 * @created Apr 5, 2016
 */
class MerlinRequest extends BaseRequest {

	/**
	 * XML serialization module.
	 *
	 * @var XMLModule
	 */
	protected $xml_module;

	/**
	 * Authorization data array.
	 *
	 * @var string[]
	 */
	protected $authorization;

	/**
	 * Creates Merlin web service request.
	 *
	 * @param XMLModule $xml_module XML serialization module.
	 * @param string $login Merlin services login.
	 * @param string $password Merlin services password.
	 * @Inject({"login" = "merlin.login", "password" = "merlin.password"})
	 */
	public function __construct(IXMLModule $xml_module, $login, $password) {
		$this->xml_module = $xml_module;
		$this->authorization = [
			'login' => $login,
			'pass' => $password
		];
	}

	public function getRequest($decoded) {
		$request = [
			'auth' => $this->authorization,
			'request' => $decoded
		];

		// Return XML serialized data
		return $this->xml_module->serialize($request, 'mst');
	}

}
