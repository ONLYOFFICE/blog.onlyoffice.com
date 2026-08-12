document.addEventListener('DOMContentLoaded', function(){
	logs_ip_info();
	edit_poll();
	design();
	polls_list();

	function logs_ip_info() {
		const requestInterval = 1000; // NOTE: provider rate limit - 60 requests per minute.
		var $ipInfoElements = jQuery( '.dem-ip-info[data-log-id]' );
		if( $ipInfoElements.length && window.democracyPollLogs ){
			var requestQueue = [];
			var requestInProgress = false;

			var requestNextIpInfo = function(){
				if( requestInProgress || ! requestQueue.length ) return;

				requestInProgress = true;

				var request = requestQueue.shift();
				var $ipInfo = request.$element;
				var previousHtml = $ipInfo.html();

				$ipInfo
					.removeData( 'ip-info-queued' )
					.addClass( 'is-loading' )
					.attr( 'aria-busy', 'true' )
					.html( '<span class="spinner is-active" aria-hidden="true" style="float:none; margin:0;"></span>' );

				jQuery.ajax( {
					url     : window.democracyPollLogs.ajaxUrl,
					method  : 'POST',
					dataType: 'json',
					data    : {
						action: window.democracyPollLogs.action,
						nonce : window.democracyPollLogs.nonce,
						log_id: $ipInfo.data( 'log-id' ),
						force : request.force ? 1 : 0
					}
				} )
					.done( ( response ) => {
						if( response.success && response.data && typeof response.data.html === 'string' ){
							$ipInfo
								.html( response.data.html )
								.removeClass( 'dem_ip_info_pending_js has-error' );
							return;
						}

						$ipInfo.html( previousHtml ).addClass( 'has-error' );
					} )
					.fail( () => {
						$ipInfo.html( previousHtml ).addClass( 'has-error' );
					} )
					.always( () => {
						$ipInfo.removeClass( 'is-loading' ).removeAttr( 'aria-busy' );
						window.setTimeout( () => {
							requestInProgress = false;
							requestNextIpInfo();
						}, requestInterval );
					} );
			};

			var enqueueIpInfo = function( $ipInfo, force ){
				if( $ipInfo.hasClass( 'is-loading' ) || $ipInfo.data( 'ip-info-queued' ) ) return;

				$ipInfo.data( 'ip-info-queued', true );
				requestQueue.push( {
					$element: $ipInfo,
					force  : Boolean( force )
				} );

				requestNextIpInfo();
			};

			$ipInfoElements.filter( '.dem_ip_info_pending_js' ).each( function(){
				enqueueIpInfo( jQuery( this ), false );
			} );

			jQuery( document ).on( 'click', '.ip_info_up_button_js', function(){
				enqueueIpInfo( jQuery( this ).closest( '.dem-ip-info' ), true );
			} );
		}
	}

	function edit_poll(){
		var $answers_wrap = jQuery( '.dem-edit-poll__answers' );
		if( $answers_wrap.length ){
			var focusFunction = function(){
				// Check whether a new field should be added.
				let $li = jQuery( this ).closest( 'li' )
				let $nextAnsw = $li.next( 'li.answ' )
				let $nextAnswTxt = $nextAnsw.find( '.answ-text' )

				if( $nextAnsw.length ) return this;

				// Add a field.
				jQuery( this ).addAnswField();
			};

			//var blurFunction = function(){};

			// Add an li block for a new answer after the current li.
			jQuery.fn.addAnswField = function(){
				var $li = this.closest( 'li' )
				var $_li = $li.clone().addClass( 'new' )

				$_li.find( 'input' ).remove();

				var $input = jQuery( '<input class="answ-text" type="text" name="dmc_new_answers[]">' );
				$input.on( 'focus', focusFunction );
				// Remove the block when no data was entered.
				$input.on( 'blur', function(){
					if( ! $input.val() ) $_li.remove();
				} );

				$_li.prepend( $input );
				$li.after( $_li );
				return this;
			};

			// New answer field.
			$answers_wrap.find( '.answ-text' ).focus( focusFunction );
			$answers_wrap.find( 'li.answ' ).last().addAnswField();


			// Delete buttons.
			$answers_wrap.find( 'li.answ' ).each( function(){
				jQuery( this ).append( '<span class="dem-del-button">×</span>' );
			} );
			// Delete event.
			$answers_wrap.on( 'click', '.dem-del-button', function(){
				jQuery( this ).parent( 'li' ).remove();

				// Rebuild the order if it is set.
				if( $answers_wrap.find( 'li.answ:first input[name $= "[aorder]"]' ).val() > 0 )
					window.updateAnswersOrder();
			} );

			// datepicker
			jQuery( 'input[name="dmc_end"], input[name="dmc_added"]' ).datepicker( { dateFormat: 'dd-mm-yy' } );

			// Multiple answers and user_voted.
			var $multiple = jQuery( 'input[name="dmc_multiple"]' ),
				$multiNum = $multiple.parent().find( '[type="number"]' ),
				$users_voted = $answers_wrap.find( 'input[name="dmc_users_voted"]' );

			// ReSet value of 'dmc_users_voted' when vote count change
			$answers_wrap.on( 'change.reset_users_voted', 'input[name$="[votes]"]', function(){
				if( ! $multiple.is( ':checked' ) ){
					var sum = 0;
					$answers_wrap.find( 'input[name$="[votes]"]' ).each( function(){
						sum += Number( jQuery( this ).val() );
					} );

					$users_voted.val( sum );
				}
			} )

			$multiple.change( function(){
				$multiple.is( ':checked' ) ? $multiNum.show().focus() : $multiNum.hide();
				$multiple.is( ':checked' ) ? $users_voted.removeProp( 'readonly' ) : $users_voted.prop( 'readonly', 1 );

				$answers_wrap.find( 'input[name$="[votes]"]' ).first().trigger( 'change.reset_users_voted' ); // to reset dmc_users_voted
			} )
			$multiNum.change( function(){
				$multiple.val( $multiNum.val() );
			} )

			// sortable - set answer order
			if( 1 ){
				var $orderEls = $answers_wrap.find( '> .answ:not(.new)' );

				// Make the function globally accessible.
				window.updateAnswersOrder = function(){
					$answers_wrap.find( '> .answ:not(.new)' ).each( function( nn ){
						jQuery( this ).find( 'input[name $= "[aorder]"]' ).val( nn + 1 );
					} );

					$answers_wrap.find( '.reset__aorder' ).slideDown();
					jQuery( '.answers__order' ).slideUp();
				};

				// add order handle
				$orderEls.css( { position: 'relative' } )
				$orderEls.prepend( '<span class="sorthand dashicons dashicons-menu" style="cursor:move;"></span>' );

				$answers_wrap.sortable( {
					axis  : 'y',
					handle: '.sorthand',
					items : '> .answ:not(.new)',
					update: window.updateAnswersOrder
				} );

				$answers_wrap.find( '.reset__aorder' ).on( 'click', function(){
					var $elsVotes = $answers_wrap.find( '> .answ:not(.new)' ),
						$elsVotesNo = $answers_wrap.find( '> .answ.new, > .not__answer' );

					// Reset values.
					$elsVotes.find( 'input[name $= "[aorder]"]' ).val( '0' );

					// Sort elements.
					$elsVotes.sort( function( a, b ){
						return parseInt( jQuery( b ).find( 'input[name $= "[votes]"]' ).val() ) - parseInt( jQuery( a ).find( 'input[name $= "[votes]"]' ).val() );
					} ).appendTo( $answers_wrap );

					// Append unsortable elements at the end.
					$elsVotesNo.appendTo( $answers_wrap );

					// Hide the button.
					jQuery( this ).slideUp();
					jQuery( '.answers__order' ).slideDown();
				} );

			}
		}
	}

	function design(){
		if( jQuery( '.dempage-design' ).length ){
			jQuery( '.dem-screen' ).height( function(){
				return jQuery( this ).outerHeight();
			} );

			jQuery( '[data-dem-act], .democracy a' ).on( 'click', e => e.preventDefault() ); // Disable clicks.

			// Preview.
			var $demLoader = jQuery( document ).find( '.dem_loader_js' ).first(); // loader
			jQuery( '.poll.show-loader .dem-screen' ).append( $demLoader.css( 'display', 'table' ) );

			// wpColorPicker
			jQuery( '.iris_color' ).wpColorPicker();

			var myOptions = {}
			var	$preview = jQuery( '.polls-preview' );
			myOptions.change = function( event, ui ){
				var hexcolor = jQuery( this ).wpColorPicker( 'color' );
				$preview.css( 'background-color', hexcolor );
				//console.log( hexcolor );
			};
			jQuery( '.preview-bg' ).wpColorPicker( myOptions );

			// checkboks for buttons
			var selectable_els = jQuery( '.selectable_els' );
			selectable_els.each( function(){
				var $elswrap = jQuery( this );
				$elswrap.find( 'label' ).on( 'click', function(){
					$elswrap.find( 'input[type="radio"]:not(.demdummy)' ).removeProp( 'checked' );
					jQuery( this ).find( 'input[type="radio"]:not(.demdummy)' ).prop( 'checked', 'checked' );
					//console.log( jQuery(this).find('input[type="radio"]')[0] );
				} );
			} );
		}

	}

	function polls_list(){
		// height toggle
		var $answs = jQuery( '.compact-answ' );
		var	$icon = jQuery( '<span class="dashicons dashicons-exerpt-view"></span>' ).on( 'click', function(){
				jQuery( this ).toggleClass( 'active' );
				$answs.trigger( 'click' );
			} );
		var $table = jQuery( '.tablenav-pages' );

		$answs.css( { cursor: 'pointer' } )
		$answs.on( 'click', function() {
			const $el = jQuery( this )
			var dataHeight = $el.data( 'height' ) || 'auto';
			$el.data( 'height', $el.height() ).height( dataHeight );
		} );

		// Ensure this is the expected table.
		if( $table.closest( '.wrap' ).find( 'table .column-id' ).length ){
			$table.prepend( $icon );
		}
	}

} );

