<?php
/**
 * Redirects settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Redirects', 'wc-zoom' ); ?></h1>

    <?php $module->render_list_table(); ?>

    <?php if ( $module->get_query_type() === 'redirects' ) : ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                
                <div class="meta-box-sortables ui-sortable">
                    <h3><?php esc_html_e( 'Add new redirect', 'wc-zoom' ); ?></h3>
                    <form method="post" id="create_redirect">
                        <p>
                            <label><?php esc_html_e( 'Source URL', 'wc-zoom' ); ?></label><br>
                            <input type="text" class="regular-text" name="source" placeholder="<?php esc_attr_e( 'The relative URL you want to redirect from', 'wc-zoom' ); ?>" />
                            <?php /* <label><input type="checkbox" name="regex" value="true" /><?php esc_html_e( 'Regex', 'wc-zoom' ); ?></label> */ ?>
                        </p>
                        <p>
                            <label><?php esc_html_e( 'Target URL', 'wc-zoom' ); ?></label><br>
                            <input type="text" class="regular-text" name="target" placeholder="<?php esc_attr_e( 'The target URL you want to redirect to', 'wc-zoom' ); ?>" />
                        </p>
                        <p>
                            
                        </p>
                        <?php submit_button( esc_attr__( 'Add Redirect', 'wc-zoom' ) ); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                
                <div class="meta-box-sortables ui-sortable">
                    <h3><?php esc_html_e( 'Import redirects', 'wc-zoom' ); ?></h3>
                    <form action="admin-post.php" method="post" id="import_redirects" enctype="multipart/form-data">
                        <p>
                            <label><?php esc_html_e( 'Upload file', 'wc-zoom' ); ?></label><br>
                            <input type="file" class="regular-text" name="file" />
                        </p>
                        <p>
                            <label><input type="checkbox" name="column_headers" value="yes" /> <?php esc_html_e( 'File has column headers', 'wc-zoom' ); ?></label><br>
                        </p>
                        <p>
                          <span class="description">
                              <?php 
                                printf( 
                                    __( 'Allowed file types: .xlsx, .xls, .csv and .ods. %s', 'wc-zoom' ), 
                                    sprintf( 
                                        '<a href="%s" target="_blank">%s</a>', 
                                        plugins_url( 'templates/admin/modules/redirects/sample-redirects-import.csv', WC_ZOOM_BASE ), 
                                        __( 'View sample import file.', 'wc-zoom' ) 
                                        ) 
                                    ); 
                                ?>
                          </span>  
                        </p>
                        <input type="hidden" name="action" value="import_redirects" />
                        <?php wp_nonce_field( 'import-redirects' ); ?>
                        <?php submit_button( esc_attr__( 'Import', 'wc-zoom' ) ); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>

    <?php endif; ?>

    <?php if ( $module->get_query_type() === '404s' ) : ?>

    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="clear_404_log" />
        <?php wp_nonce_field( 'clear-404-log' ); ?>
        <?php submit_button( __( 'Clear All Log Entries', 'wc-zoom' ), 'secondary' ); ?>
    </form>

    <?php endif; ?>
</div>
