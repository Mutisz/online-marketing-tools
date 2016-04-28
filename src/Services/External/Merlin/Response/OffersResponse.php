<?php

namespace OMT\Services\External\Merlin\Response;

// Use external class dependencies
use \OMT\Services\External\Merlin\MerlinService;
use \Sabre\Xml\Reader;

/**
 * Represents a response for Merlin offers request.
 *
 * @author Mateusz Tokarski
 * @created Apr 10, 2016
 */
class OffersResponse extends MerlinResponse {

	/**
	 * Deserializes root repsponse element.
	 *
	 * @param Reader $reader XML reader instance.
	 * @return mixed Deserialized value.
	 */
	public function deserializeResponse(Reader $reader) {
		return \Sabre\Xml\Deserializer\repeatingElements($reader, '{}ofr');
	}

	/**
	 * Deserializes single offer element.
	 *
	 * @param Reader $reader XML reader instance.
	 * @return mixed Deserialized value.
	 */
	public function deserializeOffer(Reader $reader) {
		// Add offer data to deserialized response
		$offer_data = $reader->parseAttributes();
		$this->parseDates($offer_data);
		$result = [
			'data' => $offer_data
		];

		// Add child elements to deserialized response
		$elements = $reader->parseGetElements();
		foreach ($elements as $element) {
			if ($element['name'] === '{}obj' || $element['name'] === '{}trp') {
				$name = str_replace('{}', '', $element['name']);
				$result[$name] = $element['attributes'];
				$this->parseDates($result[$name]);
			}
		}

		return $result;
	}

	protected function getDeserializers() {
		return  [
			'{}response' => array($this, 'deserializeResponse'),
			'{}ofr' => array($this, 'deserializeOffer')
		];
	}

	/**
	 * Converts Merlin dates in data to Date Time objects.
	 *
	 * @param array $data Array of data with converted dates.
	 */
	protected function parseDates(&$data) {
		array_walk($data, function(&$value, $key) {
			if (stripos($key, 'date') !== false) {
				$date_out = MerlinService::formatDateOut($value);
				$value = new \DateTime($date_out);
			}
		});
	}

}
