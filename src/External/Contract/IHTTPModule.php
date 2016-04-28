<?php

namespace OMT\External\Contract;

/**
 * Defines HTTP client module functionality.
 *
 * @author Mateusz Tokarski
 * @creaed Apr 16, 2016
 */
interface IHTTPModule {

	/**
	 * Sends post request with raw body.
	 *
	 * @param string $relative_uri URI relative to base URI from client HTTP options.
	 * @param string $request_body Raw request body.
	 * @return string|boolean Response raw body or false if response was returned with
	 * other code than 200.
	 */
	public function post($relative_uri, $request_body);

}
