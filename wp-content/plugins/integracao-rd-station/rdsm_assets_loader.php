<?php

class RDSMAssetsLoader {
  private static $src_url;

  public static function load_assets() {
    self::$src_url = RDSM_ASSETS_URL;
    add_action('admin_enqueue_scripts', array(get_class(), 'settings_page_style'));
    add_action('admin_enqueue_scripts', array(get_class(), 'settings_page_scripts'));
    add_action('admin_enqueue_scripts', array(get_class(), 'form_integrations_style'));
    add_action('admin_enqueue_scripts', array(get_class(), 'post_page_scripts'));
  }

  public static function form_integrations_style($hook) {
    $screen = get_current_screen();

    if ( 'post.php' != $hook && 'post-new.php' != $hook ) return;
    wp_enqueue_style( 'rd_admin_style', self::$src_url . '/styles/admin.css' );
  }

  public static function settings_page_scripts($hook) {
    global $rdsm_settings_page;
    if ($hook != $rdsm_settings_page) return;
    wp_enqueue_script('rdsm_general_settings_script', self::$src_url . '/js/general_settings.js');
    wp_enqueue_script('rdsm_tracking_code_script', self::$src_url . '/js/tracking_code.js');
    wp_enqueue_script('rdsm_authorization_script', self::$src_url . '/js/authorization.js');
    wp_enqueue_script('rdsm_woocommerce_fields_script', self::$src_url . '/js/woocommerce_fields.js');
    wp_enqueue_script('rdsm_log_file_script', self::$src_url . '/js/log_file.js');
  }

  public static function settings_page_style($hook) {
    global $rdsm_settings_page;
    if ($hook != $rdsm_settings_page) return;
    wp_enqueue_style('rdsm_settings_style', self::$src_url . '/styles/settings.css');
  }

  public static function post_page_scripts($hook) {
    if ( 'post.php' != $hook && 'post-new.php' != $hook ) return;
    wp_enqueue_script('rdsm_custom_fields_page_script', self::$src_url . '/js/custom_fields.js');
  }
}
