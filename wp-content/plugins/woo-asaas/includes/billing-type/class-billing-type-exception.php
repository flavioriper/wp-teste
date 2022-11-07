<?php
/**
 * Invalid billing type exception
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Billing_Type;

/**
 * Each gateway has one billing type defined by a Type object
 *
 * This exception must be thrown when any gateway has this billing type.
 */
class Billing_Type_Exception extends \Exception {
}
