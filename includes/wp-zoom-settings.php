<?php
/**
 * Admin settings
 *
 * @package SeattleWebCo\WPZoom
 */

use SeattleWebCo\WPZoom\Cache;

/**
 * Add options menu to admin
 *
 * @return void
 */
function wp_zoom_admin_menu() {
	add_options_page( esc_html__( 'Zoom for WordPress', 'wp-zoom' ), esc_html__( 'Zoom for WordPress', 'wp-zoom' ), 'wp_zoom_authorize', 'wp-zoom', 'wp_zoom_options_page' );
}
add_action( 'admin_menu', 'wp_zoom_admin_menu' );

/**
 * Options menu page content
 *
 * @return void
 */
function wp_zoom_options_page() {
	global $wp_zoom;
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Zoom for WordPress', 'wp-zoom' ); ?></h1>

		<?php
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
					/* translators: 1: Account user name 2: Account ID */
					printf( esc_html__( 'Connected to account: %1$s (%2$s)', 'wp-zoom' ), esc_html( $me['first_name'] . ' ' . $me['last_name'] ), esc_html( $me['id'] ) );
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
		?>

	</div>

	<?php
}

/**
 * Complete access token retrieval
 *
 * @return void
 */
function wp_zoom_get_access_token() {
	global $wp_zoom;

    // phpcs:ignore
	if ( ! isset( $_GET['state'] ) || ! isset( $_GET['code'] ) || ! isset( $_GET['page'] ) || $_GET['page'] !== 'wp-zoom' ) {
		return;
	}

	delete_user_meta( get_current_user_id(), 'wp_zoom_oauth_tokens' );
	delete_user_meta( get_current_user_id(), 'wp_zoom_user_id' );

	Cache::delete_all();

    // phpcs:ignore
	if ( ! empty( $_GET['state'] ) ) {
		try {
			$access_token = $wp_zoom->provider->getAccessToken(
				'authorization_code',
				array(
                    // phpcs:ignore
                    'code' => sanitize_text_field( $_GET['code'] ),
				)
			);

			$wp_zoom->update_access_token( $access_token, get_current_user_id() );

			$me = $wp_zoom->get_me();

			if ( ! empty( $me['id'] ) ) {
				update_user_meta( get_current_user_id(), 'wp_zoom_user_id', $me['id'] );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
			exit;
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}
	}
}
add_action( 'wp_loaded', 'wp_zoom_get_access_token' );

/**
 * Revoke authorization
 *
 * @return void
 */
function wp_zoom_revoke_authorization() {
	if ( ! current_user_can( 'wp_zoom_authorize' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

    // phpcs:ignore
    if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wp-zoom-revoke' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
	}

	delete_user_meta( get_current_user_id(), 'wp_zoom_oauth_tokens' );
	delete_user_meta( get_current_user_id(), 'wp_zoom_user_id' );

	Cache::delete_all();

	wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
	exit;
}
add_action( 'admin_post_wp_zoom_revoke', 'wp_zoom_revoke_authorization' );

/**
 * Purge cache
 *
 * @return void
 */
function wp_zoom_purge_cache() {
	if ( empty( $_REQUEST['purge_wp_zoom_cache'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

    // phpcs:ignore
    if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wp-zoom-purge-cache' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
	}

	Cache::delete_all();

	add_action(
		'admin_notices',
		function() {
			?>

			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Cache purged successfully.', 'wp-zoom' ); ?></p>
			</div>

			<?php
		}
	);
}
add_action( 'admin_init', 'wp_zoom_purge_cache' );

/**
 * Receive access tokens from Zoom and save to DB
 *
 * @return void
 */
function wp_zoom_save_tokens() {
	global $wp_zoom;

    // phpcs:ignore
	if ( ! isset( $_GET['wp_zoom_tokens'] ) || ! isset( $_GET['page'] ) || $_GET['page'] !== 'wp-zoom' ) {
		return;
	}

	if ( ! current_user_can( 'wp_zoom_authorize' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

    // phpcs:ignore
    $tokens = wp_zoom_sanitize_recursive( $_GET['wp_zoom_tokens'] );

	if ( $tokens ) {
		$tokens = json_decode( json_decode( stripslashes( $tokens ) ), true );

		if ( isset( $tokens['access_token'] ) ) {
			$wp_zoom->update_access_token( $tokens, get_current_user_id() );

			$zoom_user = $wp_zoom->get_me();

			if ( isset( $zoom_user['id'] ) ) {
				update_user_meta( get_current_user_id(), 'wp_zoom_user_id', $zoom_user['id'] );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
			exit;
		} else {
			add_action(
				'admin_notices',
				function() use ( $tokens ) {
					?>

					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'The following error was received during authorization', 'wp-zoom' ); ?>: <?php echo esc_html( wp_json_encode( $tokens ) ); ?></p>
					</div>

					<?php
				}
			);
		}
	}
}
add_action( 'admin_init', 'wp_zoom_save_tokens', -10 );
