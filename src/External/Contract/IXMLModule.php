<?php

namespace OMT\External\Contract;

// Use external library class dependencies
use \Sabre\Xml\XmlSerializable;

/**
 * Defines XML serialization module functionality.
 *
 * @author Mateusz Tokarski
 * @creaed Apr 16, 2016
 */
interface IXMLModule {

	/**
	 * Encode data to XML.
	 *
	 * @param array|string|XmlSerializable $deserialized Data to serialize.
	 * @param string $root Root element XML document should have.
	 * @param callable[] $serializers Array of callable functions for performing serialization.
	 * @return string XML serialized string.
	 */
	public function serialize($deserialized, $root = FALSE, $serializers = array());

	/**
	 * Decode XML data string.
	 *
	 * @param string $serialized XML serialized string to deserialize.
	 * @param callable[] $deserializers Array of deserializer functions with XML element names as keys.
	 * @return array Array of deserialized data.
	 */
	public function deserialize($serialized, $deserializers = array());

}
