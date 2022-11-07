<?php
/**
 * Saas API.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api;

use WC_Asaas\Gateway\Gateway;

/**
 * Saas API.
 */
class Api {

	/**
	 * The gateway that loaded the API
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * Instantiate the API
	 *
	 * @param string $gateway The payment gateway.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * API Customers resource.
	 *
	 * @return Resources\Customers
	 */
	public function customers() {
		return new Resources\Customers( $this->gateway );
	}

	/**
	 * API Payments resource.
	 *
	 * @return Resources\Payments
	 */
	public function payments() {
		return new Resources\Payments( $this->gateway );
	}

	/**
	 * API Subscriptions resource.
	 *
	 * @return Resources\Subscriptions
	 */
	public function subscriptions() {
		return new Resources\Subscriptions( $this->gateway );
	}

	/**
	 * API Credit Card resource.
	 *
	 * @return Resources\Credit_Card
	 */
	public function credit_card() {
		return new Resources\Credit_Card( $this->gateway );
	}
}
