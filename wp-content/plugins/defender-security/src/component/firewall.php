<?php

namespace WP_Defender\Component;

use WP_Defender\Component;
use WP_Defender\Model\Lockout_Ip;
use WP_Defender\Behavior\WPMUDEV;

class Firewall extends Component {

	/**
	 * Check if the first commencing request is proper staff remote access.
	 *
	 * @return bool
	 */
	private function is_commencing_staff_access(): bool {
		$access = \WPMUDEV_Dashboard::$site->get_option( 'remote_access' );

		return wp_doing_ajax() &&
			isset( $_GET['action'] ) &&
			'wdpunauth' === $_GET['action'] &&
			isset( $_POST['wdpunkey'] ) &&
			hash_equals( $_POST['wdpunkey'], $access['key'] );
	}

	/**
	 * Check is the access from authenticated staff.
	 *
	 * @return bool
	 */
	private function is_authenticated_staff_access(): bool {
		return isset( $_COOKIE['wpmudev_is_staff'] ) &&
			'1' === $_COOKIE['wpmudev_is_staff'];
	}

	/**
	 * Check if the access is from our staff access.
	 *
	 * @return bool
	 */
	private function is_a_staff_access(): bool {
		if ( defined( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) && true === constant( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) ) {
			return false;
		}

		$wpmu_dev = new WPMUDEV();
		$is_remote_access = $wpmu_dev->get_apikey() &&
			true === \WPMUDEV_Dashboard::$api->remote_access_details( 'enabled' );

		if (
			$is_remote_access &&
			(
				$this->is_authenticated_staff_access() ||
				$this->is_commencing_staff_access()
			)
		) {
			$access = \WPMUDEV_Dashboard::$site->get_option( 'remote_access' );
			$this->log( var_export( $access, true ), \WP_Defender\Controller\Firewall::FIREWALL_LOG );

			return true;
		}

		return false;
	}

	/**
	 * Queue hooks when this class init.
	 */
	public function add_hooks() {
		add_filter( 'defender_ip_lockout_assets', [ &$this, 'output_scripts_data' ] );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function output_scripts_data( $data ): array {
		$model = new \WP_Defender\Model\Setting\Firewall();
		$data['settings'] = [
			'storage_days' => $model->storage_days ?? 30,
			'class' => \WP_Defender\Model\Setting\Firewall::class,
		];

		return $data;
	}

	/**
	 * Cron for delete old log.
	 */
	public function firewall_clean_up_logs() {
		$settings = new \WP_Defender\Model\Setting\Firewall();
		/**
		 * Filter count days for IP logs to be saved to DB.
		 *
		 * @since 2.3
		 *
		 * @param string
		 */
		$storage_days = apply_filters( 'ip_lockout_logs_store_backward', $settings->storage_days );
		if ( ! is_numeric( $storage_days ) ) {
			return;
		}
		$time_string = '-' . $storage_days . ' days';
		$timestamp = $this->local_to_utc( $time_string );
		\WP_Defender\Model\Lockout_Log::remove_logs( $timestamp, 50 );
	}

	/**
	 * Cron for clean up temporary IP block list.
	 */
	public function firewall_clean_up_temporary_ip_blocklist() {
		$models = Lockout_Ip::get_bulk( Lockout_Ip::STATUS_BLOCKED );
		foreach( $models as $model )  {
			$model->status = Lockout_Ip::STATUS_NORMAL;
			$model->save();
		}
	}

	/**
	 * Update temporary IP blocklist of Firewall, clear cron job.
	 * The interval settings value is updated once.
	 *
	 * @param string $new_interval
	 */
	public function update_cron_schedule_interval( $new_interval ) {
		$settings = new \WP_Defender\Model\Setting\Firewall();
		// If a new interval is different from the saved value, we need to clear the cron job.
		if ( $new_interval !== $settings->ip_blocklist_cleanup_interval ) {
			update_site_option( 'wpdef_clear_schedule_firewall_cleanup_temp_blocklist_ips', true );
		}
	}

	public function skip_priority_lockout_checks( $ip, $service ) {
		$model = Lockout_Ip::get( $ip );
		$is_lockout_ip = is_object( $model ) && $model->is_locked();

		$is_country_whitelisted = ! $service->is_blacklist( $ip ) &&
			$service->is_country_whitelist( $ip ) && ! $is_lockout_ip;

		// If this IP is whitelisted, so we don't need to blacklist this.
		if ( $service->is_ip_whitelisted( $ip ) || $is_country_whitelisted ) {
			return true;
		}
		// Green light if access staff is enabled.
		if ( $this->is_a_staff_access() ) {
			return true;
		}

		return false;
	}
}
