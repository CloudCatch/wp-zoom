<?php
/**
 * Api class file
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Api class.
 */
class Api {

	/**
	 * Base API endpoint.
	 *
	 * @var string
	 */
	public $base_uri = 'https://api.zoom.us/v2';

	public $user_id;

	public $provider;

	public function __construct( AbstractProvider $provider ) {
		$this->provider = $provider;

		$this->user_id = get_option( 'wc_zoom_user_id', null );
	}

	public function update_access_token( AccessToken $access_token ) {
		update_option(
			'wc_zoom_oauth_tokens',
			$access_token->jsonSerialize()
		);

		return $access_token;
	}

	private function get_access_token() {
		$tokens = get_option( 'wc_zoom_oauth_tokens', array() );

		if ( empty( $tokens['access_token'] ) || empty( $tokens['refresh_token'] ) || empty( $tokens['expires'] ) ) {
			return null;
		}

		if ( $tokens['expires'] <= time() ) {
			$access_token = $this->provider->getAccessToken( 'refresh_token', array( 'refresh_token' => $tokens['refresh_token'] ) );

			return $this->update_access_token( $access_token );
		}

		return new AccessToken( $tokens );
	}

	public function revoke_access_token() {
		$request = $this->provider->getAuthenticatedRequest(
			'POST',
			add_query_arg( array( 'token' => (string) $this->get_access_token() ), 'https://api.zoom.us/oauth/revoke' ),
			$this->get_access_token()
		);

		return $this->provider->getParsedResponse( $request );
	}

	public function get_me() {
		$request = $this->provider->getAuthenticatedRequest(
			'GET',
			$this->base_uri . '/users/me',
			$this->get_access_token()
		);

		return $this->provider->getParsedResponse( $request );
	}

	public function get_webinars() {
		$request = $this->provider->getAuthenticatedRequest(
			'GET',
			add_query_arg( array( 'page_size' => 300 ), $this->base_uri . '/users/' . $this->user_id . '/webinars' ),
			$this->get_access_token()
		);

		return $this->provider->getParsedResponse( $request );
	}

}
