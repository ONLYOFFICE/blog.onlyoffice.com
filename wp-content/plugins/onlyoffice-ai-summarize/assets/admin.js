(function ($) {
    'use strict';

    $(function () {
        var $btn      = $('#oais-generate-btn');
        var $textarea = $('#oais_summary_field');
        var $spinner  = $btn.siblings('.oais-spinner');
        var $status   = $('#oais-status');

        if (!$btn.length) {
            return;
        }

        function setStatus(type, message) {
            $status
                .removeClass('oais-status-success oais-status-error')
                .addClass(type === 'error' ? 'oais-status-error' : 'oais-status-success')
                .text(message)
                .show();
        }

        $btn.on('click', function () {
            var postId = $btn.data('post-id');
            if (!postId) {
                return;
            }

            if ($textarea.val().trim() !== '') {
                if (!window.confirm('This will replace the current summary text. Continue?')) {
                    return;
                }
            }

            $btn.prop('disabled', true);
            $spinner.addClass('is-active');
            $status.hide();

            $.ajax({
                url: oaisData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'oais_generate_summary',
                    nonce: oaisData.nonce,
                    post_id: postId
                }
            })
                .done(function (response) {
                    if (response && response.success && response.data && typeof response.data.summary === 'string') {
                        $textarea.val(response.data.summary).trigger('change');
                        setStatus('success', 'Summary generated. Review and click Update to save.');
                    } else {
                        var msg = (response && response.data && response.data.message) || 'Unknown error.';
                        setStatus('error', 'Error: ' + msg);
                    }
                })
                .fail(function (xhr) {
                    var msg = 'Request failed.';
                    try {
                        var data = xhr.responseJSON;
                        if (data && data.data && data.data.message) {
                            msg = data.data.message;
                        }
                    } catch (e) {}
                    setStatus('error', 'Error: ' + msg);
                })
                .always(function () {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                });
        });
    });
})(jQuery);
