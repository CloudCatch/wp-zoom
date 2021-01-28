<?php
/**
 * Default settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;

use SeattleWebCo\WCZoom\Helpers;

$public_post_types = Helpers::get_public_post_types( null );
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Default Settings', 'wc-zoom' ); ?></h1>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

        <?php $module->do_settings(); ?>

        <input type="hidden" name="action" value="save_seo" />
        <?php wp_nonce_field( 'save-seo' ); ?>
        <?php submit_button(); ?>

    </form>
</div>
