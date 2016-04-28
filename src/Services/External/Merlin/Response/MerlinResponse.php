<?php

namespace OMT\Services\External\Merlin\Response;

// Use implemented interfaces and base classes
use \OMT\Services\External\Base\Response\BaseResponse;

// Use external class dependencies
use \OMT\External\Contract\IXMLModule;

/**
 * Represents a response from Merlin web service.
 *
 * @author Mateusz Tokarski
 * @created Apr 9, 2016
 */
class MerlinResponse extends BaseResponse {

	/**
	 * XML serialization module.
	 *
	 * @var XMLModule
	 */
	protected $xml_module;

	/**
	 * Creates Merlin web service response.
	 *
	 * @param XMLModule $xml_module XML serialization module.
	 */
	public function __construct(IXMLModule $xml_module) {
		// Append deserializers to XML module for response processing
		$this->xml_module = $xml_module;
	}

	public function getResponse($serialized) {
		// Deserialize serialized XML response
		$deserializers = $this->getDeserializers();
		$result = $this->xml_module->deserialize($serialized, $deserializers);

		return $result;
	}

	/**
	 * Returns an array of callable deserializer functions.
	 *
	 * @return array Array of XML element names as keys and deserialzier functions
	 * as values.
	 */
	protected function getDeserializers() {
		// Base request has no deserializers
		return array();
	}

}
