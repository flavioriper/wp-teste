<?php
/**
 * API response handler class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Response;

use WC_Asaas\Api\Client\Client;

/**
 * API response handler class
 */
class Response_Factory {

	/**
	 * Return the right response object based on a HTTP response
	 *
	 * @param int    $status The response code.
	 * @param string $data The response data.
	 * @param Client $client The HTTP client.
	 * @return Response A response object depending of the HTTP response.
	 */
	public static function create( $status, $data, Client $client ) {
		if ( 200 !== $status ) {
			return new Error_Response( $status, $data, $client );
		}

		$json = json_decode( $data );
		if ( ! empty( $json->object ) && 'list' === $json->object ) {
			return new Collection_Response( $status, $data, $client );
		}

		return new Object_Response( $status, $data, $client );
	}
}
