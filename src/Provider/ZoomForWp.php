<?php
/**
 * Hosted OAuth provider.
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom\Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OAuth through the CloudCatch connector when the site has no Zoom app credentials.
 */
class ZoomForWp extends Zoom {
	/**
	 * Endpoint to begin authorization.
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return 'https://oauth.cloudcatch.io/oauth?provider=zoom';
	}

	/**
	 * Endpoint to get an access token.
	 *
	 * @param array $params Unused.
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params = array() ) {
		return 'https://oauth.cloudcatch.io/oauth/token?provider=zoom';
	}

	/**
	 * Ask the CloudCatch connector to revoke the token. The connector holds the Zoom client secret.
	 *
	 * @param string $token Access token to revoke.
	 * @return void
	 */
	public function revoke( $token ) {
		$token = (string) $token;

		if ( '' === $token ) {
			return;
		}

		wp_remote_post(
			'https://oauth.cloudcatch.io/oauth/revoke?provider=zoom',
			array(
				'timeout' => $this->timeout,
				'body'    => array(
					'token' => $token,
				),
			)
		);
	}
}
