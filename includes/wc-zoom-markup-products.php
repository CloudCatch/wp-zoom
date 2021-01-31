<?php
/**
 * Product markup hooks
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Render webinars associated with a product and conditionally display occurrence select date and time
 *
 * @return void
 */
function wc_zoom_single_product_summary() {
	global $webinars;

	if ( ! empty( $webinars ) && is_array( $webinars ) ) {
		?>

		<?php foreach ( $webinars as $webinar ) { ?>

			<div class="wc-zoom-webinar-group">
				<div class="wc-zoom-webinar-field">
					<label>
						<?php echo esc_html( $webinar['topic'] ); ?>
					</label>
					<div class="wc-zoom-webinar-field-date">
						<?php
						switch ( $webinar['type'] ) {
							// Normal webinar with start time.
							case '5':
								echo esc_html( wc_zoom_format_date_time( $webinar['start_time'], $webinar['timezone'] ) );
								break;

							// Recurring webinar with no fixed time.
							case '6':
								esc_html_e( 'Recurring webinar', 'wc-zoom' );
								break;

							// Recurring webinar with fixed time.
							case '9':
								wc_zoom_render_field_select_webinar_occurrence(
									$webinar,
									array(
										'name' => esc_attr( '_wc_zoom_webinars_occurrences[' . $webinar['id'] . ']' ),
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
add_action( 'woocommerce_before_add_to_cart_button', 'wc_zoom_single_product_summary', 11 );

/**
 * Populate global variable containing webinar data for current product
 *
 * @param WP_Post  $post Current post.
 * @param WP_Query $wp_query Current query.
 * @return void
 */
function wc_zoom_prepare_webinar_data( $post, $wp_query ) {
	if ( $wp_query->is_main_query() && ! isset( $GLOBALS['webinars'] ) ) {
		$product = wc_get_product( $post );

		$GLOBALS['webinars'] = wc_zoom_product_get_webinars( $product );
	}
}
add_action( 'the_post', 'wc_zoom_prepare_webinar_data', 5, 2 );

/**
 * Add webinar registrant information to cart item data
 *
 * @param array   $cart_item_data Current cart item data.
 * @param integer $product_id Product ID added to cart.
 * @param integer $variation_id Variation ID added to cart.
 * @return array
 */
function wc_zoom_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	if ( empty( $variation_id ) ) {
		$webinars = wc_zoom_product_get_webinars( $product_id );

		$cart_item_data['wc_zoom_webinars']             = $webinars;
		$cart_item_data['wc_zoom_webinars_occurrences'] = array();

		foreach ( $webinars as $webinar ) {
            // phpcs:ignore
			$occurrence_id = $_POST['_wc_zoom_webinars_occurrences'][ $webinar['id'] ] ?? '';

			$cart_item_data['wc_zoom_webinars_occurrences'][ $webinar['id'] ] = wc_zoom_get_available_webinar_occurrence( $webinar, (string) $occurrence_id );
		}
	} else {
		// If variation.
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wc_zoom_add_cart_item_data', 10, 3 );

/**
 * Verify occurrence selected is available.
 *
 * @param boolean $passed Whether validation was passed.
 * @param integer $product_id Product added to cart.
 * @param integer $quantity Quantity of product added to cart.
 * @param integer $variation_id Optional variation added to cart.
 * @return boolean
 */
function wc_zoom_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = null ) {
	if ( empty( $variation_id ) ) {
		$webinars = wc_zoom_product_get_webinars( $product_id );

		foreach ( $webinars as $webinar ) {
			if ( empty( $webinar['occurrences'] ) ) {
				continue;
			}

            // phpcs:ignore
			$occurrence_id = (string) $_POST['_wc_zoom_webinars_occurrences'][ $webinar['id'] ] ?? '';

			if ( empty( $occurrence_id ) ) {
				wc_add_notice( esc_html__( 'Please select a date and time for each webinar.', 'wc-zoom' ), 'error' );
				$passed = false;
			} else {
				$available = wc_zoom_occurrence_available( $webinar, $occurrence_id );

				if ( ! $available ) {
					wc_add_notice( esc_html__( 'Selected date and time is not available.', 'wc-zoom' ), 'error' );
					$passed = false;
				}
			}
		}
	} else {
		// If variation.
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wc_zoom_add_to_cart_validation', 10, 4 );

/**
 * Render webinar registrant information in cart table
 *
 * @param array $item_data Current item data.
 * @param array $cart_item_data All cart item data.
 * @return array
 */
function wc_zoom_get_item_data( $item_data, $cart_item_data ) {
	if ( ! empty( $cart_item_data['wc_zoom_webinars'] ) ) {
		foreach ( $cart_item_data['wc_zoom_webinars'] as $webinar ) {
			$start_time = $webinar['start_time'] ?? null;
			$occurrence = $cart_item_data['wc_zoom_webinars_occurrences'][ $webinar['id'] ] ?? array();

			// Check if webinar still exists; e.g. webinar could of been deleted and old data cached.
			if ( ! isset( $webinar['topic'] ) ) {
				continue;
			}

			$display = '';

			if ( $start_time ) {
				$display = wc_zoom_format_date_time( $start_time, $webinar['timezone'] );
			} elseif ( ! empty( $occurrence ) ) {
				$display = wc_zoom_format_date_time( $occurrence['start_time'], $webinar['timezone'] );
			}

			$item_data[] = array(
				'key'       => esc_html( $webinar['topic'] ),
				'value'     => esc_html( $display ),
			);
		}
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wc_zoom_get_item_data', 10, 2 );

/**
 * Check if cart items and their webinars are still valid
 *
 * @return boolean|WP_Error
 */
function wc_zoom_check_cart_items() {
	$return = true;

	foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
		if ( ! isset( $values['wc_zoom_webinars'] ) ) {
			continue;
		}

		$product = $values['data'];

		$webinars    = wc_zoom_product_get_webinars( $product->get_id() );
		$webinar_ids = wp_list_pluck( $webinars, 'id' );

		foreach ( $values['wc_zoom_webinars'] as $webinar ) {
            // phpcs:ignore
			if ( ! in_array( $webinar['id'], $webinar_ids ) ) {
				WC()->cart->set_quantity( $cart_item_key, 0 );
				wc_add_notice( __( 'A product in your cart contains a webinar which has been modified or no longer exists, therefore the product was removed from your cart.', 'wc-zoom' ), 'error' );

				$return = false;
			}
		}
	}

	return $return;
}
add_action( 'woocommerce_check_cart_items', 'wc_zoom_check_cart_items' );

/**
 * Add order line item meta
 *
 * @param WC_Order_Item_Product $item Current product while looping through order items.
 * @param string                $cart_item_key Cart product item key.
 * @param array                 $values Cart item data.
 * @param WC_Order              $order Current order.
 * @return void
 */
function wc_zoom_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['wc_zoom_webinars'] ) ) {
		foreach ( $values['wc_zoom_webinars'] as $webinar ) {
            // phpcs:ignore
			$occurrence_id = $_POST['_wc_zoom_webinars_occurrences'][ $webinar['id'] ] ?? '';

			$cart_item_data['wc_zoom_webinars_occurrences'][ $webinar['id'] ] = wc_zoom_get_available_webinar_occurrence( $webinar, (string) $occurrence_id );

			$item->add_meta_data( __( 'Webinar', 'wc-zoom' ), esc_html( $webinar['topic'] . ' - ' ) );

		}
	}

	$item->add_meta_data( __( 'Engraving', 'wc-zoom' ), 'test' );
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wc_zoom_create_order_line_item', 10, 4 );



/**
 * Register the user to purchased webinars
 *
 * @param integer $order_id ID or order completed.
 * @return void
 */
function wc_zoom_payment_complete( $order_id ) {
	$order = wc_get_order( $order_id );

	foreach ( $order->get_items() as $item ) {
		if ( $item->is_type( 'line_item' ) ) {
			$product = $item->get_product();

			// var_dump( $item->get_meta_data() );

		}
	}
}
add_action( 'woocommerce_payment_complete', 'wc_zoom_payment_complete' );
