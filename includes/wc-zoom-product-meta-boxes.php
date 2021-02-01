<?php
/**
 * Product metabox settings
 *
 * @package SeattleWebCo\WCZoom
 */


function wc_zoom_product_data_tab( $tabs ) {
	$tabs['wc-zoom'] = array(
		'label'    => __( 'Zoom', 'wc-zoom' ),
		'target'   => 'wc_zoom_product_data',
		'class'    => array( 'show_if_virtual' ),
		'priority' => 65,
	);

	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'wc_zoom_product_data_tab' );


function wc_zoom_product_data_tab_content() {
	global $post;

	$selected = (array) get_post_meta( $post->ID, '_wc_zoom_webinars', true );
	?>

	<div id="wc_zoom_product_data" class="panel woocommerce_options_panel">
		<div class="options_group">
			<p class="form-field _purchase_note_field ">
				<label for="_wc_zoom_webinars"><?php esc_html_e( 'Webinars', 'wc-zoom' ); ?></label>
				<?php
				wc_zoom_render_field_select_webinars(
					array(
						'selected'      => $selected,
						'multiple'      => true,
						'placeholder'   => esc_attr__( 'Select', 'wc-zoom' ),
					)
				);
				?>
			</p>
		</div>
	</div>

	<?php
}
add_action( 'woocommerce_product_data_panels', 'wc_zoom_product_data_tab_content' );

function wc_zoom_product_data_save( $id, $post ) {
	// phpcs:ignore
	$webinars = $_POST['_wc_zoom_webinars'] ?? null;

	if ( null !== $webinars ) {
		update_post_meta( $id, '_wc_zoom_webinars', array_filter( $webinars ) );
	}
}
add_action( 'woocommerce_process_product_meta', 'wc_zoom_product_data_save', 10, 2 );


function wc_zoom_variable_product_fields( $loop, $variation_data, $variation ) {
	$selected = (array) get_post_meta( $variation->ID, '_wc_zoom_webinars', true );
	?>

	<div class="show_if_variation_virtual" style="display: none;">
		<p class="form-row form-row-full">
			<label><?php esc_html_e( 'Webinars', 'wc-zoom' ); ?></label>
			<?php
			wc_zoom_render_field_select_webinars(
				array(
					'selected'      => $selected,
					'name'          => '_wc_zoom_webinars_variations[' . $variation->ID . ']',
					'multiple'      => true,
					'placeholder'   => esc_attr__( 'Select', 'wc-zoom' ),
				)
			);
			?>
		</p>
	</div>

	<?php
}
add_action( 'woocommerce_product_after_variable_attributes', 'wc_zoom_variable_product_fields', 10, 3 );

function wc_zoom_save_variation( $variation_id ) {
	// phpcs:ignore
	$webinars = $_POST['_wc_zoom_webinars_variations'][ $variation_id ] ?? null;

	if ( null !== $webinars ) {
		update_post_meta( $variation_id, '_wc_zoom_webinars', array_filter( (array) $webinars ) );
	}
}
add_action( 'woocommerce_save_product_variation', 'wc_zoom_save_variation' );
