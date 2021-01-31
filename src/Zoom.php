<?php
/**
 * OAuth2 Provider class file
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

/**
 * Api class.
 */
class Zoom extends AbstractProvider {

	use BearerAuthorizationTrait;

	protected $clientId = 'RzaG1zWQKOkwSgjF7n4Pw';

	protected $clientSecret = '7apr9qHaFC5KLsLtpjsQ21PS7dYvfgwD';

	protected $redirectUri = 'https://sandbox.local.lndo.site/?wc-zoom-oauth';

	/**
	 * @var string
	 */
	private $response_error = 'error';

	/**
	 * @var string
	 */
	private $response_code;

	public function getBaseAuthorizationUrl() {
		return 'https://zoom.us/oauth/authorize';
	}

	public function getBaseAccessTokenUrl( array $params ) {
		return 'https://zoom.us/oauth/token';
	}

	public function getResourceOwnerDetailsUrl( AccessToken $token ) {
		return 'https://zoom.us/v2/me';
	}

	protected function getDefaultScopes() {
		return array(
			'meeting:read',
			'meeting:write',
			'user:read',
			'user:write',
			'user_profile',
			'webinar:read',
			'webinar:write',
		);
	}

	protected function checkResponse( ResponseInterface $response, $data ) {
		if ( ! empty( $data[ $this->response_error ] ) ) {
			$error = $data[ $this->response_error ];

			if ( ! is_string( $error ) ) {
				$error = var_export( $error, true );
			}

			$code = $this->response_code && ! empty( $data[ $this->response_code ] ) ? $data[ $this->response_code ] : 0;

			if ( ! is_int( $code ) ) {
				$code = intval( $code );
			}

			throw new IdentityProviderException( $error, $code, $data );
		}
	}

	protected function createResourceOwner( array $response, AccessToken $token ) {
		return new GenericResourceOwner( $response, $this->responseResourceOwnerId );
	}

}
