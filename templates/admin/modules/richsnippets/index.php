<?php
/**
 * Rich Snippets settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Rich Snippets', 'wc-zoom' ); ?></h1>

    <form method="post" action="admin-post.php">
        <?php $module->do_settings(); ?>

        <input type="hidden" name="action" value="save_richsnippets" />
        <?php \wp_nonce_field( 'save-richsnippets' ); ?>
        <?php \submit_button(); ?>
    </form>

</div>
