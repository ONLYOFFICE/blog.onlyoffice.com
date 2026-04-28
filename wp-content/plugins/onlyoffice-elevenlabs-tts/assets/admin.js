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
        $status
            .text('Generating audio, please wait...')
            .css('color', '#00a32a')
            .show();

        // Polling is the source of truth for completion. The XHR may outlive
        // browser/proxy idle limits on long posts (20+ min audio); even if it
        // times out, server-side PHP keeps running (set_time_limit(0) +
        // ignore_user_abort(true)) and the polling picks up the result from
        // post meta.
        startPolling(postId);

        $.ajax({
            url: oetlData.ajaxUrl,
            type: 'POST',
            timeout: 0, // no client timeout — let polling decide when we're done
            data: {
                action: 'oetl_generate_audio',
                nonce: oetlData.nonce,
                post_id: postId
            },
            success: function (response) {
                if (response.success) {
                    stopPolling();
                    $spinner.removeClass('is-active');
                    updateMetaBoxWithAudio(response.data);
                } else {
                    // Server reported a real error (validation, API key, etc.).
                    // Polling will see _oetl_audio_error on its next tick;
                    // surface it now instead of waiting up to 5s.
                    stopPolling();
                    $spinner.removeClass('is-active');
                    updateMetaBoxWithError(response.data, postId);
                }
            },
            error: function () {
                // Network glitch / proxy timeout / browser abort. Do NOT show
                // an error — the server may still be working. Let the polling
                // continue and report the actual outcome.
            }
        });
    });

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function renderProgressLabel(data) {
        if (data.progressTotal && data.progressCurrent) {
            return 'Generating audio... (' + data.progressCurrent + '/' + data.progressTotal + ')';
        }
        return 'Generating audio...';
    }

    function startPolling(postId) {
        stopPolling();

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
                        stopPolling();
                        updateMetaBoxWithAudio(data);
                    } else if (data.error) {
                        stopPolling();
                        updateMetaBoxWithError(data.error, postId);
                    } else {
                        // Still in progress — refresh the chunk counter.
                        var $box = $('#oetl_audio_status .oetl-dynamic-content');
                        var $statusMsg = $box.find('.oetl-status-msg');
                        if ($statusMsg.length) {
                            $statusMsg
                                .text(renderProgressLabel(data))
                                .css('color', '#00a32a')
                                .show();
                        }
                        var $serverSideStatus = $box.find('.oetl-status em');
                        if ($serverSideStatus.length) {
                            $serverSideStatus.text(renderProgressLabel(data));
                        }
                    }
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
