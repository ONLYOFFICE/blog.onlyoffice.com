<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style src="<?php echo YOP_POLL_URL?>/public/assets/css/yop-poll-public.css"></style>
		<script type="text/javascript">
			function YOPPollBasicIsFacebookBrowser() {
				var ua = navigator.userAgent || navigator.vendor || window.opera;
				return ( ua.indexOf( 'FBAN' ) > -1 ) || ( ua.indexOf( 'FBAV' ) > -1 );
			}
			function closeWindow() {
				var userProfile = [],
					puid,
					pollObject,
					postLink = '<?php echo admin_url( 'admin-ajax.php' )?>',
					_token = '<?php echo wp_create_nonce( 'yop-poll-vote-' . $_GET['poll_id'] );?>',
					xhr,
					response,
					redirectLink,
					redirectAfter = 0;
				if ( true === YOPPollBasicIsFacebookBrowser() ) {
					if ( 
						( null !== localStorage.getItem( 'ypVData' ) ) &&
						( '' !== localStorage.getItem( 'ypVData' ) )
						) {
						xhr = new XMLHttpRequest();
						xhr.onreadystatechange = function() {
							if ( 4 === this.readyState ) {
								if ( 200 === this.status ) {
									response = JSON.parse( this.response );
									if ( 'yes' === response.redirect ) {
										if ( '' !== response.redirectTo ) {
											redirectLink = response.redirectTo;
										} else {
											redirectLink = localStorage.getItem( 'ypRLink' );
										}
										if ( '' !== response.redirect_after ) {
											redirectAfter = response.redirect_after
										}
									} else {
										redirectLink = localStorage.getItem( 'ypRLink' );
									}
									/*C
									if ( true === response.success ) {
										localStorage.removeItem( 'ypVData' );
										localStorage.removeItem( 'ypRLink' );
									}*/
									window.setTimeout( function() {
										window.location.href = redirectLink;
									}, redirectAfter * 1000 );
								}
							}
						};
						xhr.open( 'POST', postLink, true );
						xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
						xhr.send( 'action=yop_poll_record_vote&_token=' + _token + '&data='+ localStorage.getItem( 'ypVData' ) );
					}
				} else {
					window.opener.YOPPollBasicUpdateToken( <?php echo $poll_id;?>, '<?php echo wp_create_nonce( 'yop-poll-vote-' . $_GET['poll_id'] );?>');
					userProfile.id = '';
					userProfile.firstName = '';
					userProfile.lastName = '';
					userProfile.email = '';
					puid = '<?php echo esc_html( $_REQUEST['puid'] ); ?>';
					pollObject = window.opener.document.querySelectorAll( "[data-uid='" + puid + "']" );
					var result = window.opener.YOPPollSendBasicVote( pollObject, 'wordpress', userProfile );
					if( 1 === result ) {
						window.close();
					}
				}
			}
		</script>
	</head>
	<body onload="closeWindow()">
		<div class="basic-overlay" style="width: 100%; height: 100%; text-align: center;">
        </div>
    </body>
</html>