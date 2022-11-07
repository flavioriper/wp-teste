<?php
/**
 * Abstract user for Asaas features
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Meta_Data;

/**
 * Abastract user for Asaas features.
 */
class Customer {

	/**
	 * Meta key name
	 *
	 * @var string
	 */
	const META_KEY = 'wocommerce-asaas-customer';

	/**
	 * WordPress user id
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Asaas customer id
	 *
	 * @var array
	 */
	protected $meta = array();

	/**
	 * Available meta keys
	 *
	 * @var string[]
	 */
	protected $available_meta = array( 'id', 'credit_cards' );

	/**
	 * Constructor.
	 *
	 * @param  int $user_id WP user id.
	 * @return void
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
		$this->init();
	}

	/**
	 * Set meta data properties.
	 *
	 * @return \stdClass|null The customer meta data. Null, if is empty.
	 */
	public function init() {
		$meta = get_user_meta( $this->user_id, self::META_KEY, true );

		if ( ! $meta ) {
			return;
		}

		$this->meta = unserialize( $meta ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
	}

	/**
	 * Check if the user has meta data.
	 *
	 * @return boolean True, if has meta. Otherwise, false.
	 */
	public function has_meta() {
		return ! empty( $this->meta );
	}

	/**
	 * Get Asaas customer meta
	 *
	 * @return array The customer meta.
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Set customer meta data
	 *
	 * @param string $key The Asaas customer meta key.
	 * @param mixed  $value The meta value.
	 * @return boolean|\WP_Error True, if the value was stored. Otherwise, a WP_Error object.
	 */
	public function set_meta( $key, $value ) {
		if ( ! in_array( $key, $this->available_meta, true ) ) {
			/* translators: %s: available meta keys  */
			return new \WP_Error( sprintf( __( 'Invalid customer meta key. The available keys are: %s', 'woo-asaas' ), implode( ', ', $this->available_meta ) ) );
		}

		$this->meta[ $key ] = $value;
		$this->store();
		return true;
	}

	/**
	 * Add a credit card to the customer
	 *
	 * @param array $credit_card The credit card data.
	 */
	public function add_credit_card( $credit_card ) {
		$credit_card = (array) $credit_card;

		$customer_credt_cards = ( null !== $this->meta['credit_cards'] ) ? $this->meta['credit_cards'] : array();

		$filter_credit_card = array_filter( $customer_credt_cards, function( $saved_credit_card ) use( $credit_card ) {
			return ( $saved_credit_card['creditCardToken'] === $credit_card['creditCardToken'] );
		} );

		if ( 0 !== count( $filter_credit_card ) ) {
			return;
		}

		$this->meta['credit_cards'][] = $credit_card;
		$this->store();
	}

	/**
	 * Get user meta data by key name.
	 *
	 * @param  string $name Propery name.
	 * @return string
	 */
	public function __get( $name ) {
		if ( is_array( $this->meta ) && key_exists( $name, $this->meta ) ) {
			return $this->meta[ $name ];
		}
	}

	/**
	 * Store the meta values into database
	 */
	public function store() {
		update_user_meta( $this->user_id, self::META_KEY, serialize( $this->meta ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	}

	/**
	 * Extract required Asaas data from order meta data.
	 *
	 * @param  array $data Extract order meta data from WC_Order array.
	 * @return array
	 */
	public static function extract_meta_data_from_order( $data ) {
		$exported = array();

		foreach ( $data as $meta ) {
			if ( '_billing_cpf' === $meta->key && ! empty( $meta->value ) ) {
				$exported['cpfCnpj'] = $meta->value;
			}

			if ( '_billing_cnpj' === $meta->key && ! empty( $meta->value ) ) {
				$exported['cpfCnpj'] = $meta->value;
			}

			if ( '_billing_phone' === $meta->key && ! empty( $meta->value ) ) {
				$exported['phone'] = $meta->value;
			}

			if ( '_billing_cellphone' === $meta->key && ! empty( $meta->value ) ) {
				$exported['mobilePhone'] = $meta->value;
			}

			if ( '_billing_number' === $meta->key && ! empty( $meta->value ) ) {
				$exported['addressNumber'] = $meta->value;
			}

			if ( '_billing_neighborhood' === $meta->key && ! empty( $meta->value ) ) {
				$exported['province'] = $meta->value;
			}
		}

		return $exported;
	}

	/**
	 * Extract customer from WC_Order.
	 *
	 * @param \WC_Order $wc_order The WooCommerce order object.
	 * @return array The customer data.
	 */
	public static function extract_data_from_order( \WC_Order $wc_order ) {
		$cpf_cnpj_clean_regex = '/\.|-|\//';
		$phone_clean_regex    = '/\(|\)|\s|-/';

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			if ( ! empty( $wc_order->billing_cpf ) ) {
				$cpf_cnpj = $wc_order->billing_cpf;
			}

			if ( ! empty( $wc_order->billing_cnpj ) ) {
				$cpf_cnpj = $wc_order->billing_cnpj;
			}

			$cpf_cnpj = ( 1 === absint( $wc_order->billing_persontype ) ) ? $wc_order->billing_cpf : $wc_order->billing_cnpj;
			$cpf_cnpj = preg_replace( $cpf_cnpj_clean_regex, '', $cpf_cnpj );

			return array(
				'name'              => $wc_order->billing_first_name . ' ' . $wc_order->billing_last_name,
				'email'             => $wc_order->billing_email,
				'cpfCnpj'           => false === is_null( $cpf_cnpj ) ? $cpf_cnpj : self::extract_cpf_cnpj_from_customer_id( $wc_order->get_customer_id() ),
				'postalCode'        => $wc_order->billing_postcode,
				'addressNumber'     => $wc_order->billing_number,
				'addressComplement' => $wc_order->billing_address_2,
				'phone'             => preg_replace( $phone_clean_regex, '', $wc_order->billing_phone ),
				'mobilePhone'       => preg_replace( $phone_clean_regex, '', $wc_order->billing_cellphone ),
				'remoteIp'          => $wc_order->customer_ip_address,
			);
		}

		$data     = $wc_order->get_data();
		$cpf_cnpj = '';

		$cpf = $wc_order->get_meta( '_billing_cpf' );
		if ( '' !== $cpf ) {
			$cpf_cnpj = $cpf;
		}

		$cnpj = $wc_order->get_meta( '_billing_cnpj' );
		if ( '' !== $cnpj ) {
			$cpf_cnpj = $cnpj;
		}

		return array(
			'name'              => $data['billing']['first_name'] . ' ' . $data['billing']['last_name'],
			'email'             => $data['billing']['email'],
			'cpfCnpj'           => '' !== $cpf_cnpj ? $cpf_cnpj : self::extract_cpf_cnpj_from_customer_id( $wc_order->get_customer_id() ),
			'postalCode'        => $data['billing']['postcode'],
			'addressNumber'     => $wc_order->get_meta( '_billing_number' ),
			'addressComplement' => $data['billing']['address_2'],
			'phone'             => preg_replace( $phone_clean_regex, '', $data['billing']['phone'] ),
			'mobilePhone'       => preg_replace( $phone_clean_regex, '', $wc_order->get_meta( '_billing_cellphone' ) ),
			'remoteIp'          => $data['customer_ip_address'],
		);
	}

	/**
	 * Extract CPF and CNPJ from customer if is set as user meta
	 *
	 * @param int $customer_id WooCommerce (WordPress user) id
	 * @return string|null The customer CPF or CNPJ if it is registered. Otherwise, null.
	 */
	private static function extract_cpf_cnpj_from_customer_id( $customer_id ) {
		$cpf = self::extract_customer_meta( $customer_id, 'billing_cpf' );
		if( '' !== $cpf ) {
			return $cpf;
		}

		$cnpj = self::extract_customer_meta( $customer_id, 'billing_cnpj' );
		if( '' !== $cnpj ) {
			return $cnpj;
		}

		return null;
	}

	/**
	 * Extract customer meta data
	 *
	 * @param int $customer_id WooCommerce (WordPress user) id.
	 * @param string $meta_key The meta key.
	 * @return string The meta value.
	 */
	private static function extract_customer_meta( $customer_id, $meta_key ) {
		$meta_value = get_user_meta( $customer_id, $meta_key, true );
		// $has_meta   = ! ( ( false === $meta_value ) || ( '' === $meta_value ) );

		if( false !== $meta_value ) {
			return $meta_value;
		}

		return '';
	}

	/**
	 * Extract customer from posted data
	 *
	 * @param  array $data The posted data.
	 * @return array The customer data fomatted to Asaas API.
	 */
	public static function extract_data_from_checkout( $data ) {
		$cpf_cnpj_clean_regex = '/\.|-|\//';
		$phone_clean_regex    = '/\(|\)|\s|-/';

		if ( ! empty( $data['billing_cpf'] ) ) {
			$cpf_cnpj = $data['billing_cpf'];
		}

		$billing_company = '';
		if ( ! empty( $data['billing_cnpj'] ) ) {
			$cpf_cnpj        = $data['billing_cnpj'];
			$billing_company = $data['billing_company'];
		}

		$cpf_cnpj = preg_replace( $cpf_cnpj_clean_regex, '', $cpf_cnpj );

		return array(
			'name'              => $data['billing_first_name'] . ' ' . $data['billing_last_name'],
			'email'             => $data['billing_email'],
			'address'           => $data['billing_address_1'],
			'postalCode'        => $data['billing_postcode'],
			'externalReference' => '',
			'cpfCnpj'           => $cpf_cnpj,
			'company'           => $billing_company,
			'phone'             => preg_replace( $phone_clean_regex, '', $data['billing_phone'] ),
			'mobilePhone'       => preg_replace( $phone_clean_regex, '', $data['billing_cellphone'] ),
			'addressNumber'     => $data['billing_number'],
			'province'          => $data['billing_neighborhood'],
			'complement'        => $data['billing_address_2'],
		);
	}

	/**
	 * Get all Asaas customers
	 *
	 * Asaas customers are the WP users that has this class META_KEY in your metadata.
	 *
	 * @return self[] All Asaas customers.
	 */
	public static function get_customers() {
		$query = new \WP_User_Query(
			array(
				'meta_query' => array(
					array(
						'key'     => self::META_KEY,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$customers = array_map(
			function( \WP_User $user ) {
				return new self( $user->ID );
			},
			$query->get_results()
		);

		return $customers;
	}
}
