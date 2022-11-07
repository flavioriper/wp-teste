<?php
/**
 * API '/customers' resource.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Resources;

use WC_Asaas\Api\Client\Client;
use WC_Asaas\Api\Client\Collection_Client;
use WC_Asaas\Api\Response\Response;
use function cli\safe_str_pad;

/**
 * API '/customers' resource.
 */
class Customers extends Resource {
	/**
	 * Resource path.
	 *
	 * @var string
	 */
	const PATH = '/customers/';

	/**
	 * Find a customer by id.
	 *
	 * @param  string $id Customer id.
	 * @return Response The HTTP response.
	 */
	public function find( $id ) {
		$client = new Client( $this->gateway );
		return $client->get( self::PATH . $id );
	}

	/**
	 * List all customers.
	 *
	 * @param array $data The request parameters.
	 * @return Response The HTTP response.
	 */
	public function all( $data = array() ) {
		$data = wp_parse_args(
			$data, array(
				'limit' => 50,
			)
		);

		$client = new Collection_Client( $this->gateway );
		return $client->get( self::PATH, $data );
	}

	/**
	 * Update a customer by id.
	 *
	 * @param  string $id Customer id.
	 * @param  array  $data Request body.
	 * @return Response The HTTP response.
	 */
	public function update( $id, $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH . $id, $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Create a newly customer.
	 *
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function create( $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH, $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Delete a customer by id.
	 *
	 * @param  int $id Customer id.
	 * @return Response The HTTP response.
	 */
	public function delete( $id ) {
		$client = new Client( $this->gateway );
		return $client->delete( self::PATH . $id );
	}
}
