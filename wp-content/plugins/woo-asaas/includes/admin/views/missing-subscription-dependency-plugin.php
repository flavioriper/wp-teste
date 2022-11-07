<?php
/**
 * Missing subscription plugin dependencies notice.
 *
 * @package WooAsaas
 */

use WC_Asaas\Admin\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dependency = WC_Asaas\Admin\Plugin_Dependency::get_instance();
$plugins    = get_plugins();

$all_dependencies_ok = true;

foreach ( $dependency->get_subscription_dependencies() as $plugin_slug => $plugin ) :
	if ( is_plugin_active( $plugin['plugin_file'] ) ) :
		if ($plugins[$plugin['plugin_file']]['Version'] < $plugin['min_version']) {
			/* Plugin doesn't meet the minimum required version */
			$button_action = sprintf( 'https://woocommerce.com/products/%s', $plugin_slug );
			$button_label = __( 'Visit plugin site', 'woo-asaas' );
			$target = '_blank';
		} else {
			continue;
		}
	else:
		$target = '';
		if ( ! key_exists( $plugin['plugin_file'], $plugins ) ) :
			/* Plugin not installed and hasn't permission to install */
			$button_action = sprintf( 'http://wordpress.org/plugins/%s/', $plugin_slug );

			/* translators: %s: The plugin name  */
			$button_label = sprintf( __( 'Install %s', 'woo-asaas' ), $plugin['name'] );

			if ( current_user_can( 'install_plugins' ) ) :
				/* Plugin not installed and has permission to install */
				$button_action = wp_nonce_url(
					self_admin_url( sprintf( 'update.php?action=install-plugin&plugin=%s', $plugin_slug ) ),
					sprintf( 'install-plugin_%s', $plugin_slug )
				);
			endif;
		else :
			/* Plugin not active */
			$button_action = wp_nonce_url(
				self_admin_url( sprintf( 'plugins.php?action=activate&plugin=%s&plugin_status=active', $plugin['plugin_file'] ) ),
				sprintf( 'activate-plugin_%s', $plugin['plugin_file'] )
			);
			/* translators: %s: The plugin name  */
			$button_label = sprintf( __( 'Activate %s', 'woo-asaas' ), $plugin['name'] );
		endif;
	endif;
	$all_dependencies_ok = false;
	?>
	<div class="missing-subscription-dependency-plugin">
		<p>
			<?php
				/* translators: 1: The plugin name, 2: The dependency plugin name  */
				echo wp_kses_post( sprintf( __( 'The subscriptions feature depends on the <strong>%1$s plugin version %2$s or greater</strong> to work.', 'woo-asaas' ), $plugin['name'], $plugin['min_version'] ) );
			?>
		</p>

		<p><a href="<?php echo esc_url( $button_action ); ?>" class="button button-primary" target="<?php echo $target; ?>" rel="noopener noreferrer"><?php echo esc_html( $button_label ); ?></a></p>
	</div>
<?php endforeach; ?>

<?php
if( $all_dependencies_ok === true ):
	View::get_instance()->load_template_file( 'subscription-feature-is-ready.php' );
endif;