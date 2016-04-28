<?php

namespace OMT\Model;

// Use implemented interfaces and base classes
use \OMT\Model\Base\BaseModel;

// Use internal class dependencies
use \OMT\Controller\Parameters\HotelsCompareParameters;

// Use external class dependencies
use \OMT\Services\External\Merlin\MerlinService;
use \OMT\Services\External\Merlin\MerlinDataConstants;

/**
 * Class for conducting business logic for hotel comparer.
 *
 * @author Mateusz Tokarski
 * @created Apr 5, 2016
 */
class HotelsModel extends BaseModel {

	/**
	 * Merlin web service proxy.
	 *
	 * @var MerlinService
	 */
	protected $merlin_service;

	/**
	 * Configuration for hotel comparer.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Creates an instance of hotels comparer model.
	 *
	 * @param MerlinService $merlin_service Merlin web service proxy.
	 * @param string[] $options Configuration for hotel comparer.
	 * @Inject({"options" = "merlin.compare_hotels"})
	 */
	public function __construct(MerlinService $merlin_service, $options) {
		$this->merlin_service = $merlin_service;
		$this->options = $options;
	}

	/**
	 * Returns filter values for comparison form.
	 *
	 * @return array Filter values with filter id as key (unchanged API attribute).
	 */
	public function getFilters() {
		list($common_filters, $hotels_filters) = $this->merlin_service->filters($this->options);

		// Create array of hotel options
		$hotels = [];
		foreach ($hotels_filters as $region => $filters) {
			$translated_region = MerlinDataConstants::REGIONS[$region];
			if (isset($filters['obj_xCode'])) {
				$hotels[$translated_region] = $filters['obj_xCode'];
			}
		}

		return [$common_filters, $hotels];
	}

	/**
	 * Creates hotels comparison with given parameters.
	 *
	 * <code>$comparison[$hotel][] = ['offer' => $original_offer, 'comparison' => $comparison]</code>
	 *
	 * @param HotelsCompareParameters $parameters A collection of request parameters
	 * @return array Array with complete hotels comparison.
	 */
	public function createComparison(HotelsCompareParameters $parameters) {
		$by_hotel = [];
		foreach ($parameters->to_compare as $hotel) {
			// Get offer groups for single hotel
			$groups_conditions = $this->createComparisonGroupsConditions($parameters, $hotel);
			$by_hotel[$hotel] = $this->merlin_service->groups($groups_conditions);
		}

		$result = [];
		$by_operator = $this->groupByOperator($by_hotel);
		$main_operator = $this->options['operator'];
		foreach ($by_operator as $hotel => $operators) {
			// Create comparison for each hotel
			$main_operator_offers = $operators[$main_operator];
			foreach ($main_operator_offers as $offer) {
				// Create comparison for each main operator offer
				$comparison_operators = $operators;
				unset($comparison_operators[$main_operator]);
				$result[$hotel][] = [
					'offer' => $this->createOffer($offer),
					'comparison' => $this->createOfferComparisonOperators($offer, $comparison_operators)
				];
			}
		}

		return $result;
	}

	protected function createComparisonGroupsConditions(HotelsCompareParameters $parameters, $hotel) {
		// Prepare data for offers request
		$from_date = MerlinService::formatDateIn($parameters->from_date);
		$to_date = MerlinService::formatDateIn($parameters->to_date);
		$duration = MerlinService::formatDuration($parameters->duration);

		// Create base conditions
		$conditions = [
			'par_adt' => $parameters->max_adults,
			'trp_depDate' => "$from_date:$to_date",
			'trp_durationM' => $duration,
			'trp_depName' => $parameters->departure_name,
			'obj_codeNameFts' => $hotel,
			'order_by' => 'ofr_price',
			'group_by' => 'tourOpCodeDateDuration'
		];

		if ($parameters->meal_type) {
			// Meal types should be considered in union
			$union = [];
			foreach ($parameters->meal_type as $meal_type) {
				$union[] = [
					'name' => 'conditions',
					'value' => [
						'obj_xServiceId' => $meal_type
					]
				];
			}

			// Add union to conditions
			$conditions['union'] = $union;
		}

		return $conditions;
	}

	/**
	 * Transforms offer groups into offers grouped by hotels and operators.
	 *
	 * <code>$by_operator[$hotel_name][$tour_operator_code][] = $offer_data;</code>
	 *
	 * @param array $by_hotel Offer groups grouped by hotel name.
	 * @return array Offers grouped by hotel and tour operator.
	 */
	protected function groupByOperator(array $by_hotel) {
		$by_operator = [];
		foreach ($by_hotel as $hotel => $offer_groups) {
			foreach ($offer_groups as $offer_group) {
				$offer = $offer_group['ofr'];
				$tour_operator = $offer['data']['tourOp'];
				$by_operator[$hotel][$tour_operator][] = $offer;
			}
		}

		return $by_operator;
	}

	/**
	 * Creates array with offer comparison data for each tour operator that has similar offer.
	 *
	 * @param array $offer Original offer data.
	 * @param array $comparison_operators Operators to which offer should be compared.
	 * @return array Comparison data.
	 */
	protected function createOfferComparisonOperators($offer, $comparison_operators) {
		$result = [];
		$offer_start_date = $offer['trp']['depDate']->getTimestamp();
		foreach ($comparison_operators as $operator => $comparison_offers) {
			$min_distance = false;
			foreach ($comparison_offers as $comparison_offer) {
				// Check each offer of every operator that operates on original offer's hotel
				$comparison_start_date = $comparison_offer['trp']['depDate']->getTimestamp();
				$comparison_distance = abs($offer_start_date - $comparison_start_date);
				if (($min_distance === false || $comparison_distance < $min_distance)
					&& $this->validateOfferComparison($offer, $comparison_offer)) {
					$result[$operator] = $this->createOfferComparison($offer, $comparison_offer);
					$min_distance = $comparison_distance;
				}
			}
		}

		return $result;
	}

	/**
	 * Create comparison data for single comparison offer.
	 *
	 * @param array $offer Original offer from service.
	 * @param array $comparison_offer Offer to compare with original taken from service.
	 * @return array Comparison data.
	 */
	protected function createOfferComparison($offer, $comparison_offer) {
		$offer_price = $offer['data']['price'] * $offer['obj']['maxAdt'];
		$comparison_price = $comparison_offer['data']['price'] * $comparison_offer['obj']['maxAdt'];
		return array_merge($this->createOffer($comparison_offer), [
			'diff' => $comparison_price - $offer_price
		]);
	}

	/**
	 * Creates single offer data.
	 *
	 * @param array $offer Single offer from service.
	 * @return array Original offer data.
	 */
	protected function createOffer($offer) {
		return [
			'date' => $offer['trp']['depDate']->format('d/m'),
			'duration' => $offer['trp']['durationM'],
			'price' => $offer['data']['price'],
			'adults' => $offer['obj']['maxAdt'],
			'room' => $offer['obj']['roomDesc'],
			'meal_type' => MerlinDataConstants::MEAL_TYPES[$offer['obj']['xServiceId']],
			'total' => $offer['data']['price'] * $offer['obj']['maxAdt']
		];
	}

	/**
	 * Check if two offers can be compared..
	 *
	 * @param array $offer Original offer.
	 * @param array $comparison_offer Comparison offer.
	 * @return boolean True if offers can be compared.
	 */
	protected function validateOfferComparison($offer, $comparison_offer) {
		// Offer fields to compare
		$equal_fields = [
			'trp' => [
				'durationM'
			],
			'obj' => [
				'xServiceId',
				'maxAdt'
			]
		];

		// Compare offer fields
		$valid = true;
		foreach ($equal_fields as $section => $fields) {
			foreach ($fields as $field) {
				$valid = $offer[$section][$field] == $comparison_offer[$section][$field];
				if (!$valid) {
					break;
				}
			}
		}

		return $valid;
	}

}
