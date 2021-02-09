<?php
/**
 * Enqueue assets
 *
 * @package SeattleWebCo\WPZoom
 */

/**
 * Frontend assets
 *
 * @return void
 */
function wp_zoom_enqueue_scripts() {
	wp_enqueue_style( 'wp-zoom-frontend', WP_ZOOM_URL . 'assets/css/frontend.css', array(), WP_ZOOM_VER );
	wp_register_script( 'wp-zoom-frontend', WP_ZOOM_URL . 'assets/js/frontend.js', array( 'jquery', 'wc-add-to-cart-variation' ), WP_ZOOM_VER, true );

	wp_localize_script( 'wp-zoom-frontend', 'wp_zoom', array( 
		'ajax_url'		=> admin_url( 'admin-ajax.php' )
	) );

	wp_enqueue_script( 'wp-zoom-frontend' );
}
add_action( 'wp_enqueue_scripts', 'wp_zoom_enqueue_scripts' );
