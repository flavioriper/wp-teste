<?php

/**
 * API skip class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Webhook;

/**
 * Manage if API request can be skiped
 * 
 * The skip is used for tests purpose.
 */
class Api_Skip
{
    /**
     * Skip the API request if want skip and is allowed
     *
     * @return boolean
     */
    public function can_skip() {
        $is_allowed = $this->is_allowed();

        return $is_allowed;
    }

    /**
     * Allow skip api request
     *
     * @return boolean
     */
    private function is_allowed() {
        $skip_api_is_allowed = getenv( 'ALLOW_SKIP_API' );

        return 'true' === $skip_api_is_allowed;
    }
}
