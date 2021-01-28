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
define( 'WC_ZOOM_DB_VER', '1.0.0' );
define( 'WC_ZOOM_BASE', __FILE__ );

require_once 'vendor/autoload.php';

new Api();

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
