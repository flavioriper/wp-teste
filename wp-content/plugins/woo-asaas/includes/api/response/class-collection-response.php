<?php
/**
 * Collection Response class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api\Response;

use WC_Asaas\Api\Client\Collection_Client;

/**
 * Iterate over API pagination requests
 */
class Collection_Response extends Response implements \Iterator {

	/**
	 * The response object list
	 *
	 * @var \stdClass[]
	 */
	public $items;

	/**
	 * Create a collection response based on a HTTP response
	 *
	 * @param int               $status The response code.
	 * @param string            $data The response data.
	 * @param Collection_Client $client The collection client.
	 */
	public function __construct( $status, $data, Collection_Client $client ) {
		parent::__construct( $status, $data, $client );
		$this->set_items( $this );
	}

	/**
	 * Define the page items
	 *
	 * @param Collection_Response $response The response with new items.
	 */
	public function set_items( $response ) {
		$this->items = $response->get_items();
	}

	/**
	 * Get the items from JSON
	 *
	 * @return \stdClass[] The items.
	 */
	public function get_items() {
		$json = $this->get_json();
		return $json->data;
	}

	/**
	 * Get the next element
	 *
	 * If the current item collection is over. Try load more from API.
	 *
	 * @see \Iterator::next()
	 */
	public function next() {
		$next = next( $this->items );
		if ( $next ) {
			$response = $this->client->next();
			if ( $response ) {
				$this->set_items( $response );
				$next = $this->items[0];
			}
		}

		return $next;
	}

	/**
	 * Verify if the current item exists
	 *
	 * @see \Iterator::valid()
	 */
	public function valid() {
		return key( $this->items ) !== null;
	}

	/**
	 * Get the current item
	 *
	 * @see \Iterator::current()
	 */
	public function current() {
		return current( $this->items );
	}

	/**
	 * Rewind to the first item of the first request
	 *
	 * @see \Iterator::rewind()
	 */
	public function rewind() {
		$response = $this->client->rewind();
		$this->set_items( $response );
		return reset( $this->items );
	}

	/**
	 * Get the current item key
	 *
	 * @see \Iterator::key()
	 */
	public function key() {
		return key( $this->items );
	}
}
