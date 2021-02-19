<?php
/**
 * Helper functions
 *
 * @package SeattleWebCo\WPZoom
 */

use SeattleWebCo\WPZoom\Cache;

/**
 * Format date / time string
 *
 * @param string $datetime Date / time to format.
 * @param string $timezone Timezone.
 * @return string
 */
function wp_zoom_format_date_time( string $datetime, string $timezone = '' ) {
	$gmt_timezone   = new DateTimeZone( 'GMT' );
	$local_timezone = new DateTimeZone( $timezone === '' ? wp_timezone_string() : $timezone );

	$gmt_datetime = new DateTime( trim( $datetime, 'Z' ), $gmt_timezone );
	$offset       = $local_timezone->getOffset( $gmt_datetime );
	$gmt_datetime->add( DateInterval::createFromDateString( (string) $offset . 'seconds' ) );

	$local_datetime = new DateTime( $gmt_datetime->format( 'Y-m-d H:i:s' ), $local_timezone );

	return $local_datetime->format( apply_filters( 'wp_zoom_datetime_format', 'l, F jS, Y \a\t g:ia T' ) );
}

/**
 * Return an unformatted end date / time string
 *
 * @param string  $datetime Date / time to get end.
 * @param integer $duration Duration in minutes.
 * @return string
 */
function wp_zoom_format_get_end_date_time( string $datetime, int $duration ) {
	$gmt_timezone = new DateTimeZone( 'GMT' );

	$gmt_datetime = new DateTime( trim( $datetime, 'Z' ), $gmt_timezone );
	$gmt_datetime->add( DateInterval::createFromDateString( (string) $duration . 'minutes' ) );

	return $gmt_datetime->format( 'Y-m-d\TH:i:sZ' );
}

/**
 * Get all purchase products for a given webinar
 *
 * @param string $webinar_id The webinar ID to check.
 * @return null|array
 */
function wp_zoom_get_purchase_products( string $webinar_id ) {
	global $wpdb;

	$cache = Cache::get( 'wp_zoom_webinar_purchase_products_' . $webinar_id );

	if ( false !== $cache ) {
		return $cache;
	}

	$webinar_id = (string) intval( $webinar_id );

	// phpcs:ignore
	$products = $wpdb->get_col(
		"
		SELECT pm.post_id
		FROM   {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->postmeta} pm2
					ON pm2.meta_key = '_wp_zoom_webinars'
					AND pm2.meta_value LIKE '%{$webinar_id}%'
					AND pm2.post_id = pm.post_id
		WHERE  pm.meta_key = '_wp_zoom_purchase_url'
			AND pm.meta_value = 'yes' 
	"
	);

	if ( empty( $products ) ) {
		return null;
	}

	Cache::set( 'wp_zoom_webinar_purchase_products_' . $webinar_id, $products );

	return $products;
}

/**
 * Get webinars for a given post
 *
 * @param integer|WP_Post $post Post to check.
 * @return array
 */
function wp_zoom_get_webinars( $post = null ) {
	global $wp_zoom;

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	} elseif ( ! $post ) {
		$post = get_post( get_the_ID() );
	} elseif ( is_a( $post, 'WC_Product' ) ) {
		$post = get_post( $post->get_id() );
	}

	if ( is_a( $post, 'WP_Post' ) ) {
		$webinars = get_post_meta( $post->ID, '_wp_zoom_webinars', true );

		if ( ! is_array( $webinars ) ) {
			$webinars = (array) $webinars;
		}

		if ( ! empty( $webinars ) ) {
			array_walk(
				$webinars,
				function( &$webinar ) use ( $wp_zoom ) {
					$_webinar = $wp_zoom->get_webinar( $webinar );

					$webinar = isset( $_webinar['uuid'] ) ? $_webinar : null;
				}
			);
		}

		return $webinars ? array_filter( $webinars ) : array();
	}

	return array();
}

/**
 * Does a given post contain webinars?
 *
 * @param integer|WP_Post $post Post to check.
 * @return boolean
 */
function wp_zoom_has_webinars( $post = null ) {
	return wp_zoom_get_webinars( $post ) ? true : false;
}

/**
 * Does a given post contain a Type 9 webinar?
 *
 * @param integer|WP_Post $post Post to check.
 * @return boolean
 */
function wp_zoom_has_type_9_webinar( $post = null ) {
	$webinars = wp_zoom_get_webinars( $post );

	foreach ( $webinars as $webinar ) {
		if ( $webinar['type'] ?? '' === 9 ) {
			return true;
		}
	}

	return false;
}

/**
 * Get an occurrence from a given webinar
 *
 * @param array  $webinar Webinar data.
 * @param string $occurrence_id Occurrence ID.
 * @return bool|array Array if occurrence found otherwise false
 */
function wp_zoom_get_available_webinar_occurrence( array $webinar, string $occurrence_id ) {
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
function wp_zoom_occurrence_available( array $webinar, string $occurrence_id ) {
	return (bool) wp_zoom_get_available_webinar_occurrence( $webinar, $occurrence_id );
}

/**
 * Sanitize recursively
 *
 * @param array|string $data Data to sanitize.
 * @return array
 */
function wp_zoom_sanitize_recursive( $data ) {
	if ( ! is_array( $data ) ) {
		return sanitize_text_field( $data );
	}

	foreach ( $data as &$data ) {
		if ( is_array( $data ) ) {
			$data = array_map( 'sanitize_text_field', $data );
		} else {
			$data = sanitize_text_field( $data );
		}
	}

	return $data;
}
