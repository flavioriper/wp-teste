<?php

require_once dirname( __FILE__ ) . '/../classes/mailchimp.php';

/**
 * Check and update API key and save lists in options table
 * @param  string  $api_key
 * @return boolean
 */
function save_mailchimp_api( $api_key ) {
    $mail_chimp = new MailChimp( $api_key );
    $response = $mail_chimp->call( 'lists', wpuf_mailchimp_get_query_params() );

    $lists = array();

    if ( $response ) {
        foreach ( $response['lists'] as $value ) {
            $lists[] = array(
                'id' => $value['id'],
                'name' => $value['name'],
                'web_id' => $value['web_id'],
            );
        }

        update_option( 'wpuf_mc_lists', $lists );
    }

    if ( isset( $response['status'] ) && $response['status'] === 'error' ) {
        $resp = array(
            'message' => $response['error'],
            'status' => false,
        );
    } else {
        $resp = array(
            'message' => __( 'Succesfully inserted', 'wpuf-pro' ),
            'status' => true,
        );
    }

    return $resp;
}

/**
 * Refresh the lish of API
 */
function refresh_mailchimp_api_lists() {
    $mail_chimp = new MailChimp( get_option( 'wpuf_mailchimp_api_key' ) );
    $response = $mail_chimp->call( 'lists', wpuf_mailchimp_get_query_params() );

    $lists = array();

    if ( $response ) {
        foreach ( $response['lists'] as $value ) {
            $lists[] = array(
                'id' => $value['id'],
                'name' => $value['name'],
                'web_id' => $value['web_id'],
            );
        }

        update_option( 'wpuf_mc_lists', $lists );
    }
}

function wpuf_mailchimp_get_query_params() {
    return apply_filters( 'wpuf_mailchimp_params', [ 'count' => 1000 ] );
}
