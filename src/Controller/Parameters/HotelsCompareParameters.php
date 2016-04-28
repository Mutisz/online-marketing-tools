<?php

namespace OMT\Controller\Parameters;

// Use implemented interfaces and base classes
use \OMT\Internal\Input\Parameters;

/**
 * 
 * @author Mateusz Tokarski
 * @created Apr 20, 2016
 */
class HotelsCompareParameters extends Parameters {

	const DEFAULT_DURATION = 7;

	const DEFAULT_MAX_ADULTS = 2;
	
	public $from_date;
	
	public $to_date;
	
	public $departure_name;
	
	public $duration;
	
	public $max_adults;
	
	public $meal_type;

	public $to_compare;

	protected function __construct() {
		parent::__construct();
		$this->from_date = new \DateTime($this->fetch('from_date'));
		$this->to_date = new \DateTime($this->fetch('to_date'));
		$this->departure_name = $this->fetch('departure_name');
		$this->duration = $this->fetch('duration') ?: self::DEFAULT_DURATION;
		$this->max_adults = $this->fetch('max_adults') ?: self::DEFAULT_MAX_ADULTS;
		$this->meal_type = $this->fetch('meal_type') ?: [];
		$this->to_compare = $this->fetch('to_compare');
	}

	/**
	 * Validates parameters for hotels comparison.
	 *
	 * @return boolean True if parameters are valid.
	 */
	public function validate() {
		$valid = TRUE;
		if ($this->from_date && $this->to_date
			&& $this->to_compare && $this->departure_name) {
			// Check if dates are valid
			$valid = $this->from_date->getTimestamp() < $this->to_date->getTimestamp();
		} else {
			$valid = FALSE;
		}

		return $valid;
	}

}
