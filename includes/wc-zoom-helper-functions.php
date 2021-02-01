<?php
/**
 * Helper functions
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Format date / time string
 *
 * @param string $datetime Date / time to format.
 * @param string $timezone Timezone.
 * @return string
 */
function wc_zoom_format_date_time( string $datetime, string $timezone = 'GMT' ) {
	$gmt_timezone   = new DateTimeZone( 'GMT' );
	$local_timezone = new DateTimeZone( $timezone );

	$gmt_datetime = new DateTime( trim( $datetime, 'Z' ), $gmt_timezone );
	$offset       = $local_timezone->getOffset( $gmt_datetime );
	$gmt_datetime->add( DateInterval::createFromDateString( (string) $offset . 'seconds' ) );

	$local_datetime = new DateTime( $gmt_datetime->format( 'Y-m-d H:i:s' ), $local_timezone );

	return $local_datetime->format( apply_filters( 'wc_zoom_datetime_format', 'l, F jS, Y \a\t g:ia T' ) );
}

/**
 * Get webinars for a given product
 *
 * @param integer|WC_Product|WP_Post $product Product to check.
 * @return array
 */
function wc_zoom_product_get_webinars( $product = null ) {
	global $wc_zoom;

	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	} elseif ( ! $product ) {
		$product = wc_get_product( get_the_ID() );
	} elseif ( is_a( $product, 'WP_Post' ) && get_post_type( $product ) === 'product' ) {
		$product = wc_get_product( $product->ID );
	}

	if ( is_a( $product, 'WC_Product' ) ) {
		if ( ! $product->is_type( 'variable' ) ) {
			$webinars = $product->get_meta( '_wc_zoom_webinars' );

			if ( ! is_array( $webinars ) ) {
				$webinars = (array) $webinars;
			}

			if ( ! empty( $webinars ) ) {
				array_walk(
					$webinars,
					function( &$webinar ) use ( $wc_zoom ) {
						$_webinar = $wc_zoom->get_webinar( $webinar );

						$webinar = isset( $_webinar['uuid'] ) ? $_webinar : null;
					}
				);
			}

			return $webinars ? array_filter( $webinars ) : array();
		}
	}

	return array();
}

/**
 * Does a given product contain webinars?
 *
 * @param integer|WC_Product|WP_Post $product Product to check.
 * @return boolean
 */
function wc_zoom_product_has_webinars( $product = null ) {
	return wc_zoom_product_get_webinars( $product ) ? true : false;
}

/**
 * Get an occurrence from a given webinar
 *
 * @param array  $webinar Webinar data.
 * @param string $occurrence_id Occurrence ID.
 * @return bool|array Array if occurrence found otherwise false
 */
function wc_zoom_get_available_webinar_occurrence( array $webinar, string $occurrence_id ) {
	if ( ! empty( $webinar['occurrences'] ) ) {
		// Only allow available occurrences.
		$available_occurrences = array_filter(
			$webinar['occurrences'],
			function( $occurrence ) {
				return $occurrence['status'] === 'available';
			}
		);

		foreach ( $available_occurrences as $occurrence ) {
			if ( (string) $occurrence['occurrence_id'] === $occurrence_id ) {
				return $occurrence;
			}
		}
	}

	return false;
}

/**
 * Is a given occurrence available for registration in the webinar
 *
 * @param array  $webinar Webinar data.
 * @param string $occurrence_id Occurrence ID.
 * @return boolean
 */
function wc_zoom_occurrence_available( array $webinar, string $occurrence_id ) {
	return (bool) wc_zoom_get_available_webinar_occurrence( $webinar, $occurrence_id );
}
