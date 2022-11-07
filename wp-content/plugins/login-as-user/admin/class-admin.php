<?php
/* ======================================================
 # Login as User for WordPress - v1.4.4 (free version)
 # -------------------------------------------------------
 # For WordPress
 # Author: Web357
 # Copyright @ 2014-2022 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https:/www.web357.com
 # Demo: https://demo.web357.com/wordpress/login-as-user/wp-admin/
 # Support: support@web357.com
 # Last modified: Tuesday 14 June 2022, 06:08:05 PM
 ========================================================= */
class LoginAsUser_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * This fields
	 *
	 * @var [class]
	 */
	public $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_name_clean = 'login-as-user';
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		wp_enqueue_style( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'css/admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		wp_enqueue_script( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'js/admin.min.js', array( 'jquery', ), $this->version, true );
		wp_localize_script( $this->plugin_name_clean, 'loginasuserAjax', array( 'loginasuser_ajaxurl' => admin_url( 'admin-ajax.php' )));        
	}

	
}