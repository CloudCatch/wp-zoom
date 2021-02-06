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
}
add_action( 'wp_enqueue_scripts', 'wp_zoom_enqueue_scripts' );
