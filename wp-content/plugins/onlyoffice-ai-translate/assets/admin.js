(function ($) {
    'use strict';

    var pollTimer = null;

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

                if (response.success) {
                    $status
                        .text('Translation in progress...')
                        .css('color', '#00a32a')
                        .show();
                    $btn.text('Re-translate');
                    startPolling(postId, $btn, $status);
                } else {
                    $btn.prop('disabled', false);
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

    function startPolling(postId, $btn, $status) {
        if (pollTimer) {
            clearInterval(pollTimer);
        }

        pollTimer = setInterval(function () {
            $.ajax({
                url: oaitData.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'oait_translation_status',
                    nonce: oaitData.nonce,
                    post_id: postId
                },
                success: function (response) {
                    if (!response.success) {
                        return;
                    }

                    var data = response.data;
                    updateMetaBox(data.languages, data.results);

                    if (data.complete) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                        $btn.prop('disabled', false);
                        $status
                            .text('Translation complete!')
                            .css('color', '#00a32a')
                            .show();
                    }
                }
            });
        }, 5000);
    }

    function updateMetaBox(languages, results) {
        var $box = $('#oait_translation_status .inside');
        if (!$box.length) {
            return;
        }

        var html = '<ul style="margin:0;">';
        $.each(languages, function (code, lang) {
            var icon = lang.postId ? '&#10004;' : '&#10060;';
            var color = lang.postId ? '#00a32a' : '#999';
            var style = lang.enabled ? '' : 'opacity:0.5;';
            var label = $('<span>').text(lang.name + ' (' + code + ')').html();

            if (lang.postId && lang.editLink) {
                label = '<a href="' + lang.editLink + '">' + label + '</a>';
            }

            html += '<li style="padding:2px 0;' + style + '"><span style="color:' + color + ';">' + icon + '</span> ' + label + '</li>';
        });
        html += '</ul>';

        if (results) {
            var errors = {};
            var hasErrors = false;
            $.each(results, function (lang, msg) {
                if (typeof msg === 'string' && msg.indexOf('error') === 0) {
                    errors[lang] = msg;
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                html += '<hr/><p style="color:#d63638;"><strong>Errors:</strong></p><ul style="margin:0;">';
                $.each(errors, function (lang, msg) {
                    html += '<li><code>' + $('<span>').text(lang).html() + '</code>: ' + $('<span>').text(msg).html() + '</li>';
                });
                html += '</ul>';
            }
        }

        $box.html(html);
    }
})(jQuery);
