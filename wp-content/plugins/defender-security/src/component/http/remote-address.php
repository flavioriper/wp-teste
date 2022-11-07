<?php

declare( strict_types=1 );

namespace WP_Defender\Component\Http;

/**
 * Class Remote_Addr.
 *
 * Inspired from Laminas.
 *
 * @see https://github.com/laminas/laminas-http/blob/2.16.0/src/PhpEnvironment/RemoteAddress.php
 *
 * @package WP_Defender\Component\Http
 */
class Remote_Addr {
	/**
	 * Whether to use proxy addresses or not.
	 *
	 * As default this setting is disabled - IP address is mostly needed to increase
	 * security. HTTP_* are not reliable since can easily be spoofed. It can be enabled
	 * just for more flexibility, but if user uses proxy to connect to trusted services
	 * it's his/her own risk, only reliable field for IP address is $_SERVER['REMOTE_ADDR'].
	 *
	 * @var bool
	 */
	protected $use_proxy = false;

	/**
	 * List of trusted proxy IP addresses.
	 *
	 * @var array
	 */
	protected $trusted_proxies = [];

	/**
	 * HTTP header to introspect for proxies.
	 *
	 * @var string
	 */
	protected $proxy_header = 'HTTP_X_FORWARDED_FOR';

	/**
	 * Changes proxy handling setting.
	 *
	 * This must be static method, since validators are recovered automatically
	 * at session read, so this is the only way to switch setting.
	 *
	 * @param  bool  $use_proxy Whether to check also proxied IP addresses.
	 * @return $this
	 */
	public function set_use_proxy( $use_proxy = true ): self {
		$this->use_proxy = $use_proxy;

		return $this;
	}

	/**
	 * Checks proxy handling setting.
	 *
	 * @return bool Current setting value.
	 */
	public function get_use_proxy(): bool {
		return $this->use_proxy;
	}

	/**
	 * Set list of trusted proxy addresses.
	 *
	 * @param  array $trusted_proxies
	 * @return $this
	 */
	public function set_trusted_proxies( array $trusted_proxies ): self {
		$this->trusted_proxies = $trusted_proxies;

		return $this;
	}

	/**
	 * Set the header to introspect for proxy IPs.
	 *
	 * @param  string $header
	 * @return $this
	 */
	public function set_proxy_header( $header = 'X-Forwarded-For' ): self {
		$this->proxy_header = $this->normalize_proxy_header( $header );

		return $this;
	}

	/**
	 * Returns client IP address.
	 *
	 * @return string IP address.
	 */
	public function get_ip_address(): string {
		$ip = $this->get_ip_address_from_proxy();

		if ( $ip ) {
			return $ip;
		}

		// direct IP address
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return '';
	}

	/**
	 * Attempt to get the IP address for a proxied client.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
	 *
	 * @return false|string
	 */
	protected function get_ip_address_from_proxy() {
		if (
			! $this->use_proxy ||
			(
				isset( $_SERVER['REMOTE_ADDR'] ) &&
				! in_array( $_SERVER['REMOTE_ADDR'], $this->trusted_proxies, true )
			)
		) {
			return false;
		}

		$header = $this->proxy_header;

		if ( ! isset( $_SERVER[ $header ] ) || empty( $_SERVER[ $header ] ) ) {
			return false;
		}

		// Extract IPs
		$ips = explode( ',', $_SERVER[ $header ] );
		// trim, so we can compare against trusted proxies properly
		$ips = array_map( 'trim', $ips );
		// remove trusted proxy IPs
		$ips = array_diff( $ips, $this->trusted_proxies );

		// Any left?
		if ( empty( $ips ) ) {
			return false;
		}

		// Since we've removed any known, trusted proxy servers, the right-most
		// address represents the first IP we do not know about -- i.e., we do
		// not know if it is a proxy server, or a client. As such, we treat it
		// as the originating IP.
		// @see http://en.wikipedia.org/wiki/X-Forwarded-For
		return array_pop( $ips );
	}

	/**
	 * Normalize a header string.
	 *
	 * Normalizes a header string to a format that is compatible with
	 * $_SERVER.
	 *
	 * @param  string $header
	 * @return string
	 */
	protected function normalize_proxy_header( $header ): string {
		$header = strtoupper( $header );
		$header = str_replace( '-', '_', $header );

		if ( 0 !== strpos( $header, 'HTTP_' ) ) {
			$header = 'HTTP_' . $header;
		}

		return $header;
	}
}
