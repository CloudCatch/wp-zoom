<?php
/**
 * Product markup hooks
 *
 * @package SeattleWebCo\WPZoom
 */

use SeattleWebCo\WPZoom\Cache;

/**
 * Render webinars associated with a product and conditionally display occurrence select date and time
 *
 * @return void
 */
function wp_zoom_single_product_summary() {
	global $webinars;

	if ( ! empty( $webinars ) && is_array( $webinars ) ) {
		?>

		<?php foreach ( $webinars as $webinar ) { ?>

			<div class="wp-zoom-webinar-group">
				<div class="wp-zoom-webinar-field">
					<label>
						<?php echo esc_html( $webinar['topic'] ); ?>
					</label>
					<div class="wp-zoom-webinar-field-date">
						<?php
						switch ( $webinar['type'] ) {
							// Normal webinar with start time.
							case '5':
								echo esc_html( wp_zoom_format_date_time( $webinar['start_time'], $webinar['timezone'] ) );
								break;

							// Recurring webinar with no fixed time.
							case '6':
								esc_html_e( 'Recurring webinar', 'wp-zoom' );
								break;

							// Recurring webinar with fixed time.
							case '9':
								wp_zoom_render_field_select_webinar_occurrence(
									$webinar,
									array(
										'name' => esc_attr( '_wp_zoom_webinars_occurrences[' . $webinar['id'] . ']' ),
									)
								);
								break;
						}
						?>
					</div>
				</div>
			</div>

		<?php } ?>

		<?php
	}

}
add_action( 'woocommerce_before_add_to_cart_button', 'wp_zoom_single_product_summary', 11 );

/**
 * Populate global variable containing webinar data for current product
 *
 * @param WP_Post  $post Current post.
 * @param WP_Query $wp_query Current query.
 * @return void
 */
function wp_zoom_prepare_webinar_data( $post, $wp_query ) {
	if ( $wp_query->is_main_query() && ! isset( $GLOBALS['webinars'] ) ) {
		$product = wc_get_product( $post );

		$GLOBALS['webinars'] = wp_zoom_product_get_webinars( $product );
	}
}
add_action( 'the_post', 'wp_zoom_prepare_webinar_data', 5, 2 );

/**
 * Add webinar registrant information to cart item data
 *
 * @param array   $cart_item_data Current cart item data.
 * @param integer $product_id Product ID added to cart.
 * @param integer $variation_id Variation ID added to cart.
 * @return array
 */
function wp_zoom_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	if ( empty( $variation_id ) ) {
		$webinars = wp_zoom_product_get_webinars( $product_id );

		if ( empty( $webinars ) ) {
			return $cart_item_data;
		}

		$cart_item_data['wp_zoom_webinars']             = $webinars;
		$cart_item_data['wp_zoom_webinars_occurrences'] = array();

		foreach ( $webinars as $webinar ) {
            // phpcs:ignore
			$occurrence_id = $_POST['_wp_zoom_webinars_occurrences'][ $webinar['id'] ] ?? '';

			$cart_item_data['wp_zoom_webinars_occurrences'][ $webinar['id'] ] = wp_zoom_get_available_webinar_occurrence( $webinar, (string) $occurrence_id );
		}
	} else {
		// If variation.
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wp_zoom_add_cart_item_data', 10, 3 );

/**
 * Verify occurrence selected is available.
 *
 * @param boolean $passed Whether validation was passed.
 * @param integer $product_id Product added to cart.
 * @param integer $quantity Quantity of product added to cart.
 * @param integer $variation_id Optional variation added to cart.
 * @return boolean
 */
function wp_zoom_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = null ) {
	if ( empty( $variation_id ) ) {
		$webinars = wp_zoom_product_get_webinars( $product_id );

		foreach ( $webinars as $webinar ) {
			if ( empty( $webinar['occurrences'] ) ) {
				continue;
			}

            // phpcs:ignore
			$occurrence_id = (string) $_POST['_wp_zoom_webinars_occurrences'][ $webinar['id'] ] ?? '';

			if ( empty( $occurrence_id ) ) {
				wc_add_notice( esc_html__( 'Please select a date and time for each webinar.', 'wp-zoom' ), 'error' );
				$passed = false;
			} else {
				$available = wp_zoom_occurrence_available( $webinar, $occurrence_id );

				if ( ! $available ) {
					wc_add_notice( esc_html__( 'Selected date and time is not available.', 'wp-zoom' ), 'error' );
					$passed = false;
				}
			}
		}
	} else {
		// If variation.
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wp_zoom_add_to_cart_validation', 10, 4 );

/**
 * Render webinar registrant information in cart table
 *
 * @param array $item_data Current item data.
 * @param array $cart_item_data All cart item data.
 * @return array
 */
function wp_zoom_get_item_data( $item_data, $cart_item_data ) {
	if ( ! empty( $cart_item_data['wp_zoom_webinars'] ) ) {
		foreach ( $cart_item_data['wp_zoom_webinars'] as $webinar ) {
			$start_time = $webinar['start_time'] ?? null;
			$occurrence = $cart_item_data['wp_zoom_webinars_occurrences'][ $webinar['id'] ] ?? array();

			// Check if webinar still exists; e.g. webinar could of been deleted and old data cached.
			if ( ! isset( $webinar['topic'] ) ) {
				continue;
			}

			$date_display = null;

			if ( $start_time ) {
				$date_display = wp_zoom_format_date_time( $start_time, $webinar['timezone'] );
			} elseif ( ! empty( $occurrence ) ) {
				$date_display = wp_zoom_format_date_time( $occurrence['start_time'], $webinar['timezone'] );
			}

			$item_data[] = array(
				'key'       => esc_html( $webinar['topic'] ),
				'value'     => esc_html( $date_display ?? __( 'Webinar has no fixed time.', 'wp-zoom' ) ),
			);
		}
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wp_zoom_get_item_data', 10, 2 );

/**
 * Check if cart items and their webinars are still valid
 *
 * @return boolean|WP_Error
 */
function wp_zoom_check_cart_items() {
	$return = true;

	foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
		if ( ! isset( $values['wp_zoom_webinars'] ) ) {
			continue;
		}

		$product = $values['data'];

		$webinars    = wp_zoom_product_get_webinars( $product->get_id() );
		$webinar_ids = wp_list_pluck( $webinars, 'id' );

		foreach ( $values['wp_zoom_webinars'] as $webinar ) {
            // phpcs:ignore
			if ( ! in_array( $webinar['id'], $webinar_ids ) ) {
				WC()->cart->set_quantity( $cart_item_key, 0 );
				wc_add_notice( __( 'A product in your cart contains a webinar which has been modified or no longer exists, therefore the product was removed from your cart.', 'wp-zoom' ), 'error' );

				$return = false;
			}
		}
	}

	return $return;
}
add_action( 'woocommerce_check_cart_items', 'wp_zoom_check_cart_items' );

/**
 * Add order line item meta
 *
 * @param WC_Order_Item_Product $item Current product while looping through order items.
 * @param string                $cart_item_key Cart product item key.
 * @param array                 $values Cart item data.
 * @param WC_Order              $order Current order.
 * @return void
 */
function wp_zoom_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['wp_zoom_webinars'] ) ) {
		foreach ( $values['wp_zoom_webinars'] as $webinar ) {
			$webinar_id = $webinar['id'] ?? '';
			$start_time = $webinar['start_time'] ?? null;
			$occurrence = $values['wp_zoom_webinars_occurrences'][ $webinar['id'] ] ?? array();

			// Check if webinar still exists; e.g. webinar could of been deleted and old data cached.
			if ( ! isset( $webinar['topic'] ) ) {
				continue;
			}

			$date_display = null;

			if ( $start_time ) {
				$date_display = wp_zoom_format_date_time( $start_time, $webinar['timezone'] );
			} elseif ( ! empty( $occurrence ) ) {
				$date_display = wp_zoom_format_date_time( $occurrence['start_time'], $webinar['timezone'] );

				$item->add_meta_data( 'zoom_webinar_occurrence_id', $occurrence['occurrence_id'] );
			}

			$item->add_meta_data( 'zoom_webinar_id', $webinar_id );
			$item->add_meta_data( 'zoom_webinar_topic', $webinar['topic'] );
			$item->add_meta_data( 'zoom_webinar_datetime', $date_display ?? esc_html__( 'Webinar has no fixed time.', 'wp-zoom' ) );
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wp_zoom_create_order_line_item', 10, 4 );



/**
 * Register the user to purchased webinars
 *
 * @param integer  $order_id ID or order paid for.
 * @param string   $from Status transitioning from.
 * @param string   $to Status transitioning to.
 * @param WC_Order $order The order that was paid for.
 * @return void
 */
function wp_zoom_payment_complete( $order_id, $from, $to, $order ) {
	global $wp_zoom;

	if ( ! in_array( $to, wc_get_is_paid_statuses(), true ) ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		if ( $item->is_type( 'line_item' ) ) {
			$webinar_id    = null;
			$occurrence_id = null;
			$topic         = null;
			$datetime      = null;

			foreach ( $item->get_meta_data( 'wp_zoom_webinars' ) as $meta_data ) {
				if ( $meta_data->key === 'zoom_webinar_id' ) {
					$webinar_id = $meta_data->value;
				} elseif ( $meta_data->key === 'zoom_webinar_occurrence_id' ) {
					$occurrence_id = $meta_data->value;
				} elseif ( $meta_data->key === 'zoom_webinar_topic' ) {
					$topic = $meta_data->value;
				} elseif ( $meta_data->key === 'zoom_webinar_datetime' ) {
					$datetime = $meta_data->value;
				}
			}

			if ( ! $webinar_id ) {
				continue;
			}

			// Delete cache.
			Cache::delete( 'wp_zoom_webinar_' . $webinar_id );

			$registration = $wp_zoom->add_webinar_registrant( $webinar_id, new \WC_Customer( $order->get_customer_id() ), $occurrence_id );

				// An error occurred.
			if ( isset( $registration['registrant_id'] ) ) {
				/* translators: 1: Webinar topic 2: Webinar date and time */
				$order->add_order_note( sprintf( esc_html__( 'User successfully registered for %1$s (%2$s)', 'wp-zoom' ), $topic, $datetime ) );

				add_post_meta(
					$order_id,
					'_zoom_webinar_registration',
					$registration
				);

			} else {
				/* translators: 1: Webinar topic */
				$order->add_order_note( sprintf( esc_html__( 'An error occurred while registering customer for %1$s', 'wp-zoom' ), $topic ) );

			}
		}
	}
}
add_action( 'woocommerce_order_status_changed', 'wp_zoom_payment_complete', 10, 4 );

/**
 * Display label of order item
 *
 * @param string             $display_key Display label.
 * @param WC_Order_Item_Meta $meta Order item meta.
 * @param WC_Order_Item      $item Order item object.
 * @return string
 */
function wp_zoom_order_item_display_label( $display_key, $meta, $item ) {
	switch ( $display_key ) {
		case 'zoom_webinar_id':
			$display_key = esc_html__( 'Zoom Webinar ID', 'wp-zoom' );
			break;
		case 'zoom_webinar_occurrence_id':
			$display_key = esc_html__( 'Zoom Webinar Occurrence ID', 'wp-zoom' );
			break;
		case 'zoom_webinar_topic':
			$display_key = esc_html__( 'Zoom Webinar Topic', 'wp-zoom' );
			break;
		case 'zoom_webinar_datetime':
			$display_key = esc_html__( 'Zoom Webinar Date & Time', 'wp-zoom' );
			break;
	}

	return $display_key;
}
add_filter( 'woocommerce_order_item_display_meta_key', 'wp_zoom_order_item_display_label', 10, 3 );
