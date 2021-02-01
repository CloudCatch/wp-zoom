<?php
/**
 * WooCommerce Integration Settings
 *
 * @package SeattleWebCo\WCZoom
 */

namespace SeattleWebCo\WCZoom;

class Settings extends \WC_Integration {

	/**
	 * Initialize the integration.
	 */
	public function __construct() {
		$this->id                 = 'wc_zoom';
		$this->method_title       = __( 'Zoom', 'wc-zoom' );
		$this->method_description = __( 'Integrate Zoom with WooCommerce to ease selling webinars seamlessly.', 'wc-zoom' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_post_wc_zoom_oauth', array( $this, 'process_authorization' ) );

		add_action( 'admin_post_wc_zoom_revoke', array( $this, 'revoke_authorization' ) );

		add_action( 'admin_init', array( $this, 'purge_cache' ) );

		add_action( 'admin_init', array( $this, 'save_tokens' ), -10 );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'wc-zoom', WC_ZOOM_URL . 'assets/css/admin.css' );
		wp_enqueue_script( 'wc-zoom', WC_ZOOM_URL . 'assets/js/admin.js', array( 'jquery', 'selectWoo' ), null, true );
	}

	public function process_authorization() {
		global $wc_zoom;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wc-zoom' ) );
		}

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wc-zoom-oauth' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wc-zoom' ) );
		}

		// phpcs:ignore
		wp_redirect( $wc_zoom->provider->getAuthorizationUrl() );
		exit;
	}

	public function revoke_authorization() {
		global $wc_zoom;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wc-zoom' ) );
		}

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wc-zoom-revoke' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wc-zoom' ) );
		}

		delete_option( 'wc_zoom_oauth_tokens' );
		delete_option( 'wc_zoom_user_id' );

		Cache::delete_all();

		wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=wc_zoom' ) );
		exit;
	}

	public function purge_cache() {
		global $wc_zoom;

		if ( empty( $_REQUEST['purge_wc_zoom_cache'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wc-zoom' ) );
		}

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wc-zoom-purge-cache' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wc-zoom' ) );
		}

		Cache::delete_all();

		add_action(
			'admin_notices',
			function() {
				?>

			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Cache purged successfully.', 'wc-zoom' ); ?></p>
			</div>

				<?php
			}
		);
	}

	public function save_tokens() {
		global $wc_zoom;

		// phpcs:ignore
		$tokens = $_GET['wc_zoom_tokens'] ?? null;

		if ( $tokens ) {
			$tokens = json_decode( json_decode( stripslashes( $tokens ) ), true );

			if ( isset( $tokens['access_token'] ) ) {
				$wc_zoom->update_access_token( $tokens );

				$zoom_user = $wc_zoom->get_me();

				if ( isset( $zoom_user['id'] ) ) {
					update_option( 'wc_zoom_user_id', $zoom_user['id'] );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=wc_zoom' ) );
				exit;
			} else {
				add_action(
					'admin_notices',
					function() use ( $tokens ) {
						?>
		
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'The following error was received during authorization', 'wc-zoom' ); ?>: <?php echo esc_html( wp_json_encode( $tokens ) ); ?></p>
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
		global $wc_zoom;

		$me = $wc_zoom->get_me();

		if ( empty( $me['id'] ) ) {
			?>

			<p>
				<a class="button zoom-button" href="<?php echo esc_url( add_query_arg( array( 'action' => 'wc_zoom_oauth' ), wp_nonce_url( admin_url( 'admin-post.php' ), 'wc-zoom-oauth' ) ) ); ?>">
					<?php esc_html_e( 'Authorize with', 'wc-zoom' ); ?> 
					<span class="zoom-icon"></span>
				</a>
			</p>

			<?php
		} else {
			?>

			<p>
				<?php printf( __( 'Connected to account: %s', 'wc-zoom' ), esc_html( $me['first_name'] . ' ' . $me['last_name'] ) ); ?>
			</p>
			<p>
				<a class="disconnect-wc-zoom button" href="<?php echo esc_url( \wp_nonce_url( admin_url( 'admin-post.php?action=wc_zoom_revoke' ), 'wc-zoom-revoke' ) ); ?>">
					<?php esc_html_e( 'Revoke Zoom Authorization', 'wc-zoom' ); ?>
				</a> 
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'purge_wc_zoom_cache' => 1 ) ), 'wc-zoom-purge-cache' ) ); ?>" class="button">
					<?php esc_html_e( 'Purge Zoom API Cache', 'wc-zoom' ); ?>
				</a>
			</p>

			<?php
		}
	}
}
