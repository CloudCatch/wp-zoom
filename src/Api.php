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
		error_log( 'API called' );

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

	/**
	 * Get current authenticated user details
	 *
	 * @return string
	 */
	public function get_me() {
		$request = $this->provider->getAuthenticatedRequest(
			'GET',
			$this->base_uri . '/users/me',
			$this->get_access_token()
		);

		return $this->provider->getParsedResponse( $request );
	}

	/**
	 * Get a single webinar
	 *
	 * @param string  $webinar_id The webinar ID.
	 * @param boolean $cached Retrieve cached results or not.
	 * @return array
	 */
	public function get_webinar( string $webinar_id, bool $cached = true ) {
		if ( $cached ) {
			$cache = Cache::get( 'wc_zoom_webinar_' . $webinar_id );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$request = $this->provider->getAuthenticatedRequest(
			'GET',
			$this->base_uri . '/webinars/' . $webinar_id,
			$this->get_access_token()
		);

		$response = $this->provider->getParsedResponse( $request );

		Cache::set( 'wc_zoom_webinar_' . $webinar_id, $response, 'wc_zoom_webinars' );

		return $response;
	}

	/**
	 * Get all webinars
	 *
	 * @param boolean $cached Retrieve cached results or not.
	 * @return array
	 */
	public function get_webinars( bool $cached = true ) {
		if ( $cached ) {
			$cache = Cache::get( 'wc_zoom_webinars' );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$request = $this->provider->getAuthenticatedRequest(
			'GET',
			add_query_arg( array( 'page_size' => 300 ), $this->base_uri . '/users/' . $this->user_id . '/webinars' ),
			$this->get_access_token()
		);

		$response = $this->provider->getParsedResponse( $request );

		Cache::set( 'wc_zoom_webinars', $response, 'wc_zoom_webinars' );

		return $response;
	}

}
