<?php
declare( strict_types = 1 );

namespace WP_Defender\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Buddypress integration module.
 * Class Buddypress
 *
 * @since 3.3.0
 * @package WP_Defender\Integrations
 */
class Buddypress {
	public const REGISTER_FORM = 'buddypress_register', NEW_GROUP_FORM = 'buddypress_new group';

	/**
	 * Check if Buddypress is activated.
	 *
	 * @return bool
	 */
	public function is_activated(): bool {
		return class_exists( 'buddypress' );
	}

	/**
	 * @return array
	 */
	public static function get_forms(): array {
		return [
			self::REGISTER_FORM => __( 'Registration', 'wpdef' ),
			self::NEW_GROUP_FORM => __( 'Add new group', 'wpdef' ),
		];
	}
}
