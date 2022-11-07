<?php
declare( strict_types = 1 );

namespace WP_Defender\Component;

use WP_Defender\Traits\IO;
use WP_Error;

/**
 * Class Crypt.
 *
 * @since 3.3.1
 * @package WP_Defender\Component
 */
class Crypt extends \Calotes\Base\Component {
	use IO;

	/**
	 * Generates cryptographically secure pseudo-random bytes.
	 *
	 * @param int $bytes
	 *
	 * @return string
	*/
	public static function random_bytes( int $bytes ): string {
		// Try with random_bytes.
		if ( function_exists( 'random_bytes' ) ) {
			try {
				$rand = random_bytes( $bytes );
				if ( is_string( $rand ) && strlen( $rand ) === $bytes ) {
					return $rand;
				}
			} catch ( \Exception $e ) {
				$_this = new self();
				$_this->log( $e->getMessage(), 'internal.log' );
			}
		}
		// Try with openssl_random_pseudo_bytes.
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$rand = openssl_random_pseudo_bytes( $bytes, $strong );
			if ( is_string( $rand ) && strlen( $rand ) === $bytes ) {
				return $rand;
			}
		}
		// Not safe. Use in extreme cases.
		$return = '';
		for ( $i = 0; $i < $bytes; $i++ ) {
			$return .= chr( mt_rand( 0, 255 ) );
		}

		return $return;
	}

	/**
	 * Generates cryptographically secure pseudo-random integers.
	 *
	 * @param int $min
	 * @param int $max
	 *
	 * @return int
	 */
	public static function random_int( $min = 0, $max = 0x7FFFFFFF ): int {
		if ( function_exists( 'random_int' ) ) {
			try {
				return random_int( $min, $max );
			} catch ( \Exception $e ) {
				$_this = new self();
				$_this->log( $e->getMessage(), 'internal.log' );
			}
		}
		$diff = $max - $min;
		$bytes = self::random_bytes( 4 );
		if ( 4 !== strlen( $bytes ) ) {
			throw new \RuntimeException( 'Unable to get 4 bytes' );
		}
		$val = unpack( 'nint', $bytes );
		$val = $val['int'] & 0x7FFFFFFF;
		// Convert to [0,1].
		$fp = (float) $val / 2147483647.0;

		return (int) ( round( $fp * $diff ) + $min );
	}

	/**
	 * Compare two strings to avoid timing attacks.
	 *
	 * @param string $expected
	 * @param string $actual
	 *
	 * @return bool
	 */
	public static function compare_lines( $expected, $actual ): bool {
		if ( function_exists( 'hash_equals' ) ) {
			return hash_equals( $expected, $actual );
		}

		$len_expected = mb_strlen( $expected, '8bit' );
		$len_actual = mb_strlen( $actual, '8bit' );
		$len = min( $len_expected, $len_actual );

		$result = 0;
		for ( $i = 0; $i < $len; $i++ ) {
			$result |= ord( $expected[ $i ] ) ^ ord( $actual[ $i ] );
		}
		$result |= $len_expected ^ $len_actual;

		return 0 === $result;
	}

	/**
	 * Encrypt data.
	 *
	 * @param string $value
	 * @param string $key
	 *
	 * @return string
	 * @throws \SodiumException
	 */
	private static function encrypt( $value, $key ): string {
		$key = base64_decode( $key );
		$nonce = self::random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$ciphertext = sodium_crypto_secretbox( $value, $nonce, $key );

		return base64_encode( $nonce . $ciphertext );
	}

	/**
	 * Decrypt encrypted data.
	 *
	 * @param string $encoded_value
	 * @param string $key
	 *
	 * @return string|WP_Error
	 * @throws \SodiumException
	 */
	private static function decrypt( $encoded_value, $key ) {
		if ( ! $encoded_value || '' === $key ) {
			return new WP_Error(
				Error_Code::TFA_DECRYPT_ERROR,
				__( 'Please re-setup 2FA TOTP method again.', 'wpdef' )
			);
		}
		$key = base64_decode( $key );
		$decoded = base64_decode( $encoded_value );
		$nonce = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' );
		$ciphertext = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' );

		$decrypted = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
		if ( false === $decrypted) {
			return new WP_Error(
				Error_Code::TFA_DECRYPT_ERROR,
				__( 'Please re-setup 2FA TOTP method again.', 'wpdef' )
			);
		}

		return $decrypted;
	}

	/**
	 * Get the path to a file with a random key. This is used for 2FA TOTP.
	 *
	 * @return string
	 */
	public static function get_path_to_key_file() {
		return wp_normalize_path( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'wp-defender-secrets.php';
	}

	/**
	 * Get decrypted data.
	 *
	 * @param string $data
	 *
	 * @return string|WP_Error
	 * @throws \SodiumException
	 */
	public static function get_decrypted_data( $data ) {
		$key = self::get_random_key();
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		return self::decrypt( $data, $key );
	}

	/**
	 * Get encrypted data.
	 *
	 * @param string $data
	 *
	 * @return string|WP_Error
	 * @throws \SodiumException
	 */
	public static function get_encrypted_data( $data ) {
		$key = self::get_random_key();
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		return self::encrypt( $data, $key );
	}

	/**
	 * Get random key.
	 *
	 * @return string|WP_Error
	 * @throws \SodiumException
	 */
	private static function get_random_key() {
		$file = self::get_path_to_key_file();
		if ( ! file_exists( $file ) ) {
			return new WP_Error(
				Error_Code::IS_EMPTY,
				__( 'The Defender file with the random key does not exist.', 'wpdef' )
			);
		}

		if ( ! defined( 'WP_DEFENDER_TOTP_KEY' ) ) {
			require_once $file;
		}

		if ( '{{__REPLACE_CODE__}}' !== constant( 'WP_DEFENDER_TOTP_KEY' ) ) {
			return WP_DEFENDER_TOTP_KEY;
		} else {
			return new WP_Error( Error_Code::INVALID, __( 'The Defender file with the random key is incorrect.', 'wpdef' ) );
		}
	}

	/**
	 * Generate a random key.
	 *
	 * @return string
	 */
	protected function generate_random_key(): string {
		return base64_encode( sodium_crypto_secretbox_keygen() );
	}

	/**
	 * Create a file with a random key.
	 *
	 * @return bool
	 */
	public function create_key_file(): bool {
		$to = self::get_path_to_key_file();
		if ( ! file_exists( $to ) ) {
			// Move a template file to WP_CONTENT and replace the file content.
			$template_file = WP_DEFENDER_DIR . 'src' . DIRECTORY_SEPARATOR . 'component' . DIRECTORY_SEPARATOR
				. 'wp-defender-sample.php';
			if ( copy( $template_file, $to ) ) {
				$content = file_get_contents( $to );
				if ( false !== strpos( $content, '{{__REPLACE_CODE__}}' ) ) {
					$new_content = str_replace( '{{__REPLACE_CODE__}}', $this->generate_random_key(), $content );

					return (bool) file_put_contents( $to, $new_content, LOCK_EX );
				}
			}
			// The file was not copied.
			return false;
		}
		// Everything is fine. The file exists.
		return true;
	}
}
