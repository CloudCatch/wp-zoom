<?php
/**
 * Remove stored Zoom credentials and logs when the plugin is deleted.
 *
 * @package SeattleWebCo\WPZoom
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wp_zoom_oauth_tokens' );
delete_option( 'wp_zoom_user_id' );
delete_option( 'wp_zoom_settings' );

delete_metadata( 'user', 0, 'wp_zoom_oauth_state', '', true );

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	"
	DELETE FROM {$wpdb->options}
	WHERE option_name LIKE '_transient_timeout_wp_zoom%'
	OR option_name LIKE '_transient_wp_zoom%'
	"
);

$uploads = wp_upload_dir( null, false );

if ( empty( $uploads['basedir'] ) ) {
	return;
}

$dir = trailingslashit( $uploads['basedir'] ) . 'wp-zoom-logs';

if ( ! is_dir( $dir ) ) {
	return;
}

$files = glob( $dir . '/*' );

if ( is_array( $files ) ) {
	foreach ( $files as $file ) {
		if ( is_file( $file ) ) {
			wp_delete_file( $file );
		}
	}
}

rmdir( $dir );
