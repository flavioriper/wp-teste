<?php
/**
 * Coupon class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Coupon;

use Exception;
use WC_Asaas\Helper\Subscriptions_Helper;

/**
 * Coupon functions and validations
 */
class Coupon {

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
	 * Shows the list of supported coupon types on coupon admin page
	 *
	 * @return void
	 */
	public function show_notice_about_supported_coupon_types() {
		$subscriptions_helper = new Subscriptions_Helper();
		?>
		<div class="asaas-supported-coupon-types">
			<hr>
			<p>
				<span class="dashicons-before dashicons-warning"></span>
				<strong>
					<?php
					/* translators: %s: coupon types */
					printf( esc_html__('Asaas gateway currently supports the following coupon types: %s.', 'woo-asaas'), $subscriptions_helper->get_supported_coupon_types_string());
					?>
				</strong>
			</p>
		</div>
		<?php
	}

}
