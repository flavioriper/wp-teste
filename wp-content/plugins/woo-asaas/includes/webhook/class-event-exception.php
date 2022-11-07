<?php
/**
 * Not supported event exception
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Webhook;

/**
 * This exception must be thrown when a webhook endpoint receveid an event not
 * supported by the plugin.
 */
class Event_Exception extends \Exception {
}
