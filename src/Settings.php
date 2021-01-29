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
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'wc-zoom', WC_ZOOM_URL . 'assets/css/admin.css' );
	}

	public function process_authorization() {
		global $wc_zoom;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'wc-zoom' ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'wc-zoom-oauth' ) ) {
			wp_die( esc_html__( 'Invalid nonce, please try again.', 'wc-zoom' ) );
		}

		wp_redirect( $wc_zoom->provider->getAuthorizationUrl() );
		exit;
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
				<a class="disconnect-wc-zoom" href="#">
					<?php esc_html_e( 'Revoke Zoom Authorization', 'wc-zoom' ); ?>
				</a> 
			</p>

			<?php
		}
	}
}
