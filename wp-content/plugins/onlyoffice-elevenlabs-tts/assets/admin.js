(function ($) {
    'use strict';

    var pollTimer = null;

    // Generate Audio button click
    $(document).on('click', '.oetl-generate-btn', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $metabox = $('#oetl_audio_status .oetl-dynamic-content');
        var $status = $metabox.find('.oetl-status-msg');
        var $spinner = $metabox.find('.oetl-spinner');
        var postId = $btn.data('post-id');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        $status.hide();

        $.ajax({
            url: oetlData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oetl_generate_audio',
                nonce: oetlData.nonce,
                post_id: postId
            },
            success: function (response) {
                $spinner.removeClass('is-active');

                if (response.success) {
                    $status
                        .text('Audio generation in progress...')
                        .css('color', '#00a32a')
                        .show();
                    startPolling(postId);
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

    function startPolling(postId) {
        if (pollTimer) {
            clearInterval(pollTimer);
        }

        pollTimer = setInterval(function () {
            $.ajax({
                url: oetlData.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'oetl_audio_status',
                    nonce: oetlData.nonce,
                    post_id: postId
                },
                success: function (response) {
                    if (!response.success) {
                        return;
                    }

                    var data = response.data;

                    if (data.hasAudio) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                        updateMetaBoxWithAudio(data);
                    } else if (data.error) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                        updateMetaBoxWithError(data.error, postId);
                    }
                    // If still in progress, keep polling
                }
            });
        }, 5000);
    }

    function updateMetaBoxWithAudio(data) {
        var $box = $('#oetl_audio_status .oetl-dynamic-content');
        if (!$box.length) {
            return;
        }

        var html = '<div class="oetl-preview">'
            + '<audio controls preload="metadata" style="width:100%;margin:6px 0;">'
            + '<source src="' + data.audioUrl + '" type="audio/mpeg">'
            + '</audio>';

        if (data.generatedAt) {
            html += '<p class="description" style="margin:4px 0;">Generated: ' + data.generatedAt + '</p>';
        }

        html += '</div>'
            + '<div style="margin-top:8px;">'
            + '<button type="button" class="button oetl-generate-btn" '
            + 'data-post-id="' + oetlData.postId + '">Regenerate Audio</button> '
            + '<span class="spinner oetl-spinner" style="float:none;margin:0 4px;"></span>'
            + '</div>'
            + '<span class="oetl-status-msg" style="display:none;margin-top:6px;font-size:12px;"></span>';

        $box.html(html);
    }

    function updateMetaBoxWithError(errorMsg, postId) {
        var $box = $('#oetl_audio_status .oetl-dynamic-content');
        if (!$box.length) {
            return;
        }

        var html = '<p style="color:#d63638;font-size:12px;margin:4px 0;">'
            + '<strong>Error:</strong> ' + $('<span>').text(errorMsg).html()
            + '</p>'
            + '<div style="margin-top:4px;">'
            + '<button type="button" class="button button-primary oetl-generate-btn" '
            + 'data-post-id="' + postId + '">Retry</button> '
            + '<span class="spinner oetl-spinner" style="float:none;margin:0 4px;"></span>'
            + '</div>'
            + '<span class="oetl-status-msg" style="display:none;margin-top:6px;font-size:12px;"></span>';

        $box.html(html);
    }

    // Auto-start polling if generation is in progress on page load
    $(document).ready(function () {
        var $metabox = $('#oetl_audio_status .oetl-dynamic-content');
        if ($metabox.find('.oetl-status .spinner.is-active').length) {
            startPolling(oetlData.postId);
        }
    });
})(jQuery);
