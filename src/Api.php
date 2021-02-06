<?php
/**
 * Api class file
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use SeattleWebCo\WPZoom\Exception\InvalidTokenException;

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

	/**
	 * Update access token in database
	 *
	 * @param AccessToken|Array $access_token Access token data to save to database.
	 * @return AccessToken
	 */
	public function update_access_token( $access_token ) {
		if ( is_array( $access_token ) ) {
			$access_token = wp_parse_args(
				$access_token,
				array(
					'access_token'      => 'null',
				)
			);

			$access_token = new AccessToken( (array) $access_token );
		}

		if ( (string) $access_token === 'null' ) {
			return $access_token;
		}

		update_option(
			'wp_zoom_oauth_tokens',
			$access_token->jsonSerialize()
		);

		return $access_token;
	}

	/**
	 * Returns a valid access token and refreshes the current one if needed
	 *
	 * @return AccessToken|string
	 */
	private function get_access_token() {
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

	/**
	 * Perform a request against the Zoom API
	 *
	 * @param string $uri Request endpoint.
	 * @param string $method Request method.
	 * @param mixed  $body Request body.
	 * @param array  $headers Request headers.
	 * @return mixed
	 */
	public function request( $uri, $method = 'GET', $body = null, $headers = array() ) {
		try {
			$access_token = $this->get_access_token();

			$request = $this->provider->getAuthenticatedRequest(
				$method,
				$uri,
				$access_token,
				array(
					'headers' => $headers,
					'body'    => $body,
				)
			);

			$response = $this->provider->getParsedResponse( $request );

			return $response;
		} catch ( InvalidTokenException $e ) {
			delete_option( 'wp_zoom_oauth_tokens' );
			delete_option( 'wp_zoom_user_id' );

			Cache::delete_all();

			do_action( 'wp_zoom_disconnected', $e );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

		return array();
	}

	/**
	 * Get current authenticated user details
	 *
	 * @return string
	 */
	public function get_me() {
		return $this->request( $this->base_uri . '/users/me', 'GET', null, array( 'Content-Type' => 'application/json;charset=UTF-8' ) );
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

		$response = $this->request( $this->base_uri . '/webinars/' . $webinar_id, 'GET', null, array( 'Content-Type' => 'application/json;charset=UTF-8' ) );

		if ( empty( $response ) ) {
			return $response;
		}

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

		$response = $this->request(
			add_query_arg( array( 'page_size' => 300 ), $this->base_uri . '/users/' . $this->user_id . '/webinars' ),
			'GET',
			null,
			array( 'Content-Type' => 'application/json;charset=UTF-8' )
		);

		if ( empty( $response ) ) {
			return $response;
		}

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
		$response = $this->request(
			add_query_arg( array( 'occurrence_ids' => $occurrence_id ), $this->base_uri . '/webinars/' . $webinar_id . '/registrants' ),
			'POST',
			wp_json_encode(
				array(
					'email'      => $customer->get_billing_email(),
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
			array( 'Content-Type' => 'application/json;charset=UTF-8' )
		);

		do_action( 'wp_zoom_add_webinar_registrant_success', $response, $customer, $webinar_id, $occurrence_id );

		return $response;
	}

}
