<?php
/**
 * File for class Endpoint
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Webhook;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Api\Api;
use WC_Asaas\WC_Asaas;
use WC_Asaas\Billing_Type\Billing_Type_Exception;
use WC_Asaas\Helper\Subscriptions_Helper;

/**
 * Endpoint
 */
class Endpoint {

	/**
	 * Variable for save value of the query var
	 *
	 * @var string
	 */
	private $query_var = 'asaas-webhook';

	/**
	 * The URL endpoint
	 *
	 * @var string
	 */
	private $url_endpoint = 'asaas-webhook';

	/**
	 * The gateway to load the settings
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * The query URL to the endpoint
	 *
	 * @var string
	 */
	protected $query;

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Billing type name for ticket
	 *
	 * @var string
	 */
	const BOLETO = 'BOLETO';

	/**
	 * Billing type name for credit card
	 *
	 * @var string
	 */
	const CREDIT_CARD = 'CREDIT_CARD';

	/**
	 * Billing type name for deposit
	 *
	 * @var string
	 */
	const DEPOSIT = 'DEPOSIT';

	/**
	 * Billing type name for transfer
	 *
	 * @var string
	 */
	const TRANSFER = 'TRANSFER';

	/**
	 * Billing type name for pix
	 *
	 * @var string
	 */
	const PIX = 'PIX';

	/**
	 * Initialize the plugin public actions
	 */
	private function __construct() {
		$this->query = "index.php?{$this->query_var}=1";

		add_action( 'template_redirect', array( $this, 'process_webhook' ) );

		add_filter( 'query_vars', array( $this, 'query_vars' ) );
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
	 * Add rewrite rule for asaas-webhook
	 */
	public function custom_rewrite_basic() {
		add_rewrite_rule( "{$this->url_endpoint}/?$", $this->query, 'top' );
	}

	/**
	 * Register query_var
	 *
	 * @param  array $qvars Query vars.
	 *
	 * @return array $qvars Query vars with query of the endopoit
	 */
	public function query_vars( $qvars ) {
		$qvars[] = $this->query_var;
		return $qvars;
	}

	/**
	 * Processes webhook and redirects to its specific method
	 *
	 * @throws \Exception Returned message to API.
	 */
	public function process_webhook() {
		if ( '1' === get_query_var( $this->query_var ) ) {
			try {
				$raw_data = file_get_contents( 'php://input' ); // @codingStandardsIgnoreLine WordPress.WP.AlternativeFunctions.file_get_contents wp_remote_get not work with php://input
				$this->validate_data( $raw_data );
				$data = json_decode( $raw_data );

				$this->validate_event( $data->event );
				$this->validate_billing_type( $data->payment->billingType );

				$data->payment->billingType = $this->convert_other_billing_types_to_boleto( $data->payment->billingType );

				$this->gateway = WC_Asaas::get_instance()->get_gateway_by_billing_type( $data->payment->billingType );

				$this->gateway->get_logger()->log( 'WEBHOOK REQUEST ' . $raw_data );

				$order                = false;
				$subscriptions_helper = new Subscriptions_Helper();
				if ( isset( $data->payment->subscription ) ) {
					// Tries to find the order without externalReference beacause maybe it isn't assigned yet.
					$order = $subscriptions_helper->get_order_by_payment_id( $data->payment->id );
				}
				if ( false === $order ) {
					$order = wc_get_order( $data->payment->externalReference );
				}
				$this->ignore_non_woocommerce_payment( $order );

				$subscription = null;
				if ( isset( $data->payment->subscription ) ) {
					$subscription = $subscriptions_helper->get_subscription_by_id( $data->payment->subscription );
					$this->ignore_non_woocommerce_subscription( $subscription );
				}

				$this->validate_token();
				$this->validate_content();
				$this->validate_status( $data );

				$webhook = new Webhook( $this->gateway, $order, $subscription, $data );
				$webhook->process_event();
				$this->response( 200, __( 'Webhook has been processed with success', 'woo-asaas' ) );
			} catch ( Billing_Type_Exception $error ) {
				$this->response( 200, $error->getMessage() );
			} catch ( Inconsistency_Data_Exception $error ) {
				$this->response( 200, $error->getMessage() );
			} catch ( Event_Exception $error ) {
				$this->response( 200, $error->getMessage() );
			} catch ( \Exception $error ) {
				$this->response( 500, $error->getMessage() );
			}
		}
	}

	/**
	 * Get the order gateway id
	 *
	 * Normalize old versions that used `woocommerce-` as gateway prefix.
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 * @return string The gateway id sanitized.
	 */
	private function get_gateway_id( $order ) {
		$gateway_id = $order->get_payment_method( false );
		$gateway_id = preg_replace( '/^woocommerce-/', '', $gateway_id );
		return $gateway_id;
	}

	/**
	 * Ignore request returning 200 if `externalReference` is null.
	 *
	 * @param \WC_Order $order The request data.
	 */
	private function ignore_non_woocommerce_payment( $order ) {
		if ( false === $order ) {
			$this->response( 200, 'This request isn\'t a WooCommerce order.' );
		}
	}

	/**
	 * Ignore request returning 200 if `subscription` is invalid.
	 *
	 * @param \WC_Subscription $subscription The WooCommerce Subscription object.
	 */
	private function ignore_non_woocommerce_subscription( $subscription ) {
		if ( false === $subscription ) {
			$this->response( 200, 'This request isn\'t a WooCommerce subscription.' );
		}
	}

	/**
	 * Validate if gateway exist
	 *
	 * The gateway is created by the billing type. The gateway is null if the billing type doesn't exist.
	 *
	 * @param string $data The request data.
	 * @throws \Exception If data is empty.
	 */
	private function validate_data( $data ) {
		if ( '' === $data ) {
			throw new \Exception( 'Data is empty.' );
		}
	}

	/**
	 * Validate if event exist
	 *
	 * The event must be accepted for the webhook to be processed.
	 *
	 * @param string $event The request event.
	 * @throws \Exception If event is not acceptable.
	 */
	private function validate_event( $event ) {
		$accepted_events = array( Webhook::PAYMENT_CONFIRMED, Webhook::PAYMENT_CREATED, Webhook::PAYMENT_DELETED, Webhook::PAYMENT_OVERDUE, Webhook::PAYMENT_RECEIVED, Webhook::PAYMENT_REFUNDED, Webhook::PAYMENT_RESTORED, Webhook::PAYMENT_UPDATED );
		if ( false === array_search( $event, $accepted_events ) ) {
			/* translators: %s: event name  */
			throw new Event_Exception( sprintf( __( 'Event %s wasn\'t registered.', 'woo-asaas' ), $event ) );
		}
	}

	/**
	 * Validate if billing type exist
	 *
	 * The billing type must be accepted for the webhook to be processed.
	 *
	 * @param string $billing_type The request billing type.
	 * @throws \Exception If billing type is not acceptable.
	 */
	private function validate_billing_type( $billing_type ) {
		$accepted_billing_type = array( self::BOLETO, self::CREDIT_CARD, self::DEPOSIT, self::TRANSFER, self::PIX );
		if ( false === array_search( $billing_type, $accepted_billing_type ) ) {
			/* translators: %s: billing type name  */
			throw new Billing_Type_Exception( sprintf( __( 'Billing type %s wasn\'t registered.', 'woo-asaas' ), $billing_type ) );
		}
	}

	/**
	 * Converts other types of charge to boleto
	 *
	 * The billing type must be DEPOSIT or TRANSFER for it to be converted..
	 *
	 * @param string $billing_type The request billing type.
	 * @return string
	 */
	private function convert_other_billing_types_to_boleto( $billing_type ) {
		$other_billing_types = array( self::DEPOSIT, self::TRANSFER );
		if ( false === array_search( $billing_type, $other_billing_types ) ) {
			return $billing_type;
		}
		return self::BOLETO;
	}

	/**
	 * Validate if the request token is the same set in gateway settings
	 *
	 * @throws \Exception If the token is invalid.
	 */
	private function validate_token() {
		$access_token = isset( $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ) ) : '';
        if ( $this->gateway->get_option( 'webhook_access_token' ) !== $access_token && html_entity_decode( $this->gateway->get_option( 'webhook_access_token' ) ) !== $access_token ) {
			throw new \Exception( 'Invalid Token' );
		}
	}

	/**
	 * Validate request content type
	 *
	 * The content type must be `application/json`.
	 *
	 * @throws \Exception If the content is invalid.
	 */
	private function validate_content() {
		$content_type = isset( $_SERVER['CONTENT_TYPE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['CONTENT_TYPE'] ) ) : '';
		if ( 'application/json' !== $content_type ) {
			throw new \Exception( 'Content-Type not accepted' );
		}
	}

	/**
	 * Validate if the payment exists and the status is the same of the request
	 *
	 * @param \stdClass $data The request data.
	 * @throws \Exception If the respose is an error or the status in request doesn't match with the request one.
	 */
	private function validate_status( $data ) {
		$want_skip = isset( $data->skip_api_status_validation ) ? $data->skip_api_status_validation : false;
		$api_skip = new Api_Skip();
		if( true === $api_skip->can_skip() && true === $want_skip ) {
			return;
		}

		$api      = new Api( $this->gateway );
		$response = $api->payments()->find( $data->payment->id );

		if ( 200 !== $response->code ) {
			throw new \Exception( sprintf( 'Error verifying payment status in Asaas. Response HTTP status: %d', $response->code ) );
		}

		if ( $data->payment->status !== $response->get_json()->status ) {
			throw new \Exception( 'Status doesn\'t match with Asaas' );
		}
	}

	/**
	 * Get the webhook URL to show in settings page
	 *
	 * @return string The webhook URL.
	 */
	public function get_url() {
		$query = $this->query;
		if ( '' !== get_option( 'permalink_structure', '' ) ) {
			$query = $this->url_endpoint;
		}

		return home_url( '/' . $query );
	}

	/**
	 * Log endpoint request response and return it
	 *
	 * When the gateway couldn't be created, it is impossible log some information.
	 *
	 * @param int    $code The HTTP response code.
	 * @param string $message The response message.
	 */
	protected function response( $code, $message ) {
		if ( ! is_null( $this->gateway ) ) {
			$this->gateway->get_logger()->log( 'WEBHOOK RESPONSE ' . $code . ' ' . $message );
		}

		status_header( $code );
		die( wp_kses( $message, array() ) );
	}
}
