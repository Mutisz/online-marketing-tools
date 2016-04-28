<?php

namespace OMT\Services\External\Base\Request;

/**
 * Represents any request to HTTP service.
 *
 * @author Mateusz Tokarski
 * @created Apr 6, 2016
 */
abstract class BaseRequest {

	/**
	 * Returns an encoded request for HTTP service.
	 *
	 * Encoding depends on called request and must
	 * conform to implemented service protocol.
	 *
	 * @param mixed $decoded Request data to encode.
	 * @return string Encoded request.
	 */
	public abstract function getRequest($decoded);

}
