<?php
/**
 * Installments Checkout class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Checkout;

use WC_Asaas\Api\Api_Limit;
use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Installments\Admin\Settings\Default_Installment_Settings;
use WC_Asaas\Installments\Helper\Installments_Calculator_Helper;
use WC_Asaas\Installments\Helper\Values_Formater_Helper;

/**
 * Handle checkout installments.
 */
class Installments_Checkout {

	/**
	 * The billing type object data
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * Init the default field sections
	 *
	 * @param Gateway $gateway The gateway that call the logger.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Defines the information for the installment field.
	 *
	 * @param string $field_prefix
	 * @return array
	 */
	public function get_installments_field( string $field_prefix ) : array {
		$field = array(
			'type'     => 'select',
			'label'    => __( 'Installments', 'woo-asaas' ),
			'required' => false,
			'class'    => array( 'asaas_field_' . $field_prefix ),
			'id'       => "asaas-{$field_prefix}-installments",
			'options'  => $this->get_option_list(),
		);

		return $field;
	}

	/**
	 * Returns the options list for the intallments field.
	 *
	 * @return array
	 */
	private function get_option_list() : array {
		$options      = array();
		$installments = $this->get_installment_list();

		foreach ( $installments as $installment => $value ) {
			if ( false === $this->have_interest_on_installments( $installment ) ) {
				$options[ $installment ] = $this->get_installment_option( $installment, $value );
				continue;
			}

			$options[ $installment ] = $this->get_installment_option_with_interest( $installment, $value );
		}

		return $options;
	}

	/**
	 * Get the minimum installment amount.
	 *
	 * @return float
	 */
	private function get_min_installment_value() : float {
		$api_limit = new Api_Limit();
		$default_settings = new Default_Installment_Settings( $this->gateway, $api_limit );
		$default_min_installment_value = $default_settings->get_min_installment_value();

		$min_installment_value = $this->gateway->settings['min_installment_value'];
		if ( null === $min_installment_value ) {
			return $default_min_installment_value;
		}

		$min_installment_value = ( new Values_Formater_Helper() )->format_to_float_value( $this->gateway->settings['min_installment_value'] );
		$min_installment_value = max( $min_installment_value, $default_min_installment_value );

		return $min_installment_value;
	}

	/**
	 * Returns the value to display in the installment field.
	 *
	 * @param int $installment Installment number.
	 * @param float $value     Value of current installment.
	 * @return string
	 */
	private function get_installment_option( int $installment, float $value ) : string {
		$option_value = sprintf(
			'%dx %s',
			$installment,
			strip_tags( wc_price( $value ) )
		);

		return $option_value;
	}

	/**
	 * Returns the value to display in the installment field with interest information.
	 *
	 * @param int $installment Installment number.
	 * @param float $value     Value of current installment.
	 * @return string
	 */
	private function get_installment_option_with_interest( int $installment, float $value ) : string {
		$interest_installment = $this->gateway->settings['interest_installment'];
		$interest_value       = $interest_installment[ $installment ];
		$installment_value    = ( new Installments_Calculator_Helper() )->get_value_with_interest( $value, $interest_value );
		$interest_value       = ( new Values_Formater_Helper() )->replace_dot_with_comma( $interest_value );

		$option_value = sprintf(
			/* translators: %d: installment number, %s: installment value, %s: interest value */
			__( '%dx %s (%s%% interest)', 'woo-asaas' ),
			$installment,
			strip_tags( wc_price( $installment_value ) ),
			$interest_value
		);

		return $option_value;
	}

	/**
	 * Check if there is interest on the installment.
	 *
	 * @param int $index
	 * @return bool
	 */
	public function have_interest_on_installments( int $index ) : bool {
		if ( false === isset( $this->gateway->settings['interest_installment'] ) ) {
			return false;
		}

		$interest_installment = $this->gateway->settings['interest_installment'];

		if ( true === empty( $interest_installment ) ) {
			return false;
		}

		if ( 0.0 === floatval( $interest_installment[ $index ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the installment list with the number of installments and the value
	 * for each ones
	 *
	 * @return array The list with the intallment count (key) and the
	 *               value of each installment (value).
	 */
	private function get_installment_list() {
		// Disables installments if order/cart contains subscription.
		if ( $this->gateway->order_has_subscription() ) {
			return array();
		}

		$total            = $this->gateway->get_order_total();
		$max_installments = absint( $this->gateway->settings['max_installments'] );

		$min_installment_value = $this->get_min_installment_value();
		$installments_qty = ( new Installments_Calculator_Helper() )->calculate_max_installment_qty( $total, $max_installments, $min_installment_value );

		$installments_list = array();
		for ( $i = 1; $i <= $installments_qty; $i++ ) {
			$installments_list[ $i ] = ( new Installments_Calculator_Helper() )->calculate_installment( $total, $i );
		}

		return $installments_list;
	}
}
