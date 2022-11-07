<?php
/**
 * Ticket settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Admin\Settings;

/**
 * Ticket settings
 */
class Ticket extends Settings {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_fields()
	 */
	public function get_fields() {
		$fields                           = parent::get_fields( $this->gateway->get_type() );
		$fields['description']['default'] = __( 'Pay your purchase with ticket.', 'woo-asaas' );

		return array_merge(
			apply_filters( 'woocommerce_asaas_ticket_settings_fields', $fields, $this ),
			array(
				'validity_days' => array(
					'title'       => __( 'Validity Days', 'woo-asaas' ),
					'type'        => 'text',
					'description' => __( 'Quantity days that the ticket is valid after the purchase.', 'woo-asaas' ),
					'default'     => '',
					'section'     => 'gateway',
					'priority'    => 20,
				),
				'validity_period' => array(
					'title'       => __( 'Validity Period', 'woo-asaas' ),
					'type'        => 'text',
					'description' => __( 'Number of days that the ticket will be kept at Asaas after its expiration. At the end of this period, the ticket will be removed. It will keep if blank.', 'woo-asaas' ),
					'default'     => '',
					'section'     => 'gateway',
					'priority'    => 20,
				),
			)
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_sections()
	 */
	public function get_sections() {
		$sections                     = parent::get_sections();
		$sections['gateway']['title'] = __( 'Ticket', 'woo-asaas' );
		return apply_filters( 'woocommerce_asaas_ticket_settings_sections', $sections );
	}
}
