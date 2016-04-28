<?php

namespace OMT\External\Contract;

/**
 * Defines templating module functionality.
 *
 * @author Mateusz Tokarski
 * @creaed Apr 16, 2016
 */
interface ITemplateModule {

	/**
	 * Outputs template with given variables.
	 *
	 * @param string $template name of template file to display. This
	 * file must exist in configured templates path. Extension doesn't have to be provided.
	 * @param array $variables Array of template variables.
	 */
	public function display($template, $variables);

}
