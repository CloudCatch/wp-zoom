<?php
/**
 * Integration settings
 *
 * @package SeattleWebCo\WPZoom
 */

/**
 * Init plugin settings under WordPress integrations
 *
 * @return void
 */
function wp_zoom_load_integration() {
	if ( class_exists( '\WC_Integration' ) ) {
		add_filter(
			'woocommerce_integrations',
			function( $integrations ) {
				$integrations[] = '\\SeattleWebCo\\WPZoom\\Settings';

				return $integrations;
			}
		);
	}
}
add_action( 'plugins_loaded', 'wp_zoom_load_integration' );
