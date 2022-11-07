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
class Logger {

	/**
	 * The gateway to log
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * The WooCommerce set logger
	 *
	 * The WC_Logger object is used for the legacy versions compatibilities.
	 *
	 * @var \WC_Logger_Interface|\WC_Logger
	 */
	protected $logger;

	/**
	 * Get the logger to be used in the class
	 *
	 * @param Gateway $gateway The gateway that call the logger.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
		$this->logger  = wc_get_logger();
	}

	/**
	 * Define the source log name based in the checkout billing type
	 *
	 * @return string The source log name
	 */
	public function source() {
		return 'asaas-' . $this->gateway->get_type()->get_slug();
	}

	/**
	 * Get the log file path
	 *
	 * @return string The log file path
	 */
	public function get_log_path() {
		return \WC_Log_Handler_File::get_log_file_path( $this->source() );
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
		$this->logger->log( $level, $message, array( 'source' => $this->source() ) );
	}
}
