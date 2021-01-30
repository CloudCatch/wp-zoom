<?php
/**
 * Enqueue assets
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Frontend assets
 *
 * @return void
 */
function wc_zoom_enqueue_scripts() {
	wp_enqueue_style( 'wc-zoom-frontend', WC_ZOOM_URL . 'assets/css/frontend.css', array(), WC_ZOOM_VER );
}
add_action( 'wp_enqueue_scripts', 'wc_zoom_enqueue_scripts' );
