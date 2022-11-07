<?php
declare( strict_types = 1 );

namespace WP_Defender\Controller;

use Calotes\Component\Request;
use Calotes\Component\Response;
use WP_Defender\Model\Setting\Recaptcha as Recaptcha_Model;
use WP_Defender\Component\Config\Config_Hub_Helper;
use WP_Defender\Component\Recaptcha as Recaptcha_Component;
use WP_Defender\Controller;
use WP_Defender\Traits\Hummingbird;
use WP_Error;
use WP_Defender\Integrations\Woocommerce;
use WP_Defender\Integrations\Buddypress;

/**
 * Class Recaptcha
 *
 * @package WP_Defender\Controller
 * @since 2.5.4
 */
class Recaptcha extends Controller {
	use Hummingbird;

	/**
	 * Accepted values: v2_checkbox, v2_invisible, v3_recaptcha.
	 * @var string
	 */
	private $recaptcha_type;

	/**
	 * Accepted values: light and dark.
	 * @var string
	 */
	private $recaptcha_theme;

	/**
	 * Accepted values: normal and compact.
	 * @var string
	 */
	private $recaptcha_size;

	/**
	 * @var string
	 */
	private $public_key;

	/**
	 * @var string
	 */
	private $private_key;

	/**
	 * @var string
	 */
	private $language;

	/**
	 * @var string
	 */
	private $default_msg;

	/**
	 * Use for cache.
	 *
	 * @var Recaptcha_Model
	 */
	public $model;

	/**
	 * @var Recaptcha_Component
	 */
	protected $service;

	/**
	 * @var bool
	 */
	private $is_woo_activated;

	/**
	 * @var bool
	 */
	private $is_buddypress_activated;

	public function __construct() {
		$this->model = wd_di()->get( Recaptcha_Model::class );
		$this->service = new Recaptcha_Component( $this->model );
		// Use default msg to avoid empty message error.
		$default_values = $this->model->get_default_values();
		$this->default_msg = $default_values['message'];
		$this->register_routes();
		$this->is_woo_activated = wd_di()->get( Woocommerce::class )->is_activated();
		$this->is_buddypress_activated = wd_di()->get( Buddypress::class )->is_activated();
		add_filter( 'wp_defender_advanced_tools_data', [ $this, 'script_data' ] );

		if (
			$this->model->is_active()
			// No need the check by Woo and Buddypress are activated because we use this below.
			&& $this->service->enable_any_location( $this->is_woo_activated, $this->is_buddypress_activated )
			&& ! $this->service->exclude_recaptcha_for_requests()
		) {
			$this->declare_variables();
			$this->add_actions();

			add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 10, 2 );
		}
	}

	/**
	 * @return void
	 */
	protected function declare_variables(): void {
		$this->recaptcha_type = $this->model->active_type;
		$this->recaptcha_theme = 'light';
		$this->recaptcha_size = 'invisible';
		$this->language = ! empty( $this->model->language ) && 'automatic' !== $this->model->language
			? $this->model->language
			: get_locale();

		// Add the reCAPTCHA keys depending on the reCAPTCHA type.
		if ( 'v2_checkbox' === $this->recaptcha_type ) {
			$this->public_key = $this->model->data_v2_checkbox['key'];
			$this->private_key = $this->model->data_v2_checkbox['secret'];
			$this->recaptcha_theme = $this->model->data_v2_checkbox['style'];
			$this->recaptcha_size = $this->model->data_v2_checkbox['size'];
		} elseif ( 'v2_invisible' === $this->recaptcha_type ) {
			$this->public_key = $this->model->data_v2_invisible['key'];
			$this->private_key = $this->model->data_v2_invisible['secret'];
		} elseif ( 'v3_recaptcha' === $this->recaptcha_type ) {
			$this->public_key = $this->model->data_v3_recaptcha['key'];
			$this->private_key = $this->model->data_v3_recaptcha['secret'];
		}
	}

	/**
	 * @return null|void
	 */
	protected function add_actions() {
		$extra_conditions = is_admin() && ! defined( 'DOING_AJAX' );
		// @since 2.5.6
		do_action( 'wd_recaptcha_before_actions', $extra_conditions );
		if ( $extra_conditions ) {
			return;
		}
		$is_user_logged_in = is_user_logged_in();
		$locations = $this->model->locations;
		// Default login form.
		if ( in_array( Recaptcha_Component::DEFAULT_LOGIN_FORM, $locations, true ) ) {
			add_action( 'login_form', [ $this, 'display_login_recaptcha' ] );
			add_filter( 'wp_authenticate_user', [ $this, 'validate_captcha_field_on_login' ], 8 );
		}
		// Default register form.
		if ( in_array( Recaptcha_Component::DEFAULT_REGISTER_FORM, $locations, true ) ) {
			if ( ! is_multisite() ) {
				add_action( 'register_form', [ $this, 'display_login_recaptcha' ] );
				add_filter( 'registration_errors', [ $this, 'validate_captcha_field_on_registration' ], 10 );
			} else {
				add_action( 'signup_extra_fields', [ $this, 'display_signup_recaptcha' ] );
				add_action( 'signup_blogform', [ $this, 'display_signup_recaptcha' ] );
				add_filter( 'wpmu_validate_user_signup', [ $this, 'validate_captcha_field_on_wpmu_registration' ], 10 );
			}
		}
		// Default lost password form.
		if ( in_array( Recaptcha_Component::DEFAULT_LOST_PASSWORD_FORM, $locations, true ) ) {
			add_action( 'lostpassword_form', [ $this, 'display_login_recaptcha' ] );
			if ( $this->maybe_validate_captcha_for_lostpassword() ) {
				add_action( 'lostpassword_post', [ $this, 'validate_captcha_field_on_lostpassword' ] );
			}
		}
		// Default comment form.
		if ( ! $is_user_logged_in && in_array( Recaptcha_Component::DEFAULT_COMMENT_FORM, $locations, true ) ) {
			add_action( 'comment_form_after_fields', [ $this, 'display_comment_recaptcha' ] );
			add_action( 'pre_comment_on_post', [ $this, 'validate_captcha_field_on_comment' ] );
			// When comments are loaded via Hummingbird's lazy load feature.
			if ( $this->is_lazy_load_comments_enabled() ) {
				add_action( 'wp_footer', [ $this, 'add_scripts_for_lazy_load' ] );
			}
		}
		// Todo: move code to related class.
		// For Woo forms. Mandatory check for the activated Woo before.
		if ( $this->model->check_woo_locations( $this->is_woo_activated ) ) {
			$woo_locations = $this->model->woo_checked_locations;
			// Woo login form.
			if ( in_array( Woocommerce::WOO_LOGIN_FORM, $woo_locations, true ) ) {
				add_action( 'woocommerce_login_form', [ $this, 'display_login_recaptcha' ] );
				add_filter( 'woocommerce_process_login_errors', [ $this, 'validate_captcha_field_on_woo_login' ], 10 );
			}
			// Woo register form.
			if ( in_array( Woocommerce::WOO_REGISTER_FORM, $woo_locations, true ) ) {
				add_action( 'woocommerce_register_form', [ $this, 'display_login_recaptcha' ] );
				add_filter( 'woocommerce_registration_errors', [ $this, 'validate_captcha_field_on_woo_registration' ], 10 );
			}
			// Woo lost password form.
			if ( in_array( Woocommerce::WOO_LOST_PASSWORD_FORM, $woo_locations, true ) ) {
				add_action( 'woocommerce_lostpassword_form', [ $this, 'display_login_recaptcha' ] );
				// Use default WP hook because Woo doesn't have own hook, so there's the extra check for Woo form.
				if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {
					add_action( 'lostpassword_post', [ $this, 'validate_captcha_field_on_lostpassword' ] );
				}
			}
			// Woo checkout form.
			if ( in_array( Woocommerce::WOO_CHECKOUT_FORM, $woo_locations, true ) ) {
				add_action( 'woocommerce_after_checkout_billing_form', [ $this, 'display_login_recaptcha' ] );
				add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_captcha_field_on_woo_checkout' ], 10, 2 );
			}
		}
		// Todo: move code to related class.
		// For BuddyPress forms. Mandatory check for the activated BuddyPress before.
		if ( $this->model->check_buddypress_locations( $this->is_buddypress_activated ) ) {
			$buddypress_locations = $this->model->buddypress_checked_locations;
			// Register form.
			if ( in_array( Buddypress::REGISTER_FORM, $buddypress_locations, true ) ) {
				add_action( 'bp_before_registration_submit_buttons', [ $this, 'display_buddypress_recaptcha' ] );
				add_filter( 'bp_signup_validate', [ $this, 'validate_captcha_field_on_buddypress_registration' ], 10 );
			}
			// Group form.
			if ( in_array( Buddypress::NEW_GROUP_FORM, $buddypress_locations, true ) ) {
				add_action( 'bp_after_group_details_creation_step', [ $this, 'display_login_recaptcha' ] );
				add_action( 'groups_group_before_save', [ $this, 'validate_captcha_field_on_buddypress_group' ] );
			}
		}
		// @since 2.5.6
		do_action( 'wd_recaptcha_after_actions', $is_user_logged_in );
	}

	/**
	 * Add the async and defer tag.
	 * @param string $tag
	 * @param string $handle
	 *
	 * @return string
	 */
	public function script_loader_tag( string $tag, string $handle ): string {
		if ( 'wpdef_recaptcha_api' === $handle ) {
			$tag = str_replace( ' src', ' data-cfasync="false" async="async" defer="defer" src', $tag );
		}

		return $tag;
	}

	/**
	 * @return null|string
	 */
	protected function get_api_url(): ?string {
		$api_url = null;
		if ( isset( $this->recaptcha_type ) &&
			in_array( $this->recaptcha_type, [ 'v2_checkbox', 'v2_invisible' ], true )
		) {
			$api_url = sprintf( 'https://www.google.com/recaptcha/api.js?hl=%s&render=explicit', $this->language );
		}
		if ( isset( $this->recaptcha_type ) && 'v3_recaptcha' === $this->recaptcha_type ) {
			$api_url = sprintf( 'https://www.google.com/recaptcha/api.js?hl=%s&render=%s', $this->language, $this->public_key );
		}
		return $api_url;
	}

	/**
	 * @return void
	 */
	public function add_scripts(): void {
		if ( isset( $this->recaptcha_type ) ) {
			$this->service->remove_dublicate_scripts();
		}

		wp_enqueue_script( 'wpdef_recaptcha_script', plugins_url( 'assets/js/recaptcha_frontend.js', WP_DEFENDER_FILE ), [ 'jquery', 'wpdef_recaptcha_api' ], DEFENDER_VERSION, true );
		// @since 2.5.6
		do_action( 'wd_recaptcha_extra_assets' );

		$error_text = __( 'More than one reCAPTCHA has been found in the current form. Please remove all unnecessary reCAPTCHA fields to make it work properly.', 'wpdef' );
		$options = [
			'hl' => $this->language,
			'size' => $this->recaptcha_size,
			'version' => $this->recaptcha_type,
			'sitekey' => $this->public_key,
			'error' => sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Warning', 'wpdef' ), $error_text ),
			// For default comment form.
			'disable' => '',
		];

		if ( 'v2_checkbox' === $this->recaptcha_type ) {
			$options['theme'] = $this->recaptcha_theme;
		}

		wp_localize_script(
			'wpdef_recaptcha_script',
			'WPDEF',
			[
				'options' => $options,
				'vars' => [
					'visibility' => ( 'login_footer' === current_filter() ),
				],
			]
		);
	}

	/**
	 * Add scripts when comments are lazy loaded.
	 *
	 * @return void
	 * @since 2.6.1
	 */
	public function add_scripts_for_lazy_load(): void {
		if (
			in_array( $this->recaptcha_type, [ 'v2_checkbox', 'v2_invisible' ], true )
			&& ( is_single() || is_page() )
			&& comments_open()
		) {
			if ( ! wp_script_is( 'wpdef_recaptcha_api', 'registered' ) ) {
				$api_url = $this->get_api_url();
				$deps = [ 'jquery' ];
				wp_register_script( 'wpdef_recaptcha_api', $api_url, $deps, DEFENDER_VERSION, true );
			}

			$this->add_scripts();
		}
	}

	/**
	 * Display the reCAPTCHA field.
	 *
	 * @return void
	 */
	public function display_login_recaptcha(): void {
		if ( 'v2_checkbox' === $this->recaptcha_type ) {
				$from_width = 302; ?>
				<style media="screen">
					.login-action-login #loginform,
					.login-action-lostpassword #lostpasswordform,
					.login-action-register #registerform {
						width: <?php echo $from_width; ?>px !important;
					}
					#login_error,
					.message {
						width: <?php echo $from_width + 20; ?>px !important;
					}
					.login-action-login #loginform .recaptcha_wrap,
					.login-action-lostpassword #lostpasswordform .recaptcha_wrap,
					.login-action-register #registerform .recaptcha_wrap {
						margin-bottom: 10px;
					}
					#group-create-body .recaptcha_wrap {
						margin-top: 15px;
					}
				</style>
			<?php
		} elseif ( 'v2_invisible' === $this->recaptcha_type ) {
			?>
			<style>
				.login-action-lostpassword #lostpasswordform .recaptcha_wrap,
				.login-action-login #loginform .recaptcha_wrap,
				.login-action-register #registerform .recaptcha_wrap {
					margin-bottom: 10px;
				}
				#signup-content .recaptcha_wrap,
				#group-create-body .recaptcha_wrap {
					margin-top: 10px;
				}
			</style>
			<?php
		}
		echo $this->display_recaptcha();
	}

	/**
	 * Display the output of the recaptcha.
	 *
	 * @return string
	 */
	protected function display_recaptcha(): string {
		$deps = null;
		$content = '<div class="recaptcha_wrap wpdef_recaptcha_' . $this->recaptcha_type . '">';
		if ( ! $this->private_key || ! $this->public_key || empty( $this->recaptcha_type ) ) {
			// Display nothing.
			$content .= '</div>';

			return $content;
		}

		$api_url = $this->get_api_url();

		// Generate random id value if there's content with pagination plugin for not getting duplicate id values.
		$id = random_int( 0, mt_getrandmax() );
		if ( in_array( $this->recaptcha_type, [ 'v2_checkbox', 'v2_invisible' ], true ) ) {
			$content .= '<div id="wpdef_recaptcha_' . $id . '" class="wpdef_recaptcha"></div>
			<noscript>
				<div style="width: 302px;">
					<div style="width: 302px; height: 422px; position: relative;">
						<div style="width: 302px; height: 422px; position: absolute;">
							<iframe src="https://www.google.com/recaptcha/api/fallback?k=' . $this->public_key . '" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
						</div>
					</div>
					<div style="border-style: none; bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px; height: 60px; width: 300px;">
						<textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px !important; height: 40px !important; border: 1px solid #c1c1c1 !important; margin: 10px 25px !important; padding: 0px !important; resize: none !important;"></textarea>
					</div>
				</div>
			</noscript>';
			$deps = [ 'jquery' ];
		} elseif ( 'v3_recaptcha' === $this->recaptcha_type ) {
			$content .= '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" />';
		}
		$content .= '</div>';

		// Register reCAPTCHA script.
		$locations = $this->model->locations;
		if ( ! wp_script_is( 'wpdef_recaptcha_api', 'registered' ) ) {

			if ( 'v3_recaptcha' === $this->recaptcha_type ) {
				wp_register_script( 'wpdef_recaptcha_api', $api_url, false, null, false );
			} else {
				wp_register_script( 'wpdef_recaptcha_api', $api_url, $deps, DEFENDER_VERSION, true );
			}
			add_action( 'wp_footer', [ $this, 'add_scripts' ] );
			if (
				in_array( Recaptcha_Component::DEFAULT_LOGIN_FORM, $locations, true )
				|| in_array( Recaptcha_Component::DEFAULT_REGISTER_FORM, $locations, true )
				|| in_array( Recaptcha_Component::DEFAULT_LOST_PASSWORD_FORM, $locations, true )
			) {
				add_action( 'login_footer', [ $this, 'add_scripts' ] );
			}
		}

		return $content;
	}

	/**
	 * Check the current page from is from the Woo plugin.
	 *
	 * @retun bool
	 */
	protected function is_woocommerce_page(): bool {
		if ( ! $this->is_woo_activated ) {
			return false;
		}

		$traces = debug_backtrace();
		foreach ( $traces as $trace ) {
			if ( isset( $trace['file'] ) && false !== strpos( $trace['file'], 'woocommerce' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Verify the recaptcha code on the Login page.
	 *
	 * @param \WP_User|WP_Error $user
	 *
	 * @return WP_Error|\WP_User
	 */
	public function validate_captcha_field_on_login( $user ) {
		if ( $this->is_woocommerce_page() ) {
			return $user;
		}
		// Skip check if connecting to XMLRPC.
		if ( defined( 'XMLRPC_REQUEST' ) ) {
			return $user;
		}

		if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
			return $user;
		}

		if ( ! $this->recaptcha_response( 'default_login' ) ) {
			if ( is_wp_error( $user ) ) {
				$user->add( 'invalid_captcha', $this->service->error_message() );

				return $user;
			} else {
				return new WP_Error( 'invalid_captcha', $this->service->error_message() );
			}
		}

		return $user;
	}

	/**
	 * Verify the recaptcha code on the Registration page.
	 *
	 * @param WP_Error $errors
	 *
	 * @return WP_Error
	 */
	public function validate_captcha_field_on_registration( WP_Error $errors ): WP_Error {
		// Skip check if connecting to XMLRPC.
		if ( defined( 'XMLRPC_REQUEST' ) ) {
			return $errors;
		}

		if ( ! $this->recaptcha_response( 'default_registration' ) ) {
			$errors->add( 'invalid_captcha', $this->service->error_message() );
		}
		$_POST['g-recaptcha-response-check'] = true;

		return $errors;
	}

	/**
	 * Add google recaptcha to the multisite signup form.
	 *
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	public function display_signup_recaptcha( WP_Error $errors ): void {
		$error_message = $errors->get_error_message( 'invalid_captcha' );
		if ( ! empty( $error_message ) ) {
			printf( '<p class="error">%s</p>', $error_message );
		}
		echo $this->display_recaptcha();
	}

	/**
	 * Verify the recaptcha code on the multisite signup page.
	 *
	 * @param array $result
	 *
	 * @return array
	 */
	public function validate_captcha_field_on_wpmu_registration( array $result ): array {
		global $current_user;
		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! empty( $current_user->data->ID ) ) {
			return $result;
		}

		if ( ! $this->recaptcha_response( 'wpmu_registration' ) ) {
			if ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) {
				$errors = $result['errors'];
			} else {
				$errors = new WP_Error();
			}
			$errors->add( 'invalid_captcha', $this->service->error_message() );
			$result['errors'] = $errors;

			return $result;
		}

		return $result;
	}

	/**
	 * Verify the recaptcha code on Woo login page.
	 *
	 * @param WP_Error $errors
	 *
	 * @return WP_Error
	 */
	public function validate_captcha_field_on_woo_login( WP_Error $errors ): WP_Error {
		// Skip check if connecting to XMLRPC.
		if ( defined( 'XMLRPC_REQUEST' ) ) {
			return $errors;
		}

		if ( ! $this->recaptcha_response( 'woo_login' ) ) {
			// Remove 'Error: ' because Woo has it by default.
			$message = str_replace( __( '<strong>Error:</strong> ', 'wpdef' ), '', $this->service->error_message() );
			$errors->add( 'invalid_captcha', $message );
		}

		return $errors;
	}

	/**
	 * Check recaptcha on Woo registration form.
	 *
	 * @param WP_Error $errors
	 *
	 * @return WP_Error
	 */
	public function validate_captcha_field_on_woo_registration( WP_Error $errors ): WP_Error {
		if ( defined( 'WOOCOMMERCE_CHECKOUT' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $errors;
		}
		if ( ! $this->recaptcha_response( 'woo_registration' ) ) {
			// Remove 'Error: ' because Woo has it by default.
			$message = str_replace( __( '<strong>Error:</strong> ', 'wpdef' ), '', $this->service->error_message() );
			$errors->add( 'invalid_captcha', $message );
		}

		return $errors;
	}

	/**
	 * Fires before errors are returned from a password reset request.
	 * Without 2nd `$user_data` parameter because it's since WP 5.4.0.
	 *
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	public function validate_captcha_field_on_lostpassword( WP_Error $errors ): void {
		if ( ! $this->recaptcha_response( 'default_lost_password' ) ) {
			$errors->add( 'invalid_captcha', $this->service->error_message() );
		}
	}

	/**
	 * @param array $fields
	 * @param WP_Error $errors
	 *
	 * @return WP_Error
	 */
	public function validate_captcha_field_on_woo_checkout( $fields, $errors ): WP_Error {
		if ( ! $this->recaptcha_response( 'woo_checkout' ) ) {
			// Remove 'Error: ' because Woo has it by default.
			$message = str_replace( __( '<strong>Error:</strong> ', 'wpdef' ), '', $this->service->error_message() );
			$errors->add( 'invalid_captcha', $message );
		}

		return $errors;
	}

	/**
	 * Add google recaptcha to the comment form.
	 *
	 * @return bool
	 */
	public function display_comment_recaptcha(): bool {
		echo '<style>#commentform .recaptcha_wrap {margin: 0 0 10px;}</style>';
		echo $this->display_recaptcha();
		return true;
	}

	/**
	 * Check JS enabled for comment form.
	 *
	 * @return null|void
	 */
	public function validate_captcha_field_on_comment() {
		if ( $this->service->exclude_recaptcha_for_requests() ) {
			return;
		}

		if ( ! $this->recaptcha_response( 'default_comments' ) ) {
			// @since v2.5.6
			wp_die( (string) apply_filters( 'wd_recaptcha_require_valid_comment', $this->service->error_message() ) );
		}
	}

	/**
	 * Get the reCAPTCHA API response.
	 *
	 * @param string $form
	 *
	 * @return bool
	 */
	protected function recaptcha_response( string $form ): bool {
		if ( empty( $this->private_key ) || empty( $_POST['g-recaptcha-response'] ) ) {
			return false;
		}
		// reCAPTCHA response post data.
		$response = stripslashes( sanitize_text_field( $_POST['g-recaptcha-response'] ) );
		$remote_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
		// @since v2.5.6
		$remote_ip = (string) apply_filters( 'wd_recaptcha_remote_ip', $remote_ip );

		$post_body = [
			'secret' => $this->private_key,
			'response' => $response,
			'remoteip' => $remote_ip,
		];

		$result = $this->service->recaptcha_post_request( $post_body );
		// @since 2.5.6
		return apply_filters( 'wd_recaptcha_check_result', $result, $form );
	}

	/**
	 * @return void
	 */
	public function display_buddypress_recaptcha() {
		if ( ! empty( buddypress()->signup->errors['failed_recaptcha_verification'] ) ) {
			$output = '<div class="error">';
			$output .= buddypress()->signup->errors['failed_recaptcha_verification'];
			$output .= '</div>';

			echo wp_kses_post( $output );
		}
		echo $this->display_recaptcha();
	}

	/**
	 * @return void
	 */
	public function validate_captcha_field_on_buddypress_registration(): void {
		if ( ! $this->recaptcha_response( 'buddypress_registration' ) ) {
			buddypress()->signup->errors['failed_recaptcha_verification'] = $this->service->error_message();
		}
	}

	/**
	 * Verify BuddyPress group form captcha.
	 */
	public function validate_captcha_field_on_buddypress_group() {
		if ( ! bp_is_group_creation_step( 'group-details' ) ) {
			return false;
		}

		if ( ! $this->recaptcha_response( 'buddypress_create_group' ) ) {
			bp_core_add_message( $this->service->error_message(), 'error' );
			bp_core_redirect( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create/step/group-details/' );
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public function script_data( array $data ): array {
		$data['recaptcha'] = $this->data_frontend();

		return $data;
	}

	/**
	 * Save settings.
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function save_settings( Request $request ): Response {
		$data = $request->get_data_by_model( $this->model );
		$this->model->import( $data );
		if ( $this->model->validate() ) {
			$this->model->save();
			Config_Hub_Helper::set_clear_active_flag();

			return new Response(
				true,
				array_merge(
					[
						'message' => __( 'Google reCAPTCHA settings saved successfully.', 'wpdef' ),
					],
					$this->data_frontend()
				)
			);
		}

		return new Response(
			false,
			// Merge stored data to avoid errors.
			array_merge(
				[
					'message' => $this->model->get_formatted_errors(),
					'error_keys' => $this->model->get_error_keys(),
				],
				$this->data_frontend()
			)
		);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function load_recaptcha_preview( Request $request ): Response {
		$onload = null;
		$js = null;
		$data = $request->get_data(
			[
				'captcha_type' => [
					'type' => 'string',
				],
			]
		);
		$this->recaptcha_type = $data['captcha_type'];

		$model = $this->model;
		$language = ! empty( $model->language ) && 'automatic' !== $model->language ? $model->language : get_locale();

		$notice = '<div class="sui-notice sui-notice-default">';
		$notice .= '<div class="sui-notice-content">';
		$notice .= '<div class="sui-notice-message">';
		$notice .= '<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>';
		$notice .= '<p>' . esc_html__( 'Save your API keys to load the reCAPTCHA preview.', 'wpdef' ) . '</p>';
		$notice .= '</div>';
		$notice .= '</div>';
		$notice .= '</div>';

		$theme = 'light';
		$captcha_size = 'invisible';
		$data_args = '';
		if ( 'v2_checkbox' === $this->recaptcha_type ) {
			$this->public_key = $model->data_v2_checkbox['key'];
			$theme = $model->data_v2_checkbox['style'];
			$captcha_size = $model->data_v2_checkbox['size'];
			$onload = 'defender_render_admin_captcha_v2';
			// Onload method for Recaptcha works only for js 'var'.
			$js = "<script>var defender_render_admin_captcha_v2 = function () {
			setTimeout( function () {
			var captcha_v2 = jQuery( '.defender-g-recaptcha-v2_checkbox' ),
				sitekey_v2 = captcha_v2.data('sitekey'),
				theme_v2 = captcha_v2.data('theme'),
				size_v2 = captcha_v2.data('size')
			;
			window.grecaptcha.render( captcha_v2[0], {
				sitekey: sitekey_v2,
				theme: theme_v2,
				size: size_v2,
				'error-callback': function() {
					jQuery('#v2_checkbox_notice_1').hide();
					jQuery('#v2_checkbox_notice_2').show();
				}
			} );
			}, 100 );
			};</script>";
		} elseif ( 'v2_invisible' === $this->recaptcha_type ) {
			$this->public_key = $model->data_v2_invisible['key'];
			$onload = 'defender_render_admin_captcha_v2_invisible';
			$data_args = 'data-badge="inline" data-callback="setResponse"';
			$js = "<script>var defender_render_admin_captcha_v2_invisible = function () {
			setTimeout( function () {
				var captcha = jQuery( '.defender-g-recaptcha-v2_invisible' ),
					sitekey = captcha.data('sitekey'),
					theme = captcha.data('theme'),
					size = captcha.data('size')
				;
				window.grecaptcha.render( captcha[0], {
					sitekey: sitekey,
					theme: theme,
					size: size,
					badge: 'inline',
					'error-callback': function() {
						jQuery('#v2_invisible_notice_1').hide();
						jQuery('#v2_invisible_notice_2').show();
					}
				} );
			}, 100 );
			};</script>";
		} elseif ( 'v3_recaptcha' === $this->recaptcha_type ) {
			$this->public_key = $model->data_v3_recaptcha['key'];
			$onload = 'defender_render_admin_captcha_v3';
			$js = "<script>var defender_render_admin_captcha_v3 = function () {
			setTimeout( function () {
				var captcha = jQuery( '.defender-g-recaptcha-v3_recaptcha' ),
					sitekey = captcha.data('sitekey'),
					theme = captcha.data('theme'),
					size = captcha.data('size')
				;
				window.grecaptcha.render( captcha[0], {
					sitekey: sitekey,
					theme: theme,
					size: size,
					badge: 'inline',
					'error-callback': function() {
						jQuery('#v3_recaptcha_notice_1').hide();
						jQuery('#v3_recaptcha_notice_2').show();
					}
				} );
			}, 100 );
			};</script>";
		}

		$html = '';
		if ( isset( $this->recaptcha_type ) && ! empty( $this->public_key ) ) {
			$html .= '<script src="https://www.google.com/recaptcha/api.js?hl=' . $language . '&render=explicit&onload=' . $onload . '" async defer></script>' . $js;

			$html .= sprintf(
				'<div class="%s" data-sitekey="%s" data-theme="%s" data-size="%s" %s></div>',
				'defender-g-recaptcha-' . $this->recaptcha_type,
				$this->public_key,
				$theme,
				$captcha_size,
				$data_args
			);
		} else {
			$html .= $notice;
		}

		return new Response(
			true,
			[
				'preview' => true,
				'html' => $html,
			]
		);
	}

	public function remove_settings() {}

	public function remove_data() {
		$this->model->delete();
	}

	/**
	 * @return array
	 */
	public function to_array(): array {
		return [];
	}

	/**
	 * @return array
	 */
	public function data_frontend(): array {
		$model = $this->model;
		$is_active = $model->is_active();
		/**
		 * Different cases for entered keys and locations:
		 * success - one default, Woo or BuddyPress location is checked at least,
		 * warning - default, Woo and BuddyPress locations are unchecked,
		 * warning - default location is unchecked, also Woo and BuddyPress is deactivated,
		 * warning - non-entered keys.
		*/
		if ( $is_active ) {
			if ( $this->service->enable_any_location( $this->is_woo_activated, $this->is_buddypress_activated ) ) {
				switch ( $model->active_type ) {
					case 'v2_invisible':
						$type = 'V2 Invisible';
						break;
					case 'v3_recaptcha':
						$type = 'V3';
						break;
					case 'v2_checkbox':
					default:
						$type = 'V2 Checkbox';
						break;
				}
				$notice_type = 'success';
				$notice_text = sprintf(
				/* translators: */
					__( 'Google reCAPTCHA is currently active. %s type has been set successfully.', 'wpdef' ),
					$type
				);
			} elseif ( ! $this->is_woo_activated && ! $this->is_buddypress_activated && ! $model->enable_default_location() ) {
				$notice_type = 'warning';
				$notice_text = __( 'Google reCAPTCHA is currently inactive for all forms. You can deploy reCAPTCHA for specific forms in the <b>reCAPTCHA Locations</b> below.', 'wpdef' );
			} elseif (
				! $model->enable_default_location()
				&& (
					( $this->is_woo_activated && ! $model->enable_woo_location() )
					|| ( $this->is_buddypress_activated && ! $model->enable_buddypress_location() )
				)
			) {
				$notice_type = 'warning';
				$notice_text = __( 'Google reCAPTCHA is currently inactive for all forms. You can deploy reCAPTCHA for specific forms in the <b>reCAPTCHA Locations</b>, <b>WooCommerce</b> or <b>BuddyPress</b> settings below.', 'wpdef' );
			}
		} else {
			// Inactive case.
			$notice_type = 'warning';
			$notice_text = __( 'Google reCAPTCHA is currently inactive. Enter your Site and Secret keys and save your settings to finish setup.', 'wpdef' );
		}

		/**
		 * Cases:
		 * Invalid domain for Site Key,
		 * Google ReCAPTCHA is in localhost,
		 * Cannot contact reCAPTCHA. Check your connection.
		 */
		$ticket_text = __( 'If you see any errors in the preview, make sure the keys you’ve entered are valid, and you\'ve listed your domain name while generating the keys.', 'wpdef' );

		if ( ( new \WP_Defender\Behavior\WPMUDEV() )->show_support_links() ) {
			$ticket_text .= sprintf(
			/* translators: ... */
				__( 'Still having trouble? <a target="_blank" href="%s">Open a support ticket</a>.', 'wpdef' ),
				WP_DEFENDER_SUPPORT_LINK
			);
		}

		return array_merge(
			[
				'model' => $model->export(),
				'is_active' => $is_active,
				'default_message' => $this->default_msg,
				'default_locations' => Recaptcha_Component::get_forms(),
				'notice_type' => $notice_type,
				'notice_text' => $notice_text,
				'ticket_text' => $ticket_text,
				'is_woo_active' => $this->is_woo_activated,
				'woo_locations' => Woocommerce::get_forms(),
				'is_buddypress_active' => $this->is_buddypress_activated,
				'buddypress_locations' => Buddypress::get_forms(),
			],
			$this->dump_routes_and_nonces()
		);
	}

	/**
	 * @return array
	 */
	public function dashboard_widget(): array {
		$model = $this->model;
		$notice_type = ( $model->is_active()
			&& $this->service->enable_any_location( $this->is_woo_activated, $this->is_buddypress_activated )
		)
			? 'success'
			: 'warning';

		return [
			'model' => $model->export(),
			'notice_type' => $notice_type,
		];
	}

	public function import_data( $data ) {
		$model = $this->model;

		$model->import( $data );
		if ( $model->validate() ) {
			$model->save();
		}
	}

	/**
	 * @return array
	 */
	public function export_strings(): array {
		return [ $this->model->is_active() ? __( 'Active', 'wpdef' ) : __( 'Inactive', 'wpdef' ) ];
	}

	/**
	 * Maybe validate reCaptcha for lost password.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	protected function maybe_validate_captcha_for_lostpassword(): bool {
		return ! $this->is_woocommerce_page() &&
			! isset( $_POST['wc_reset_password'], $_POST['user_login'] ) &&
			! ( is_admin() && isset( $_POST['action'] ) && 'send-password-reset' === $_POST['action'] );
	}
}
