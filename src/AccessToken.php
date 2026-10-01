<?php
/**
 * OAuth access token.
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores an OAuth access token without a third-party client library.
 */
class AccessToken {

	/**
	 * Access token string.
	 *
	 * @var string
	 */
	public $access_token = '';

	/**
	 * Refresh token string.
	 *
	 * @var string
	 */
	public $refresh_token = '';

	/**
	 * Unix timestamp when the access token expires.
	 *
	 * @var int
	 */
	public $expires = 0;

	/**
	 * Zoom API host returned with the token, without a path.
	 *
	 * @var string
	 */
	public $api_url = '';

	/**
	 * Build a token from a Zoom token response or a stored option value.
	 *
	 * @param array $options Token values.
	 */
	public function __construct( array $options ) {
		$this->access_token  = isset( $options['access_token'] ) ? (string) $options['access_token'] : '';
		$this->refresh_token = isset( $options['refresh_token'] ) ? (string) $options['refresh_token'] : '';
		$this->api_url       = isset( $options['api_url'] ) ? esc_url_raw( $options['api_url'] ) : '';

		if ( isset( $options['expires'] ) ) {
			$this->expires = (int) $options['expires'];
		} elseif ( isset( $options['expires_in'] ) ) {
			$this->expires = time() + (int) $options['expires_in'];
		}
	}

	/**
	 * Values stored in the wp_zoom_oauth_tokens option.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$data = array(
			'access_token'  => $this->access_token,
			'refresh_token' => $this->refresh_token,
			'expires'       => $this->expires,
		);

		if ( '' !== $this->api_url ) {
			$data['api_url'] = $this->api_url;
		}

		return $data;
	}

	/**
	 * Access token string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->access_token;
	}
}
