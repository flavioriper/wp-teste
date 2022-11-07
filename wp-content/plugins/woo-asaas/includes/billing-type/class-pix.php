<?php
/**
 * Pix billing type class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Billing_Type;

/**
 * Pix billing type
 */
class Pix extends Billing_Type {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_id()
	 */
	public function get_id() {
		return 'PIX';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_name()
	 */
	public function get_slug() {
		return 'pix';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_name()
	 */
	public function get_name() {
		return __( 'Pix', 'woo-asaas' );
	}
}
