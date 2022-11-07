<?php
declare( strict_types=1 );

namespace WP_Defender\Model\Setting;

use Calotes\Model\Setting;

class User_Agent_Lockout extends Setting {
	/**
	 * Option name.
	 * @var string
	 */
	public $table = 'wd_user_agent_settings';

	/**
	 * @var bool
	 * @defender_property
	 */
	public $enabled = false;

	/**
	 * @var string
	 * @defender_property
	 */
	public $blacklist = '';

	/**
	 * @var string
	 * @defender_property
	 */
	public $whitelist = '';

	/**
	 * @var string
	 * @defender_property
	 */
	public $message = '';

	/**
	 * @var bool
	 * @defender_property
	 */
	public $empty_headers = false;

	protected $rules = [
		[ [ 'enabled', 'empty_headers' ], 'boolean' ],
	];

	/**
	 * @return array
	*/
	public function get_default_values(): array {
		// Allowled User Agents.
		$whitelist = "a6-indexer\nadsbot-google\naolbuild\napis-google\nbaidu\nbingbot\nbingpreview";
		$whitelist .= "\nbutterfly\ncloudflare\nchrome\nduckduckbot\nembedly\nfacebookexternalhit\nfacebot\ngoogle page speed";
		$whitelist .= "\ngooglebot\nia_archiver\nlinkedinbot\nmediapartners-google\nmsnbot\nnetcraftsurvey";
		$whitelist .= "\noutbrain\npinterest\nquora\nslackbot\nslurp\ntweetmemebot\ntwitterbot\nuptimerobot";
		$whitelist .= "\nurlresolver\nvkshare\nw3c_validator\nwordpress\nwp rocket\nyandex";

		return [
			'message' => __( 'You have been blocked from accessing this website.', 'wpdef' ),
			'whitelist' => $whitelist,
			// Blocked User Agents.
			'blacklist' => "MJ12Bot\nAhrefsBot\nSEMrushBot\nDotBot",
		];
	}

	protected function before_load(): void {
		$default_values = $this->get_default_values();
		$this->message = $default_values['message'];
		$this->whitelist = $default_values['whitelist'];
		$this->blacklist = $default_values['blacklist'];
	}

	/**
	 * @return bool
	 */
	public function is_active(): bool {
		return (bool) apply_filters(
			'wd_user_agents_enable',
			$this->enabled
		);
	}

	/**
	 * Get list of blocklisted or allowlisted data.
	 *
	 * @param string $type blocklist|allowlist
	 * @param bool   $lower
	 *
	 * @return array
	 */
	public function get_lockout_list( $type = 'blocklist', $lower = true ): array {
		$data = ( 'blocklist' === $type ) ? $this->blacklist : $this->whitelist;
		$arr = is_array( $data ) ? $data : array_filter( explode( PHP_EOL, $data ) );
		$arr = array_map( 'trim', $arr );
		if ( $lower ) {
			$arr = array_map( 'strtolower', $arr );
		}

		return $arr;
	}

	/**
	 * Define settings labels.
	 *
	 * @return array
	 */
	public function labels(): array {
		return [
			'enabled' => __( 'User Agent Banning', 'wpdef' ),
			'message' => __( 'Message', 'wpdef' ),
			'blacklist' => __( 'Blocklist', 'wpdef' ),
			'whitelist' => __( 'Allowlist', 'wpdef' ),
			'empty_headers' => __( 'Empty Headers', 'wpdef' ),
		];
	}

	/**
	 * Get the access status of this UA.
	 *
	 * @param string $ua
	 *
	 * @return array
	 */
	public function get_access_status( $ua ): array {
		$blocklist = str_replace( '#', '\#', $this->get_lockout_list( 'blocklist' ) );
		$allowlist = str_replace( '#', '\#', $this->get_lockout_list( 'allowlist' ) );

		$blocklist_regex_pattern = '#' . implode( '|', $blocklist ) . '#i';
		$allowlist_regex_pattern = '#' . implode( '|', $allowlist ) . '#i';

		$blocklist_match = preg_match( $blocklist_regex_pattern, $ua );
		$allowlist_match = preg_match( $allowlist_regex_pattern, $ua );

		if ( empty( $blocklist_match ) && empty( $allowlist_match ) ) {

			return [ 'na' ];
		}

		$result = [];

		if ( ! empty( $blocklist_match ) && empty( $allowlist_match ) ) {
			$result[] = 'banned';
		}
		if ( ! empty( $allowlist_match ) ) {
			$result[] = 'allowlist';
		}

		return $result;
	}

	/**
	 * @param string $ua
	 * @param string $list blocklist|allowlist
	 *
	 * @return bool
	 */
	public function is_ua_in_list( $ua, $list ): bool {
		$arr = str_replace( '#', '\#', $this->get_lockout_list( $list ) );

		$list_regex_pattern = '#' . implode( '|', $arr ) . '#i';

		$list_match = preg_match( $list_regex_pattern, $ua );

		return ! empty( $list_match );
	}

	/**
	 * Remove User Agent from a list.
	 *
	 * @param string $ua
	 * @param string $list blocklist|allowlist
	 *
	 * @return void
	 */
	public function remove_from_list( $ua, $list ) {
		$arr = $this->get_lockout_list( $list );
		// Array can contain uppercase.
		$orig_arr = str_replace( '#', '\#', $this->get_lockout_list( $list, false ) );

		$list_regex_pattern = '#' . implode( '|', $arr ) . '#i';

		$list_match = preg_match( $list_regex_pattern, $ua );

		if ( false !== $list_match ) {

			// Plain string match. For e.g. r.n regex matches ran & run but we can add/block UA string name if user send the useragent name as run then we include that in allowlist so run won't be blocked but ran will be blocked.
			$key = array_search( $ua, $arr, true );

			if ( false !== $key && isset( $orig_arr[ $key ] ) ) {
				unset( $orig_arr[ $key ] );
				$is_string_match = true;
			} else {
				// If plain string not matched then add the user agent in opposite list if unban is clicked then add that string to allow list, else if ban user agent clicked then add that string to blocklist though allow list take higher priority i.e. in allow list r.n present then adding r.n or run or ran in blocklist won't block the user agent because of priority.
				$is_string_match = false;
			}

			if ( 'blocklist' === $list ) {
				$this->blacklist = implode( PHP_EOL, $orig_arr );

				if ( false === $is_string_match ) {
					$this->whitelist = $this->push_ua_to_list( $ua, 'allowlist' );
				}
			} elseif ( 'allowlist' === $list ) {
				$this->whitelist = implode( PHP_EOL, $orig_arr );

				if ( false === $is_string_match ) {
					$this->blacklist = $this->push_ua_to_list( $ua, 'blocklist' );
				}
			}

			$this->save();
		}
	}

	/**
	 * Add an UA to the list.
	 *
	 * @param string $ua
	 * @param string $list blocklist|allowlist
	 *
	 * @return void
	 */
	public function add_to_list( $ua, $list ) {
		if ( 'blocklist' === $list ) {
			$this->blacklist = $this->push_ua_to_list( $ua, $list );
		} elseif ( 'allowlist' === $list ) {
			$this->whitelist = $this->push_ua_to_list( $ua, $list );
		}

		$this->save();
	}

	/**
	 * Push the UA to either blocklist or allowlist
	 *
	 * @param string $ua User agent name.
	 * @param string $list List type i.e. blocklist or allowlist.
	 *
	 * @return string List as string format with UA delimited with newline character.
	 */
	private function push_ua_to_list( string $ua, string $list ): string {
		$arr = $this->get_lockout_list( $list, false );
		$arr[] = trim( $ua );
		$arr = array_unique( $arr );

		return implode( PHP_EOL, $arr );
	}
}
