<?php

namespace OMT\Services\External\Base\Response;

/**
 * Represents any response from HTTP service.
 *
 * @author Mateusz Tokarski
 * @created Apr 9, 2016
 */
abstract class BaseResponse {

	/**
	 * Returns a decoded response from HTTP service.
	 *
	 * Decoding depends on called request and must
	 * conform to implemented service protocol.
	 *
	 * @param string $encoded Request data to decode.
	 * @return mixed Decoded request.
	 */
	public abstract function getResponse($encoded);

}
