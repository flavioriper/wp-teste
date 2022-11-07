<?php

/**
 * Determine form is vendor reg or not
 *
 * @since 3.4.7
 *
 * @param $form_id
 *
 * @return bool
 */
function wpuf_is_vendor_reg( $form_id ) {
    $vendor_reg = false;

    if ( isset( $form_id ) ) {
        $form_settings     = wpuf_get_form_settings( $form_id );
        $profile_templates = ( new WPUF_Admin_Profile_Form_Template() )->wpuf_get_profile_form_templates();

        $profile_templates = array_map(
            function ( $template_name, $profile_form ) {
                return $template_name;
            }, array_keys( $profile_templates ), $profile_templates
        );

        $role = [ 'shop_manager', 'seller' ];

        if ( in_array( $form_settings['form_template'], $profile_templates, true ) && in_array( $form_settings['role'], $role, true ) ) {
            $vendor_reg = true;
        }
    }

    return $vendor_reg;
}

/**
 * Get setup wizard or WC account page url
 *
 * @since 3.4.7
 *
 * @return url
 */
function wpuf_get_dokan_redirect_url() {
    $wc_acc_page  = get_option( 'woocommerce_myaccount_page_id' );
    $redirect_url = wpuf_get_option( 'account_page', 'wpuf_my_account', 0 );

    if ( function_exists( 'dokan_get_option' ) ) {
        $redirect_url = dokan_get_option( 'disable_welcome_wizard', 'dokan_general', 'off' ) !== 'on' ? get_site_url() . '/?page=dokan-seller-setup' : '';
    }

    if ( $wc_acc_page ) {
        $redirect_url = $redirect_url === '' ? get_permalink( $wc_acc_page ) : $redirect_url;
    }

    return $redirect_url;
}

/**
 * Include a template file
 *
 * If the third arguments is not passed,
 * looks up the file on the child theme directory, then on parent theme directory,
 * if not found loads from pro plugin folder
 * We can use this function to include a template file from module directory
 *
 * @since 3.1.11
 * @since 3.4.11 param $wpuf_pro_dir added
 *
 * @param string $file file name
 * @param string $wpuf_pro_dir the file path
 *
 * @return void
 */
if ( ! function_exists( 'wpuf_load_pro_template' ) ) {
    function wpuf_load_pro_template($file, $args = [], $wpuf_pro_dir = '' ) {
        if ( ! $wpuf_pro_dir ) {
            $wpuf_pro_dir = WPUF_PRO_INCLUDES . '/templates/';
        }
        if (!wpuf()->is_pro()) {
            return;
        }

        if ($args && is_array($args)) {
            extract($args); //phpcs:ignore
        }

        $child_theme_dir  = get_stylesheet_directory() . '/wpuf/';
        $parent_theme_dir = get_template_directory() . '/wpuf/';

        if (file_exists($child_theme_dir . $file)) {
            include $child_theme_dir . $file;
        } else if (file_exists($parent_theme_dir . $file)) {
            include $parent_theme_dir . $file;
        } else {
            include $wpuf_pro_dir . $file;
        }
    }
}
