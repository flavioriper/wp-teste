<?php
/**
 * Product settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Product\Admin\Settings;

use Exception;
use WC_Asaas\Helper\Subscriptions_Helper;

/**
 * Product settings
 */
class Product_Settings {

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
	 * Shows tip with Asaas gateway supported billing cycles
	 */
	public function show_tip_supported_billing_cycles() {
		$subscriptions_helper = new Subscriptions_Helper();
		?>
		<div class="options_group show_if_subscription hidden subscription_pricing">
			<p>
				<span class="dashicons-before dashicons-warning"></span>
				<strong>
					<?php 
					/* translators: %s: billing cycles */
					printf( esc_html__('Asaas gateway currently supports the following billing cycles: %s.', 'woo-asaas'), $subscriptions_helper->get_supported_billing_periods_string());
					?>
				</strong>
			</p>
		</div>
		<?php
	}
}
	