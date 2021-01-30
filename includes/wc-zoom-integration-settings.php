<?php
/**
 * Integration settings
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Init plugin settings under WooCommerce integrations
 *
 * @return void
 */
function wc_zoom_load_integration() {
	if ( class_exists( '\WC_Integration' ) ) {
		add_filter(
			'woocommerce_integrations',
			function( $integrations ) {
				$integrations[] = '\\SeattleWebCo\\WCZoom\\Settings';

				return $integrations;
			}
		);
	}
}
add_action( 'plugins_loaded', 'wc_zoom_load_integration' );
