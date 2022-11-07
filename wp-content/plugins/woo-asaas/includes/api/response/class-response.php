<?php
/**
 * API response handler class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Response;

use WC_Asaas\Api\Client\Client;

/**
 * API object response handler
 */
abstract class Response {

	/**
	 * JSON response body in raw format
	 *
	 * @var string
	 */
	protected $data;

	/**
	 * The collection client
	 *
	 * Used to get more items.
	 *
	 * @var Client
	 */
	public $client;

	/**
	 * JSON response body in object format
	 *
	 * @var \stdClass
	 */
	public $json;

	/**
	 * The HTTP response code
	 *
	 * @var int
	 */
	public $code;

	/**
	 * Create a response object based on a HTTP response
	 *
	 * @param int    $status The response code.
	 * @param string $data The response data.
	 * @param Client $client The HTTP client.
	 */
	public function __construct( $status, $data, $client ) {
		$this->data   = $data;
		$this->code   = $status;
		$this->client = $client;
	}

	/**
	 * Get raw response.
	 *
	 * @return string
	 */
	public function get_raw() {
		return $this->data;
	}

	/**
	 * Get json response.
	 *
	 * @return \stdClass The json response object.
	 */
	public function get_json() {
		if ( ! is_null( $this->json ) ) {
			return $this->json;
		}

		$this->json = json_decode( $this->data );

		return $this->json;
	}

	/**
	 * Get response property
	 *
	 * If is an error response, always return null.
	 *
	 * @param  string $name Property name.
	 * @return string|null The property value. Null, if not exists or is error.
	 */
	public function __get( $name ) {
		$json = $this->get_json();

		if ( property_exists( $json, $name ) ) {
			return $json->{$name};
		}

		return null;
	}
}
