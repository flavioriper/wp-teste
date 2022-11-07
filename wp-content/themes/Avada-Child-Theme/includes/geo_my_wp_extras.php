<?php
/**
 * @author Lightson
 * @version 1.0.0
 * @package Geo My WP
 * 
 * Adiciona os scripts adicionais
 * 
 */

function add_geo_wp_scripts() {

    //Estilos
    wp_enqueue_style( 'geocoder-child', get_stylesheet_directory_uri() . '/assets/css/geocoder.css', array(), '1.0.0' );

 } 
 add_action('wp_enqueue_scripts', 'add_geo_wp_scripts');
