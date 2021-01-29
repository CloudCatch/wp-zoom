<?php
/**
 * Product metabox settings
 *
 * @package SeattleWebCo\WCZoom
 */

function wc_zoom_render_field_select_webinars( $args = array() ) {
	global $wc_zoom;

	$args = wp_parse_args(
		$args,
		array(
			'name'          => '_wc_zoom_webinars',
			'id'            => '_wc_zoom_webinars',
			'placeholder'   => esc_html__( 'Select Webinar', 'wc-zoom' ),
			'selected'      => null,
		)
	);

	$webinars = $wc_zoom->get_webinars();
	?>

	<select name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
		<option value=""><?php echo esc_html( $args['placeholder'] ); ?></option>

		<?php foreach ( $webinars['webinars'] as $webinar ) : ?>

			<option <?php selected( $args['selected'], $webinar['id'] ); ?> value="<?php echo esc_attr( $webinar['id'] ); ?>"><?php esc_html_e( $webinar['topic'], 'wc-zoom' ); ?></option>

		<?php endforeach; ?>
	</select>

	<?php
}
