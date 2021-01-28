<?php
/**
 * Search and replace settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Search / Replace', 'wc-zoom' ); ?></h1>

    <p><strong><?php esc_html_e( 'IMPORTANT: Be sure to backup the database before running a search and replace.', 'wc-zoom' ); ?></strong></p>

    <form method="post" action="" id="searchreplace-form">
        <div id="sem_searchreplace_notices"></div>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="search_for"><?php esc_html_e( 'Search for', 'wc-zoom' ); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="search_for" name="search_for" value="" autocomplete="nope" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="replace_with"><?php esc_html_e( 'Replace with', 'wc-zoom' ); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="replace_with" name="replace_with" value="" autocomplete="nope" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="select_tables"><?php esc_html_e( 'Select tables', 'wc-zoom' ); ?></label></th>
                    <td>
                        <select name="select_tables[]" id="select_tables" multiple style="height: 300px;">
                            <?php 
                                $tables = $module->get_tables();

                                array_walk( $tables, function( $value ) {
                                    printf( '<option value="%1$s">%1$s</option>', esc_attr( $value ) );
                                } );
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button( __( 'Run Search / Replace', 'wc-zoom' ) ); ?>
    </form>
</div>
