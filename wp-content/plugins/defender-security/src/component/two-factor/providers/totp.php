<?php
declare( strict_types = 1 );

namespace WP_Defender\Component\Two_Factor\Providers;

use Calotes\Helper\HTTP;
use WP_Defender\Component\Two_Fa as Two_Fa_Component;
use WP_Defender\Component\Crypt;
use WP_Defender\Component\Two_Factor\Two_Factor_Provider;
use WP_Defender\Extra\Base2n;
use WP_Defender\Traits\IO;
use WP_User;
use WP_Error;

/**
 * Class Totp
 * Note: key 'defenderAuthOn' only for TOTP method.
 *
 * @since 2.8.0
 * @package WP_Defender\Component\Two_Factor\Providers
 */
class Totp extends Two_Factor_Provider {
	use IO;

	/**
	 * 2fa provider slug.
	 *
	 * @var string
	 */
	public static $slug = 'totp';

	/**
	 * @type string
	 */
	public const TOTP_AUTH_KEY = 'defenderAuthOn';

	/**
	 * Used def.key before v3.4.0.
	 *
	 * @type string
	 */
	public const TOTP_SECRET_KEY = 'defenderAuthSecret';

	/**
	 * Use Sodium library since v3.4.0.
	 *
	 * @type string
	 */
	public const TOTP_SODIUM_SECRET_KEY = 'defenderAuthSodiumSecret';

	/**
	 * @type string
	 */
	public const TOTP_FORCE_KEY = 'defenderForceAuth';

	/**
	 * @type int
	 */
	public const TOTP_DIGIT_COUNT = 6;

	/**
	 * @type int
	 */
	public const TOTP_TIME_STEP_SEC = 30;

	/**
	 * @type int
	 */
	public const TOTP_LENGTH = 16;

	/**
	 * RFC 4648 base32 alphabet.
	 * @type string
	 */
	public const TOTP_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

	/**
	 * @type string
	 */
	public const DEFAULT_CRYPTO = 'sha1';

	protected $label;

	protected $description;

	public function __construct() {
		add_action( 'wd_2fa_init_provider_' . self::$slug, [ &$this, 'init_provider' ] );
		add_action( 'wd_2fa_user_options_' . self::$slug, [ &$this, 'user_options' ] );
	}

	/**
	 * Get the name of the provider.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'TOTP Authenticator App', 'wpdef' ) . $this->label;
	}

	/**
	 * @return string
	 */
	public function get_login_label(): string {
		return __( 'TOTP Authentication', 'wpdef' );
	}

	/**
	 * @return string
	 */
	public function get_user_label(): string {
		return __( 'TOTP', 'wpdef' );
	}

	/**
	 * Get the desc of the provider.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	public function authentication_form() {
		?>
		<p class="wpdef-2fa-label"><?php echo $this->get_login_label(); ?></p>
		<p class="wpdef-2fa-text def-otp-text"><?php echo esc_html( $this->get_model()->app_text ); ?></p>
		<input type="text" autofocus value="" autocomplete="off" name="otp" />
		<button class="button button-primary float-r" type="submit"><?php _e( 'Authenticate', 'wpdef' ); ?></button>
		<?php
	}

	/**
	 * @return array
	 */
	public function get_auth_apps(): array {
		return [
			'google-authenticator' => 'Google Authenticator',
			'microsoft-authenticator' => 'Microsoft Authenticator',
			'authy' => 'Authy',
		];
	}

	/**
	 * @param WP_User $user
	 */
	public function init_provider( WP_User $user ) {
		$is_on = $this->is_available_for_user( $user );
		$this->label = $is_on
			? sprintf(
			/* translators: %s: style class */
				__( '<button type="button" class="button reset-totp-keys button-secondary hide-if-no-js" %s>Reset Keys</button>', 'wpdef' ),
				$this->get_component()->is_checked_enabled_provider_by_slug( $user, self::$slug ) ? '' : ' disabled'
			)
			: '';
		$this->description = $is_on
			? __( 'TOTP Authentication method is active for this site', 'wpdef' )
			: __( 'Use an authenticator app to sign in with a separate passcode.', 'wpdef' );
	}

	/**
	 * Display auth method.
	 *
	 * @param WP_User $user
	 */
	public function user_options( WP_User $user ) {
		if ( ! wp_script_is( 'clipboard', 'enqueued' ) ) {
			wp_enqueue_script( 'clipboard' );
		}
		$model = $this->get_model();
		$service = $this->get_component();
		$default_values = $model->get_default_values();
		$is_on = $this->is_available_for_user( $user );
		if ( $is_on ) {
			$this->get_controller()->render_partial(
				'two-fa/providers/totp-enabled',
				[
					'url' => $this->get_url( 'disable_totp' ),
				]
			);
		} else {
			$is_success = true;
			$result = self::get_user_secret();
			if ( is_wp_error( $result ) ) {
				$secret = $result->get_error_message();
				$is_success = false;
			} elseif ( is_bool( $result ) ) {
				// Sometimes we can get a boolean value due to errors with writing to the database. In this case, we need to reset the value.
				delete_user_meta( $user->ID, self::TOTP_SECRET_KEY );
				// Also for new key.
				delete_user_meta( $user->ID, self::TOTP_SODIUM_SECRET_KEY );
				$secret = self::get_user_secret();
			} else {
				$secret = $result;
			}
			$this->get_controller()->render_partial(
				'two-fa/providers/totp-disabled',
				[
					'url' => $this->get_url( 'verify_otp_for_enabling' ),
					'default_message' => $default_values['message'],
					'auth_apps' => $this->get_auth_apps(),
					'user' => $user,
					'secret_key' => $secret,
					'class' => $service->is_checked_enabled_provider_by_slug( $user, self::$slug ) ? '' : 'hidden',
					'is_success' => $is_success,
				]
			);
		}
	}

	/**
	 * Generate a QR code for apps can use. Apps from get_auth_apps().
	 *
	 * @param string $secret_key
	 */
	public static function generate_qr_code( $secret_key ) {
		$settings = new \WP_Defender\Model\Setting\Two_Fa();
		$issuer = $settings->app_title;
		$user = wp_get_current_user();
		$chl = ( 'otpauth://totp/' . rawurlencode( $issuer ) . ':' . rawurlencode( $user->user_email ) . '?secret=' . $secret_key . '&issuer=' . rawurlencode( $issuer ) );
		require_once defender_path( 'src/extra/phpqrcode/phpqrcode.php' );

		\QRcode::svg( $chl, false, QR_ECLEVEL_L, 4 );
	}

	/**
	 * Whether this 2FA provider is configured and available for the user specified.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @return boolean
	 */
	public function is_available_for_user( WP_User $user ) {
		return (bool) get_user_meta( $user->ID, self::TOTP_AUTH_KEY, true );
	}

	/**
	 * @param WP_User $user
	 *
	 * @return bool|WP_Error
	 */
	public function validate_authentication( $user ) {
		$otp = HTTP::post( 'otp' );
		if ( empty( $otp ) ) {
			$lockout_message = $this->get_component()->verify_attempt( $user->ID, self::$slug );

			return new WP_Error(
				'opt_fail',
				empty( $lockout_message )
					? __( 'Whoops, the passcode you entered was incorrect or expired.', 'wpdef' )
					: $lockout_message
			);
		}
		// Todo: maybe new validate_code() method?
		return $this->get_component()->verify_otp( $otp, $user );
	}

	/**
	 * @param WP_User|null $user
	 *
	 * @return string|WP_Error|bool
	 */
	private static function get_user_secret( $user = null ) {
		// This should only use in testing.
		if ( is_object( $user ) ) {
			$user_id = $user->ID;
		} else {
			$user_id = get_current_user_id();
		}
		// First, we check the new 'TOTP_SODIUM_SECRET_KEY' key.
		$data = get_user_meta( $user_id, self::TOTP_SODIUM_SECRET_KEY, true );
		if ( ! empty( $data ) ) {
			return Crypt::get_decrypted_data( $data );
		}
		// Then check the old 'TOTP_SECRET_KEY' key.
		if ( ( new Two_Fa_Component() )->maybe_update( $user_id ) ) {
			// Check a new key again.
			$data = get_user_meta( $user_id, self::TOTP_SODIUM_SECRET_KEY, true );
			if ( ! empty( $data ) && is_string( $data )  ) {
				return Crypt::get_decrypted_data( $data );
			}
		}
		// Finally, add a new one.
		$plaintext = defender_generate_random_string( self::TOTP_LENGTH, self::TOTP_CHARACTERS );
		$secret = Crypt::get_encrypted_data( $plaintext );
		if ( is_wp_error( $secret ) ) {
			return $secret;
		}
		// Todo: Maybe save token only when we verify it?
		update_user_meta( $user_id, self::TOTP_SODIUM_SECRET_KEY, $secret );

		return $plaintext;
	}

	/**
	 * Generate an OTP code base on current time.
	 *
	 * @param int|null $counter
	 * @param WP_User|null $user
	 *
	 * @return string|WP_Error
	 */
	public static function generate_otp( $counter = null, $user = null ) {
		$result = self::get_user_secret( $user );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		include_once defender_path( 'src/extra/binary-to-text-php/Base2n.php' );
		$base32 = new Base2n( 5, self::TOTP_CHARACTERS, false, true, true );
		$secret = $base32->decode( $result );
		if ( is_null( $counter ) ) {
			$counter = time();
		}
		$input = floor( $counter / self::TOTP_TIME_STEP_SEC );
		// According to https://tools.ietf.org/html/rfc4226#section-5.3, should be 8 bytes value.
		$time = chr( 0 ) . chr( 0 ) . chr( 0 ) . chr( 0 ) . pack( 'N*', $input );
		$hmac = hash_hmac( self::DEFAULT_CRYPTO, $time, $secret, true );
		// Now we have 20 bytes of DEFAULT_CRYPTO, need to short it down. Getting last byte of the hmac.
		$offset = ord( substr( $hmac, - 1 ) ) & 0x0F;
		$four_bytes = substr( $hmac, $offset, 4 );
		// Now convert it into INT.
		$value = unpack( 'N', $four_bytes );
		$value = $value[1];
		// Make sure it always actual like 32 bits.
		$value = $value & 0x7FFFFFFF;
		// Close.
		$code = $value % 10 ** self::TOTP_DIGIT_COUNT;
		// In some case we have the 0 before, so it becomes lesser than TOTP_DIGIT_COUNT, make sure it always right.
		return str_pad( (string) $code, self::TOTP_DIGIT_COUNT, '0', STR_PAD_LEFT );
	}
}
