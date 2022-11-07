<?php

namespace WP_Defender\Component;

use WP_Defender\Component;
use WP_Defender\Model\Lockout_Log;
use WP_Defender\Model\Lockout_Ip;

/**
 * Class User_Agent.
 * Example of User-Agent format:
 * User-Agent: Mozilla/5.0 (<system-information>) <platform> (<platform-details>) <extensions>
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
 *
 * @package WP_Defender\Component
 * @since 2.6.0
 */
class User_Agent extends Component {
	use \WP_Defender\Traits\Country;

	public const SCENARIO_USER_AGENT_LOCKOUT = 'user_agent_lockout';
	public const REASON_BAD_USER_AGENT = 'bad_user_agent', REASON_BAD_POST = 'bad_post';

	/**
	 * Human Readable text denotes user agent header is empty.
	 */
	public const EMPTY_USER_AGENT_TEXT = 'Empty User Agent';

	/**
	 * Use for cache.
	 *
	 * @var \WP_Defender\Model\Setting\User_Agent_Lockout
	 */
	protected $model;

	/**
	 * Lockout IP model instance.
	 *
	 * @var Lockout_Ip
	 */
	protected $lockout_ip_model;

	public function __construct() {
		$this->model = wd_di()->get( \WP_Defender\Model\Setting\User_Agent_Lockout::class );
		$this->lockout_ip_model = wd_di()->get( Lockout_Ip::class );
	}

	/**
	 * Log the event into db, we will use the data in logs page later.
	 *
	 * @param string $ip
	 * @param string $user_agent
	 * @param string $reason
	 */
	private function log_event( $ip, $user_agent, $reason ) {
		$model = new Lockout_Log();
		$model->ip = $ip;
		$model->user_agent = $user_agent;
		$model->date = time();
		$model->tried = $user_agent;
		$model->blog_id = get_current_blog_id();
		$model->type = Lockout_Log::LOCKOUT_UA;

		$ip_to_country = $this->ip_to_country( $ip );

		if ( ! empty( $ip_to_country ) && isset( $ip_to_country['iso'] ) ) {
			$model->country_iso_code = $ip_to_country['iso'];
		}

		switch ( $reason ) {
			case self::REASON_BAD_POST:
				// Distinguish between different block cases of User agent lockouts.
				$model->tried = self::REASON_BAD_POST;
				$model->log = __( 'Locked out due to empty User-Agent and Referer headers', 'wpdef' );
				break;
			case self::REASON_BAD_USER_AGENT:
			default:
				$model->tried = $user_agent;
				$model->log = __( 'Locked out due to attempted login with banned user agent', 'wpdef' );
				break;
		}
		$model->save();
		// The 'defender_notify' hook doesn't work, so send notify directly.
		$module = wd_di()->get( \WP_Defender\Model\Notification\Firewall_Notification::class );
		if ( $module->check_options( $model ) ) {
			$module->send( $model );
		}
	}

	/**
	 * Queue hooks when this class init.
	 */
	public function add_hooks() {}

	public function is_active_component(): bool {
		return $this->model->is_active() && ! is_admin();
	}

	/**
	 * Is the current UA bad?
	 *
	 * @param string $user_agent
	 *
	 * @return bool
	 */
	public function is_bad_user_agent( $user_agent ): bool {
		$allowlist = str_replace( '#', '\#', $this->model->get_lockout_list( 'allowlist' ) );
		$blocklist = str_replace( '#', '\#', $this->model->get_lockout_list( 'blocklist' ) );

		$allowlist_regex_pattern = '#' . implode( '|', $allowlist ) . '#i';
		$blocklist_regex_pattern = '#' . implode( '|', $blocklist ) . '#i';

		$allowlist_match = preg_match( $allowlist_regex_pattern, $user_agent );
		$blocklist_match = preg_match( $blocklist_regex_pattern, $user_agent );

		if ( ! empty( $allowlist_match ) ) {
			return false;
		}

		if ( ! empty( $blocklist_match ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function get_message(): string {
		return ! empty( $this->model->message )
			? $this->model->message
			: __( 'You have been blocked from accessing this website.', 'wpdef' );
	}

	/**
	 * Block the current UA.
	 *
	 * @param string $user_agent
	 * @param string $ip
	 * @param string $reason
	 *
	 * @return void
	 */
	public function block_user_agent_or_ip( $user_agent, $ip, $reason ) {
		// @since 2.6.0
		do_action( 'wd_user_agent_before_block', $user_agent, $ip, $reason );
		$this->log_event( $ip, $user_agent, $reason );
		do_action( 'wd_user_agent_lockout', $this->model, self::SCENARIO_USER_AGENT_LOCKOUT );
		// Shouldn't block IP via hook 'wd_blacklist_this_ip', block only when the button 'Ban IP' is clicked.
	}

	/**
	 * @param string $user_agent
	 *
	 * @return string
	 */
	public static function fast_cleaning( $user_agent ): string {
		return trim( sanitize_text_field( $user_agent ) );
	}

	/**
	 * Sanitize User Agent.
	 *
	 * @return string
	 */
	public function sanitize_user_agent(): string {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return '';
		}

		$user_agent = apply_filters( 'wd_current_user_agent', $_SERVER['HTTP_USER_AGENT'] );
		$user_agent = self::fast_cleaning( $user_agent );
		$user_agent = strtolower( $user_agent );

		return $user_agent;
	}

	/**
	 * Is the POST request with blank User-Agent and Referer?
	 *
	 * @param string $user_agent
	 *
	 * @return bool
	 */
	public function is_bad_post( $user_agent ): bool {

		return true === $this->model->empty_headers
			&& 'POST' === $_SERVER['REQUEST_METHOD']
			&& empty( $user_agent )
			&& empty( $_SERVER['HTTP_REFERER'] );
	}

	/**
	 * Validate import file is in right format and usable for User Agent Lockout.
	 *
	 * @param $file
	 *
	 * @return array|bool
	 */
	public function verify_import_file( $file ) {
		$fp = fopen( $file, 'r' );
		$data = [];
		while ( ( $line = fgetcsv( $fp ) ) !== false ) { //phpcs:ignore
			if ( 2 !== count( (array) $line ) ) {
				return false;
			}

			if ( ! in_array( $line[1], [ 'allowlist', 'blocklist' ], true ) ) {
				return false;
			}

			$ua = $line[0];
			$ua = self::fast_cleaning( $ua );

			if ( '' === $ua ) {
				continue;
			}
			$line[0] = $ua;

			$data[] = $line;
		}
		fclose( $fp );

		return $data;
	}

	/**
	 * Get human readable user agent log status text.
	 *
	 * @param string $log_type   Type of the log. Handles on 'ua_lockout'.
	 * @param string $user_agent User Agent name.
	 *
	 * @return string Human readable text if log_type is UA else empty string.
	 */
	public function get_status_text( $log_type, $user_agent ): string {
		if ( Lockout_Log::LOCKOUT_UA !== $log_type ) {
			return '';
		}

		$status_text = self::EMPTY_USER_AGENT_TEXT;

		if ( self::REASON_BAD_POST === $user_agent ) {
			return $status_text;
		}

		$user_agent_key = $this->model->get_access_status( $user_agent );

		if ( ! empty( $user_agent_key[0] ) ) {
			$status_text = $this->lockout_ip_model->get_access_status_text( $user_agent_key[0] );
		}

		return $status_text;
	}
}
