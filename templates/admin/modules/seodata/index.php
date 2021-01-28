<?php
/**
 * SEO Data settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'SEO Data', 'wc-zoom' ); ?></h1>

    <?php $module->render_list_table(); ?>

</div>
