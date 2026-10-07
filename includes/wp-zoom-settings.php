<?php
/**
 * Admin settings
 *
 * @package SeattleWebCo\WPZoom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SeattleWebCo\WPZoom\Cache;

/**
 * Add options menu to admin
 *
 * @return void
 */
function wp_zoom_admin_menu() {
	add_options_page( esc_html__( 'WP Events for Zoom', 'wp-zoom' ), esc_html__( 'WP Events for Zoom', 'wp-zoom' ), 'manage_options', 'wp-zoom', 'wp_zoom_options_page' );
}
add_action( 'admin_menu', 'wp_zoom_admin_menu' );

/**
 * Options menu page content
 *
 * @return void
 */
function wp_zoom_options_page() {
	// phpcs:ignore
	$tab = sanitize_key( $_REQUEST['tab'] ?? 'general' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'WP Events for Zoom', 'wp-zoom' ); ?></h1>

		<div class="wp-zoom-tabs">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'general' ), admin_url( 'options-general.php?page=wp-zoom' ) ) ); ?>" class="wp-zoom-tab <?php echo esc_attr( $tab === 'general' ? 'active' : '' ); ?>">
				<?php esc_html_e( 'General', 'wp-zoom' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'registration' ), admin_url( 'options-general.php?page=wp-zoom' ) ) ); ?>" class="wp-zoom-tab <?php echo esc_attr( $tab === 'registration' ? 'active' : '' ); ?>">
				<?php esc_html_e( 'Registration', 'wp-zoom' ); ?>
			</a>
		</div>

		<?php
		switch ( $tab ) {
			case 'registration':
				wp_zoom_load_template( 'settings/registration.php' );
				break;

			case 'general':
			default:
				wp_zoom_load_template( 'settings/general.php' );
		}

		?>

	</div>

	<?php
}

/**
 * Complete access token retrieval
 *
 * @return void
 */
function wp_zoom_get_access_token() {
	global $wp_zoom;

	if ( ! isset( $_GET['state'], $_GET['code'], $_GET['page'] ) ) {
		return;
	}

	if ( 'wp-zoom' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) || ! wp_zoom_verify_oauth_state() ) {
		wp_die( esc_html__( 'Invalid authorization request. Please start the connection again from the settings screen.', 'wp-zoom' ) );
	}

	delete_option( 'wp_zoom_oauth_tokens' );
	delete_option( 'wp_zoom_user_id' );

	Cache::delete_all();

	try {
		$access_token = $wp_zoom->provider->getAccessToken(
			'authorization_code',
			array(
				'code' => sanitize_text_field( wp_unslash( $_GET['code'] ) ),
			)
		);

		$wp_zoom->update_access_token( $access_token );

		$me = $wp_zoom->get_me();

		if ( ! empty( $me['id'] ) ) {
			update_option( 'wp_zoom_user_id', $me['id'] );
		}

		wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
		exit;
	} catch ( \Exception $e ) {
		wp_die( esc_html( $e->getMessage() ) );
	}
}
add_action( 'wp_loaded', 'wp_zoom_get_access_token' );

/**
 * Revoke authorization
 *
 * @return void
 */
function wp_zoom_revoke_authorization() {
	global $wp_zoom;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

	if ( ! wp_zoom_verify_request_nonce( 'wp-zoom-revoke' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
	}

	$tokens = get_option( 'wp_zoom_oauth_tokens', array() );

	if ( ! empty( $tokens['access_token'] ) && isset( $wp_zoom->provider ) ) {
		$wp_zoom->provider->revoke( $tokens['access_token'] );
	}

	delete_option( 'wp_zoom_oauth_tokens' );
	delete_option( 'wp_zoom_user_id' );

	Cache::delete_all();

	wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
	exit;
}
add_action( 'admin_post_wp_zoom_revoke', 'wp_zoom_revoke_authorization' );

/**
 * Purge cache
 *
 * @return void
 */
function wp_zoom_purge_cache() {
	if ( empty( $_REQUEST['purge_wp_zoom_cache'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

	if ( ! wp_zoom_verify_request_nonce( 'wp-zoom-purge-cache' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
	}

	Cache::delete_all();

	add_action(
		'admin_notices',
		function() {
			?>

			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Cache purged successfully.', 'wp-zoom' ); ?></p>
			</div>

			<?php
		}
	);
}
add_action( 'admin_init', 'wp_zoom_purge_cache' );

/**
 * Receive access tokens from Zoom and save to DB
 *
 * @return void
 */
function wp_zoom_save_tokens() {
	global $wp_zoom;

	if ( ! isset( $_GET['wp_zoom_tokens'], $_GET['page'] ) ) {
		return;
	}

	if ( 'wp-zoom' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) || ! wp_zoom_verify_oauth_state() ) {
		wp_die( esc_html__( 'Invalid authorization request. Please start the connection again from the settings screen.', 'wp-zoom' ) );
	}

	$tokens = sanitize_text_field( wp_unslash( $_GET['wp_zoom_tokens'] ) );

	if ( $tokens ) {
		$tokens = json_decode( $tokens, true );

		if ( is_string( $tokens ) ) {
			$tokens = json_decode( $tokens, true );
		}

		if ( isset( $tokens['access_token'] ) ) {
			$wp_zoom->update_access_token( $tokens );

			$zoom_user = $wp_zoom->get_me();

			if ( isset( $zoom_user['id'] ) ) {
				update_option( 'wp_zoom_user_id', $zoom_user['id'] );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wp-zoom' ) );
			exit;
		} else {
			add_action(
				'admin_notices',
				function() use ( $tokens ) {
					?>

					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'The following error was received during authorization', 'wp-zoom' ); ?>: <?php echo esc_html( wp_json_encode( $tokens ) ); ?></p>
					</div>

					<?php
				}
			);
		}
	}
}
add_action( 'admin_init', 'wp_zoom_save_tokens', -10 );

/**
 * Outputs various types of settings fields
 *
 * @param string $field Field setting key.
 * @param array  $args Field args.
 * @return void
 */
function wp_zoom_render_settings_field( $field, $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'type'        => 'text',
			'label'       => '',
			'cb_label'    => '',
			'sanitize_cb' => '',
			'default'     => '',
		)
	);

	$settings = (array) get_option( 'wp_zoom_settings', array() );

	$value = array_key_exists( $field, $settings ) ? $settings[ $field ] : $args['default'];
	?>

	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
		</th>
		<td>
			<?php
			switch ( $args['type'] ) {
				case 'checkbox':
					printf(
						'<label><input type="checkbox" name="wp_zoom_settings[%1$s]" id="%1$s" value="yes" %2$s /> %3$s</label>',
						esc_attr( $field ),
						checked( 'yes', $value, false ),
						esc_html( $args['cb_label'] )
					);
					break;

				case 'select':
					printf( '<select name="wp_zoom_settings[%1$s]" id="%1$s">', esc_attr( $field ) );
					foreach ( (array) $args['options'] as $option => $label ) {
						printf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $option ),
							selected( (string) $value, (string) $option, false ),
							esc_html( $label )
						);
					}
					echo '</select>';
					break;

				case 'text':
				default:
					printf( '<input type="text" name="wp_zoom_settings[%1$s]" id="%1$s" value="%2$s" />', esc_attr( $field ), esc_attr( $value ) );
			}

			if ( ! empty( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
			}
			?>
		</td>
	</tr>

	<?php
}

/**
 * Array of all settings fields
 *
 * @param string $tab Specific tab settings to retrieve.
 * @return array
 */
function wp_zoom_get_settings_fields( $tab = '' ) {
	$fields = apply_filters(
		'wp_zoom_settings_fields',
		array(
			'general'      => array(
				'enable_logging' => array(
					'label'       => esc_html__( 'Logging', 'wp-zoom' ),
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Write Zoom API errors to the uploads directory', 'wp-zoom' ),
					'description' => esc_html__( 'Logs are stored in uploads/wp-zoom-logs and removed after 14 days.', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
			),
			'registration' => array(
				'register_on_status'                => array(
					'label'       => esc_html__( 'Register customer', 'wp-zoom' ),
					'type'        => 'select',
					'default'     => 'paid',
					'options'     => wp_zoom_register_on_status_options(),
					'description' => esc_html__( 'Choose when a paid order should register the customer for the webinar.', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_register_on_status',
				),
				'hide_webinar_occurrences_disabled' => array(
					'label'       => esc_html__( 'Occurrences', 'wp-zoom' ),
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Hide webinar occurrences unavailable for registration', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_address'              => array(
					'label'       => esc_html__( 'Customer details', 'wp-zoom' ),
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Street address', 'wp-zoom' ),
					'description' => esc_html__( 'Email and name are always sent to Zoom. These fields are sent only when checked.', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_city'                 => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'City', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_state'                => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'State', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_postcode'             => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Postal code', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_country'              => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Country', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_phone'                => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Phone', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
				'send_billing_company'              => array(
					'label'       => '',
					'type'        => 'checkbox',
					'cb_label'    => esc_html__( 'Company', 'wp-zoom' ),
					'sanitize_cb' => 'wp_zoom_sanitize_checkbox_setting',
				),
			),
		)
	);

	if ( $tab && array_key_exists( $tab, $fields ) ) {
		return $fields[ $tab ];
	}

	return $fields;
}

/**
 * Handles saving form settings
 *
 * @return void
 */
function wp_zoom_settings_save() {
	if ( ! wp_zoom_verify_request_nonce( 'wp-zoom-settings' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'wp-zoom' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'wp-zoom' ) );
	}

	if ( empty( $_REQUEST['tab'] ) ) {
		wp_die( esc_html__( 'Unable to save settings, please try again.', 'wp-zoom' ) );
	}

	$settings_fields  = wp_zoom_get_settings_fields( sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) );
	$updated_settings = get_option( 'wp_zoom_settings', array() );

	foreach ( $settings_fields as $field => $args ) {
		$raw = wp_unslash( $_REQUEST['wp_zoom_settings'][ $field ] ?? '' );

		if ( isset( $args['sanitize_cb'] ) && is_callable( $args['sanitize_cb'] ) ) {
			$updated_settings[ $field ] = call_user_func( $args['sanitize_cb'], $raw );
		} else {
			$updated_settings[ $field ] = sanitize_text_field( $raw );
		}
	}

	update_option( 'wp_zoom_settings', $updated_settings );

	wp_safe_redirect( add_query_arg( 'updated', '1', wp_get_referer() ) );
	exit;
}
add_action( 'admin_post_wp_zoom_settings', 'wp_zoom_settings_save' );

/**
 * Read one plugin setting.
 *
 * @param string $key Setting key.
 * @param string $default Value when the setting has not been saved.
 * @return string
 */
function wp_zoom_get_setting( $key, $default = '' ) {
	$settings = (array) get_option( 'wp_zoom_settings', array() );

	if ( ! array_key_exists( $key, $settings ) || '' === $settings[ $key ] ) {
		return $default;
	}

	return (string) $settings[ $key ];
}

/**
 * Store a checkbox as yes or an empty string.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function wp_zoom_sanitize_checkbox_setting( $value ) {
	return 'yes' === $value ? 'yes' : '';
}

/**
 * Order statuses that can trigger webinar registration.
 *
 * @return array
 */
function wp_zoom_register_on_status_options() {
	$options = array(
		'paid' => esc_html__( 'When the order is paid', 'wp-zoom' ),
	);

	if ( function_exists( 'wc_get_order_statuses' ) ) {
		foreach ( wc_get_order_statuses() as $status => $label ) {
			$options[ preg_replace( '/^wc-/', '', $status ) ] = $label;
		}
	}

	return $options;
}

/**
 * Keep only a known order-status choice.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function wp_zoom_sanitize_register_on_status( $value ) {
	$value   = sanitize_key( $value );
	$options = wp_zoom_register_on_status_options();

	return array_key_exists( $value, $options ) ? $value : 'paid';
}
