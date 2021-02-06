<?php
/**
 * Api class file
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

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

		$this->user_id = get_option( 'wp_zoom_user_id', null );
	}

	public function update_access_token( AccessToken $access_token ) {
		update_option(
			'wp_zoom_oauth_tokens',
			$access_token->jsonSerialize()
		);

		return $access_token;
	}

	private function get_access_token() {
		error_log( 'API called' );

		$tokens = get_option( 'wp_zoom_oauth_tokens', array() );

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
			$cache = Cache::get( 'wp_zoom_webinar_' . $webinar_id );

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

		Cache::set( 'wp_zoom_webinar_' . $webinar_id, $response, 'wp_zoom_webinars' );

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
			$cache = Cache::get( 'wp_zoom_webinars' );

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

		Cache::set( 'wp_zoom_webinars', $response, 'wp_zoom_webinars' );

		return $response;
	}

	/**
	 * Register customer for a webinar
	 *
	 * @param string       $webinar_id The webinar ID.
	 * @param \WC_Customer $customer The customer to register.
	 * @param string       $occurrence_id The webinar occurrence if applicable.
	 * @return array
	 */
	public function add_webinar_registrant( string $webinar_id, \WC_Customer $customer, string $occurrence_id = null ) {
		$request = $this->provider->getAuthenticatedRequest(
			'POST',
			add_query_arg( array( 'occurrence_ids' => $occurrence_id ), $this->base_uri . '/webinars/' . $webinar_id . '/registrants' ),
			$this->get_access_token(),
			array(
				'body' => json_encode(
					array(
						'email'      => $customer->get_email(),
						'first_name' => $customer->get_first_name(),
						'last_name'  => $customer->get_last_name(),
						'address'    => $customer->get_billing_address(),
						'city'       => $customer->get_billing_city(),
						'country'    => $customer->get_billing_country(),
						'zip'        => $customer->get_billing_postcode(),
						'state'      => $customer->get_billing_state(),
						'phone'      => $customer->get_billing_phone(),
						'org'        => $customer->get_billing_company(),
					)
				),
			)
		);

		$response = $this->provider->getParsedResponse( $request );

		return $response;
	}

}
