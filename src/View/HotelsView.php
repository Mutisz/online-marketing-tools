<?php

namespace OMT\View;

// Use implemented interfaces and base classes
use \OMT\View\Base\BaseView;

/**
 * Represents view for hotel comparer.
 *
 * @author Mateusz Tokarski
 * @created Apr 5, 2016
 */
class HotelsView extends BaseView {
	
	/**
	 * Template name for hotel comparer page.
	 *
	 * @var string
	 */
	const HOTELS_TEMPLATE = 'hotels';

	public function getViewId() {
		return 'view_hotels';
	}

	public function getDefaultTemplate() {
		return self::HOTELS_TEMPLATE;
	}

	/**
	 * Sets the earliest date to use in filter.
	 *
	 * @param string $date Earliest date to use in filter.
	 */
	public function setMinDate($date) {
		$this->set('min_date', $date);
	}

	/**
	 * Sets the latest date to use in filter.
	 *
	 * @param string $date Latest date to use in filter.
	 */
	public function setMaxDate($date) {
		$this->set('max_date', $date);
	}

	/**
	 * Sets a list of departure names.
	 *
	 * @param string[] $departure_names A list of departure names.
	 */
	public function setDepartureNames($departure_names) {
		$this->set('departure_names', $departure_names);
	}

	/**
	 * Sets a list of meal types.
	 *
	 * @param string[] $meal_types A list of meal types.
	 */
	public function setMealTypes($meal_types) {
		$this->set('meal_types', $meal_types);
	}

	/**
	 * Sets a list of hotels grouped by regions.
	 *
	 * @param array $regions A list of hotels by region.
	 */
	public function setRegions($regions) {
		$this->set('regions', $regions);
	}

}
