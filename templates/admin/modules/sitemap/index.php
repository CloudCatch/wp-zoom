<?php
/**
 * Sitemap settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module, $wp_rewrite;

$sitemap_url = $wp_rewrite->using_permalinks() ? home_url( '/wp-sitemap.xml' ) : add_query_arg( 'sitemap-stylesheet', 'xsl', home_url( '/' ) );
?>

<div class="wrap">
    <h1><?php \esc_html_e( 'Sitemap', 'wc-zoom' ); ?></h1>

    <p><?php esc_html_e( 'The XML sitemap link below can be used to submit your sitemap to Google Search Console.', 'wc-zoom' ); ?></p>

    <p><input type="text" value="<?php echo esc_attr( $sitemap_url ); ?>" class="regular-text" onClick="this.setSelectionRange(0, this.value.length)" readonly /></p>

    <p><a href="<?php echo esc_url( $sitemap_url ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'View Sitemap', 'wc-zoom' ); ?></a></p>
</div>
