<?php
/**
 * WordPress Integration Settings
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

class Settings extends \WC_Integration {

	/**
	 * Initialize the integration.
	 */
	public function __construct() {
		$this->id                 = 'wp_zoom';
		$this->method_title       = __( 'Zoom', 'wp-zoom' );
		$this->method_description = __( 'Integrate Zoom with WordPress to ease selling webinars seamlessly.', 'wp-zoom' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_post_wp_zoom_oauth', array( $this, 'process_authorization' ) );

		add_action( 'admin_post_wp_zoom_revoke', array( $this, 'revoke_authorization' ) );

		add_action( 'admin_init', array( $this, 'purge_cache' ) );

		add_action( 'admin_init', array( $this, 'save_tokens' ), -10 );

		add_action( 'wp_loaded', array( $this, 'get_access_token' ) );
	}

	public function get_access_token() {
		global $wp_zoom;

		if ( ! isset( $_REQUEST['wp-zoom-oauth'] ) ) {
			return;
		}

		session_start();

		delete_option( 'wp_zoom_oauth_tokens' );
		delete_option( 'wp_zoom_user_id' );

		Cache::delete_all();

		if ( empty( $_GET['state'] ) || ( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			exit( 'Invalid state' );

		} else {

			try {
				$access_token = $wp_zoom->provider->getAccessToken(
					'authorization_code',
					array(
						// phpcs:ignore
						'code' => sanitize_text_field( $_GET['code'] ),
					)
				);

				$wp_zoom->update_access_token( $access_token );

				$me = $wp_zoom->get_me();

				if ( ! empty( $me['id'] ) ) {
					update_option( 'wp_zoom_user_id', $me['id'] );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom' ) );
				exit;
			} catch ( \Exception $e ) {
				wp_die( esc_html( $e->getMessage() ) );
			}
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-zoom', WP_ZOOM_URL . 'assets/css/admin.css' );
		wp_enqueue_script( 'wp-zoom', WP_ZOOM_URL . 'assets/js/admin.js', array( 'jquery', 'selectWoo' ), null, true );
	}

	public function process_authorization() {
		global $wp_zoom;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
		}

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wp-zoom-oauth' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
		}

		// phpcs:ignore
		wp_redirect( $wp_zoom->provider->getAuthorizationUrl() );
		exit;
	}

	public function revoke_authorization() {
		global $wp_zoom;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
		}

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wp-zoom-revoke' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
		}

		delete_option( 'wp_zoom_oauth_tokens' );
		delete_option( 'wp_zoom_user_id' );

		Cache::delete_all();

		wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom' ) );
		exit;
	}

	public function purge_cache() {
		global $wp_zoom;

		if ( empty( $_REQUEST['purge_wp_zoom_cache'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
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

	public function save_tokens() {
		global $wp_zoom;

		// phpcs:ignore
		$tokens = wp_zoom_sanitize_recursive( $_GET['wp_zoom_tokens'] ?? '' );

		if ( $tokens ) {
			$tokens = json_decode( json_decode( stripslashes( $tokens ) ), true );

			if ( isset( $tokens['access_token'] ) ) {
				$wp_zoom->update_access_token( $tokens );

				$zoom_user = $wp_zoom->get_me();

				if ( isset( $zoom_user['id'] ) ) {
					update_option( 'wp_zoom_user_id', $zoom_user['id'] );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom' ) );
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

	public function admin_options() {
		?>

		<p>
			<?php $this->authorize_zoom_button(); ?>
		</p>

		<?php
	}

	public function authorize_zoom_button() {
		global $wp_zoom;

		$me = $wp_zoom->get_me();

		if ( empty( $me['id'] ) ) {
			?>

			<p>
				<a class="button zoom-button" href="<?php echo esc_url( add_query_arg( array( 'action' => 'wp_zoom_oauth' ), wp_nonce_url( admin_url( 'admin-post.php' ), 'wp-zoom-oauth' ) ) ); ?>">
					<?php esc_html_e( 'Authorize with', 'wp-zoom' ); ?> 
					<span class="zoom-icon"></span>
				</a>
			</p>

			<?php
		} else {
			?>

			<p>
				<?php printf( __( 'Connected to account: %s', 'wp-zoom' ), esc_html( $me['first_name'] . ' ' . $me['last_name'] ) ); ?>
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
	}
}
