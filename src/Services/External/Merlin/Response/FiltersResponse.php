<?php

namespace OMT\Services\External\Merlin\Response;

// Use external module class dependencies
use \OMT\Services\External\Merlin\MerlinService;
use \OMT\Services\External\Merlin\MerlinDataConstants;

/**
 * Represents a response for Merlin filters request.
 * 
 * @author Mateusz Tokarski
 * @created Apr 9, 2016
 */
class FiltersResponse extends MerlinResponse {

	/**
	 * Deserializes root response element.
	 *
	 * Each element key must correspond to its
	 * filter id, hense repeating values deserializer is not used here.
	 *
	 * @param Reader $reader XML reader instance.
	 * @return mixed Deserialized value.
	 */
	public function deserializeResponse(\XMLReader $reader) {
		$result = [];
		$children = $reader->parseInnerTree() ?: [];
		foreach($children as $child) {
			$filter_name = $child['attributes']['id'];
			$result[$filter_name] = $child['value'];
		}

		return $result;
	}

	/**
	 * Deserializes single filter definition element.
	 *
	 * @param Reader $reader XML reader instance.
	 * @return mixed Deserialized value.
	 */
	public function deserializeFilterDefinition(\XMLReader $reader) {
		$result = [];
		$filter_type = $reader->getAttribute('id');
		$elements = $reader->parseGetElements();
		foreach ($elements as $element) {
			if ($element['name'] === '{}f') {
				$id = $element['attributes']['id'];
				if (isset($element['attributes']['v'])) {
					// If filter has value it should be used instead of id
					$value = $element['attributes']['v'];
					$result[$value] = $value;
				} else {
					// Filter is not a date and has no value, just id
					$value = $this->translateFilterId($filter_type, $id);
					$result[$id] = $value;
				}
			}
		}

		return $result;
	}

	protected function getDeserializers() {
		return  [
			'{}response' => array($this, 'deserializeResponse'),
			'{}fdef' => array($this, 'deserializeFilterDefinition')
		];
	}

	/**
	 * Translates filter item id to readable format.
	 *
	 * @param string $filter_type Type of translated filter item id.
	 * @param string $id Filter item id.
	 * @return string Translated Filter item id.
	 */
	protected function translateFilterId($filter_type, $id) {
		switch ($filter_type) {
			case 'trp_depDate':
				// Filter dates should be converted to understandable format
				$result = MerlinService::formatDateOut($id);
				break;
			case 'obj_xServiceId':
				// Result should be one of meal type constants
				$result = MerlinDataConstants::MEAL_TYPES[$id] != null ? MerlinDataConstants::MEAL_TYPES[$id] : $id;
				break;
			default:
				$result = $id;
				break;
		}

		return $result;
	}

}
