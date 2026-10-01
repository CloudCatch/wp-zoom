<?php
/**
 * Logger.
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes daily log files under the uploads directory and drops files older than 14 days.
 */
class Log {

	/**
	 * Log a message.
	 *
	 * @param string $message Message to log.
	 * @param string $type warning|error|notice.
	 * @param array  $data Additional context data.
	 * @return void
	 */
	public static function write( $message, $type = 'notice', $data = array() ) {
		$settings = get_option( 'wp_zoom_settings', array() );

		if ( empty( $settings['enable_logging'] ) || 'yes' !== $settings['enable_logging'] ) {
			return;
		}

		$upload_dir = wp_upload_dir( null, false );

		if ( empty( $upload_dir['basedir'] ) ) {
			return;
		}

		$dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-zoom-logs';

		if ( ! wp_mkdir_p( $dir ) ) {
			return;
		}

		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}

		self::prune( $dir );

		$line = sprintf(
			"[%s] %s: %s %s\n",
			gmdate( 'c' ),
			strtoupper( (string) $type ),
			$message,
			$data ? wp_json_encode( $data ) : ''
		);

		file_put_contents( $dir . '/wp-zoom-' . gmdate( 'Y-m-d' ) . '.log', $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Delete log files older than 14 days.
	 *
	 * @param string $dir Log directory.
	 * @return void
	 */
	private static function prune( $dir ) {
		$files = glob( $dir . '/wp-zoom-*.log' );

		if ( ! is_array( $files ) ) {
			return;
		}

		$cutoff = time() - ( 14 * DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			$mtime = filemtime( $file );
			if ( false !== $mtime && $mtime < $cutoff ) {
				wp_delete_file( $file );
			}
		}
	}
}
