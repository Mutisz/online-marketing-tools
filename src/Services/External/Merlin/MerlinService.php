<?php

namespace OMT\Services\External\Merlin;

// Use implemented interfaces and base classes
use \OMT\Services\External\Base\BaseService;

// Use external class dependencies
use \OMT\External\Contract\IHTTPModule;
use \OMT\Services\External\Merlin\Request\MerlinRequest;
use \OMT\Services\External\Merlin\Response\MerlinResponse;
use \OMT\Services\External\Merlin\Response\FiltersResponse;
use \OMT\Services\External\Merlin\Response\GroupsResponse;
use \OMT\Services\External\Merlin\Response\OffersResponse;

/**
 * Merlin web service proxy.
 *
 * @author Mateusz Tokarski
 * @created Apr 4, 2016
 */
class MerlinService extends BaseService {

	/**
	 * Relative URI for MerlinX data queries.
	 */
	const MERLIN_DATA_URI = 'dataV3/';

	/**
	 * Maximum number of elements in single response.
	 */
	const REQUEST_LIMIT_COUNT = 100;

	/**
	 * Maxmium number of elements in multi response.
	 */
	const REQUEST_LIMIT = 1000;

	/**
	 * Merlin request object.
	 *
	 * @var MerlinRequest
	 */
	protected $request;

	/**
	 * Merlin response object.
	 *
	 * @var MerlinResponse
	 */
	protected $response;

	/**
	 * Creates Merlin web service proxy.
	 *
	 * @param HTTPModule $http_module Module for creating HTTP requests to Merlin service.
	 * @Inject({"http_module" = "merlin.http_module"})
	 */
	public function __construct(IHTTPModule $http_module) {
		parent::__construct($http_module);
		$this->request = $this->getFromContainer(MerlinRequest::class);
		$this->response = $this->getFromContainer(MerlinResponse::class);
	}

	/**
	 * Formats date to be used in API queries.
	 *
	 * @param \DateTime $date Date to be formatted.
	 * @return string Date to be used in API queries.
	 */
	public static function formatDateIn(\DateTime $date) {
		return $date->format('Ymd');
	}

	/**
	 * Formats date from API reponses.
	 *
	 * @param string $date Date from API.
	 * @return string Properly formatted date.
	 */
	public static function formatDateOut($date) {
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);

		return implode('-', [$year, $month, $day]);
	}

	/**
	 * Formats duration to be used in API queries.
	 *
	 * @param string $duration Duration input.
	 * @return string Duration for API.
	 */
	public static function formatDuration($duration) {
		$stripped_spaces = str_replace(' ', '', $duration);
		$replaced_separator = str_replace('-', ':', $stripped_spaces);

		return $replaced_separator;
	}

	/**
	 * Returns filter values relevant to hotels comparison.
	 *
	 * @param array $options MerlinX service global options.
	 * @return array Array of relevant filters with two elements - a common filters for tour operator
	 * as first and array of hotels by region as the second.
	 */
	public function filters($options) {
		$common = [];
		$hotels = [];

		// Create request body for common operator filters
		$request_xml = $this->request->getRequest([
			'type' => 'filters',
			'conditions' => [
				'ofr_tourOp' => $options['operator'],
				'filters' => 'obj_xServiceId,trp_depDate,trp_depName'
			]
		]);

		// Send request for common operator fitlers
		$common = $this->sendRequest($request_xml, FiltersResponse::class);

		foreach ($options['destinations'] as $destination) {
			// Create request body for region hotels
			$request_xml = $this->request->getRequest([
				'type' => 'filters',
				'conditions' => [
					'trp_destination' => $destination,
					'filters' => 'obj_xCode'
				]
			]);

			// Send request for region hotels
			$hotels[$destination] = $this->sendRequest($request_xml, FiltersResponse::class);
		}

		return [$common, $hotels];
	}

	/**
	 * Sends requests for grouped offers.
	 *
	 * Offers are grouped by operator, departure date and duration.
	 * Only offers with field values corresponding to given filters are returned.
	 *
	 * @param array $conditions Array of conditions for groups request.
	 * @return array Array of grouped offers matching given criteria.
	 */
	public function groups(array $conditions) {
		// Send request as long as there are offers left
		$result = $this->sendLimitedRequest([
			'type' => 'groups',
			'conditions' => $conditions
		], GroupsResponse::class);

		return $result;
	}

	/**
	 * Sends requests for all offers.
	 *
	 * Offers are not grouped. Only offers with field values corresponding
	 * to given filters are returned.
	 *
	 * @param \DateTime $from_date Earliest start date for offers.
	 * @param \DateTime $to_date Latest start date for offers.
	 * @param string $hotel Name of hotel.
	 * @param string[] $operators Comma separated offer operators.
	 * @param string[] $options Global offer options.
	 * @return array Array of all offers matching given criteria.
	 */
	public function offers(\DateTime $from_date, \DateTime $to_date, $hotel, $operators, $options) {
		// Prepare data for offers request
		$from_date = self::formatDateIn($from_date);
		$to_date = self::formatDateIn($to_date);
		$operators_condition = implode(',', $operators);

		// Send request as long as there are offers left
		$result = $this->sendLimitedRequest([
			'type' => 'offers',
			'conditions' => [
				'ofr_tourOp' => $operators_condition,
				'trp_depDate' => "$from_date:$to_date",
				'obj_codeNameFts' => $hotel
			]
		], OffersResponse::class);

		return $result;
	}

	/**
	 * Sends multiple requests to get many records.
	 *
	 * @param array $request_data Array of data to be encoded in request body.
	 * @param string $response_class Class defining response deserialization.
	 * @return array Deserialized response data.
	 */
	protected function sendLimitedRequest($request_data, $response_class = false) {
		$result = [];
		$start_from = 1;
		do {
			// Create request body with changed limits
			$request_data['conditions']['limit_from'] = $start_from;
			$request_data['conditions']['limit_count'] = self::REQUEST_LIMIT_COUNT;
			$request_xml = $this->request->getRequest($request_data);

			// Send request and merge its response to result
			$response = $this->sendRequest($request_xml, $response_class);
			$result = array_merge($result, $response);
			$start_from += count($response);
		} while (count($response) >= self::REQUEST_LIMIT_COUNT
			&& $start_from <= self::REQUEST_LIMIT);

		return $result;
	}

	/**
	 * Sends single request.
	 *
	 * @param string $request_xml XML request body.
	 * @param string $response_class Class defining response deserialization.
	 * @return array Deserialized response data.
	 */
	protected function sendRequest($request_xml, $response_class = false) {
		$result = array();
		$response_raw = $this->http_module->post(self::MERLIN_DATA_URI, $request_xml);
		if ($response_raw) {
			// Raw response is returned, get deserialized version
			$response = $response_class ? $this->getFromContainer($response_class) : $this->response;
			$result = $response->getResponse($response_raw);
		}

		return $result;
	}

}
