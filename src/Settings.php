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
		$this->method_description = __( 'An integration for utilizing MaxMind to do Geolocation lookups. Please note that this integration will only do country lookups.', 'wc-zoom' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'admin_post_wc_zoom_oauth', array( $this, 'process_authorization' ) );
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
		global $wc_zoom;

		var_dump( $wc_zoom->get_me() );
		?>

		<p>
			<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'wc_zoom_oauth' ), wp_nonce_url( admin_url( 'admin-post.php' ), 'wc-zoom-oauth' ) ) ); ?>"><?php esc_html_e( 'Authorize', 'wc-zoom' ); ?></a>
		</p>


		<?php
	}
}
