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
			'wc_zoom_oauth_tokens',
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
		try {
			$tokens = get_option( 'wc_zoom_oauth_tokens', array() );

			if ( empty( $tokens['access_token'] ) || empty( $tokens['refresh_token'] ) || empty( $tokens['expires'] ) ) {
				return null;
			}

			if ( $tokens['expires'] <= time() ) {
				$access_token = $this->provider->getAccessToken( 'refresh_token', array( 'refresh_token' => $tokens['refresh_token'] ) );

				return $this->update_access_token( $access_token );
			}

			return new AccessToken( $tokens );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return '';
		}
	}

	/**
	 * Get current authenticated user details
	 *
	 * @return string
	 */
	public function get_me() {
		try {
			$request = $this->provider->getAuthenticatedRequest(
				'GET',
				$this->base_uri . '/users/me',
				$this->get_access_token(),
				array(
					'headers' => array( 'Content-Type' => 'application/json;charset=UTF-8' ),
				)
			);

			return $this->provider->getParsedResponse( $request );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return wp_json_encode( array() );
		}
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
			$this->get_access_token(),
			array(
				'headers' => array( 'Content-Type' => 'application/json;charset=UTF-8' ),
			)
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
			$this->get_access_token(),
			array(
				'headers' => array( 'Content-Type' => 'application/json;charset=UTF-8' ),
			)
		);

		$response = $this->provider->getParsedResponse( $request );

		Cache::set( 'wc_zoom_webinars', $response, 'wc_zoom_webinars' );

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
				'headers' => array(
					'headers' => array( 'Content-Type' => 'application/json;charset=UTF-8' ),
				),
				'body' => json_encode(
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
			)
		);

		$response = $this->provider->getParsedResponse( $request );

		return $response;
	}

}
