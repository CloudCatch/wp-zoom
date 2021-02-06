<?php
/**
 * Plugin Name:     Zoom for WordPress
 * Description:     Sell virtual Zoom webinars with WordPress
 * Version:         1.0.0
 * Author:          Seattle Web Co.
 * Author URI:      https://seattlewebco.com
 * Text Domain:     wp-zoom
 * Domain Path:     /languages/
 * Contributors:    seattlewebco, dkjensen
 * Requires PHP:    7.0.0
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

define( 'WP_ZOOM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_ZOOM_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_ZOOM_VER', function_exists( 'get_plugin_data' ) ? get_plugin_data( __FILE__ )['Version'] : '1.0.0' );
define( 'WP_ZOOM_DB_VER', '1.0.0' );
define( 'WP_ZOOM_BASE', __FILE__ );

require_once 'vendor/autoload.php';

require_once 'includes/wp-zoom-enqueue-scripts.php';
require_once 'includes/wp-zoom-integration-settings.php';
require_once 'includes/wp-zoom-product-meta-boxes.php';
require_once 'includes/wp-zoom-markup-products.php';
require_once 'includes/wp-zoom-markup-functions.php';
require_once 'includes/wp-zoom-helper-functions.php';

$GLOBALS['wp_zoom'] = new Api(
	new Zoom(
		array(
			'redirectUri' => admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom' ),
			'timeout'     => 30,
		)
	)
);

/*
session_start();

add_action(
	'wp_loaded',
	function() {
		global $wp_zoom;

		if ( ! isset( $_GET['wp-zoom-oauth'] ) ) {
			return;
		}

		delete_option( 'wp_zoom_user_id' );

		if ( empty( $_GET['state'] ) || ( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			exit( 'Invalid state' );

		} else {

			try {
				$access_token = $wp_zoom->provider->getAccessToken(
					'authorization_code',
					array(
						// phpcs:ignore
						'code' => sanitize_text_field( wp_unslash( $_GET['code'] ) ),
					)
				);

				$wp_zoom->update_access_token( $access_token );

				$me = $wp_zoom->get_me();

				if ( ! empty( $me['id'] ) ) {
					update_option( 'wp_zoom_user_id', $me['id'] );
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
function wp_zoom_activation() {
	if ( version_compare( PHP_VERSION, '7.2.0', '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			esc_html__( 'This plugin requires a minimum PHP version of 7.0.0', 'wp-zoom' ),
			esc_html__( 'Plugin activation error', 'wp-zoom' ),
			array(
				'response'  => 200,
				'back_link' => true,
			)
		);
	}

	delete_option( 'rewrite_rules' );

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, '\\SeattleWebCo\\WPZoom\\wp_zoom_activation' );
