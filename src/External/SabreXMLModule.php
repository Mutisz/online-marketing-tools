<?php

namespace OMT\External;

// Use implemented interfaces and base classes
use \OMT\External\Base\BaseModule;
use \OMT\External\Contract\IXMLModule;

// Use external module class dependencies
use \OMT\External\Contract\ILoggingModule;

// Use external library class dependencies
use \Sabre\Xml\Service;

/**
 * Module wrapper for XML service.
 *
 * This implementation uses Sabre XML.
 *
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */
class SabreXMLModule extends BaseModule implements IXMLModule {

	/**
	 * XML service instance.
	 *
	 * @var Service
	 */
	protected $xml_service;

	/**
	 * Creates Sabre based XML service.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 * @param Service $xml_service XML service instance.
	 */
	public function __construct(ILoggingModule $logging_module, Service $xml_service) {
		parent::__construct($logging_module);
		$this->xml_service = $xml_service;
	}
	
	public function getModuleId() {
		return 'module_xml';
	}
	
	public function serialize($deserialized, $root = FALSE, $serializers = array()) {
		$result = '';
		try {
			// Attempt to write XML
			$writer = $this->xml_service->getWriter();
			$writer->classMap = $serializers;
			$writer->openMemory();
			$writer->startDocument();
			if ($root) {
				// Serialize with root element
				$writer->writeElement($root, $deserialized);
			} else {
				// Serialize without root element
				$writer->write($deserialized);
			}

			$result = $writer->outputMemory();
		} catch (\Exception $exception) {
			// Handle serialization error
			$message = 'XML serialization failed';
			$this->logException($exception, $message, [
				'to_serialize' => \json_encode($deserialized)
			]);
		}
		
		return $result;
	}
	
	public function deserialize($serialized, $deserializers = array()) {
		$result = array();
		try {
			// Attempt to read XML
			$reader = $this->xml_service->getReader();
			$reader->elementMap = $deserializers;
			$reader->xml($serialized);

			$result = $reader->parse()['value'];
		} catch (\Exception $exception) {
			// Handle deserialization error
			$message = 'XML deserialization failed';
			$this->logException($exception, $message, [
				'to_deserialize' => $serialized
			]);
		}

		return $result;
	}

}
