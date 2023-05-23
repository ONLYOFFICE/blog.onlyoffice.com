// Actions for Jetpack Protect notice

jQuery( document ).ready(
	function( $ ) {

		let protect_notice = $( '.protect-notice' );
		let dismissible_protect_notice = $( '#dismissible-protect-notice' );

		// Permanently dimisses the Jetpack Protect admin notice
		dismissible_protect_notice.on( 'click', (event) => {
			event.preventDefault();

			$.ajax(
				{
					url: wpscan.ajaxurl,
					method: 'POST',
					data: {
						action: wpscan.action_dismiss_protect_notice,
						_ajax_nonce: wpscan.ajax_nonce
					},
					success: function() {
						protect_notice.addClass( 'hidden' );
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						alert( errorThrown );
						location.reload();
					}
				}
			);

		} );
	}
);
