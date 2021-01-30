<?php
/**
 * Product metabox settings
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Render field which displays available webinars
 *
 * @param array $args <select> field arguments.
 * @return void
 */
function wc_zoom_render_field_select_webinars( array $args = array() ) {
	global $wc_zoom;

	$args = wp_parse_args(
		$args,
		array(
			'name'          => '_wc_zoom_webinars',
			'id'            => '_wc_zoom_webinars',
			'placeholder'   => esc_html__( 'Select Webinar', 'wc-zoom' ),
			'selected'      => array(),
			'multiple'      => false,
		)
	);

	$webinars = $wc_zoom->get_webinars( false );
	?>

	<select 
		name="<?php echo esc_attr( $args['name'] ); ?><?php echo $args['multiple'] ? '[]' : ''; ?>" 
		id="<?php echo esc_attr( $args['id'] ); ?>" 
		<?php echo $args['multiple'] ? 'multiple' : ''; ?>
		placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
	>
		<?php foreach ( $webinars['webinars'] as $webinar ) { ?>

			<?php // phpcs:ignore ?>
			<option <?php selected( in_array( $webinar['id'], $args['selected'] ), true ); ?> value="<?php echo esc_attr( $webinar['id'] ); ?>">
				<?php echo esc_html( $webinar['topic'] ); ?>
			</option>

		<?php } ?>
	</select>

	<?php
}

/**
 * Render field which displays available webinar occurrences
 *
 * @param array $webinar Webinar containing occurrences.
 * @param array $args <select> field arguments.
 * @return void
 */
function wc_zoom_render_field_select_webinar_occurrence( array $webinar, array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'name'          => '_wc_zoom_webinars_occurrences',
			'id'            => '_wc_zoom_webinars_occurrences',
			'placeholder'   => esc_html__( 'Select Date & Time', 'wc-zoom' ),
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
				>
					<?php echo esc_html( wc_zoom_format_date_time( $occurrence['start_time'], $webinar['timezone'] ) ); ?>
				</option>
			<?php } ?>``

		</select>

	<?php } else { ?>

	<span class="wc-zoom-no-occurrences"><?php esc_html_e( 'No available dates and times', 'wc-zoom' ); ?></span>

		<?php
	}
}
