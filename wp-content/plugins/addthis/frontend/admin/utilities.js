(function ($) {

	$(document).ready(function () {
		$('.is-dismissible').on('click', '.notice-dismiss', function (e) {
			var parent = $(this).parent(),
				key = parent.data('atnoticekey'),
				nonce = null;

			var nonceField = parent.find('input[name=_wpnonce]');
			if (nonceField.length) {
				nonce = nonceField.first().val();
			}

			$.post(ajaxurl, {
				action: 'addthis_admin_notice_dismiss',
				url: ajaxurl,
				noticekey: key,
				nonce: nonce
			});
		});
	});

})(jQuery);