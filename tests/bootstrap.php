<?php
/**
 * PHPUnit bootstrap file
 *
 * @package SeattleWebCo\WPZoom
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Include helper functions.
require_once 'helpers/class-wc-helper-product.php';
require_once 'helpers/class-wc-helper-customer.php';

// Include mocks.
//require_once 'mocks/class-zoom-mock-api.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/../woocommerce/woocommerce.php';

	WC_Install::init();

	require dirname( dirname( __FILE__ ) ) . '/wp-zoom.php';

	\SeattleWebCo\WPZoom\wp_zoom_activation();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
