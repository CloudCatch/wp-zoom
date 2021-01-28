<?php
/**
 * Api class file
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

use \Firebase\JWT\JWT;
use GuzzleHttp\Client;

/**
 * Api class.
 */
class Api {

	/**
	 * Testing
	 */
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
			$client = new Client(
				array(
					// Base URI is used with relative requests
					'base_uri' => 'https://api.zoom.us/v2',
					// You can set any number of default request options.
					'timeout'  => 2.0,
				)
			);

			$response = $client->request(
				'GET',
				'/users',
				array(
					'headers' => array(
						'authorization' => 'Bearer ' . $jwt,
					),
				)
			);

			var_dump( $response->getBody() );
		}

		/*
		NOTE: This will now be an object instead of an associative array. To get
		an associative array, you will need to cast it as such:
		*/

		$decoded_array = (array) $decoded;
	}

}
