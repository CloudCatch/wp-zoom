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
function wp_zoom_load_woocommerce_integration() {
	if ( class_exists( '\WC_Integration' ) ) {
		require_once 'woocommerce/wp-zoom-woocommerce-product-meta-boxes.php';
		require_once 'woocommerce/wp-zoom-woocommerce-markup-products.php';

		add_filter(
			'woocommerce_integrations',
			function( $integrations ) {
				$integrations[] = '\\SeattleWebCo\\WPZoom\\Settings';

				return $integrations;
			}
		);
	}
}
add_action( 'plugins_loaded', 'wp_zoom_load_woocommerce_integration' );
