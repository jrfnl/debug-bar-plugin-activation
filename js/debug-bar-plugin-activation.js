/* globals debugBarPluginActivation */
jQuery( window ).ready( function() {

	var dbMenuItem = jQuery( '#debug-menu-link-Debug_Bar_Plugin_Activation .debug-bar-issue-count' );

	/* Make sure the spinner also works in the front-end */
	jQuery( '#debug-bar-plugin-activation' ).find( 'span.spinner' ).css( { 'background-image': 'url("' + debugBarPluginActivation.spinner + '")' } );


	/**
	 * Allow for deleting individual items.
	 */
	jQuery( 'table.debug-bar-plugin-activation' ).on( 'click', 'a.debug-bar-plugin-activation-delete', function( event ) {
		var eventTarget = jQuery( this ),
			rowType, spinner, eventData;

		event.preventDefault();

		rowType = eventTarget.attr( 'data-type' );
		spinner = eventTarget.closest( 'td' ).find( 'span.spinner' );
		spinner.addClass( 'is-active' );

		// Event Data to be passed to ajax backend.
		eventData = {
			dbpa_nonce: debugBarPluginActivation.dbpa_nonce,
			type:       rowType,
			plugin:     eventTarget.attr( 'data-plugin' ),
			action:     'debug-bar-plugin-activation_delete'
		};

		//	Performing ajax request and proccesing response.
		jQuery.post( debugBarPluginActivation.ajaxurl, eventData, function( response ) {

			if ( 'number' === typeof response && 1 === response ) {
				// Remove table and h3 header if it was the last row.
				if ( 1 === eventTarget.closest( 'tbody' ).prop( 'rows' ).length ) {
					eventTarget.closest( 'table' ).prev( 'h3' ).remove();
					eventTarget.closest( 'table' ).remove();
					jQuery( '#debug-bar-plugin-activation span.count.' + rowType ).text( 0 );
				} else {
					eventTarget.closest( 'tr' ).remove();
					// Lower the counter by one.
					jQuery( '#debug-bar-plugin-activation span.count.' + rowType ).text( function( index, text ) {
						text = parseInt( text, 10 );
						return ( text - 1 );
					} );
				}

				// Lower the number in the menu on the left.
				dbMenuItem.text( function( index, text ) {
					text = parseInt( text, 10 );
					return ( text - 1 );
				} );
			}

			spinner.removeClass( 'is-active' );

		}, 'json' );
	} );


	/**
	 * Allow for deleting all items in one go.
	 */
	jQuery( '#debug-bar-plugin-activation-delete-all' ).on( 'click', function( event ) {
		var eventTarget = jQuery( this ),
			spinner, eventData;

		event.preventDefault();

		spinner = eventTarget.closest( 'h2' ).find( 'span.spinner' );
		spinner.addClass( 'is-active' );

		// Event Data to be passed to ajax backend.
		eventData = {
			dbpa_nonce: debugBarPluginActivation.dbpa_nonce,
			type:       'all',
			action:     'debug-bar-plugin-activation_delete'
		};

		//	Performing ajax request and proccesing response.
		jQuery.post( debugBarPluginActivation.ajaxurl, eventData, function( response ) {

			if ( 'number' === typeof response && 1 === response ) {
				jQuery( '#debug-bar-plugin-activation h3, #debug-bar-plugin-activation table' ).remove();
				jQuery( '#debug-bar-plugin-activation span.count' ).text( '0' ); // Set all counters to 0.
				dbMenuItem.remove();
			}

			spinner.removeClass( 'is-active' );

		}, 'json' );
	} );

} );
