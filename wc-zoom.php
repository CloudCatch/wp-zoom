<?php
/**
 * Plugin Name:     WooCommerce - Zoom Integration
 * Description:     Sell virtual Zoom webinars with WooCommerce
 * Version:         1.0.0
 * Author:          Seattle Web Co.
 * Author URI:      https://seattlewebco.com
 * Text Domain:     wc-zoom
 * Domain Path:     /languages/
 * Contributors:    seattlewebco, dkjensen
 * Requires PHP:    7.0.0
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

define( 'WC_ZOOM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_ZOOM_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_ZOOM_VER', function_exists( 'get_plugin_data' ) ? get_plugin_data( __FILE__ )['Version'] : '1.0.0' );
define( 'WC_ZOOM_DB_VER', '1.0.0' );
define( 'WC_ZOOM_BASE', __FILE__ );

require_once 'vendor/autoload.php';

require_once 'includes/wc-zoom-enqueue-scripts.php';
require_once 'includes/wc-zoom-integration-settings.php';
require_once 'includes/wc-zoom-product-meta-boxes.php';
require_once 'includes/wc-zoom-markup-products.php';
require_once 'includes/wc-zoom-markup-functions.php';
require_once 'includes/wc-zoom-helper-functions.php';

$GLOBALS['wc_zoom'] = new Api(
	new Zoom(
		array(
			'timeout' => 30,
		)
	)
);

/*
session_start();

add_action(
	'wp_loaded',
	function() {
		global $wc_zoom;

		if ( ! isset( $_GET['wc-zoom-oauth'] ) ) {
			return;
		}

		delete_option( 'wc_zoom_user_id' );

		if ( empty( $_GET['state'] ) || ( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			exit( 'Invalid state' );

		} else {

			try {
				$access_token = $wc_zoom->provider->getAccessToken(
					'authorization_code',
					array(
						// phpcs:ignore
						'code' => sanitize_text_field( wp_unslash( $_GET['code'] ) ),
					)
				);

				$wc_zoom->update_access_token( $access_token );

				$me = $wc_zoom->get_me();

				if ( ! empty( $me['id'] ) ) {
					update_option( 'wc_zoom_user_id', $me['id'] );
				}
			} catch ( \Exception $e ) {
				wp_die( esc_html( $e->getMessage() ) );
			}
		}
	}
);
*/

/**
 * Activation hook
 */
function wc_zoom_activation() {
	if ( version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			esc_html__( 'This plugin requires a minimum PHP version of 7.0.0', 'wc-zoom' ),
			esc_html__( 'Plugin activation error', 'wc-zoom' ),
			array(
				'response'  => 200,
				'back_link' => true,
			)
		);
	}

	delete_option( 'rewrite_rules' );

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, '\\SeattleWebCo\\WCZoom\\wc_zoom_activation' );
