<?php
/**
 * Credit Card class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Cron;

use Exception;
use WC_Asaas\Billing_Type\Pix;
use WC_Asaas\Meta_Data\Order;

/**
 * Handle checkout installments.
 */
class Expired_Pix_Cron {

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
	 * Create a custom event to run when new order was created.
	 * 
	 * Event to remove overdue Pix.
	 * @param int $order_id The order ID.
	 */
	public function schedule_remove_expired_pix( $order_id ) {
        $pix = new \WC_Asaas\Gateway\Pix;
        $payment_method = $pix->id;

        $order    = new Order( $order_id );
		$wc_order = $order->get_wc();
        $order_payment_method = $wc_order->get_payment_method();

		if ( $payment_method !== $order_payment_method ) { 
			return;
		}

        $due_time = $pix->create_due_date();
		$run_event = $due_time->getTimestamp();

		wp_schedule_single_event( $run_event, 'remove_expired_pix_asaas', array( $wc_order ) );
	}

	/**
	 * Execute the call to remove overdue Pix.
	 * 
	 * @param \WC_Order $order The order.
	 * @return string Response message with removal result.
	 */
	public function remove_expired_pix( $order ) {
		$pix = new \WC_Asaas\Gateway\Pix;
		return $pix->remove_expired_pix( $order );
	}
}