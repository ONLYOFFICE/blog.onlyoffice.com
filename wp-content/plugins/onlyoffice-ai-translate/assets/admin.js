(function ($) {
    'use strict';

    var pollTimer = null;

    // Translate Selected button click
    $(document).on('click', '.oait-translate-btn', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $metabox = $('#oait_translation_status .inside');
        var $status = $metabox.find('.oait-translate-status');
        var $spinner = $metabox.find('.oait-spinner');
        var postId = $btn.data('post-id');

        // Collect checked languages
        var selectedLangs = [];
        $metabox.find('.oait-lang-checkbox:checked').each(function () {
            selectedLangs.push($(this).val());
        });

        if (selectedLangs.length === 0) {
            $status.text('Please select at least one language.').css('color', '#d63638').show();
            return;
        }

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        $status.hide();

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
                $spinner.removeClass('is-active');

                if (response.success) {
                    $status
                        .text('Translation in progress...')
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

    function startPolling(postId) {
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
                    updateMetaBox(data.languages, data.results, postId);

                    if (data.complete) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                    }
                }
            });
        }, 5000);
    }

    function updateMetaBox(languages, results, postId) {
        var $box = $('#oait_translation_status .inside');
        if (!$box.length) {
            return;
        }

        // Count how many selectable languages exist (not translated, not in progress)
        var hasCheckboxes = false;
        $.each(languages, function (code, lang) {
            if (lang.enabled && !lang.postId && !lang.inProgress) {
                hasCheckboxes = true;
                return false;
            }
        });

        var html = '';

        // Select all checkbox (only show if there are checkboxes)
        if (hasCheckboxes) {
            html += '<label style="display:block;margin:4px 0 8px;font-weight:600;">'
                  + '<input type="checkbox" id="oait_metabox_select_all" /> Select all</label>';
        }

        html += '<ul style="margin:0;" class="oait-language-list">';

        $.each(languages, function (code, lang) {
            if (!lang.enabled) {
                return; // skip disabled
            }

            var label = $('<span>').text(lang.name + ' (' + code + ')').html();

            if (lang.postId) {
                // Already translated: checkmark + edit link
                var link = lang.editLink
                    ? '<a href="' + lang.editLink + '">' + label + '</a>'
                    : label;
                html += '<li style="padding:2px 0;">'
                      + '<span style="color:#00a32a;">&#10004;</span> ' + link
                      + '</li>';
            } else if (lang.inProgress) {
                // In progress: spinner
                html += '<li style="padding:2px 0;">'
                      + '<span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span> '
                      + label + ' <em style="color:#999;">translating...</em>'
                      + '</li>';
            } else if (lang.error) {
                // Error (timeout, API failure, etc): red error message + checkbox to retry
                var errorText = lang.error.replace(/^error:\s*/i, '');
                html += '<li style="padding:2px 0;">'
                      + '<label><input type="checkbox" class="oait-lang-checkbox" value="' + code + '"> '
                      + label + '</label>'
                      + ' <em style="color:#d63638;font-size:11px;">(' + $('<span>').text(errorText).html() + ')</em>'
                      + '</li>';
            } else {
                // Not translated: checkbox
                html += '<li style="padding:2px 0;">'
                      + '<label><input type="checkbox" class="oait-lang-checkbox" value="' + code + '"> '
                      + label + '</label>'
                      + '</li>';
            }
        });
        html += '</ul>';

        // Button (only show if there are checkboxes)
        if (hasCheckboxes) {
            html += '<div style="margin-top:10px;">'
                  + '<button type="button" class="button button-primary oait-translate-btn" '
                  + 'data-post-id="' + postId + '">Translate Selected</button> '
                  + '<span class="spinner oait-spinner" style="float:none;margin:0 4px;"></span>'
                  + '</div>';
        }
        html += '<span class="oait-translate-status" style="display:none;margin-top:6px;"></span>';

        $box.html(html);
    }
})(jQuery);
