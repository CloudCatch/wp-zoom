<?php
/**
 * General settings template
 *
 * @package SeattleWebCo\WPZoom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_zoom;

$me = $wp_zoom->get_me();

if ( empty( $me['id'] ) ) {
	?>

	<p>
		<a class="button zoom-button" href="<?php echo esc_url( $wp_zoom->provider->getAuthorizationUrl() ); ?>">
			<?php esc_html_e( 'Authorize with', 'wp-zoom' ); ?> 
			<span class="zoom-icon"></span>
		</a>
	</p>

	<?php
} else {
	?>

	<p>
		<?php
			/* translators: 1: Account user name */
			printf( esc_html__( 'Connected to account: %s', 'wp-zoom' ), esc_html( $me['first_name'] . ' ' . $me['last_name'] ) );
		?>
	</p>
	<p>
		<a class="disconnect-wp-zoom button" href="<?php echo esc_url( \wp_nonce_url( admin_url( 'admin-post.php?action=wp_zoom_revoke' ), 'wp-zoom-revoke' ) ); ?>">
			<?php esc_html_e( 'Revoke Zoom Authorization', 'wp-zoom' ); ?>
		</a> 
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'purge_wp_zoom_cache' => 1 ) ), 'wp-zoom-purge-cache' ) ); ?>" class="button">
			<?php esc_html_e( 'Purge Zoom API Cache', 'wp-zoom' ); ?>
		</a>
	</p>

	<?php
}

$general_fields = wp_zoom_get_settings_fields( 'general' );

if ( $general_fields ) {
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate="novalidate">
		<table class="form-table" role="presentation">
			<tbody>
				<?php
				foreach ( $general_fields as $field => $args ) {
					wp_zoom_render_settings_field( $field, $args );
				}
				?>
			</tbody>
		</table>

		<input type="hidden" name="action" value="wp_zoom_settings" />
		<input type="hidden" name="tab" value="general" />
		<?php wp_nonce_field( 'wp-zoom-settings' ); ?>
		<?php submit_button(); ?>
	</form>

	<?php
}