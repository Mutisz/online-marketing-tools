<?php

namespace OMT\Controller;

// Use implemented interfaces and base classes
use \OMT\Controller\Base\BaseController;

// Use internal class dependencies
use \OMT\Internal\Utility\ContainerAccess;
use \OMT\Controller\Parameters\HotelsCompareParameters;
use \OMT\View\HotelsExcelView;

/**
 * Controller for hotels comparer.
 *
 * @author Mateusz Tokarski
 * @created Mar 28, 2016
 */
class HotelsController extends BaseController {
	use ContainerAccess;

	public function getControllerId() {
		return 'controller_hotels';
	}

	/**
	 * Fetches required filters from MerlinX service and displays them in a from.
	 */
	public function showDefault() {
		// Get required filters
		list($common, $hotels) = $this->model->getFilters();
		$dates = isset($common['trp_depDate']) ? $common['trp_depDate'] : [];
		$departure_names = isset($common['trp_depName']) ? $common['trp_depName'] : [];
		$meal_types = isset($common['obj_xServiceId']) ? $common['obj_xServiceId'] : [];

		if ($dates && $departure_names && $meal_types && $hotels) {
			// Create view
			$this->view->setMinDate(reset($dates));
			$this->view->setMaxDate(end($dates));
			$this->view->setDepartureNames($departure_names);
			$this->view->setMealTypes($meal_types);
			$this->view->setRegions($hotels);

			// Output template
			$this->view->display();
		} else {
			// Not all filters are set, log error
			$message = 'Not all filters were present in API response';
			$this->controller_logger->error($message, [
				'common' => \json_encode($common),
				'hotels' => \json_encode($common),
			]);

			// Show default error page
			$this->showError();
		}
	}

	/**
	 * Creates Excel file with hotels comparison.
	 */
	public function showCompare() {
		$parameters = HotelsCompareParameters::get();
		if ($parameters->validate()) {
			// Parameters are correct, create comparison
			$comparison = $this->model->createComparison($parameters);
			$view = $this->getFromContainer(HotelsExcelView::class);
			$view->display($comparison);
		} else {
			// Parameters invalid, log warning
			$message = 'Incorrect parameters passed to hotels comparison';
			$this->controller_logger->warning($message, compact('from_date', 'to_date', 'to_compare', 'departure_name'));
			
			// Show default page
			$this->showDefault();
		}
	}

}
