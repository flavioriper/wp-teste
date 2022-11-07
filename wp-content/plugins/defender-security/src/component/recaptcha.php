<?php
declare( strict_types = 1 );

namespace WP_Defender\Component;

use Calotes\Base\Component;
use WP_Defender\Model\Setting\Recaptcha as Recaptcha_Model;

class Recaptcha extends Component {
	public const DEFAULT_LOGIN_FORM = 'login',
		DEFAULT_REGISTER_FORM = 'register',
		DEFAULT_LOST_PASSWORD_FORM = 'lost_password',
		DEFAULT_COMMENT_FORM = 'comments';
	/**
	 * @var Recaptcha_Model
	 */
	protected $model;

	/**
	 * @param Recaptcha_Model $model
	 */
	public function __construct( Recaptcha_Model $model ) {
		$this->model = $model;
	}

	/**
	 * Check that at least one location is enabled
	 *
	 * @param bool $exist_woo
	 * @param bool $exist_bp
	 *
	 * @return bool
	 */
	public function enable_any_location( $exist_woo, $exist_bp ): bool {
		return $this->model->enable_default_location()
			|| $this->model->check_woo_locations( $exist_woo )
			|| $this->model->check_buddypress_locations( $exist_bp );
	}

	/**
	 * @since 2.5.6
	 * @return bool
	 */
	public function exclude_recaptcha_for_requests(): bool {
		$current_request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$excluded_requests = (array) apply_filters( 'wd_recaptcha_excluded_requests', [] );

		return in_array( $current_request, $excluded_requests, true );
	}

	/**
	 * @return bool|void
	 */
	public function remove_dublicate_scripts() {
		global $wp_scripts;

		if ( ! is_object( $wp_scripts ) || empty( $wp_scripts ) ) {
			return false;
		}

		foreach ( $wp_scripts->registered as $script_name => $args ) {
			if ( is_string( $args->src ) && preg_match( '|google\.com/recaptcha/api\.js|', $args->src )
				&& 'wpdef_recaptcha_api' !== $script_name
			) {
				// Remove a previously enqueued script.
				wp_dequeue_script( $script_name );
			}
		}
	}

	/**
	 * Display custom error message.
	 *
	 * @return string
	 */
	public function error_message(): string {
		$default_values = $this->model->get_default_values();

		return sprintf(
		/* translators: %s: Error message. */
			__( '<strong>Error:</strong> %s', 'wpdef' ),
			empty( $this->model->message ) ? $default_values['message'] : $this->model->message
		);
	}

	/**
	 * Send HTTP POST request and return the response.
	 * Also initialize the error text if an error response is received.
	 *
	 * @param array $post_body - HTTP POST body
	 *
	 * @return bool
	 */
	public function recaptcha_post_request( array $post_body ): bool {
		$args = [
			'body' => $post_body,
			'sslverify' => false,
		];
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$request = wp_remote_post( $url, $args );
		// Get the request response body
		if ( is_wp_error( $request ) ) {
			return false;
		}

		$response_body = wp_remote_retrieve_body( $request );
		$response_keys = json_decode( $response_body, true );
		if ( 'v3_recaptcha' === $this->model->active_type ) {
			if (
				$response_keys['success']
				&& isset( $this->model->data_v3_recaptcha['threshold'], $response_keys['score'] )
				&& is_numeric( $this->model->data_v3_recaptcha['threshold'] )
				&& is_numeric( $response_keys['score'] )
			) {
				$is_success = $response_keys['score'] >= (float) $this->model->data_v3_recaptcha['threshold'];
			} else {
				$is_success = false;
			}
		} else {
			$is_success = (bool) $response_keys['success'];
		}

		return $is_success;
	}

	/**
	 * @return array
	 */
	public static function get_forms(): array {
		return [
			self::DEFAULT_LOGIN_FORM => __( 'Login', 'wpdef' ),
			self::DEFAULT_REGISTER_FORM => __( 'Register', 'wpdef' ),
			self::DEFAULT_LOST_PASSWORD_FORM => __( 'Lost Password', 'wpdef' ),
			self::DEFAULT_COMMENT_FORM => __( 'Comments', 'wpdef' ),
		];
	}
}
