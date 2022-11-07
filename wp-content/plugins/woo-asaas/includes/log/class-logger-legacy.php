<?php
/**
 * Logger class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Log;

use WC_Asaas\Gateway\Gateway;

/**
 * Log Asaas interation with API and webhook
 */
class Logger_Legacy extends Logger {

	/**
	 * Get the logger to be used in the class
	 *
	 * @param Gateway $gateway The gateway that call the logger.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
		$this->logger  = new \WC_Logger();
	}

	/**
	 * Get the log file path
	 *
	 * @return string The log file path
	 */
	public function get_log_path() {
		return wc_get_log_file_path( $this->source() );
	}

	/**
	 * Logging method
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. emergency|alert|critical|error|warning|notice|info|debug. Default 'info'.
	 */
	public function log( $message, $level = 'info' ) {
		if ( 'no' === $this->gateway->get_option( 'debug' ) ) {
			return;
		}

		$message = apply_filters( 'woocommerce_asaas_log_message', preg_replace( '/\s+/', ' ', $message ) );
		$this->logger->add( $this->source(), '[' . $level . ']' . $message );
	}
}
