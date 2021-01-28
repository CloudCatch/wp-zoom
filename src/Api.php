<?php
/**
 * Api class file
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

use \Firebase\JWT\JWT;

/**
 * Api class.
 */
class Api {

	public function __construct() {
		$key     = defined( 'WC_ZOOM_API_SECRET' ) ? constant( 'WC_ZOOM_API_SECRET' ) : '';
		$payload = array(
			'iss'   => defined( 'WC_ZOOM_API_KEY' ) ? constant( 'WC_ZOOM_API_KEY' ) : '',
			'exp'   => time() + 30,
		);

		/**
		 * IMPORTANT:
		 * You must specify supported algorithms for your application. See
		 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
		 * for a list of spec-compliant algorithms.
		 */
		$jwt     = JWT::encode( $payload, $key );
		$decoded = JWT::decode( $jwt, $key, array( 'HS256' ) );

		if ( ! is_admin() ) {
			print 123;
			var_dump( $jwt );
			print_r( $decoded );
		}

		/*
		NOTE: This will now be an object instead of an associative array. To get
		an associative array, you will need to cast it as such:
		*/

		$decoded_array = (array) $decoded;
	}

}
