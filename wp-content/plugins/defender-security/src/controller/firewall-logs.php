<?php

namespace WP_Defender\Controller;

use Calotes\Component\Request;
use Calotes\Component\Response;
use Calotes\Helper\HTTP;
use Valitron\Validator;
use WP_Defender\Component\Table_Lockout;
use WP_Defender\Controller;
use WP_Defender\Model\Lockout_Log;
use WP_Defender\Model\Setting\Blacklist_Lockout;
use WP_Defender\Model\Setting\User_Agent_Lockout;
use WP_Defender\Traits\Formats;
use WP_Defender\Component\User_Agent;

class Firewall_Logs extends Controller {
	use Formats;

	/**
	 * @var string
	 */
	protected $slug = 'wdf-ip-lockout';

	public function __construct() {
		$this->register_routes();
		add_action( 'defender_enqueue_assets', [ &$this, 'enqueue_assets' ] );
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function bulk( Request $request ) {
		$data = $request->get_data(
			[
				'action' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ids' => [
					'type' => 'array',
				],
			]
		);
		$ids = $data['ids'];
		$ips = [];
		if ( is_array($ids) || $ids instanceof \Countable ? count( $ids ) : 0 ) {
			foreach ( $ids as $id ) {
				$model = Lockout_Log::find_by_id( $id );
				if ( is_object( $model ) ) {
					$bl = wd_di()->get( Blacklist_Lockout::class );
					switch ( $data['action'] ) {
						case 'ban':
							$bl->remove_from_list( $model->ip, 'allowlist' );
							$bl->add_to_list( $model->ip, 'blocklist' );
							$ips[ $model->ip ] = $model->ip;
							break;
						case 'allowlist':
							$bl->remove_from_list( $model->ip, 'blocklist' );
							$bl->add_to_list( $model->ip, 'allowlist' );
							$ips[ $model->ip ] = $model->ip;
							break;
						case 'delete':
							$ips[ $model->ip ] = $model->ip;
							$model->delete();
							break;
						default:
							break;
					}
				}
			}
		}

		switch ( $data['action'] ) {
			case 'allowlist':
				$messages = sprintf(
				/* translators: 1: IP Address(es), 2: URL for Defender > Firewall > IP Banning */
					__(
						'IP %1$s has been added to your allowlist. You can control your allowlist in <a href="%2$s">IP Lockouts.</a>',
						'wpdef'
					),
					implode( ', ', $ips ),
					network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' )
				);
				break;
			case 'ban':
				$messages = sprintf(
				/* translators: 1: IP Address(es), 2: URL for Defender > Firewall > IP Banning */
					__(
						'IP %1$s has been added to your blocklist You can control your blocklist in <a href="%2$s">IP Lockouts.</a>',
						'wpdef'
					),
					implode( ', ', $ips ),
					network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' )
				);
				break;
			case 'delete':
				$messages = sprintf(
				/* translators: IP Address(es) */
					__( 'IP %s has been deleted', 'wpdef' ),
					implode( ', ', $ips )
				);
				break;

		}

		return new Response(
			true,
			[ 'message' => $messages ]
		);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function export_as_csv( Request $request ) {
		$filters = [
			'from' => strtotime( 'midnight', strtotime( HTTP::get( 'date_from', strtotime( '-7 days midnight' ) ) ) ),
			'to' => strtotime( 'tomorrow', strtotime( HTTP::get( 'date_to', strtotime( 'tomorrow' ) ) ) ),
			'type' => HTTP::get( 'term', '' ),
			'ip' => HTTP::get( 'ip', '' ),
			'ban_status' => HTTP::get( 'ban_status', '' ),
		];

		if ( 'all' === $filters['type'] ) {
			$filters['type'] = '';
		}

		if ( 'all' === $filters['ban_status'] ) {
			$filters['ban_status'] = '';
		}

		// User can export the number of logs that are set.
		$per_page = isset( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : 20;

		if ( -1 === (int) $per_page ) {
			$per_page = false;
		}

		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
		$logs = Lockout_Log::query_logs( $filters, $paged, 'date', 'desc', $per_page );

		$tl_component = new Table_Lockout();

		$ua_component = wd_di()->get( User_Agent::class );

		$filename = 'wdf-lockout-logs-export-' . gmdate( 'ymdHis' ) . '.csv';

		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		header( 'Content-Transfer-Encoding: binary' );

		extension_loaded( 'zlib' ) ? ob_start( 'ob_gzhandler' ) : ob_start();

		$fp = fopen( 'php://output', 'w' );
		$headers = [
			__( 'Log', 'wpdef' ),
			__( 'Date / Time', 'wpdef' ),
			__( 'Type', 'wpdef' ),
			__( 'IP address', 'wpdef' ),
			__( 'IP Status', 'wpdef' ),
			__( 'User Agent Status', 'wpdef' ),
		];
		fputcsv( $fp, $headers );

		$flush_limit = Lockout_Log::INFINITE_SCROLL_SIZE;
		foreach ( $logs as $key => $log ) {
			$item = [
				$log->log,
				$this->get_date( $log->date ),
				$tl_component->get_type( $log->type ),
				$log->ip,
				$tl_component->get_ip_status_text( $log->ip ),
				$ua_component->get_status_text( $log->type, $log->tried ),
			];
			fputcsv( $fp, $item );

			if ( 0 === $key % $flush_limit ) {
				ob_flush();
				flush();
			}
		}

		fclose( $fp );
		exit();
	}

	/**
	 * Get formatted date.
	 *
	 * @param $date
	 *
	 * @return string
	 */
	public function get_date( $date ) {
		if ( strtotime( '-24 hours' ) > $date ) {
			return $this->format_date_time( gmdate( 'Y-m-d H:i:s', $date ) );
		} else {
			return human_time_diff( $date, time() ) . ' ' . __( 'ago', 'wpdef' ); // phpcs:ignore
		}
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function toggle_ip_to_list( Request $request ):Response {
		$data = $request->get_data(
			[
				'ip' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'list' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ban_status' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
			]
		);

		$ip = $data['ip'];
		$list = $data['list'];

		$model = wd_di()->get( Blacklist_Lockout::class );
		if ( $model->is_ip_in_list( $ip, $list ) ) {
			$model->remove_from_list( $ip, $list );
			/* translators: 1: IP address, 2: IP address list, 3: IP address list, 4: URL for Defender > Firewall > IP Banning */
			$message = __(
				'IP %1$s has been removed from your %2$s You can control your %3$s in <a href="%4$s">IP Lockouts.</a>',
				'wpdef'
			);
		} else {
			$model->add_to_list( $ip, $list );
			/* translators: 1: IP address, 2: IP address list, 3: IP address list, 4: URL for Defender > Firewall > IP Banning */
			$message = __(
				'IP %1$s has been added to your %2$s You can control your %3$s in <a href="%4$s">IP Lockouts.</a>',
				'wpdef'
			);
		}
		$filter_data = $request->get_data(
			[
				'date_from' => [
					'type'     => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'date_to' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ip_filter' => [
					'type'     => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'type' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'paged' => [
					'type' => 'int',
					'sanitize' => 'sanitize_text_field',
				],
				'per_page' => [
					'type' => 'int',
					'sanitize' => 'sanitize_text_field',
				],
			]
		);
		$logs = Lockout_Log::get_logs_and_format(
			[
				'from' => strtotime( $filter_data['date_from'] . ' 00:00:00' ),
				'to' => strtotime( $filter_data['date_to'] . ' 23:59:59' ),
				'ip' => $filter_data['ip_filter'],
				// If this is all, then we set to null to exclude it from the filter.
				'type' => 'all' === $filter_data['type'] ? '' : $filter_data['type'],
			],
			$filter_data['paged'],
			'id',
			'desc',
			$filter_data['per_page']
		);

		return new Response(
			true,
			[
				'message' => sprintf(
					$message,
					$data['ip'],
					$data['list'],
					$data['list'],
					network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' )
				),
				'logs' => $logs,
			]
		);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function toggle_ua_to_list( Request $request ): Response {
		$data = $request->get_data(
			[
				'ua' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'list' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'scenario' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
			]
		);

		$ua = $data['ua'];
		$list = $data['list'];
		$action = $data['scenario'];

		/**
		 * @var User_Agent_Lockout
		 */
		$model = wd_di()->get( User_Agent_Lockout::class );

		if ( 'remove' === $action && $model->is_ua_in_list( $ua, $list ) ) {
			$model->remove_from_list( $ua, $list );
			/* translators: 1: User agent, 2: User agent list, 3: User agent list, 4: URL for Defender > Firewall > User Agent Banning */
			$message = __(
				'User agent <strong>%1$s</strong> has been removed from your %2$s You can control your %3$s in <a href="%4$s">User Agent Banning.</a>',
				'wpdef'
			);
		} elseif ( 'add' === $action ) {

			/**
			 * Possible scenario on regex blocklist. For e.g. UA term `run` present in allowlist & `r.n` regex in blocklist then remove `run` to block `run` user agent using regex `r.n`.
			 */
			if ( 'blocklist' === $list && $model->is_ua_in_list( $ua, 'allowlist' ) ) {
				$model->remove_from_list( $ua, 'allowlist' );
			}

			if ( ! $model->is_ua_in_list( $ua, $list ) ) {
				$model->add_to_list( $ua, $list );
			}
			/* translators: 1: User agent, 2: User agent list, 3: User agent list, 4: URL for Defender > Firewall > User Agent Banning */
			$message = __(
				'User agent <strong>%1$s</strong> has been added to your %2$s You can control your %3$s in <a href="%4$s">User Agent Banning.</a>',
				'wpdef'
			);
		} else {
			return new Response(
				false,
				[ 'message' => __( 'Wrong result.', 'wpdef' ) ]
			);
		}

		$filter_data = $request->get_data(
			[
				'date_from' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'date_to' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ip_filter' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'type' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'paged' => [
					'type' => 'int',
					'sanitize' => 'sanitize_text_field',
				],
				'per_page' => [
					'type' => 'int',
					'sanitize' => 'sanitize_text_field',
				],
			]
		);
		$logs = Lockout_Log::get_logs_and_format(
			[
				'from' => strtotime( $filter_data['date_from'] . ' 00:00:00' ),
				'to' => strtotime( $filter_data['date_to'] . ' 23:59:59' ),
				'ip' => $filter_data['ip_filter'],
				// If this is all, then we set to null to exclude it from the filter.
				'type' => 'all' === $filter_data['type'] ? '' : $filter_data['type'],
			],
			$filter_data['paged'],
			'id',
			'desc',
			$filter_data['per_page']
		);

		return new Response(
			true,
			[
				'message' => sprintf(
					$message,
					$data['ua'],
					$data['list'],
					$data['list'],
					network_admin_url( 'admin.php?page=wdf-ip-lockout&view=ua-lockout' )
				),
				'logs' => $logs,
			]
		);
	}

	/**
	 * Query the logs and display on frontend.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function query_logs( Request $request ): Response {
		$data = $request->get_data(
			[
				'date_from' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'date_to' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ip' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'type' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'paged' => [
					'type' => 'int',
					'sanitize' => 'sanitize_text_field',
				],
				'sort' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'ban_status' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
			]
		);

		// Validate.
		$v = new Validator( $data, [] );
		$v->rule( 'required', [ 'date_from', 'date_to' ] );
		$v->rule( 'date', [ 'date_from', 'date_to' ] );
		if ( ! $v->validate() ) {
			return new Response(
				false,
				[ 'message' => __( 'Wrong start and end date', 'wpdef' ) ]
			);
		}
		$sort = $data['sort'] ?? Table_Lockout::SORT_DESC;
		switch ( $sort ) {
			case 'ip':
				$order = 'desc';
				$order_by = 'ip';
				break;
			case 'oldest':
				$order = 'asc';
				$order_by = 'id';
				break;
			case 'user_agent':
				$order = 'asc';
				$order_by = 'user_agent';
				break;
			default:
				$order = 'desc';
				$order_by = 'id';
				break;
		}
		$data = $this->retrieve_logs(
			[
				'from' => strtotime( $data['date_from'] . ' 00:00:00' ),
				'to' => strtotime( $data['date_to'] . ' 23:59:59' ),
				'ip' => $data['ip'],
				// If this is all, then we set to null to exclude it from the filter.
				'type' => 'all' === $data['type'] ? '' : $data['type'],
				'ban_status' => 'all' === $data['ban_status'] ? '' : $data['ban_status'],
			],
			$data['paged'],
			$order,
			$order_by
		);

		return new Response(
			true,
			$data
		);
	}

	public function enqueue_assets() {
		if ( ! $this->is_page_active() ) {
			return;
		}
		wp_enqueue_script( 'def-momentjs', defender_asset_url( '/assets/js/vendor/moment/moment.min.js' ) );
		wp_enqueue_script(
			'def-daterangepicker',
			defender_asset_url( '/assets/js/vendor/daterangepicker/daterangepicker.js' )
		);
		wp_localize_script(
			'def-iplockout',
			'lockout_logs',
			array_merge( $this->data_frontend(), $this->dump_routes_and_nonces() )
		);
	}

	/**
	 * All the variables that we will show on frontend, both in the main page, or dashboard widget.
	 *
	 * @return array
	 */
	public function data_frontend(): array {
		$def_filters = [ 'misc' => wd_di()->get( Table_Lockout::class )->get_filters() ];
		$init_filters = [
			'from' => strtotime( '-30 days' ),
			'to' => time(),
			'type' => '',
			'ip' => '',
			'ban_status' => '',
		];

		return array_merge( $this->retrieve_logs( $init_filters, 1 ), $def_filters );
	}

	/**
	 * @param array  $filters
	 * @param int    $paged
	 * @param string $order
	 * @param string $order_by
	 *
	 * @return array
	 */
	private function retrieve_logs( $filters, $paged = 1, $order = 'desc', $order_by = 'id' ): array {
		// User can set the number of logs to retrieve per page.
		$per_page = isset( $_POST['per_page'] ) && 0 !== (int) $_POST['per_page']
			? sanitize_text_field( $_POST['per_page'] )
			: 20;

		$conditions = [ 'ban_status' => $filters['ban_status'] ];

		$count = Lockout_Log::count( $filters['from'], $filters['to'], $filters['type'], $filters['ip'], $conditions );
		$logs  = Lockout_Log::get_logs_and_format( $filters, $paged, $order_by, $order, $per_page );

		if( -1 === (int) $per_page ) {
			$per_page = Lockout_Log::INFINITE_SCROLL_SIZE;
		}

		return [
			'count' => $count,
			'logs' => $logs,
			'per_page' => $per_page,
			'total_pages' => ceil( $count / $per_page ),
		];
	}

	/**
	 * Export the data of this module, we will use this for export to HUB, create a preset etc.
	 */
	public function to_array() {}

	/**
	 * Import the data of other source into this, it can be when HUB trigger the import, or user apply a preset.
	 *
	 * @param array $data
	 */
	public function import_data( $data ) {}

	/**
	 * Remove all settings, configs generated in this container runtime.
	 */
	public function remove_settings() {}

	/**
	 * Remove all data.
	 */
	public function remove_data() {}

	/**
	 * @return array
	 */
	public function export_strings(): array {
		return [];
	}
}
