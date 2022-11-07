<?php
/**
 * Sass HTTP Client.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Client;

use WC_Asaas\Api\Response\Collection_Response;
use Composer\DependencyResolver\Request;
use WC_Asaas\Api\Response\Response;

/**
 * Sass HTTP Client.
 */
class Collection_Client extends Client {

	/**
	 * The request params
	 *
	 * @var Request
	 */
	protected $request_params;

	/**
	 * The response object
	 *
	 * @var Collection_Response $response
	 */
	protected $response;

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Api\Client\Client::request()
	 */

	/**
	 * Send a HTTP request and store the request params and response to future requests
	 *
	 * @see Client::request()
	 *
	 * @param string           $method HTTP request method.
	 * @param string           $endpoint The API endpoint to be requested.
	 * @param array            $data HTTP request body.
	 * @param callable|boolean $filter_callback A function to filter the log data.
	 * @return Response The Asaas API response object.
	 */
	protected function request( $method, $endpoint, $data = array(), $filter_callback = false ) {
		$this->request_params = array(
			'method'   => $method,
			'endpoint' => $endpoint,
			'data'     => $data,
		);

		$this->response = parent::request( $method, $endpoint, $data, $filter_callback );

		return $this->response;
	}

	/**
	 * Request the next page
	 *
	 * @return boolean|Collection_Response The next page response. False, if hasn't more pages.
	 */
	public function next() {
		if ( ! $this->response->hasMore ) {
			return false;
		}

		$params = $this->request_params;

		if ( ! isset( $params['data']['offset'] ) ) {
			$params['data']['offset'] = 0;
		}

		$params['data']['offset'] += $params['data']['limit'];

		return call_user_func_array( array( $this, 'request' ), $params );
	}

	/**
	 * Request the first page
	 *
	 * @return Collection_Response The first page response.
	 */
	public function rewind() {
		$params                   = $this->request_params;
		$params['data']['offset'] = 0;

		return call_user_func_array( array( $this, 'request' ), $params );
	}
}
