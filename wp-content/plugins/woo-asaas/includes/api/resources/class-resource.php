<?php
/**
 * Abastract class to represent a resource.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Resources;

use WC_Asaas\Gateway\Gateway;

/**
 * Abastract class to represent a resource.
 */
abstract class Resource {

	/**
	 * The gateway that call the resource
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param Gateway $gateway The gateway that call the resource.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Filter a JSON data to be stored in the log
	 *
	 * @param string|\stdClass $data The data to be stored.
	 * @return string|false The data encoded on string.
	 */
	public function filter_data_log( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		return wp_json_encode( $data );
	}
}
