<?php
/**
 * Checkout Installments class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Gateway;

use Exception;
use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Installments\Checkout\Installments_Checkout;

/**
 * Handle init checkout installments.
 */
class Checkout_Installments {
	
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
	 * Add installment field to ticket checkout form.
	 *
     * @param array $fields Gateway fields.
	 * @param Gateway fields Ticket Gateway.
     * @return array
	 */
	public function add_ticket_installment_field( array $fields, Gateway $gateway ) {
		$field_prefix = 'ticket';
		$installments = $this->get_installment_field( $field_prefix, $gateway );
		
		$fields['asaas_ticket_installments'] = $installments;
		
		return $fields;
	}
	
	/**
	 * Get installment field from gateway.
	 * 
	 * @param string $field_prefix Payment gateway prefix.
	 * @param Gateway fields Payment gateway.
	 * @return array
	 */
	private function get_installment_field( string $field_prefix, Gateway $gateway ) : array {
		$installments_checkout = new Installments_Checkout( $gateway );
		$installments          = $installments_checkout->get_installments_field( $field_prefix );
		
		return $installments;
	}

    /**
	 * Add installment field to credit card checkout form.
	 *
     * @param array $fields Gateway fields.
	 * @param Gateway fields Ticket Gateway.
     * @return array
	 */
	public function add_cc_installment_field( array $fields, Gateway $gateway ) {
		$field_prefix = 'cc';
		$installments = $this->get_installment_field( $field_prefix, $gateway );
		
		$fields['asaas_cc_installments'] = $installments;

		return $fields;
	}
}