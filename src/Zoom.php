<?php
/**
 * OAuth2 Provider class file
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

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

	/**
	 * Response error
	 *
	 * @var string
	 */
	private $response_error = 'error';

	/**
	 * Store response code
	 *
	 * @var string
	 */
	private $response_code;

	/**
	 * Field to get
	 *
	 * @var string
	 */
	private $response_resource_owner_id = 'id';

	/**
	 * Endpoint to begin authorization
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return 'https://api.seattlewebco.com/oauth?provider=zoom';
	}

	/**
	 * Endpoint to get access token
	 *
	 * @param array $params Additional parameters.
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params ) {
		return 'https://api.seattlewebco.com/oauth/token?provider=zoom';
	}

	/**
	 * Endpoint for resource owner details
	 *
	 * @param AccessToken $token The access token.
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl( AccessToken $token ) {
		return 'https://zoom.us/v2/me';
	}

	/**
	 * Scopes required
	 *
	 * @return array
	 */
	protected function getDefaultScopes() {
		return array(
			'user_profile',
			'webinar:read',
			'webinar:write',
		);
	}

	/**
	 * Checks API request response
	 *
	 * @param ResponseInterface $response PSR-7 Response.
	 * @param array             $data Data from response.
	 * @return void
	 * @throws IdentityProviderException Identity provider exception.
	 */
	protected function checkResponse( ResponseInterface $response, $data ) {
		try {
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
		} catch ( \Exception $e ) {
			
		}
	}

	/**
	 * Create resource owner
	 *
	 * @param array       $response From response.
	 * @param AccessToken $token Access token.
	 * @return GenericResourceOwner
	 */
	protected function createResourceOwner( array $response, AccessToken $token ) {
		return new GenericResourceOwner( $response, $this->response_resource_owner_id );
	}

}
