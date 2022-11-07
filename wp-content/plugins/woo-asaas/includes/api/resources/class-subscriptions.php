<?php
/**
 * API '/subscriptions' resource.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Resources;

use WC_Asaas\Api\Response\Response;
use WC_Asaas\Api\Client\Client;
use WC_Asaas\Api\Client\Collection_Client;

/**
 * API '/subscriptions' resource.
 */
class Subscriptions extends Resource {

	/**
	 * Resource path.
	 *
	 * @var string
	 */
	const PATH = '/subscriptions/';

	/**
	 * Create a newly subscription.
	 *
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function create( $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH, $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Find a subscription by id.
	 *
	 * @param  int $id Subscription id.
	 * @return Response The HTTP response.
	 */
	public function find( $id ) {
		$client = new Client( $this->gateway );
		return $client->get( self::PATH . $id );
	}

	/**
	 * Get all payments related to subscription.
	 *
	 * @param string $subscription_id The subscription id.
	 * @return Response The HTTP response.
	 */
	public function payments( $subscription_id ) {
		$client = new Collection_Client( $this->gateway );
		return $client->get( self::PATH . $subscription_id . '/payments' );
	}

	/**
	 * Update a subscription by id.
	 *
	 * @param  int   $id Subscription id.
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function update( $id, $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH . $id, $data, array( $this, 'filter_data_log' ) );
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
