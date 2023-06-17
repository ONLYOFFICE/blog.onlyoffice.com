var as3cfDeactivatePluginModal = (function( $, as3cfModal ) {

	var modal = {
		selector: '.as3cf-deactivate-plugin-container',
		event: {}
	};

	var wpApiSettings = window.wpApiSettings;

	/**
	 * Open modal
	 *
	 * @param {object} event
	 */
	modal.open = function( event ) {
		modal.event = event;
		modal.event.preventDefault();

		as3cfModal.open( modal.selector, null, 'deactivate-plugin' );
	};

	/**
	 * Close modal
	 */
	modal.close = function( download ) {
		as3cfModal.setLoadingState( false );
		as3cfModal.close();

		if ( 1 === parseInt( download ) ) {
			// Start tool and let it redirect to Tools page.
			modal.startTool();
		} else {
			// Just let page do its thing.
			window.location = modal.event.target;
		}
	};

	modal.startTool = function() {
		$.ajax( {
			url: wpApiSettings.root + 'wp-offload-media/v1/tool/',
			method: 'POST',
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'Accept', 'application/json' );
				xhr.setRequestHeader( 'Content-Type', 'application/json' );
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			data: JSON.stringify( {
				'id': 'downloader',
				'action': 'start'
			} )
		} ).done( function( response ) {
			window.location = as3cfpro_downloader.plugin_url;
		} );
	};

	// Setup click handlers
	$( document ).ready( function() {

		$( 'body' ).on( 'click', '.deactivate-plugin [data-download-tool]', function( e ) {
			var value = $( this ).data( 'download-tool' );

			$( '[data-download-tool]' ).prop( 'disabled', true ).siblings( '.spinner' ).css( 'visibility', 'visible' ).show();

			as3cfModal.setLoadingState( true );

			modal.close( value );
		} );

		$( 'body' ).on( 'click', '#' + as3cfpro_downloader.plugin_slug + ' .deactivate a, [data-slug="' + as3cfpro_downloader.plugin_slug + '"]  .deactivate a', function( event ) {
			as3cfDeactivatePluginModal.open( event );
		} );

	} );

	return modal;

})( jQuery, as3cfModal );
