<?php
/**
 * Credit Card class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Cron;

use Exception;

/**
 * Handle checkout installments.
 */
class Expired_Ticket_Cron {

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
	 * Create a daily cron event, if one does not already exist.
	 * 
	 * Scheduling to remove overdue ticket.
	 */
	public function schedule_remove_expired_ticket() {
		if ( ! wp_next_scheduled( 'remove_expired_ticket' ) ) {
			wp_schedule_event( time(), 'daily', 'remove_expired_ticket', array() );
		}
	}

	/**
	 * Executes the call to remove overdue tickets.
	 * 
	 * @return string Response message with removal result.
	 */
	public function remove_expired_ticket() {
		$ticket = new \WC_Asaas\Gateway\Ticket;
		return $ticket->remove_expired_ticket();
	}
}