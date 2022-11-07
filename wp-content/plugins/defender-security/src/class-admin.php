<?php
declare( strict_types=1 );

namespace WP_Defender;

use WP_Defender\Behavior\WPMUDEV;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Admin
 *
 * @since 2.4
 */
class Admin {

	/**
	 * @var bool
	 */
	public $is_pro;

	/**
	 * Init admin actions.
	 */
	public function init() {
		$this->is_pro = ( new WPMUDEV() )->is_pro();
		$is_def_page = defender_current_page();
		// Display plugin links.
		add_filter( 'network_admin_plugin_action_links_' . DEFENDER_PLUGIN_BASENAME, [ $this, 'settings_link' ] );
		add_filter( 'plugin_action_links_' . DEFENDER_PLUGIN_BASENAME, [ $this, 'settings_link' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 3 );
		// WP_DEFENDER_PRO sometimes doesn't match $this->is_pro, e.g. WPMU DEV Dashboard is deactivated.
		if ( defined( 'WP_DEFENDER_PRO' ) && WP_DEFENDER_PRO ) {
			// This area is only for Pro version.
			add_action(
				'load-plugins.php',
				function () {
					add_action( 'after_plugin_row_' . DEFENDER_PLUGIN_BASENAME, [ $this, 'show_upgrade_notice' ], 10, 2 );
				},
				22
			);
		} else {
			// This area is only for wordpress.org members.
			// Add plugin upgrade notification.
			add_action( 'in_plugin_update_message-' . DEFENDER_PLUGIN_BASENAME, [ $this, 'show_upgrade_notice' ], 10, 2 );
			if ( $is_def_page && ! is_multisite() ) {
				add_action( 'admin_notices', [ $this, 'show_rating_notice' ] );
			} elseif ( $is_def_page && is_multisite() && is_main_site() ) {
				add_action( 'network_admin_notices', [ $this, 'show_rating_notice' ] );
			}
			add_action( 'wp_ajax_defender_dismiss_notification', [ $this, 'dismiss_notice' ] );
			add_action( 'admin_init', [ $this, 'register_free_modules' ], 20 );
		}
	}

	/**
	 * Return URL link.
	 *
	 * @param string $link_for Accepts: 'docs', 'plugin', 'rate', 'support', 'roadmap'.
	 * @param string $campaign Utm campaign tag to be used in link. Default: ''.
	 * @param string $adv_path Advanced path. Default: ''.
	 *
	 * @return string
	 */
	public function get_link( $link_for, $campaign = '', $adv_path = '' ): string {
		$domain = 'https://wpmudev.com';
		$wp_org = 'https://wordpress.org';
		$utm_tags = "?utm_source=defender&utm_medium=plugin&utm_campaign={$campaign}";
		switch ( $link_for ) {
			case 'docs':
				$link = "{$domain}/docs/wpmu-dev-plugins/defender/{$utm_tags}";
				break;
			case 'plugin':
				$link = "{$domain}/project/wp-defender/{$utm_tags}";
				break;
			case 'rate':
				$link = "{$wp_org}/support/plugin/defender-security/reviews/#new-post";
				break;
			case 'support':
				$link = $this->is_pro ? "{$domain}/get-support/" : "{$wp_org}/support/plugin/defender-security/";
				break;
			case 'roadmap':
				$link = "{$domain}/roadmap/";
				break;
			case 'pro_link':
				$link = "{$domain}/$adv_path";
				break;
			default:
				$link = '';
				break;
		}

		return $link;
	}

	/**
	 * Adds a settings link on plugin page.
	 *
	 * @param array $links Current links.
	 *
	 * @return array
	 */
	public function settings_link( $links ): array {
		$action_links = [];
		$wpmu_dev = new WPMUDEV();
		// Settings link.
		$action_links['dashboard'] = '<a href="' . network_admin_url( 'admin.php?page=wdf-setting' ) . '" aria-label="' . esc_attr( __( 'Go to Defender Settings', 'wpdef' ) ) . '">' . esc_html__( 'Settings', 'wpdef' ) . '</a>';
		// Documentation link.
		$action_links['docs'] = '<a target="_blank" href="' . $this->get_link( 'docs', 'defender_pluginlist_docs' ) . '" aria-label="' . esc_attr( __( 'Docs', 'wpdef' ) ) . '">' . esc_html__( 'Docs', 'wpdef' ) . '</a>';
		if ( ! $wpmu_dev->is_member() ) {
			if ( WP_DEFENDER_PRO_PATH !== DEFENDER_PLUGIN_BASENAME ) {
				$action_links['upgrade'] = '<a style="color: #8D00B1;" target="_blank" href="' . $this->get_link( 'plugin', 'defender_pluginlist_upgrade' ) . '" aria-label="' . esc_attr( __( 'Upgrade to Defender Pro', 'wpdef' ) ) . '">' . esc_html__( 'Upgrade', 'wpdef' ) . '</a>';
			} elseif ( ! $wpmu_dev->is_hosted_site_connected_to_tfh() ) {
				$action_links['renew'] = '<a style="color: #8D00B1;" target="_blank" href="' . $this->get_link( 'plugin', 'defender_pluginlist_renew' ) . '" aria-label="' . esc_attr( __( 'Renew Your Membership', 'wpdef' ) ) . '">' . esc_html__( 'Renew Membership', 'wpdef' ) . '</a>';
			}
		}

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param string[] $links       Plugin Row Meta.
	 * @param string   $file        Plugin Base file.
	 * @param array    $plugin_data Plugin data.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file, $plugin_data ): array {
		$row_meta = [];
		if ( ! defined( 'DEFENDER_PLUGIN_BASENAME' ) || DEFENDER_PLUGIN_BASENAME !== $file ) {
			return $links;
		}

		// Change AuthorURI link.
		if ( isset( $links[1] ) ) {
			$author_uri = $this->is_pro ? 'https://wpmudev.com/' : 'https://profiles.wordpress.org/wpmudev/';
			$author_uri = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$author_uri,
				__( 'WPMU DEV', 'wpdef' )
			);
			$links[1] = sprintf(
					/* translators: %s - author URI */
				__( 'By %s', 'wpdef' ), $author_uri
			);
		}

		if ( ! $this->is_pro ) {
			// Change AuthorURI link.
			if ( isset( $links[2] ) && false === strpos( $links[2], 'target="_blank"' ) ) {
				if ( ! isset( $plugin_data['slug'] ) && $plugin_data['Name'] ) {
					$links[2] = sprintf(
						'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
						esc_url(
							network_admin_url(
								'plugin-install.php?tab=plugin-information&plugin=defender-security' .
								'&TB_iframe=true&width=600&height=550'
							)
						),
						/* translators: %s: Plugin name. */
						esc_attr( sprintf( __( 'More information about %s', 'wpdef' ), $plugin_data['Name'] ) ),
						esc_attr( $plugin_data['Name'] ),
						__( 'View details', 'wpdef' )
					);
				} else {
					$links[2] = str_replace( 'href=', 'target="_blank" href=', $links[2] );
				}
			}
			$row_meta['rate'] = '<a href="' . esc_url( $this->get_link( 'rate' ) ) . '" aria-label="' . esc_attr__( 'Rate Defender', 'wpdef' ) . '" target="_blank">' . esc_html__( 'Rate Defender', 'wpdef' ) . '</a>';
			$row_meta['support'] = '<a href="' . esc_url( $this->get_link( 'support' ) ) . '" aria-label="' . esc_attr__( 'Support', 'wpdef' ) . '" target="_blank">' . esc_html__( 'Support', 'wpdef' ) . '</a>';
		} else {
			// Change 'Visit plugins' link to 'View details'.
			if ( isset( $links[2] ) && false !== strpos( $links[2], 'project/wp-defender' ) ) {
				$links[2] = sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( $this->get_link( 'pro_link', '', 'project/wp-defender/' ) ),
					__( 'View details', 'wpdef' )
				);
			}
			$row_meta['support'] = '<a href="' . esc_url( $this->get_link( 'support' ) ) . '" aria-label="' . esc_attr__( 'Premium Support', 'wpdef' ) . '" target="_blank">' . esc_html__( 'Premium Support', 'wpdef' ) . '</a>';
		}
		$row_meta['roadmap'] = '<a href="' . esc_url( $this->get_link( 'roadmap' ) ) . '" aria-label="' . esc_attr__( 'Roadmap', 'wpdef' ) . '" target="_blank">' . esc_html__( 'Roadmap', 'wpdef' ) . '</a>';

		return array_merge( $links, $row_meta );
	}

	/**
	 * Dismiss notice.
	 */
	public function dismiss_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'defender_dismiss_notification' ) ) {
			wp_send_json_error(
				[ 'message' => __( 'Invalid request, you are not allowed to do that action.', 'wpdef' ) ]
			);
		}

		$notification_name = ! empty( $_POST['prop'] ) ? sanitize_text_field( $_POST['prop'] ) : false;
		if ( false === $notification_name ) {
			wp_send_json_error(
				[ 'message' => __( 'Invalid request, allowed data not provided.', 'wpdef' ) ]
			);
		}
		update_site_option( $notification_name, true );

		wp_send_json_success();
	}

	/**
	 * Register sub-modules.
	 */
	public function register_free_modules() {
		if (
			! file_exists( defender_path( 'extra/free-dashboard/module.php' ) )
			|| ! file_exists( defender_path( 'extra/recommended-plugins-notice/notice.php' ) )
		) {
			return;
		}
		/* @noinspection PhpIncludeInspection */
		require_once( defender_path( 'extra/free-dashboard/module.php' ) );
		/* @noinspection PhpIncludeInspection */
		require_once( defender_path( 'extra/recommended-plugins-notice/notice.php' ) );

		// Register the current plugin.
		do_action(
			'wdev_register_plugin',
			/* 1             Plugin ID */ DEFENDER_PLUGIN_BASENAME,
			/* 2          Plugin Title */ 'Defender',
			/* 3 https://wordpress.org */ '/plugins/defender-security/',
			/* 4      Email Button CTA */ __( 'Get Fast!', 'wpdef' )
			/* 5  Mailchimp List id for the plugin - No */
		);

		// Recommended plugin notice.
		do_action(
			'wpmudev-recommended-plugins-register-notice',
			DEFENDER_PLUGIN_BASENAME, // Plugin basename
			'Defender', // Plugin Name
			[
				'toplevel_page_wp-defender',
				'toplevel_page_wp-defender-network',
			],
			[ 'after', '.sui-wrap .sui-header' ]
		);
	}

	/**
	 * Show the rating notice.
	 */
	public function show_rating_notice() {
		// @since 2.6.1
		if ( get_site_option( 'defender_rating_success', apply_filters( 'wd_display_rating', false ) ) ) {
			return;
		}

		$install_date = (int) get_site_option( 'defender_free_install_date', false );
		// @since 2.6.1
		$days_later_dismiss = get_site_option(
			'defender_days_rating_later_dismiss',
			apply_filters( 'wd_dismiss_rating', false )
		);

		if (
			$install_date && ! $days_later_dismiss
			&& current_time( 'timestamp' ) > strtotime( '+7 days', $install_date )
		) { ?>
			<div id="defender-free-usage-notice"
				class="defender-rating-notice notice notice-info"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'defender_dismiss_notification' ) ); ?>">

				<p style="color: #72777C; line-height: 22px;"><?php esc_html_e( 'We\'ve spent countless hours developing Defender and making it free for you to use. We would really appreciate it if you dropped us a quick rating!', 'wpdef' ); ?></p>

				<p>
					<button type="button" class="button button-primary button-large"
						data-prop="defender_rating_success"><?php esc_html_e( 'Rate Defender', 'wpdef' ); ?></button>
					<a href="#" class="dismiss"
						style="margin-left: 11px; color: #555; line-height: 16px; font-weight: 500; text-decoration: none;"
						data-prop="defender_days_rating_later_dismiss"><?php esc_html_e( 'Maybe later', 'wpdef' ); ?></a>
				</p>
			</div>
			<?php
		}
		?>

		<script type="text/javascript">
			jQuery('.defender-rating-notice a, .defender-rating-notice button').on('click', function (e) {
				e.preventDefault();

				var $notice = jQuery(e.currentTarget).closest('.defender-rating-notice'),
					prop = jQuery(this).data('prop'),
					ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

				if ('defender_rating_success' === prop) {
					window.open('https://wordpress.org/support/plugin/defender-security/reviews/#new-post', '_blank');
				}

				jQuery.post(
					ajaxUrl,
					{
						action: 'defender_dismiss_notification',
						prop: prop,
						_ajax_nonce: $notice.data('nonce')
					}
				).always(function () {
					$notice.hide();
				});
			});
		</script>

		<?php
	}

	/**
	 * Show plugin update notice.
	 *
	 * @param string $plugin_file
	 * @param array  $response
	 *
	 * @since 2.8.3
	 */
	public function show_upgrade_notice( $plugin_file, $response ) {
		if ( version_compare( PHP_VERSION, '7.2', '>=' ) ) {
			return;
		}
		$plugin_data = (object) $response;
		// Preparing for the next major plugin v3.0.0 and subsequent versions.
		if ( isset( $plugin_data->new_version )
			&& '3' === substr( trim( $plugin_data->new_version ), 0, 1 )
			&& version_compare( DEFENDER_VERSION, '3', '<' )
		) {
			// Major plugin version or subsequent versions.
			$new_version = ( '3' === $plugin_data->new_version ) ? '3.0.0' : $plugin_data->new_version;
			// Collect notice.
			$notice = '<p>' . esc_html__( 'There is a new version of Defender available - Deprecating PHP version 7.1. and lower.', 'wpdef' ) . '</p>';
			$notice .= '<span style="display: inherit;background: #D63638;color: #FFFFFF;margin-top: 12px;padding: 10px 12px;border-radius: 2px;">';
			$notice .= '<strong>';
			$notice .= esc_html__( 'Important Upgrade Notice:', 'wpdef' );
			$notice .= '</strong> ';
			$notice .= sprintf(
			/* translators: %s: Version number. */
				esc_html__( 'The upcoming version %s is compatible only with PHP 7.2.0 and higher. Please make sure to upgrade your PHP version.', 'wpdef' ),
				$new_version
			);
			$notice .= '</span>';

			echo "<script type='text/javascript'>
            (function ($) {
               $( function () {
                   $( '.wp-list-table tr[data-plugin=\"" . esc_attr( $plugin_data->plugin ) . "\"] .notice-warning' ).html( '" . addslashes( $notice ) . "' ).css('padding-bottom', '10px');
               });
            })(jQuery);
           </script>";
		}
	}
}
