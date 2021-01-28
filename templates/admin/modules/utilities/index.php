<?php
/**
 * Utilities settings template
 * 
 * @package WCZoom
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $module;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Utilities', 'wc-zoom' ); ?></h1>

    <h3><?php esc_html_e( 'Password Settings', 'wc-zoom' ); ?></h3>

    <form method="post" action="admin-post.php">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="passphrase"><?php esc_html_e( 'Change plugin passphrase', 'wc-zoom' ); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="passphrase" name="passphrase" value="" autocomplete="nope" />
                        <p class="description"><?php esc_html_e( 'Change or set the passphrase used to access this plugin.', 'wc-zoom' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="action" value="save_passphrase" />
        <?php wp_nonce_field( 'save-passphrase' ); ?>
        <?php submit_button(); ?>
    </form>

    <h3><label for="htaccess"><?php esc_html_e( 'Modify .htaccess', 'wc-zoom' ); ?></label></h3>

    <p>
        <strong>
            <?php 
                /* translators: %s link to test .htaccess */
                printf( __( 'NOTE: It is important to make sure the .htaccess changes you are making are valid before saving. Please test the new .htaccess contents using the following link: %s', 'wc-zoom' ), sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( 'http://www.lyxx.com/freestuff/002.html' ) ) ); 
            ?>
        </strong>
    </p>

    <?php 
    /**
     * .htaccess file editor
     */
    $htaccess = get_home_path() . '.htaccess';

    if ( $module->is_file_readable( $htaccess ) && $module->is_file_writable( $htaccess ) ) { ?>

    <form method="post" action="admin-post.php">
        <textarea id="htaccess" data-load-editor name="htaccess" rows="10" cols="40"><?php echo esc_textarea( $module->get_file_contents( $htaccess ) ); ?></textarea>
        <input type="hidden" name="action" value="save_htaccess" />
        <?php wp_nonce_field( 'save-htaccess' ); ?>
        <?php submit_button(); ?>
    </form>

    <?php } else { ?>

    <p><?php esc_html_e( 'Unable to write to .htaccess. Please change the .htaccess file permissions to allow writing by the system user or group.', 'wc-zoom' ); ?></p>

    <?php } ?>

    <h3><label for="htaccess"><?php esc_html_e( 'Modify robots.txt', 'wc-zoom' ); ?></label></h3>

    <?php 
    /**
     * Robots.txt file editor
     */
    $robots = get_home_path() . 'robots.txt';

    if ( ! $module->is_valid_file( $robots ) ) { ?>

    <p><?php esc_html_e( 'A robots.txt file does not currently exist, click the button below to create one now.', 'wc-zoom' ); ?></p>

    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="create_robots" />
        <?php wp_nonce_field( 'create-robots' ); ?>
        <?php submit_button( esc_attr__( 'Create robots.txt file', 'wc-zoom' ) ); ?>
    </form>

    <?php } elseif ( $module->is_file_readable( $robots ) && $module->is_file_writable( $robots ) ) { ?>

    <form method="post" action="admin-post.php">
        <textarea id="robots" data-load-editor name="robots" rows="10" cols="40"><?php echo esc_textarea( $module->get_file_contents( $robots ) ); ?></textarea>
        <input type="hidden" name="action" value="save_robots" />
        <?php wp_nonce_field( 'save-robots' ); ?>
        <?php submit_button(); ?>
    </form>

    <?php } else { ?>

    <p><?php esc_html_e( 'Unable to write to robots.txt. Please change the robots.txt file permissions to allow writing by the system user or group.', 'wc-zoom' ); ?></p>

    <?php } ?>

    <h3><label for="header_scripts"><?php esc_html_e( 'Header Scripts', 'wc-zoom' ); ?></label></h3>

    <form method="post" action="admin-post.php">
        <textarea id="header_scripts" data-load-editor data-type="htmlmixed" name="header_scripts" rows="10" cols="40"><?php echo esc_textarea( $module->get_header_scripts() ); ?></textarea>
        <input type="hidden" name="action" value="save_header_scripts" />
        <?php wp_nonce_field( 'save-header-scripts' ); ?>
        <?php submit_button(); ?>
    </form>

    <h3><label for="footer_scripts"><?php esc_html_e( 'Footer Scripts', 'wc-zoom' ); ?></label></h3>

    <form method="post" action="admin-post.php">
        <textarea id="footer_scripts" data-load-editor data-type="htmlmixed" name="footer_scripts" rows="10" cols="40"><?php echo esc_textarea( $module->get_footer_scripts() ); ?></textarea>
        <input type="hidden" name="action" value="save_footer_scripts" />
        <?php wp_nonce_field( 'save-footer-scripts' ); ?>
        <?php submit_button(); ?>
    </form>

</div>
