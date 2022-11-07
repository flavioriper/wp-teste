<?php
/**
 * Pix settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Admin\Settings;

use WC_Asaas\WC_Asaas;

/**
 * Pix settings
 */
class Pix extends Settings {

	/**
	 * Default payment validity days.
	 *
	 * @var int
	 */
	const DEFAULT_VALIDITY_DAYS = 3;

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_fields()
	 */
	public function get_fields() {
		$fields                           = parent::get_fields( $this->gateway->get_type() );
		$fields['description']['default'] = __( 'Pay your purchase with Pix.', 'woo-asaas' );

		$asaas               = WC_Asaas::get_instance();
		$credit_card_gateway = $asaas->get_gateway_by_id( 'asaas-credit-card' );

		$fields['endpoint']['default']             = $credit_card_gateway->settings['endpoint'];
		$fields['api_key']['default']              = $credit_card_gateway->settings['api_key'];
		$fields['notification']['default']         = $credit_card_gateway->settings['notification'];
		$fields['webhook_access_token']['default'] = $credit_card_gateway->settings['webhook_access_token'];

		return array_merge(
			apply_filters( 'woocommerce_asaas_pix_settings_fields', $fields, $this ),
			array(
				'validity_days' => array(
					'title'             => __( 'Expiration Period', 'woo-asaas' ),
					'type'              => 'text',
					'description'       => __( 'Pix validity after purchase. At the end of this period, the Pix will be removed. The value can be minutes (<code>10m</code> for 10 minutes), hours (<code>3h</code> for 3 hours) or days (<code>1d</code> for 1 day). If you do not enter <code>m</code>, <code>h</code> or <code>d</code>, the default will be <code>d</code>. Leave blank to not expire. The minimum period is 10 minutes.', 'woo-asaas' ),
					'default'           => $this->get_default_pix_validity_days(),
					'section'           => 'gateway',
					'priority'          => 20,
					'sanitize_callback' => array( $this, 'valid_min_expiration_period' )
				),
				'copy_and_paste' => array(
					'title'    => __( 'Copy and paste', 'woo-asaas' ),
					'type'     => 'checkbox',
					'label'    => __( 'Enable copy and paste', 'woo-asaas' ),
					'default'  => 'no',
					'section'  => 'gateway',
					'priority' => 20,
				),
			)
		);
	}

	/**
	 * Returns the default payment validity days for the PIX method.
	 *
	 * @return int
	 */
	public function get_default_pix_validity_days() : int {
		$validity_days = intval( apply_filters( 'woocommerce_asaas_default_pix_validity_days', self::DEFAULT_VALIDITY_DAYS ) );
		return $validity_days;
	}

	public function valid_min_expiration_period( string $value ) : string {
		if( '' ===  $value ) {
			return '';
		}

		$period = substr( $value, -1 );
		if( 'm' !== $period ) {
			return $value;
		}

		$expiration_value = intval( substr( $value, 0, -1 ) );
		if( 10 > $expiration_value ) {
			return '10m';
		}

		return $value;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_sections()
	 */
	public function get_sections() {
		$sections                     = parent::get_sections();
		$sections['gateway']['title'] = __( 'Pix', 'woo-asaas' );
		unset( $sections['subscriptions'] );
		return apply_filters( 'woocommerce_asaas_pix_settings_sections', $sections );
	}
}
