<?php
/**
 * Inconsistency data exception exception
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Webhook;

/**
 * This exception must be thrown when a webhook call happen when it can't
 * processed because the status of the order.
 */
class Inconsistency_Data_Exception extends \Exception {
}
