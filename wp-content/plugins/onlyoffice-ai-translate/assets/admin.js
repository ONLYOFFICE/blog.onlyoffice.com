(function ($) {
    'use strict';

    $(document).on('click', '.oait-translate-btn', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $section = $btn.closest('.oait-translate-section');
        var $status = $section.find('.oait-translate-status');
        var $spinner = $section.find('.oait-spinner');
        var postId = $btn.data('post-id');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        $status.hide();

        $.ajax({
            url: oaitData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oait_translate_post',
                nonce: oaitData.nonce,
                post_id: postId
            },
            success: function (response) {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);

                if (response.success) {
                    $status
                        .text('Queued for translation!')
                        .css('color', '#00a32a')
                        .show();
                    $btn.text('Re-translate');
                } else {
                    $status
                        .text('Error: ' + response.data)
                        .css('color', '#d63638')
                        .show();
                }
            },
            error: function () {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);
                $status
                    .text('Request failed. Please try again.')
                    .css('color', '#d63638')
                    .show();
            }
        });
    });
})(jQuery);
