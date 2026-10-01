<?php
/**
 * Enqueue assets
 *
 * @package SeattleWebCo\WPZoom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend assets
 *
 * @return void
 */
function wp_zoom_enqueue_scripts() {
	wp_enqueue_style( 'wp-zoom-frontend', WP_ZOOM_URL . 'assets/css/frontend.css', array(), WP_ZOOM_VER );

	if ( ! function_exists( 'is_product' ) || ! is_product() || ! wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
		return;
	}

	wp_enqueue_script( 'wp-zoom-frontend', WP_ZOOM_URL . 'assets/js/frontend.js', array( 'jquery', 'wc-add-to-cart-variation' ), WP_ZOOM_VER, true );

	wp_localize_script(
		'wp-zoom-frontend',
		'wp_zoom',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce(),
		)
	);
}

/**
 * Register and enqueue the calendar script for the current request.
 *
 * @return void
 */
function wp_zoom_enqueue_calendar_script() {
	wp_enqueue_script( 'wp-zoom-calendar', WP_ZOOM_URL . 'assets/js/calendar.js', array( 'jquery' ), WP_ZOOM_VER, true );

	wp_localize_script(
		'wp-zoom-calendar',
		'wp_zoom',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wp_zoom_enqueue_scripts', 20 );

/**
 * Backend assets
 *
 * @return void
 */
function wp_zoom_admin_enqueue_scripts() {
	wp_enqueue_style( 'wp-zoom-fonts', WP_ZOOM_URL . 'assets/css/fonts.css', array(), WP_ZOOM_VER );
	wp_enqueue_style( 'wp-zoom', WP_ZOOM_URL . 'assets/css/admin.css', array( 'wp-zoom-fonts' ), WP_ZOOM_VER );
	wp_enqueue_script( 'wp-zoom', WP_ZOOM_URL . 'assets/js/admin.js', array(), WP_ZOOM_VER, true );

	wp_localize_script(
		'wp-zoom',
		'wp_zoom',
		array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce(),
		)
	);

	wp_enqueue_script( 'wp-zoom' );
}
add_action( 'admin_enqueue_scripts', 'wp_zoom_admin_enqueue_scripts' );
