<?php

namespace OMT\Services\External\Merlin\Response;

// Use external class dependencies
use \OMT\Services\External\Merlin\MerlinService;
use \Sabre\Xml\Reader;

/**
 * 
 * @author Mateusz Tokarski
 * @created Apr 12, 2016
 */
class GroupsResponse extends OffersResponse {

	/**
	 * Deserializes root repsponse element.
	 *
	 * @param Reader $reader XML reader instance.
	 * @return mixed Deserialized value.
	 */
	public function deserializeResponse(Reader $reader) {
		return \Sabre\Xml\Deserializer\repeatingElements($reader, '{}grp');
	}

	public function deserializeGroup(Reader $reader) {
		$result = [];
		$elements = $reader->parseGetElements();
		foreach ($elements as $element) {
			$name = str_replace('{}', '', $element['name']);
			if ($name === 'variants') {
				$variants = isset($element['attributes']['ofr_tourOp']) ? explode(',', $element['attributes']['ofr_tourOp']) : [];
				$result[$name] = $variants;
			} else {
				$result[$name] = $element['value'];
			}
		}
		
		return $result;
	}

	protected function getDeserializers() {
		$deserializers = parent::getDeserializers();
		$deserializers['{}grp'] = array($this, 'deserializeGroup');
		
		return $deserializers;
	}

}
