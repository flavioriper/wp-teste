<?php

namespace WC_Autocomplete_Address;

use WC_Autocomplete_Address;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

class Checkout_Scripts {
  function __construct() {
    add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
  }

  public function load_scripts() {
    if ( is_checkout() ) {
      wp_enqueue_script(
        'wc-autocomplete-address',
        WC_Autocomplete_Address::plugin_url() . '/assets/js/autocomplete-address.4dd81181ce7c15cd78b1.js',
        array( 'jquery' ),
        WC_Autocomplete_Address::VERSION,
        true
      );
    }
  }
}

new Checkout_Scripts();
