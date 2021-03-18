<?php
/**
 * Install class file
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

/**
 * Installing functions
 */
class Install {

	public static function setup_roles() {
		$role_admin = get_role( 'administrator' );
		$role_admin->add_cap( 'wp_zoom_authorize' );

		add_role(
			'wp_zoom_admin',
			esc_html__( 'Zoom Admin', 'wp-zoom' ),
			array(
				'read'                 => true,
				'level_0'              => true,
				'wp_zoom_authorize'    => true,
				'view_admin_dashboard' => false,
			)
		);
	}

}
