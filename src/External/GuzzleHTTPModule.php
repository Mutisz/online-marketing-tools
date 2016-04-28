<?php

namespace OMT\External;

// Use implemented interfaces and base classes
use \OMT\External\Base\BaseModule;
use \OMT\External\Contract\IHTTPModule;

// Use external module class dependencies
use \OMT\External\Contract\ILoggingModule;

// Use external library class dependencies
use \GuzzleHttp\Client;

/**
 * Module wrapper for HTTP client.
 *
 * This implementation uses Guzzle.
 *
 * @author Mateusz Tokarski
 * @created Apr 4, 2016
 */
class GuzzleHTTPModule extends BaseModule implements IHTTPModule {

	/**
	 * POST request type.
	 */
	const REQUEST_TYPE_POST = 'POST';

	/**
	 * Guzzle HTTP client.
	 *
	 * @var Client
	 */
	protected $http_client;

	/**
	 * Creates Guzzle based HTTP module.
	 *
	 * @param ILoggingModule $logging_module Module providing logging functionality.
	 * @param Client $http_client Already configured HTTP client.
	 */
	public function __construct(ILoggingModule $logging_module, Client $http_client) {
		parent::__construct($logging_module);
		$this->http_client = $http_client;
	}

	public function getModuleId() {
		return 'module_http';
	}

	public function post($relative_uri, $request_body) {
		try {
			// Try to send request and receive response
			$response = $this->http_client->request(self::REQUEST_TYPE_POST, $relative_uri, [
				'body' => $request_body
			]);

			// Process response
			$result = $this->processResponse($relative_uri, $request_body, $response);
		} catch (\Exception $exception) {
			$message = 'POST request failed';
			$this->logException($exception, $message, [
				'uri' => $relative_uri,
				'request' => $request_body,
			]);
		}

		return $result;
	}

	protected function processResponse($relative_uri, $request_body, $response) {
		$result = '';
		if ($response && $response->getStatusCode() == 200) {
			$result = $response->getBody()->getContents();
		} else {
			$message = 'POST request returned invalid response';
			$status_code = $response ? $response->getStatusCode() : 'None';
			$response_body = $response ? $response->getBody()->getContents() : 'None';
			$this->module_logger->error($message, [
				'uri' => $relative_uri,
				'code' => $status_code,
				'request' => $request_body,
				'response' => $response_body
			]);
		}

		return $result;
	}

}
