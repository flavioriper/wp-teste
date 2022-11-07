<?php
/**
 * Installments Fields class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Admin\Settings;

use Exception;
use WC_Asaas\Admin\Settings\Settings;
use WC_Asaas\Api\Api_Limit;

/**
 * Installments fields
 */
class Installments_Fields {

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;


	/**
	 * Is not allowed to call from outside to prevent from creating multiple instances.
	 */
	private function __construct() {
	}

    /**
     * Prevent the instance from being cloned.
     */
    private function __clone() {
    }

    /**
     * Prevent from being unserialized.
	 *
	 * @throws Exception If create a second instance of it.
     */
    public function __wakeup() {
        throw new Exception( __( 'Cannot unserialize singleton', 'woo-asaas' ) );
    }

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add installments gateway settings.
	 *
	 * @param array $fields Gatway fields.
	 * @param Settings $settings Gateway Settings.
	 * @return array
	 */
	public function add_installments_fields( array $fields, Settings $settings ) {
		$api_limit = new Api_Limit();
		$gateway = $settings->gateway;
		$default_settings = new Default_Installment_Settings( $gateway, $api_limit );

		$max_installments_message = sprintf(
			/* translators: %d: maximum installments allowed  */
			__( 'Define the installment limit allowed. The max value is <code>%d</code>. Use <code>0</code> to disable this option.', 'woo-asaas' ),
			$default_settings->get_max_installments()
		);


		if ( ! isset( $settings->gateway->settings['max_installments'] ) || 0 === $settings->gateway->settings['max_installments'] ) {
			$max_installments_message .= ' ' .  __( 'When saving the changes with the installments enabled, an interest table will be released.', 'woo-asaas' );
		}

		$default_min_installment_value = $default_settings->get_min_installment_value();

		$installments_settings = new Installments_Settings( $settings->gateway );

		$installments_fields = array(
			'max_installments'      => array(
				'title'       => __( 'Installments', 'woo-asaas' ),
				'type'        => 'text',
				'description' => $max_installments_message,
				'default'     => '0',
				'section'     => 'gateway',
				'priority'    => 20,
				'sanitize_callback' => array( $installments_settings, 'validate_max_installments_field' )
			),
			'interest_installment'  => array(
				'title'       => __( 'Interest', 'woo-asaas' ),
				'type'        => 'interest_installment',
				'section'     => 'gateway',
				'priority'    => 20,
				'sanitize_callback' => array( $installments_settings, 'validate_interest_installment_field' )
			),
			'min_installment_value' => array(
				'title'       => __( 'Minimum installment value', 'woo-asaas' ),
				'type'        => 'text',
				/* translators: %d: minimum installments value  */
				'description' => sprintf( __( 'The minimum value for each installment. The minimum value accepted by Asaas is <code>%s</code>.', 'woo-asaas' ), wc_price( $default_min_installment_value ) ),
				'default'     => $default_min_installment_value,
				'section'     => 'gateway',
				'priority'    => 20,
				'sanitize_callback' => array( $installments_settings, 'validate_min_installment_value_field' )
			),
		);

		$new_fields = array_merge( $fields, $installments_fields );

		return $new_fields;
	}
}
