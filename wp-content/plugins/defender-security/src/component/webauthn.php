<?php

namespace WP_Defender\Component;

use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use WP_Defender\Traits\Webauthn as Webauthn_Trait;

/**
 * Class Webauthn.
 *
 * @since 3.0.0
 */
class Webauthn implements PublicKeyCredentialSourceRepository {
	use Webauthn_Trait;

	/**
	 * Option key for storing user credentials.
	 *
	 * @type string
	 */
	public const CREDENTIAL_OPTION_KEY = 'user_credentials';

	/**
	 * Get user credentials.
	 *
	 * @param int $userId
	 *
	 * @return mixed|null
	 */
	public function getCredentials( int $userId ) {
		return $this->get_user_meta( $userId, self::CREDENTIAL_OPTION_KEY );
	}

	/**
	 * Set user credentials.
	 *
	 * @param int $userId
	 * @param array $data
	 *
	 * @return bool
	 */
	public function setCredentials( int $userId, array $data ): bool {
		return false !== $this->update_user_meta( $userId, self::CREDENTIAL_OPTION_KEY, $data );
	}

	/**
	 * Get one credential by credential ID.
	 *
	 * @param string $publicKeyCredentialId
	 *
	 * @return PublicKeyCredentialSource|null
	 */
	public function findOneByCredentialId( string $publicKeyCredentialId ): ?PublicKeyCredentialSource {
		if ( isset( $_POST['username'] ) ) {
			$username = sanitize_text_field( $_POST['username'] );
			$user = get_user_by( 'login', $username );

			if ( is_object( $user ) ) {
				$userId = $user->ID;
			} else {
				$userId = 0;
			}
		} else {
			$userId = get_current_user_id();
		}
		$data = $this->getCredentials( $userId );
		if ( isset( $data[ base64_encode( $publicKeyCredentialId ) ]['credential_source'] ) ) {
			return PublicKeyCredentialSource::createFromArray( $data[ base64_encode( $publicKeyCredentialId ) ]['credential_source'] );
		}
		return null;
	}

	/**
	 * Get all credentials of a user
	 *
	 * @param PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity
	 *
	 * @return array
	 */
	public function findAllForUserEntity( PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity ): array {
		$credentials = [];
		$username = $publicKeyCredentialUserEntity->getName();
		$user = get_user_by( 'login', $username );

		if ( is_object( $user ) ) {
			$credentials = $this->findAllForUserByType( $user->ID );
		}

		return $credentials;
	}

	/**
	 * Get all credentials of a user by authenticator type.
	 *
	 * @param int $userID
	 * @param null|string $type
	 *
	 * @return array
	 * @since 3.1.0
	 */
	public function findAllForUserByType( int $userID, $type = null ): array {
		$sources = [];
		$user_data = $this->getCredentials( $userID );

		if ( is_array( $user_data ) ) {
			foreach ( $user_data as $data ) {
				if ( ! empty( $type ) && ! empty( $data['authenticator_type'] ) && $type !== $data['authenticator_type'] ) {
					continue;
				}

				if ( isset( $data['credential_source'] ) ) {
					$sources[] = PublicKeyCredentialSource::createFromArray( $data['credential_source'] );
				}
			}
		}

		return $sources;
	}

	/**
	 * Store credential into database.
	 *
	 * @param PublicKeyCredentialSource $publicKeyCredentialSource
	 *
	 * @return void
	 */
	public function saveCredentialSource( PublicKeyCredentialSource $publicKeyCredentialSource ): void {
		$userId = get_current_user_id();
		$data = $this->getCredentials( $userId );
		$key = base64_encode( $publicKeyCredentialSource->getPublicKeyCredentialId() );

		if ( ! isset( $data[ $key ] ) ) {
			$data[ $key ] = array(
				'label'              => ! empty( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
				'added'              => time(),
				'authenticator_type' => ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '',
				'user'               => $publicKeyCredentialSource->getUserHandle(),
				'credential_source'  => $publicKeyCredentialSource,
			);
		} else {
			$data[ $key ]['credential_source'] = $publicKeyCredentialSource;
		}

		$this->setCredentials( $userId, $data );
	}
}
