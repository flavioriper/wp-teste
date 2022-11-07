<?php
/**
 * API '/creditCard' resource.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Resources;

use WC_Asaas\Api\Response\Response;
use WC_Asaas\Api\Client\Client;

/**
 * API '/creditCard' resource.
 */
class Credit_Card extends Resource {

	/**
	 * Resource path.
	 *
	 * @var string
	 */
	const PATH = '/creditCard/';

	/**
	 * Tokenize a credit card.
	 *
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function tokenize( $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH . 'tokenize', $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Remove sensitive card and holder data to not be stored in log
	 *
	 * @param string|\stdClass $data The data to be stored.
	 * @return string|false The data encoded on string.
	 */
	public function filter_data_log( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		if ( isset( $data['creditCard'] ) ) {
			unset( $data['creditCard'] );
		}

		if ( ! empty( $data['creditCardHolderInfo'] ) ) {
			unset( $data['creditCardHolderInfo'] );
		}

		return wp_json_encode( $data );
	}
}
