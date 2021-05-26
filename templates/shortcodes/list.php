<?php
/**
 * List shortcode template
 *
 * @package SeattleWebCo\WPZoom
 */

?>

<div class="wp-zoom-list">
	<?php
	if ( ! empty( $args['data'] ) ) {
		foreach ( $args['data'] as $object ) {
			$purchase_products = wp_zoom_get_purchase_products( $object['id'] );
			$purchase_url      = $purchase_products ? get_permalink( current( $purchase_products ) ) : '#';

			wp_zoom_load_template(
				'shortcodes/list-single.php',
				false,
				array(
					'data'     => $object,
					'products' => $purchase_products,
					'url'      => $purchase_url,
				)
			);
		}
	} else {
		printf( '<p class="wp-zoom-no-results">%s</div>', esc_html__( 'No upcoming results', 'wp-zoom' ) );
	}
	?>

	<?php
	if ( $args['total'] > $args['atts']['per_page'] ) {
		$big = 999999999;

		echo '<div class="wp-zoom-pagination">';

		// phpcs:ignore
		echo paginate_links(
			array(
				'base'   => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'type'   => 'list',
				'total'  => round( $args['total'] / $args['atts']['per_page'] ),
			)
		);

		echo '</div>';
	}
	?>

</div>
