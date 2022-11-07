<?php
/**
 * WooCommerce Subscriptions settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Subscription\Admin\Settings;

use WC_Asaas\Admin;
use Exception;
use WC_Asaas\Admin\Settings\Settings;
use WC_Asaas\Admin\View;

/**
 * Interact with WooCommerce Subscriptions settings
 */
class WooCommerce_Subscriptions_Settings {

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
	 * Forces deactivation of early renewal option from WooCommerce Subscriptions
	 */
	public function disable_early_renewal() {
		return false;
	}

	/**
	 * Shows notice informing that Asaas doesn't support the eary renewal option from WooCommerce Subscriptions
	 *
	 * @param array $settings Current settings.
	 */
	public function show_notice_unsupport_early_renewal( $settings ) {
		if ( class_exists( '\WCS_Early_Renewal_Manager' ) ) {
			if ( 'yes' === get_option( 'woocommerce_subscriptions_enable_early_renewal' ) ) {
				ob_start();
				View::get_instance()->load_template_file( 'unsupported-woocommerce-subscriptions-feature-early-renewal.php' );
				$message = ob_get_contents();
				ob_end_clean();

				$notice = array(
					array(
						'name' => '',
						'type' => 'title',
						'desc' => $message,
						'id'   => 'woo-asaas-non-support-woocommerce-subscriptions-early-renewal',
					),
				);
				return array_merge( $notice, $settings );
			}
		}
		return $settings;
	}

	/**
	 * Shows notice informing that Asaas doesn't support the manual renewal option from WooCommerce Subscriptions
	 *
	 * @param array    $settings Current settings.
	 * @param Settings $instance (optional) Asaas Settings insntace.
	 */
	public function show_notice_unsupport_manual_renewal( $settings, $instance = null ) {
		if ( function_exists( '\wcs_is_manual_renewal_enabled' ) ) {
			if ( \wcs_is_manual_renewal_enabled() ) {
				ob_start();
				View::get_instance()->load_template_file( 'unsupported-woocommerce-subscriptions-feature-manual-renewal.php' );
				$message = ob_get_contents();
				ob_end_clean();

				if ( $instance instanceof Settings ) {
					// Asaas settings page.
					$settings['woo-asaas-non-support-woocommerce-subscriptions-manual-renewal'] = array(
						'title'    => $message,
						'priority' => -10,
					);
					return $settings;
				} else {
					// WooCommerce Subscriptions settings page.
					$notice = array(
						array(
							'name' => '',
							'type' => 'title',
							'desc' => $message,
							'id'   => 'woo-asaas-non-support-woocommerce-subscriptions-manual-renewal',
						),
					);
					return array_merge( $notice, $settings );
				}
			}
		}
		return $settings;
	}

	/**
	 * Shows notice informing that Asaas doesn't support the auto renewal toggle option from WooCommerce Subscriptions
	 *
	 * @param array    $settings Current settings.
	 * @param Settings $instance (optional) Asaas Settings insntace.
	 */
	public function show_notice_unsupport_auto_renewal_toggle( $settings, $instance = null ) {
		if ( class_exists( '\WCS_My_Account_Auto_Renew_Toggle' ) ) {
			if ( \WCS_My_Account_Auto_Renew_Toggle::is_enabled() ) {
				ob_start();
				View::get_instance()->load_template_file( 'unsupported-woocommerce-subscriptions-feature-auto-renewal-toggle.php' );
				$message = ob_get_contents();
				ob_end_clean();

				if ( $instance instanceof Settings ) {
					// Asaas settings page.
					$settings['woo-asaas-non-support-woocommerce-subscriptions-auto-renewal-toggle'] = array(
						'title'    => $message,
						'priority' => -10,
					);
					return $settings;
				} else {
					// WooCommerce Subscriptions settings page.
					$notice = array(
						array(
							'name' => '',
							'type' => 'title',
							'desc' => $message,
							'id'   => 'woo-asaas-non-support-woocommerce-subscriptions-auto-renewal-toggle',
						),
					);
					return array_merge( $notice, $settings );
				}
			}
		}
		return $settings;
	}

}
