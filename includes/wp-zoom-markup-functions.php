<?php
/**
 * Product metabox settings
 *
 * @package SeattleWebCo\WPZoom
 */

/**
 * Render field which displays available webinars
 *
 * @param array $args <select> field arguments.
 * @return void
 */
function wp_zoom_render_field_select_webinars( array $args = array() ) {
	global $wp_zoom;

	$args = wp_parse_args(
		$args,
		array(
			'name'          => '_wp_zoom_webinars',
			'placeholder'   => esc_html__( 'Select Webinar', 'wp-zoom' ),
			'selected'      => array(),
			'multiple'      => false,
		)
	);

	$webinars = $wp_zoom->get_webinars( get_user_meta( get_current_user_id(), 'wp_zoom_user_id', true ), false );
	?>

	<select 
		name="<?php echo esc_attr( $args['name'] ); ?><?php echo $args['multiple'] ? '[]' : ''; ?>" 
		<?php echo $args['multiple'] ? 'multiple' : ''; ?>
		placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
		class="wp-zoom-webinars-field"
	>	
		<?php if ( ! empty( $webinars['webinars'] ) ) { ?>
			<?php foreach ( $webinars['webinars'] as $webinar ) { ?>

				<?php // phpcs:ignore ?>
				<option <?php selected( in_array( $webinar['id'], $args['selected'] ), true ); ?> value="<?php echo esc_attr( $webinar['id'] ); ?>">
					<?php echo esc_html( $webinar['topic'] ); ?>
				</option>

			<?php } ?>
		<?php } ?>
	</select>

	<?php
}

/**
 * Render field which displays webinar info/field
 *
 * @param array $webinar Webinar data.
 * @param array $args <select> field arguments.
 * @return void
 */
function wp_zoom_render_field_webinar( array $webinar, array $args = array() ) {
	switch ( $webinar['type'] ) {
		// Normal webinar with start time.
		case '5':
			echo esc_html( wp_zoom_format_date_time( $webinar['start_time'] ) );
			break;

		// Recurring webinar with no fixed time.
		case '6':
			esc_html_e( 'Recurring webinar', 'wp-zoom' );
			break;

		// Recurring webinar with fixed time.
		case '9':
			wp_zoom_render_field_select_webinar_occurrence( $webinar, $args );
			break;
	}
}

/**
 * Render field which displays available webinar occurrences
 *
 * @param array $webinar Webinar containing occurrences.
 * @param array $args <select> field arguments.
 * @return void
 */
function wp_zoom_render_field_select_webinar_occurrence( array $webinar, array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'name'          => '_wp_zoom_webinars_occurrences',
			'id'            => '_wp_zoom_webinars_occurrences',
			'placeholder'   => esc_html__( 'Select Date & Time', 'wp-zoom' ),
			'selected'      => array(),
			'multiple'      => false,
		)
	);

	$occurrences = $webinar['occurrences'] ?? array();
	?>

	<?php if ( ! empty( $occurrences ) ) { ?>

		<select 
			name="<?php echo esc_attr( $args['name'] ); ?>" 
			id="<?php echo esc_attr( $args['id'] ); ?>" 
			<?php echo $args['multiple'] ? 'multiple' : ''; ?>
		>
			<option value=""><?php echo esc_attr( $args['placeholder'] ); ?></option>

			<?php foreach ( $occurrences as $occurrence ) { ?>
				<option 
					value="<?php echo esc_attr( $occurrence['occurrence_id'] ); ?>"
					<?php echo esc_attr( $occurrence['status'] !== 'available' ? 'disabled' : '' ); ?>
					<?php selected( in_array( $occurrence['occurrence_id'], $args['selected'] ), true ); ?>
				>
					<?php echo esc_html( wp_zoom_format_date_time( $occurrence['start_time'] ) ); ?>
				</option>
			<?php } ?>

		</select>

	<?php } else { ?>

	<span class="wp-zoom-no-occurrences"><?php esc_html_e( 'No available dates and times', 'wp-zoom' ); ?></span>

		<?php
	}
}

function wp_zoom_render_countdown_timer( array $webinar ) {
	print wp_zoom_get_next_webinar_date_time( $webinar );
}
