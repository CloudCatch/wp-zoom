( function () {
	function webinarValues() {
		var values = [];

		document.querySelectorAll( '.wp-zoom-webinars-field' ).forEach( function ( select ) {
			Array.prototype.forEach.call( select.selectedOptions, function ( option ) {
				if ( option.value ) {
					values.push( option.value );
				}
			} );
		} );

		return values;
	}

	function refreshPurchaseNotice( source ) {
		var checkbox = document.getElementById( '_wp_zoom_purchase_url' );
		var notice = document.querySelector( '.wp-zoom-purchase-url-notice' );

		if ( ! notice || ! window.wp_zoom ) {
			return;
		}

		if ( ! checkbox || ! checkbox.checked ) {
			notice.innerHTML = '';
			return;
		}

		var form = source && source.closest ? source.closest( 'form' ) : checkbox.closest( 'form' );
		var postInput = form ? form.querySelector( '[name="post_ID"]' ) : null;
		var params = new URLSearchParams();

		params.set( 'action', 'wp_zoom_get_purchase_url_products' );
		params.set( '_wpnonce', window.wp_zoom.nonce );
		params.set( 'current_post', postInput ? postInput.value : '' );
		webinarValues().forEach( function ( id ) {
			params.append( 'webinars[]', id );
		} );

		window.fetch( window.wp_zoom.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: params.toString(),
		} ).then( function ( response ) {
			return response.text();
		} ).then( function ( html ) {
			notice.innerHTML = html;
		} );
	}

	function syncVirtualFields( checkbox ) {
		var variation = checkbox.closest( '.woocommerce_variation' );

		if ( ! variation ) {
			return;
		}

		variation.querySelectorAll( '.show_if_variation_virtual' ).forEach( function ( field ) {
			field.style.display = checkbox.checked ? 'block' : 'none';
		} );
	}

	document.addEventListener( 'change', function ( event ) {
		var target = event.target;

		if ( ! target || ! target.matches ) {
			return;
		}

		if ( target.matches( 'input.variable_is_virtual' ) ) {
			syncVirtualFields( target );
		}

		if ( target.matches( '.wp-zoom-webinars-field, #_wp_zoom_purchase_url' ) ) {
			refreshPurchaseNotice( target );
		}
	} );

	document.querySelectorAll( 'input.variable_is_virtual' ).forEach( syncVirtualFields );

	var purchaseUrl = document.getElementById( '_wp_zoom_purchase_url' );

	if ( purchaseUrl ) {
		refreshPurchaseNotice( purchaseUrl );
	}
} )();
