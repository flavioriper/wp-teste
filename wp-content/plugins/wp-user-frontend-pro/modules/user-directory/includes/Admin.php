<?php
namespace WPUF\UserDirectory;

/**
 * Class Admin
 */
class Admin {

    /**
     * Constructor for the Admin class
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
        $this->init_classes();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    public function init_hooks() {
        register_activation_hook( WPUF_UD_FILE, array( $this, 'install_plugin' ) );

        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'wpuf_admin_menu', [ $this, 'add_admin_menu_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Includes all required file for the admin
     *
     * @return void
     */
    public function includes() {
        require_once WPUF_UD_INCLUDES . '/Admin/Settings.php';
        require_once WPUF_UD_INCLUDES . '/Admin/Builder.php';
        require_once WPUF_UD_INCLUDES . '/Admin/Form.php';
    }

    /**
     * Init all required classes
     *
     * @return void
     */
    public function init_classes() {
        new \WPUF\UserDirectory\Admin\Builder();
        new \WPUF\UserDirectory\Admin\Settings();
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function enqueue_scripts() {
        /**
         * Enqueue all admin scripts here
         */
        wp_enqueue_script( 'wpuf-user-directory-admin-script' );

        /**
         * Enqueue all admin styles here
         */
        wp_enqueue_style( 'wpuf-user-directory-admin-style' );
    }

    /**
     * Add admin menu page
     *
     * @return void
     */
    public function add_admin_menu_page() {
        add_submenu_page( 'wp-user-frontend', __( 'User Listing', 'wpuf-userlisting' ), __( 'User Listing', 'wpuf-userlisting' ), 'manage_options', 'wpuf_userlisting', [ $this, 'include_page_template' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wpuf_userlisting', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Include user directory admin page
     *
     * @return void
     */
    public function include_page_template() {
        include WPUF_UD_ROOT . '/includes/views/admin/user-directory.php';
    }

    /**
     * Install activation
     *
     * @return void
     */
    public function install() {
        global $wp_roles;

        $role = array();
        global $wp_roles;

        foreach ( $wp_roles->get_names() as $key => $role_name ) {
            $role[] = $key;
        }

        $current_user_role = array_merge( $role, array( 'guest' ) ); // add "guest" on viewer roles
        $user_listing      = get_option( 'wpuf_userlisting', array() );

        // bail out if existing options already there
        if ( $user_listing ) {
            return;
        }

        $query = array(
            'fields' => array(
                array(
                    'type'              => 'section',
                    'label'             => __( 'Username', 'wpuf-pro' ),
                    'meta'              => 'user_login',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                    'in_table'          => true,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'First Name', 'wpuf-pro' ),
                    'meta'              => 'first_name',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                    'in_table'          => true,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'Last Name', 'wpuf-pro' ),
                    'meta'              => 'last_name',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                    'in_table'          => true,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'Nickname', 'wpuf-pro' ),
                    'meta'              => 'nickname',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'E-mail', 'wpuf-pro' ),
                    'meta'              => 'user_email',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'Website', 'wpuf-pro' ),
                    'meta'              => 'user_url',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                ),
                array(
                    'type'              => 'meta',
                    'label'             => __( 'Biographical Info', 'wpuf-pro' ),
                    'meta'              => 'description',
                    'all_user_role'     => $role,
                    'current_user_role' => $current_user_role,
                ),
            ),
            'settings' => array(
                'avatar' => true,
            ),
        );

        update_option( 'wpuf_userlisting', $query );
    }
}
