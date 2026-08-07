(function ($) {
    'use strict';

    var POLL_INTERVAL = 5000;
    var pollTimer = null;

    function $body() {
        return $('#oait_translation_status .oait-body');
    }

    function esc(text) {
        return $('<span>').text(text == null ? '' : text).html();
    }

    // Mirrors OAIT_Bulk_Actions::format_elapsed().
    function formatElapsed(seconds) {
        if (seconds < 60) {
            return seconds + 's';
        }
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + 'm ' + (s < 10 ? '0' : '') + s + 's';
    }

    function setStatus($box, text, colour) {
        $box.find('.oait-translate-status').text(text).css('color', colour).show();
    }

    // Translate Selected
    $(document).on('click', '.oait-translate-btn', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $box = $body();
        var postId = $btn.data('post-id');

        var selectedLangs = [];
        $box.find('.oait-lang-checkbox:checked').each(function () {
            selectedLangs.push($(this).val());
        });

        if (selectedLangs.length === 0) {
            setStatus($box, 'Please select at least one language.', '#d63638');
            return;
        }

        $btn.prop('disabled', true);
        $box.find('.oait-spinner').addClass('is-active');
        $box.find('.oait-translate-status').hide();

        $.ajax({
            url: oaitData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oait_translate_post',
                nonce: oaitData.nonce,
                post_id: postId,
                languages: selectedLangs
            },
            success: function (response) {
                $box.find('.oait-spinner').removeClass('is-active');

                if (response.success) {
                    setStatus($box, response.data.message || 'Translation in progress...', '#00a32a');
                    refresh(postId, true);
                } else {
                    $btn.prop('disabled', false);
                    setStatus($box, 'Error: ' + response.data, '#d63638');
                }
            },
            error: function () {
                $box.find('.oait-spinner').removeClass('is-active');
                $btn.prop('disabled', false);
                setStatus($box, 'Request failed. Please try again.', '#d63638');
            }
        });
    });

    // Stop queued/running translations for this post.
    $(document).on('click', '.oait-cancel-btn', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $box = $body();
        var postId = $btn.data('post-id');

        $btn.prop('disabled', true);
        $box.find('.oait-spinner').addClass('is-active');

        $.ajax({
            url: oaitData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oait_cancel_translation',
                nonce: oaitData.nonce,
                post_id: postId
            },
            success: function (response) {
                $box.find('.oait-spinner').removeClass('is-active');

                if (response.success) {
                    setStatus($box, response.data.message, '#996800');
                } else {
                    setStatus($box, 'Error: ' + response.data, '#d63638');
                }
                refresh(postId, true);
            },
            error: function () {
                $box.find('.oait-spinner').removeClass('is-active');
                $btn.prop('disabled', false);
                setStatus($box, 'Request failed. Please try again.', '#d63638');
            }
        });
    });

    // Select all checkbox toggle
    $(document).on('change', '#oait_metabox_select_all', function () {
        $('#oait_translation_status .oait-lang-checkbox').prop('checked', this.checked);
    });

    // Update Select all state when individual checkboxes change
    $(document).on('change', '.oait-lang-checkbox', function () {
        var all = $('#oait_translation_status .oait-lang-checkbox');
        var checked = $('#oait_translation_status .oait-lang-checkbox:checked');
        $('#oait_metabox_select_all').prop('checked', all.length === checked.length);
    });

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function startPolling(postId) {
        stopPolling();
        pollTimer = setInterval(function () {
            refresh(postId, false);
        }, POLL_INTERVAL);
    }

    /**
     * Fetch the current status and repaint the metabox.
     *
     * @param {number}  postId
     * @param {boolean} keepMessage Preserve the status line already on screen
     *                              (it describes the action just taken).
     */
    function refresh(postId, keepMessage) {
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

                var $box = $body();
                var previous = keepMessage ? $box.find('.oait-translate-status') : null;
                var text = previous && previous.length ? previous.text() : '';
                var colour = previous && previous.length ? previous.css('color') : '';
                var visible = previous && previous.length && previous.is(':visible');

                renderMetaBox(response.data, postId);

                if (visible && text) {
                    setStatus($body(), text, colour);
                }

                if (response.data.active) {
                    if (!pollTimer) {
                        startPolling(postId);
                    }
                } else {
                    stopPolling();
                }
            }
        });
    }

    /**
     * Repaint the metabox body. Must stay in sync with
     * OAIT_Bulk_Actions::render_body().
     */
    function renderMetaBox(data, postId) {
        var $box = $body();
        if (!$box.length) {
            return;
        }

        var languages = data.languages;
        var hasActions = false;

        $.each(languages, function (code, lang) {
            if (lang.enabled && !lang.postId) {
                hasActions = true;
                return false;
            }
        });

        var html = '';

        if (hasActions) {
            html += '<label style="display:block;margin:4px 0 8px;font-weight:600;">'
                  + '<input type="checkbox" id="oait_metabox_select_all" /> Select all</label>';
        }

        html += '<ul style="margin:0;" class="oait-language-list">';

        $.each(languages, function (code, lang) {
            if (!lang.enabled) {
                return;
            }

            var label = esc(lang.name + ' (' + code + ')');

            if (lang.postId) {
                var link = lang.editLink
                    ? '<a href="' + esc(lang.editLink) + '">' + label + '</a>'
                    : label;
                html += '<li style="padding:2px 0;">'
                      + '<span style="color:#00a32a;">&#10004;</span> ' + link
                      + '</li>';
                return;
            }

            var checkbox = '<label><input type="checkbox" class="oait-lang-checkbox" value="'
                         + esc(code) + '"> ' + label + '</label>';

            if (lang.active) {
                var note = lang.status === 'queued' ? 'queued' : 'translating';
                if (lang.elapsed) {
                    note += ' ' + formatElapsed(lang.elapsed);
                }
                html += '<li style="padding:2px 0;">'
                      + '<span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span> '
                      + checkbox
                      + ' <em style="color:#999;font-size:11px;">(' + esc(note) + ')</em>'
                      + '</li>';
                return;
            }

            if ((lang.status === 'error' || lang.status === 'cancelled') && lang.message) {
                var colour = lang.status === 'cancelled' ? '#996800' : '#d63638';
                html += '<li style="padding:2px 0;">'
                      + checkbox
                      + ' <em style="color:' + colour + ';font-size:11px;">('
                      + esc(lang.message) + ')</em>'
                      + '</li>';
                return;
            }

            html += '<li style="padding:2px 0;">' + checkbox + '</li>';
        });

        html += '</ul>';

        if (hasActions) {
            html += '<div style="margin-top:10px;">'
                  + '<button type="button" class="button button-primary oait-translate-btn" '
                  + 'data-post-id="' + esc(postId) + '">Translate Selected</button> ';

            if (data.active) {
                html += '<button type="button" class="button oait-cancel-btn" '
                      + 'data-post-id="' + esc(postId) + '">Stop</button> ';
            }

            html += '<span class="spinner oait-spinner" style="float:none;margin:0 4px;"></span>'
                  + '</div>';
        }

        if (data.active) {
            html += '<p style="margin:8px 0 0;color:#999;font-size:11px;">'
                  + 'A language stuck for more than ' + Math.round(data.staleTimeout / 60)
                  + ' min is released automatically and can be re-run.</p>';
        }

        html += '<span class="oait-translate-status" style="display:none;margin-top:6px;"></span>';

        $box.html(html);
    }

    // Refresh once on load. The server sweeps stale jobs while building the
    // payload, so opening a post whose translation died is enough to release it
    // — previously the metabox kept a frozen spinner because polling only ever
    // started after clicking Translate.
    $(function () {
        if (typeof oaitData === 'undefined' || !oaitData.postId || !$body().length) {
            return;
        }
        refresh(oaitData.postId, false);
    });
})(jQuery);
