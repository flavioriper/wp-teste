<?php
/**
 * Sass HTTP Client.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Client;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Api\Response\Response_Factory;
use WC_Asaas\Api\Response\Response;

/**
 * Sass HTTP Client.
 */
class Client {

	/**
	 * WP HTTP.
	 *
	 * @var \WP_Http
	 */
	protected $http;

	/**
	 * The gateway that will call the API
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * Instantiate the API client
	 *
	 * @param Gateway $gateway The payment gateway.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
		$this->http    = new \WP_Http();

		add_action( 'requests-requests.before_request', array( $this, 'force_body_data_format' ), 10, 5 );
	}

	/**
	 * Force body data format for all Asaas GET requests
	 *
	 * @param string $url The request URL.
	 * @param array  $headers The request headers.
	 * @param array  $data The request data.
	 * @param string $type The request type.
	 * @param array  $options The request transport options.
	 */
	public function force_body_data_format( $url, $headers, $data, $type, &$options ) {
		if ( 0 !== strpos( $url, $this->gateway->get_option( 'endpoint' ) ) ) {
			return;
		}

		if ( 'GET' !== $type ) {
			return;
		}

		$options['data_format'] = 'body';
	}

	/**
	 * Send a HTTP request.
	 *
	 * @param  string           $method HTTP request method.
	 * @param  string           $endpoint The API endpoint to be requested.
	 * @param  array            $data HTTP request body.
	 * @param  callable|boolean $filter_callback A function to filter the log data.
	 * @return Response The Asaas API response object.
	 */
	protected function request( $method, $endpoint, $data = array(), $filter_callback = false ) {
		$url = $this->gateway->get_option( 'endpoint' ) . $endpoint;

		$data['origin'] = 'WooCommerce';

		$this->gateway->get_logger()->log( 'REQUEST ' . $url . ' ' . $this->filter( $data, $filter_callback ) );

		$response = $this->http->request(
			$url, array(
                'method'  => $method,
                'timeout'  => 30,
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'access_token' => apply_filters( 'woocommerce_asaas_request_api_key', $this->gateway->get_api_key(), $data ),
					'Content-Type' => 'application/json',
				),
			)
		);

		$response = $this->get_response_info( $response );

		$this->gateway->get_logger()->log( 'RESPONSE ' . $response['status'] . ' ' . $this->filter( $response['data'], $filter_callback ) );

		return Response_Factory::create( $response['status'], $response['data'], $this );
	}

	/**
	 * Call the filter function, if it's set
	 *
	 * @param array            $data Request body.
	 * @param callable|boolean $filter_callback A function to filter the log data.
	 * @return array The data filtered.
	 */
	protected function filter( $data, $filter_callback ) {
		if ( is_array( $data ) ) {
			$data = wp_json_encode( $data );
		}

		if ( false !== $filter_callback && is_callable( $filter_callback ) ) {
			return call_user_func( $filter_callback, $data );
		}

		return $data;
	}

	/**
	 * Get the response status and data
	 *
	 * @param \WP_HTTP_Requests_Response|array $response The HTTP response. An object for recent versions and array for older.
	 * @return array {
	 *     The reponse status and data.
	 *
	 *     @type int    $status The status code.
	 *     @type string $data   The body.
	 * }
	 */
	private function get_response_info( $response ) {
		global $wp_version;

		// Legacy code support.
		if ( version_compare( $wp_version, '4.6.0', '<' ) ) {
			return array(
				'status' => $response['response']['code'],
				'data'   => $response['body'],
			);
		}

        if (is_wp_error($response)) {
            return array(
                'status' => $response->get_error_code(),
                'data'   => $response->get_error_data(),
            );
        } else {
            $http_response = $response['http_response'];

            return array(
                'status' => $http_response->get_status(),
                'data'   => $http_response->get_data(),
            );
        }
	}

	/**
	 * Send a GET HTTP request
	 *
	 * @param string           $url Request URL.
	 * @param array            $data Request parameters.
	 * @param callable|boolean $filter_callback A function to filter the log data.
	 * @return Response The Asaas API response object.
	 */
	public function get( $url, $data = array(), $filter_callback = false ) {
		return $this->request( 'GET', $url, $data, $filter_callback );
	}

	/**
	 * Send a POST HTTP request
	 *
	 * @param string           $url Request URL.
	 * @param array            $data Request body.
	 * @param callable|boolean $filter_callback A function to filter the log data.
	 * @return Response The Asaas API response object.
	 */
	public function post( $url, $data = array(), $filter_callback = false ) {
		return $this->request( 'POST', $url, $data, $filter_callback );
	}

	/**
	 * Send a PUT HTTP request
	 *
	 * @param string           $url Request URL.
	 * @param array            $data Request body.
	 * @param callable|boolean $filter_callback A function to filter the log data.
	 * @return Response The Asaas API response object.
	 */
	public function put( $url, $data = array(), $filter_callback = false ) {
		return $this->request( 'PUT', $url, $data, $filter_callback );
	}

	/**
	 * Send a DELETE HTTP request
	 *
	 * @param string $url Request URL.
	 * @return Response The Asaas API response object.
	 */
	public function delete( $url ) {
		return $this->request( 'DELETE', $url );
	}

	/**
	 * Get the gateway that will call the API
	 *
	 * @return Gateway The gateway.
	 */
	public function get_gateway() {
		return $this->gateway;
	}
}
