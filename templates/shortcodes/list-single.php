<?php
/**
 * Single view of object for List shortcode template
 *
 * @package SeattleWebCo\WPZoom
 */

?>

<div class="wp-zoom-list-item" data-id="<?php echo esc_attr( $args['data']['id'] ); ?>">
	<div class="wp-zoom-list-item--date">
		<div class="wp-zoom-list-item--calendar">
			<div class="wp-zoom-list-item--calendar-month"><?php echo esc_html( wp_zoom_format_date_time( $args['data']['start_time'], '', 'M' ) ); ?></div>
			<div class="wp-zoom-list-item--calendar-day"><?php echo esc_html( wp_zoom_format_date_time( $args['data']['start_time'], '', 'j' ) ); ?></div>
			<div class="wp-zoom-list-item--calendar-weekday"><?php echo esc_html( wp_zoom_format_date_time( $args['data']['start_time'], '', 'l' ) ); ?></div>
		</div>
	</div>
	<div class="wp-zoom-list-item--info">
		<h3 class="wp-zoom-list-item--info-topic">
		<?php
		if ( $args['url'] ) {
			 printf( '<a href="%s">%s</a>', esc_url( $args['url'] ), esc_html( $args['data']['topic'] ) );
		} else {
			echo esc_html( $args['data']['topic'] );
		}
		?>
		</h3>
		<div class="wp-zoom-list-item--info-date"><?php echo esc_html( wp_zoom_format_date_time( $args['data']['start_time'] ) ); ?></div>

		<?php do_action( 'wp_zoom_list_after_info', $args ); ?>

		<div class="wp-zoom-list-item--info-actions">
			<?php do_action( 'wp_zoom_list_after_info_actions', $args ); ?>
		</div>
	</div>
</div>
