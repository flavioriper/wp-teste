<?php
/**
 * Plugin Name:     Asaas Gateway for WooCommerce
 * Plugin URI:      https://www.asaas.com
 * Description:     Take transparent credit card and bank ticket payment checkouts on your store using Asaas.
 * Author:          Asaas
 * Author URI:      https://www.asaas.com
 * Text Domain:     woo-asaas
 * Domain Path:     /languages
 * Version:         2.0.1
 *
 * @package         WooAsaas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'autoload.php';

add_action( 'plugins_loaded', array( \WC_Asaas\WC_Asaas::class, 'get_instance' ) );
