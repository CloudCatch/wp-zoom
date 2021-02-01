( function( $ ) {
    $( '#woocommerce-product-data .wc-zoom-webinars-field' ).selectWoo();

    $( document.body ).on( 'wc-enhanced-select-init', function() {
        $( '#woocommerce-product-data .wc-zoom-webinars-field' ).selectWoo();

        var wrapper = $( '#woocommerce-product-data' );

        $( 'input.variable_is_virtual', wrapper ).on( 'change', function( e ) {
            if ( $( this ).is( ':checked' ) ) {
                $( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_virtual' ).show();
            } else {
                $( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_virtual' ).hide();
            }
        } );

        $( 'input.variable_is_virtual', wrapper ).change();
    } );

} )( jQuery );