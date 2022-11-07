<?php
declare( strict_types=1 );

namespace WP_Defender\Model\Setting;

use Calotes\Model\Setting;

class Recaptcha extends Setting {
	/**
	 * Option name.
	 * @var string
	 */
	public $table = 'wd_recaptcha_settings';

	/**
	 * Main switch of this function.
	 * @var bool
	 * @defender_property
	 */
	public $enabled = false;

	/**
	 * @var string
	 * @defender_property
	 * @rule required
	 * @rule in[v2_checkbox,v2_invisible,v3_recaptcha]
	 */
	public $active_type = 'v2_checkbox';

	/**
	 * @var array
	 * @defender_property
	 */
	public $data_v2_checkbox;

	/**
	 * @var array
	 * @defender_property
	 */
	public $data_v2_invisible;

	/**
	 * @var array
	 * @defender_property
	 */
	public $data_v3_recaptcha;

	/**
	 * @var string
	 * @defender_property
	 * @rule required
	 */
	public $language = '';

	/**
	 * @var string
	 * @defender_property
	 */
	public $message = '';

	/**
	 * @var array
	 * @defender_property
	 * @rule required
	 */
	public $locations = [];

	/**
	 * @var bool
	 * @defender_property
	 */
	public $detect_woo = false;

	/**
	 * @var array
	 * @defender_property
	 * @rule required
	 */
	public $woo_checked_locations = [];

	/**
	 * @var bool
	 * @defender_property
	 */
	public $detect_buddypress = false;

	/**
	 * @var array
	 * @defender_property
	 * @rule required
	 */
	public $buddypress_checked_locations = [];

	protected $rules = [
		[ [ 'enabled', 'detect_woo', 'detect_buddypress' ], 'boolean' ],
		[ [ 'active_type' ], 'in', [ 'v2_checkbox', 'v2_invisible', 'v3_recaptcha' ] ],
	];

	/**
	 * @return array
	 */
	public function get_default_values(): array {
		return [
			'message' => __( 'reCAPTCHA verification failed. Please try again.', 'wpdef' ),
		];
	}

	protected function before_load(): void {
		$default_values = $this->get_default_values();
		$this->message = $default_values['message'];
		$this->language = 'automatic';
		$this->data_v2_checkbox = [
			'key' => '',
			'secret' => '',
			'size' => 'normal',
			'style' => 'light',
		];
		$this->data_v2_invisible = [
			'key' => '',
			'secret' => '',
		];
		$this->data_v3_recaptcha = [
			'key' => '',
			'secret' => '',
			'threshold' => '0.5',
		];
	}

	/**
	 * @param string $active_type
	 *
	 * @return bool
	 */
	private function check_recaptcha_type( $active_type ): bool {
		if (
			'v2_checkbox' === $active_type
			&& ! empty( $this->data_v2_checkbox['key'] )
			&& ! empty( $this->data_v2_checkbox['secret'] )
		) {
			return true;
		} elseif (
			'v2_invisible' === $active_type
			&& ! empty( $this->data_v2_invisible['key'] )
			&& ! empty( $this->data_v2_invisible['secret'] )
		) {
			return true;
		} elseif (
			'v3_recaptcha' === $active_type
			&& ! empty( $this->data_v3_recaptcha['key'] ) && ! empty( $this->data_v3_recaptcha['secret'] )
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function is_active(): bool {
		return (bool) apply_filters(
			'wd_recaptcha_enable',
			$this->enabled
			&& '' !== $this->active_type
			&& '' !== $this->language
			// For each Recaptcha type.
			&& $this->check_recaptcha_type( $this->active_type )
		);
	}

	/**
	 * Is activated any default location?
	 *
	 * @return bool
	 */
	public function enable_default_location(): bool {
		return ! empty( $this->locations );
	}

	/**
	 * Level#2 check by any activated location. Only if the plugin is enabled.
	 *
	 * @return bool
	 */
	public function enable_woo_location(): bool {
		return $this->detect_woo && ! empty( $this->woo_checked_locations );
	}

	/**
	 * Level#2 check by deactivated locations. Only if the plugin is enabled.
	 *
	 * @return bool
	 */
	public function is_unchecked_woo_locations(): bool {
		return $this->detect_woo && empty( $this->woo_checked_locations );
	}

	/**
	 * Level#1 check. If the plugin is disabled, there is no point further.
	 *
	 * @param bool $is_woo_activated
	 *
	 * @return bool
	 */
	public function check_woo_locations( $is_woo_activated ): bool {
		if ( ! $is_woo_activated ) {
			return false;
		}

		return $this->enable_woo_location();
	}

	/**
	 * Level#2 check by any activated location. Only if the plugin is enabled.
	 *
	 * @return bool
	 */
	public function enable_buddypress_location(): bool {
		return $this->detect_buddypress && ! empty( $this->buddypress_checked_locations );
	}

	/**
	 * Level#2 check by deactivated locations. Only if the plugin is enabled.
	 *
	 * @return bool
	 */
	public function is_unchecked_buddypress_locations(): bool {
		return  $this->detect_buddypress && empty( $this->buddypress_checked_locations );
	}

	/**
	 * Level#1 check. If the plugin is disabled, there is no point further.
	 *
	 * @param bool $is_buddypress_activated
	 *
	 * @return bool
	 */
	public function check_buddypress_locations( $is_buddypress_activated ): bool {
		if ( ! $is_buddypress_activated ) {
			return false;
		}

		return $this->enable_buddypress_location();
	}

	/**
	 * Define settings labels.
	 *
	 * @return array
	 */
	public function labels(): array {
		return [
			'active_type' => __( 'Configure reCaptcha', 'wpdef' ),
			'language' => __( 'Language', 'wpdef' ),
			'message' => __( 'Error Message', 'wpdef' ),
			'locations' => __( 'CAPTCHA Locations', 'wpdef' ),
			'detect_woo' => __( 'WooCommerce', 'wpdef' ),
			'detect_buddypress' => __( 'BuddyPress', 'wpdef' ),
		];
	}

	protected function after_validate(): void {
		// Case with multi errors.
		if ( $this->is_unchecked_woo_locations() && $this->is_unchecked_buddypress_locations() ) {
			// The text of the notation is only in the first key, but we add the number of keys depending on the disabled locations of the plugins.
			$this->errors['enable_woo'] = __( 'You have enabled reCaptcha for more than one plugin. Please select at least one form location for each plugin and click Save Changes again.', 'wpdef' );
			$this->errors['enable_buddypress'] = '';
		} else {
			// Individual cases with plugins.
			if ( $this->is_unchecked_woo_locations() ) {
				$this->errors['enable_woo'] = __( 'reCAPTCHA for WooCommerce is enabled, but no WooCommerce forms are selected. Please select at least one WooCommerce form location and then click Save Changes again.', 'wpdef' );
			}
			if ( $this->is_unchecked_buddypress_locations() ) {
				$this->errors['enable_buddypress'] = __( 'reCAPTCHA for BuddyPress is enabled, but no BuddyPress forms are selected. Please select at least one BuddyPress form location and then click Save Changes again.', 'wpdef' );
			}
		}
	}
}
