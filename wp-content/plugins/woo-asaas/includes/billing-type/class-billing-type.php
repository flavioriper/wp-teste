<?php
/**
 * Billing type base class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Billing_Type;

/**
 * Billing type base class
 */
abstract class Billing_Type {

	/**
	 * The name of the type in the API
	 *
	 * @link https://asaasv3.docs.apiary.io/#reference/0/cobrancas/criar-nova-cobranca
	 *
	 * @return string The billing type identificator in Asaas API.
	 */
	abstract public function get_id();

	/**
	 * The billing type slug
	 *
	 * @return string The billing type slug.
	 */
	abstract public function get_slug();

	/**
	 * The human name used in whole system
	 *
	 * @return string The billing type name.
	 */
	abstract public function get_name();
}
