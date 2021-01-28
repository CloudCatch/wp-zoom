<?php
/**
 * Authorization template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Authorization Required', 'wc-zoom' ); ?></h1>

    <div class="notice error">
        <p><?php esc_html_e( 'The page you have requested to access requires authorization. Please enter the authorization passphrase below to continue.', 'wc-zoom' ); ?></p>
    </div>

    <?php if ( isset( $_GET['invalid'] ) ) : ?>

    <div class="notice notice-error">
        <p><?php esc_html_e( 'The passphrase entered is invalid, please try again.', 'wc-zoom' ); ?></p>
    </div>

    <?php endif; ?>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="password" name="sem_passphrase" value="" />
        <input type="hidden" name="action" value="sem_authorization" />
        <input type="hidden" name="redirect_to" value="<?php echo add_query_arg( [] ); ?>" />
        <?php wp_nonce_field( 'sem-auth' ); ?>
        <?php submit_button( esc_attr__( 'Authorize', 'wc-zoom' ) ); ?>
    </form>
</div>
