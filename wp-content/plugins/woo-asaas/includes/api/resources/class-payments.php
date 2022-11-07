<?php
/**
 * API '/payments' resource.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Resources;

use WC_Asaas\Api\Response\Response;
use WC_Asaas\Api\Client\Client;
use WC_Asaas\Api\Client\Collection_Client;

/**
 * API '/payments' resource.
 */
class Payments extends Resource {

	/**
	 * Resource path.
	 *
	 * @var string
	 */
	const PATH = '/payments/';

	/**
	 * Find a payment by id.
	 *
	 * @param  int $id Payment id.
	 * @return Response The HTTP response.
	 */
	public function find( $id ) {
		$client = new Client( $this->gateway );
		return $client->get( self::PATH . $id );
	}

	/**
	 * Get all payments.
	 *
	 * @return Response The HTTP response.
	 */
	public function all() {
		$client = new Collection_Client( $this->gateway );
		return $client->get( self::PATH );
	}
	
	/**
	 * Get all payments filtered by overdue date and billing type "BOLETO".
	 *
	 * @param string $due_date_start Payment due date start range.
	 * @param string $due_date_end   Payment due date end range.
	 * @return Response The HTTP response.
	 */
	public function all_between_dates( $due_date_start, $due_date_end ) {
		$client = new Collection_Client( $this->gateway );
		$params = array(
			'dueDate[ge]' => $due_date_start,
			'dueDate[le]' => $due_date_end,
			'status'          => 'OVERDUE',
			'billingType'     => 'BOLETO'
		);

		$request_url = sprintf( '%s?%s',
			self::PATH,
			http_build_query( $params )
		);

		return $client->get( $request_url );
	}

	/**
	 * Update a payment by id.
	 *
	 * @param  int   $id Payment id.
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function update( $id, $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH . $id, $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Pay with credit card.
	 *
	 * @param  int   $id Payment id.
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function pay_with_credit_card( $id, $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH . $id . '/payWithCreditCard', $data, array( $this, 'filter_data_log' ) );
	}	

	/**
	 * Create a newly payment.
	 *
	 * @param  array $data Request body.
	 * @return Response The HTTP response.
	 */
	public function create( $data ) {
		$client = new Client( $this->gateway );
		return $client->post( self::PATH, $data, array( $this, 'filter_data_log' ) );
	}

	/**
	 * Delete a payment by id.
	 *
	 * @param  int $id Payment id.
	 * @return Response The HTTP response.
	 */
	public function delete( $id ) {
		$client = new Client( $this->gateway );
		return $client->delete( self::PATH . $id );
	}

	/**
	 * Refund a payment by id.
	 *
	 * @param  int $id Payment id.
	 * @return Response The HTTP response.
	 */
	public function refund( $id ) {
		$client = new Client( $this->gateway );
		return $client->delete( self::PATH . $id . '/refund' );
	}

	/**
	 * Get the minimum bank slip due date
	 *
	 * @return Response The HTTP response.
	 */
	public function minimum_bank_slip_due_date() {
		$client = new Client( $this->gateway );
		return $client->get( self::PATH . 'minimumBankSlipDueDate' );
	}

	/**
	 * Get ticket installment list.
	 *
	 * @param  int $id Installment id.
	 * @return Response The HTTP response.
	 */
	public function installment_list( $id ) {
		$client = new Collection_Client( $this->gateway );
		return $client->get( substr( self::PATH, 0, -1 ) . '?installment=' . $id );
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

	/**
	 * Get billing type "PIX" information.
	 *
	 * @param string $due_date_start Payment due date start range.
	 * @param string $due_date_end   Payment due date end range.
	 * @return Response The HTTP response.
	 */
	public function pix_info( $id ) {
		$client = new Client( $this->gateway );
		return $client->get( self::PATH . $id . '/pixQrCode' );
	}
}
