<?php
/**
 * WPUF Update class
 *
 * Performas license validation and update checking
 *
 * @package WPUF
 */
class WPUF_Updates {

    /**
     * Appsero License Instance
     *
     * @var \Appsero\License
     */
    private $license;

    /**
     * The license product ID
     *
     * @var string
     */
    private $product_id = 'wpuf-pro';

    /**
     * Initialize the class
     */
    public function __construct() {
        if ( ! class_exists( '\Appsero\Client' ) ) {
            return;
        }

        $this->init_appsero();

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                add_action( 'admin_notices', [ $this, 'license_enter_notice' ] );
            }
        } else {
            add_action( 'admin_notices', [ $this, 'license_enter_notice' ] );
        }

        add_action( 'in_plugin_update_message-' . plugin_basename( WPUF_PRO_FILE ), [ $this, 'plugin_update_message' ] );
    }

    /**
     * Initialize the updater
     *
     * @return void
     */
    protected function init_appsero() {
        $client = new \Appsero\Client( '4728ae40-53cf-4093-9e88-58eefe0a3f87', __( 'WPUF Pro', 'wpuf-pro' ), WPUF_PRO_FILE );

        // Active license page and checker
        $args = [
            'type'        => 'submenu',
            'menu_title'  => __( 'License', 'wpuf-pro' ),
            'page_title'  => __( 'WPUF Pro License', 'wpuf-pro' ),
            'capability'  => 'manage_options',
            'parent_slug' => 'wp-user-frontend',
            'menu_slug'   => 'wpuf_updates',
        ];

        $this->license = $client->license();

        // just to be safe if old Appsero SDK is being used
        if ( method_exists( $this->license, 'set_option_key' ) ) {
            $this->license->set_option_key( 'wpuf_license' );
        }

        $this->license->add_settings_page( $args );

        // Active automatic updater
        $client->updater();
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @return void
     */
    public function license_enter_notice() {
    	return;
        if ( $this->license->is_valid() ) {
            return;
        } ?>
        <div class="notice error wpuf-license-notice">
            <div class="wpuf-license-notice__logo">
                <img src="<?php echo WPUF_ASSET_URI; ?>/images/welcome/wpuf-logo.png" alt="WPUF Logo">
            </div>
            <div class="wpuf-license-notice__message">
                <strong><?php esc_html_e( 'Activate WPUF Pro License', 'wpuf-pro' ); ?></strong>
                <p><?php printf( __( 'Please <a href="%s">enter</a> your valid <strong>WPUF Pro</strong> plugin license key to unlock more features, premium support and future updates.', 'wpuf-pro' ), admin_url( 'admin.php?page=wpuf_updates' ) ); ?></p>
            </div>

            <div class="wpuf-license-notice__button">
                <a class="button" href="<?php echo admin_url( 'admin.php?page=wpuf_updates' ); ?>"><?php esc_html_e( 'Activate License', 'wpuf-pro' ); ?></a>
            </div>
        </div>

        <style>
            .notice.wpuf-license-notice {
                display: flex;
                align-items: center;
                padding: 15px 10px;
                border: 1px solid #e4e4e4;
                border-left: 4px solid #fb6e76;
                background-repeat: no-repeat;
                background-position: bottom right;
            }

            .wpuf-license-notice__logo {
                margin-right: 10px;
            }

            .wpuf-license-notice__logo img {
                width: 48px;
                height: auto;
            }

            .wpuf-license-notice__message {
                flex-basis: 100%;
            }

            .wpuf-license-notice__button {
                padding: 0 25px;
            }

            .wpuf-license-notice__button .button {
                background: #fb6e76;
                color: #fff;
                border-color: #fb6e76;
                font-size: 15px;
                padding: 3px 15px;
            }

            .wpuf-license-notice__button .button:hover {
                background: #f1545d;
                color: #fff;
                border-color: #fb6e76;
            }
        </style>
        <?php
    }

    /**
     * Show plugin udpate message
     *
     * @since  2.7.1
     *
     * @param array $args
     *
     * @return void
     */
    public function plugin_update_message( $args ) {
        if ( $this->license->is_valid() ) {
            return;
        }

        $upgrade_notice = sprintf(
            '</p><p class="wpuf-pro-plugin-upgrade-notice" style="background: #dc4b02;color: #fff;padding: 10px;">Please <a href="%s" target="_blank">activate</a> your license key for getting regular updates and support',
            admin_url( 'admin.php?page=wpuf_updates' )
        );

        echo apply_filters( $this->product_id . '_in_plugin_update_message', wp_kses_post( $upgrade_notice ) );
    }
}
