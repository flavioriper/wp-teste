<?php
declare( strict_types = 1 );

namespace WP_Defender\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Woocommerce integration module.
 * Class Woocommerce
 *
 * @since 2.6.1
 * @since 3.3.0 Add locations.
 * @package WP_Defender\Integrations
 */
class Woocommerce {
	public const WOO_LOGIN_FORM = 'woo_login',
		WOO_REGISTER_FORM = 'woo_register',
		WOO_LOST_PASSWORD_FORM = 'woo_lost_password',
		WOO_CHECKOUT_FORM = 'woo_checkout';

	/**
	 * Check if Woo is activated.
	 *
	 * @return bool
	 */
	public function is_activated(): bool {
		return class_exists( 'woocommerce' );
	}

	/**
	 * @return array
	 */
	public static function get_forms(): array {
		return [
			self::WOO_LOGIN_FORM => __( 'Login', 'wpdef' ),
			self::WOO_REGISTER_FORM => __( 'Registration', 'wpdef' ),
			self::WOO_LOST_PASSWORD_FORM => __( 'Lost Password', 'wpdef' ),
			self::WOO_CHECKOUT_FORM => __( 'Checkout', 'wpdef' ),
		];
	}
}
