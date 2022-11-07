<?php
/**
 * Subscription class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Subscription;

use Exception;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Meta_Data\Order;
use WC_Asaas\Api\Api;
use WC_Asaas\Log\Logger;
use WC_Asaas\Webhook\Webhook;

/**
 * Subscription functions
 */
class Subscription {

	/**
	 * The Asaas subscription statuses
	 *
	 * @var string
	 */
	const SUBSCRIPTION_ACTIVE  = 'ACTIVE';
	const SUBSCRIPTION_EXPIRED = 'EXPIRED';

	/**
	 * WoocCommerc x Asaas subscription statuses
	 *
	 * @var array
	 */
	protected $woo_x_asaas_subscription_statuses = array();

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;


	/**
	 * Is not allowed to call from outside to prevent from creating multiple instances.
	 */
	private function __construct() {
		$this->woo_x_asaas_subscription_statuses = array(
			'active'         => self::SUBSCRIPTION_ACTIVE,
			'on-hold'        => self::SUBSCRIPTION_ACTIVE,
			'pending-cancel' => self::SUBSCRIPTION_ACTIVE,
			'cancelled'      => self::SUBSCRIPTION_EXPIRED,
			'expired'        => self::SUBSCRIPTION_EXPIRED,
		);
	}

	/**
	 * Prevent the instance from being cloned.
	 */
	private function __clone() {
	}

	/**
	 * Prevent from being unserialized.
	 *
	 * @throws Exception If create a second instance of it.
	 */
	public function __wakeup() {
		throw new Exception( __( 'Cannot unserialize singleton', 'woo-asaas' ) );
	}

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Updates the subscriptions status due a parent order payment status change
	 *
	 * @param \WC_Order   $parent_order The parent order.
	 * @param string      $new_status The new status to set for the related subscriptions.
	 * @param string|null $event The payment event (Webhook) related to the status change.
	 * @return void
	 */
	public function update_subscriptions_related_to_parent_order( $parent_order, $new_status, $event = null ) {
		$order_meta = new Order( $parent_order->get_id() );
		if ( true === $order_meta->has_subscription() ) {
			$subscriptions = wcs_get_subscriptions_for_order( $parent_order, array( 'order_type' => array( 'any' ) ) );
			foreach ( $subscriptions as $subscription ) {
				$subscription_meta      = new Subscription_Meta( $subscription->get_id() );
				$first_payment_strategy = $subscription_meta->get_first_payment_strategy();
				if ( 0 !== $first_payment_strategy->processed_by_parent_order || $subscription->get_sign_up_fee() > 0 ) {
					// The first subscription payment OR the sign up fee was processed by the parent order .
					$this->update_status( $subscription, $new_status, $event );
				}
			}
		}
	}

	/**
	 * Updates subscription status
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @param string           $new_status The new subscription status.
	 * @param string           $event The payment event (Webhook) related to the status change.
	 * @return void
	 */
	public function update_status( $subscription, $new_status, $event = null ) {
		$final_status = $new_status;
		if ( $event ) {
			switch ( $event ) {
				case Webhook::PAYMENT_REFUNDED:
					// Cancels the subscription if it is waiting for this. This is necessary when occurs the rollback of transactions - the webhooks are sent too late.
					// See: maybe_record_subscription_payment() method on woocommerce-subscriptions plugin.
					if ( 'on-hold' === $new_status && 'pending-cancel' === $subscription->get_status() ) {
						// Updates the cancellation date so that it is greater than the last order date related to the subscription.
						$final_status = 'cancelled';
						$subscription->update_dates(
							array(
								'cancelled' => date_i18n( 'Y-m-d H:i:s' ),
							)
						);
					}
					break;
			}
		}
		try {
			$subscription->update_status( $final_status );
		} catch ( \Exception $e ) {
			$payment_gateway = wc_get_payment_gateway_by_order( $subscription->get_id() );
			if ( $payment_gateway ) {
				$logger = new Logger( $payment_gateway );
				$logger->log( 'FAILED TO UPDATE SUBSCRIPTION #' . $subscription->get_id() . ' STATUS TO ' . $new_status . '. Error message: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Syncronizes WooCommerce subscription status with Asaas subscription status
	 *
	 * @param int             $order_id Subscription order id.
	 * @param string          $status_from Subscription old status.
	 * @param string          $status_to Subscription new status.
	 * @param WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function sync_status( $order_id, $status_from, $status_to, $subscription ) {
		$payment_gateway = wc_get_payment_gateway_by_order( $order_id );
		if ( $payment_gateway && array_key_exists( $status_to, $this->woo_x_asaas_subscription_statuses ) ) {
			$api               = new Api( $payment_gateway );
			$subscription_meta = new Subscription_Meta( $order_id );
			$subscription_id   = $subscription_meta->get_subscription_id();
			if ( $subscription_id ) {
				$new_status = $this->woo_x_asaas_subscription_statuses[ $status_to ];
				$payload    = array(
					'status' => $this->woo_x_asaas_subscription_statuses[ $status_to ],
				);
				$response   = $api->subscriptions()->update( $subscription_id, $payload );

				// Expired? Removes pending or overdue payments.
				if ( 200 === $response->code && self::SUBSCRIPTION_EXPIRED === $new_status ) {
					$response = $api->subscriptions()->payments( $subscription_id );
					if ( 200 === $response->code ) {
						foreach ( $response->get_json()->data as $payment ) {
							if ( in_array( $payment->status, array( 'PENDING', 'OVERDUE' ), true ) ) {
								$api->payments()->delete( $payment->id );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Can subscription be updated to active?
	 *
	 * @param bool            $can_be_updated The original result.
	 * @param WC_Subscription $subscription Subscription object.
	 * @return bool
	 */
	public function can_subscription_be_updated_to_active( $can_be_updated, $subscription ) {

		if ( true === $can_be_updated ) {
			// Is parent order pending?
			$related_orders = $subscription->get_related_orders( 'ids', 'parent' );
			foreach ( $related_orders as $related_order_id ) {
				$parent_order = wc_get_order( $related_order_id );
				if ( false !== $parent_order && ( ! $parent_order->is_paid() && ! $parent_order->has_status( 'refunded' ) ) ) {
					$can_be_updated = false;
				}
				break;
			}
		}

		return $can_be_updated;
	}

}
