( function ( $ ) {
	$( "#woocommerce-product-data .wp-zoom-webinars-field" ).selectWoo();

	$( document.body ).on( "wc-enhanced-select-init", function () {
		$( "#woocommerce-product-data .wp-zoom-webinars-field" ).selectWoo();

		var wrapper = $( "#woocommerce-product-data" );

		$( "input.variable_is_virtual", wrapper ).on( "change", function ( e ) {
			if ( $( this ).is( ":checked" ) ) {
				$( this )
					.closest( ".woocommerce_variation" )
					.find( ".show_if_variation_virtual" )
					.show();
			} else {
				$( this )
					.closest( ".woocommerce_variation" )
					.find( ".show_if_variation_virtual" )
					.hide();
			}
		} );

		$( "input.variable_is_virtual", wrapper ).change();

		$( '.wp-zoom-webinars-field,#_wp_zoom_purchase_url' ).on( 'change', function () {
			if ( $( '#_wp_zoom_purchase_url' ).is( ':checked' ) ) {
				$.ajax( {
					url : wp_zoom.ajax_url,
					data: {
						action      : 'wp_zoom_get_purchase_url_products',
						_wpnonce    : wp_zoom.nonce,
						webinars    : $( '.wp-zoom-webinars-field' ).val(),
						current_post: $( this ).closest( 'form' ).find( '[name="post_ID"]' ).val()
					},
					success: function ( data ) {
						$( '.wp-zoom-purchase-url-notice' ).html( data );
					}
				} );
			} else {
				$( '.wp-zoom-purchase-url-notice' ).html( '' );
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );
