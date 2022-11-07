<?php 

/**
 * @author Lightson
 * @version 1.0.0
 * @package Global Mixed
 * 
 * Adiciona os scripts adicionais
 * 
 */

 function add_this_child_scripts() {
    //Scripts
    wp_enqueue_script('jquery-mask', get_stylesheet_directory_uri() . '/assets/plugins/jquery-mask/jquery.mask.min.js', array('jquery'), null, true);
    wp_enqueue_script('main-child', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery'), null, true);
 } 
 add_action('wp_enqueue_scripts', 'add_this_child_scripts');




/**
 * @author Lightson
 * @version 1.0.0
 * @package Woocommerce
 * 
 * Adiciona o Campo CRECI a lista de campos de "Billing" ao woocommerce no checkout
 * 
 */

add_filter('woocommerce_checkout_fields', 'custom_override_billing_checkout_fields');

function custom_override_billing_checkout_fields($fields) {

    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $showCRECI = true;

    $disableCRECI = array(
        'aluguel', 'lancamento'
    );

    foreach($items as $item => $values) {  
        if(in_array(get_post_meta( $values['data']->get_id(), 'publish_type', true ), $disableCRECI) ) {
            $showCRECI = false; 
            break;
        } 
    }

    if($showCRECI) {
        $fields['billing']['billing_location_type'] = array(
            'label' => __('CRECI', 'woocommerce'),
            'required' => true
        );
    }

    return $fields;
}