<?php
/**
 * Zoom OAuth provider.
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom\Provider;

use SeattleWebCo\WPZoom\AccessToken;
use SeattleWebCo\WPZoom\Exception\ApiRequestException;
use SeattleWebCo\WPZoom\Exception\InvalidTokenException;
use SeattleWebCo\WPZoom\Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Talks to Zoom OAuth and the Zoom REST API with the WordPress HTTP API.
 */
class Zoom {

	/**
	 * User meta key for the OAuth state issued to the current admin.
	 */
	const STATE_META_KEY = 'wp_zoom_oauth_state';

	/**
	 * OAuth client id.
	 *
	 * @var string
	 */
	protected $client_id = '';

	/**
	 * OAuth client secret.
	 *
	 * @var string
	 */
	protected $client_secret = '';

	/**
	 * Redirect URI registered with Zoom.
	 *
	 * @var string
	 */
	protected $redirect_uri = '';

	/**
	 * HTTP timeout in seconds. WordPress defaults to 5 seconds, which cut off Zoom calls.
	 *
	 * @var int
	 */
	protected $timeout = 300;

	/**
	 * Init.
	 *
	 * @param array $options clientId, clientSecret, and redirectUri.
	 */
	public function __construct( array $options = array() ) {
		$this->client_id     = isset( $options['clientId'] ) ? (string) $options['clientId'] : '';
		$this->client_secret = isset( $options['clientSecret'] ) ? (string) $options['clientSecret'] : '';
		$this->redirect_uri  = isset( $options['redirectUri'] ) ? (string) $options['redirectUri'] : '';
	}

	/**
	 * Endpoint to begin authorization.
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return 'https://zoom.us/oauth/authorize';
	}

	/**
	 * Endpoint to get an access token.
	 *
	 * @param array $params Unused. Kept so callers match the previous provider.
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params = array() ) {
		return 'https://zoom.us/oauth/token';
	}

	/**
	 * Scopes requested from Zoom.
	 *
	 * @return array
	 */
	protected function getDefaultScopes() {
		return array(
			'user:read',
			'webinar:read',
			'webinar:write',
			'meeting:read',
		);
	}

	/**
	 * Authorization URL for the current admin, with a stored OAuth state value.
	 *
	 * @return string
	 */
	public function getAuthorizationUrl() {
		$state   = wp_generate_password( 43, false, false );
		$user_id = get_current_user_id();

		if ( $user_id ) {
			update_user_meta( $user_id, self::STATE_META_KEY, $state );
		}

		$args = array(
			'response_type' => 'code',
			'redirect_uri'  => $this->redirect_uri,
			'state'         => $state,
			'scope'         => implode( ' ', $this->getDefaultScopes() ),
		);

		if ( '' !== $this->client_id ) {
			$args['client_id'] = $this->client_id;
		}

		return add_query_arg( $args, $this->getBaseAuthorizationUrl() );
	}

	/**
	 * Exchange an authorization code or refresh token.
	 *
	 * @param string $grant authorization_code|refresh_token.
	 * @param array  $options Grant options. code or refresh_token.
	 * @return AccessToken
	 * @throws InvalidTokenException When Zoom does not return a token.
	 */
	public function getAccessToken( $grant, array $options = array() ) {
		$body = array(
			'grant_type'   => $grant,
			'redirect_uri' => $this->redirect_uri,
		);

		if ( '' !== $this->client_id ) {
			$body['client_id']     = $this->client_id;
			$body['client_secret'] = $this->client_secret;
		}

		if ( 'authorization_code' === $grant ) {
			$body['code'] = isset( $options['code'] ) ? $options['code'] : '';
		} elseif ( 'refresh_token' === $grant ) {
			$body['refresh_token'] = isset( $options['refresh_token'] ) ? $options['refresh_token'] : '';
		}

		$response = wp_remote_post(
			$this->getBaseAccessTokenUrl(),
			array(
				'timeout' => $this->timeout,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new InvalidTokenException(
				sprintf(
					/* translators: %s: error message */
					__( 'Unable to retrieve access token from server: %s', 'wp-zoom' ),
					$response->get_error_message()
				)
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( empty( $data['access_token'] ) || ! empty( $data['error'] ) ) {
			$reason = 'NULL';
			if ( ! empty( $data['reason'] ) ) {
				$reason = $data['reason'];
			} elseif ( ! empty( $data['error'] ) ) {
				$reason = $data['error'];
			}

			throw new InvalidTokenException(
				sprintf(
					/* translators: %s: response reason */
					__( 'Unable to retrieve access token from server: %s', 'wp-zoom' ),
					$reason
				)
			);
		}

		return new AccessToken( $data );
	}

	/**
	 * Revoke an access token at Zoom.
	 *
	 * Zoom requires the app's client id and secret. The hosted connector does not
	 * expose those, so this is a no-op unless they are configured on this site.
	 *
	 * @param string $token Access token to revoke.
	 * @return void
	 */
	public function revoke( $token ) {
		$token = (string) $token;

		if ( '' === $token || '' === $this->client_id || '' === $this->client_secret ) {
			return;
		}

		wp_remote_post(
			'https://zoom.us/oauth/revoke',
			array(
				'timeout' => $this->timeout,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
				),
				'body'    => array(
					'token' => $token,
				),
			)
		);
	}

	/**
	 * Build an authenticated Zoom API request.
	 *
	 * @param string            $method HTTP method.
	 * @param string            $url Request URL.
	 * @param AccessToken|mixed $token Access token.
	 * @param array             $options headers and body.
	 * @return array
	 */
	public function getAuthenticatedRequest( $method, $url, $token, array $options = array() ) {
		$headers = isset( $options['headers'] ) && is_array( $options['headers'] ) ? $options['headers'] : array();
		$headers['Authorization'] = 'Bearer ' . (string) $token;

		return array(
			'method'  => strtoupper( (string) $method ),
			'url'     => $url,
			'headers' => $headers,
			'body'    => array_key_exists( 'body', $options ) ? $options['body'] : null,
		);
	}

	/**
	 * Send a request built by getAuthenticatedRequest() and return decoded JSON.
	 *
	 * @param array $request Request arguments.
	 * @return array
	 * @throws InvalidTokenException When Zoom rejects the access token.
	 * @throws ApiRequestException When Zoom returns an error response.
	 */
	public function getParsedResponse( $request ) {
		$args = array(
			'method'  => $request['method'],
			'timeout' => $this->timeout,
			'headers' => $request['headers'],
		);

		if ( null !== $request['body'] && '' !== $request['body'] ) {
			$args['body'] = $request['body'];
		}

		$response = wp_remote_request( $request['url'], $args );

		if ( is_wp_error( $response ) ) {
			throw new ApiRequestException( $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( $code < 200 || $code >= 300 ) {
			if ( isset( $data['code'] ) && 124 === (int) $data['code'] ) {
				throw new InvalidTokenException(
					sprintf(
						/* translators: %s: response message */
						__( 'Unable to retrieve access token from server: %s', 'wp-zoom' ),
						isset( $data['message'] ) ? $data['message'] : ''
					)
				);
			}

			// Zoom code 200: the user has no webinar plan. Callers treat an empty array as no results.
			if ( isset( $data['code'] ) && 200 === (int) $data['code'] ) {
				Log::write(
					'Zoom rejected the request because the webinar plan is missing.',
					'notice',
					array(
						'url'  => $request['url'],
						'code' => 200,
					)
				);

				return array();
			}

			$zoom_code = isset( $data['code'] ) ? (string) $data['code'] : (string) $code;

			throw new ApiRequestException(
				sprintf(
					/* translators: %s: Zoom or HTTP error code */
					__( 'Error recieved from Zoom API: %1$s', 'wp-zoom' ),
					$zoom_code
				)
			);
		}

		return $data;
	}
}
