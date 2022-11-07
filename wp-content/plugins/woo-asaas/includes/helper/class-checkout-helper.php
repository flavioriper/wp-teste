<?php
/**
 * Validation helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Helper;

/**
 * Validation helper functions
 */
class Checkout_Helper {

	/**
	 * Convert payment status to natural language
	 *
	 * @link https://asaasv3.docs.apiary.io/#reference/0/cobrancas
	 *
	 * @param string $status The payment status.
	 * @return string The natural language payment status.
	 */
	public function convert_status( $status ) {
		if ( 'PENDING' === $status ) {
			return __( 'Pending', 'woo-asaas' );
		}

		if ( true === in_array( $status, array( 'RECEIVED', 'CONFIRMED' ), true ) ) {
			return __( 'Confirmed', 'woo-asaas' );
		}

		if ( 'OVERDUE' === $status ) {
			return __( 'Overdue', 'woo-asaas' );
		}

		if ( 'REFUNDED' === $status ) {
			return __( 'Refunded', 'woo-asaas' );
		}

		if ( 'RECEIVED_IN_CASH' === $status ) {
			return __( 'Received in cash', 'woo-asaas' );
		}

		if ( 'REFUND_REQUESTED' === $status ) {
			return __( 'Refund requested', 'woo-asaas' );
		}
	}
}
