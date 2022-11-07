<?php
declare( strict_types = 1 );

namespace WP_Defender\Component\Two_Factor\Providers;

use Calotes\Helper\HTTP;
use WP_Defender\Component\Crypt;
use WP_Defender\Component\Two_Factor\Two_Factor_Provider;
use WP_User;
use WP_Error;

/**
 * Class Fallback_Email
 * @since 2.8.0
 * @package WP_Defender\Component\Two_Factor\Providers
 */
class Fallback_Email extends Two_Factor_Provider {

	/**
	 * 2fa provider slug.
	 *
	 * @var string
	 */
	public static $slug = 'fallback-email';

	/**
	 * @type string
	 */
	public const FALLBACK_EMAIL_KEY = 'defenderAuthEmail';

	/**
	 * @type string
	 */
	public const FALLBACK_BACKUP_CODE_KEY = 'defenderBackupCode';

	public function __construct() {
		add_action( 'wd_2fa_user_options_' . self::$slug, [ $this, 'user_options' ] );
	}

	/**
	 * Get the name of the provider.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Fallback Email', 'wpdef' );
	}

	/**
	 * @return string
	 */
	public function get_login_label(): string {
		return $this->get_label();
	}

	/**
	 * @return string
	 */
	public function get_user_label(): string {
		return __( 'Fallback email', 'wpdef' );
	}

	/**
	 * Get the desc of the provider.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'If you ever lose your device, you can send a fallback passcode to this email address.', 'wpdef' );
	}

	/**
	 * Display auth method.
	 *
	 * @param WP_User $user
	 */
	public function user_options( WP_User $user ) {
		$service = $this->get_component();
		$class = $service->is_checked_enabled_provider_by_slug( $user, self::$slug ) ? '' : 'hidden';
		$this->get_controller()->render_partial(
			'two-fa/providers/fallback-email',
			[
				'class' => $class,
				'backup_email' => self::get_backup_email( $user->ID ),
			]
		);
	}

	/**
	 * Whether this 2FA provider is configured and available for the user specified.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @return bool
	 */
	public function is_available_for_user( WP_User $user ): bool {
		return true;
	}

	public function authentication_form() {
		?>
		<p class="wpdef-2fa-label"><?php echo $this->get_label(); ?></p>
		<p class="wpdef-2fa-text">
			<?php echo __( "Can't access your device? We've sent a passcode to your fallback email address. Enter it below to log in to your account.", 'wpdef' ); ?>
		</p>
		<input type="text" autofocus value="" autocomplete="off" name="otp" />
		<button class="button button-primary float-r" type="submit"><?php _e( 'Authenticate', 'wpdef' ); ?></button>
		<p class="wpdef-2fa-email-resend">
			<input type="submit" class="button" name="button_resend_code" value="<?php esc_attr_e( 'Resend Code', 'wpdef' ); ?>" />
		</p>
		<?php
	}

	/**
	 * @param $user_id
	 *
	 * @return bool|string
	 */
	public static function get_backup_email( $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$email = get_user_meta( $user_id, self::FALLBACK_EMAIL_KEY, true );
		if ( empty( $email ) ) {
			$user = get_user_by( 'id', $user_id );
			if ( ! is_object( $user ) ) {
				return false;
			}
			$email = $user->user_email;
		}

		return $email;
	}

	/**
	 * Whether this 2FA provider is configured and available for the user specified.
	 *
	 * @param WP_User $user
	 *
	 * @return bool|WP_Error
	 */
	public function validate_authentication( $user ) {
		$otp = HTTP::post( 'otp', '' );
		$backup_code = get_user_meta( $user->ID, self::FALLBACK_BACKUP_CODE_KEY, true );
		if ( ! empty( $backup_code ) && Crypt::compare_lines( $backup_code['code'], wp_hash( $otp ) )
			&& strtotime( '+3 minutes', $backup_code['time'] ) > time()
		) {
			delete_user_meta( $user->ID, self::FALLBACK_BACKUP_CODE_KEY );

			return true;
		} else {
			$lockout_message = $this->get_component()->verify_attempt( $user->ID, self::$slug );

			return new WP_Error(
				'opt_fail',
				empty( $lockout_message ) ? __( 'ERROR: Invalid passcode.', 'wpdef' ) : $lockout_message
			);
		}
	}
}
