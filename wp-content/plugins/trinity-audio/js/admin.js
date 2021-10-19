const TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY = 'bulk-update';

const REGISTRATION_RESPONSE_CODE = {
    ERROR_NETWORK: 'ERROR_NETWORK',
    ERROR: 'ERROR',
    ALREADY_REGISTERED: 'ALREADY_REGISTERED',
    ALREADY_ASSIGNED_PUBLISHER_TOKEN: 'ALREADY_ASSIGNED_PUBLISHER_TOKEN',
    WRONG_INSTALLKEY: 'WRONG_INSTALLKEY',
    WRONG_PUBLISHER_TOKEN: 'WRONG_PUBLISHER_TOKEN',
    SUCCESS: 'SUCCESS'
};

const $ = jQuery;

(function ($) {
    const id = '#trinity-admin';

    function bulkUpdateWaitingDecision() {
        $('#trinity_audio_activate_for_all_posts').hide();
        $('#trinity-admin .trinity-status-wrapper .progress').show();
        $('#trinity-admin .trinity-status-wrapper .progress .description').text('Checking...');
    }

    function bulkUpdateInProgress() {
        $('#trinity_audio_activate_for_all_posts').hide();
        $('#trinity-admin .trinity-status-wrapper .progress').show();
        $('#trinity-admin .trinity-status-wrapper .progress .description').show();
    }

    function bulkUpdateEnded() {
        $('#trinity_audio_activate_for_all_posts').show();
        $('#trinity-admin .trinity-status-wrapper .progress').hide();
    }

    function checkIfPostsBulkUpdateRequested() {
        bulkUpdateWaitingDecision();

        if (!trinityAudioCheckIfLocalStorageAvailable()) return;

        if (!localStorage.getItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY)) return;

        $.ajax({
            type: 'GET',
            url: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_ADMIN_POST,
            data: {
                action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE
            },
        });

        localStorage.removeItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY);
    }

    function checkProgress() {
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_STATUS
            },
            dataType: 'json',
            success: function (response) {
                const processedPosts = response.processedPosts;
                const totalPosts = response.totalPosts;
                const numOfFailedPosts = response.numOfFailedPosts;

                if (response.inProgress && totalPosts) {
                    bulkUpdateInProgress();

                    const readyPercentage = (processedPosts / totalPosts) * 100;
                    let text;

                    const prefix = `Trinity Audio is currently active on ${processedPosts} posts out of ${totalPosts}. Failed to update ${numOfFailedPosts} posts`;

                    if (readyPercentage < 10) {
                        text = `${prefix}. Keep it going!`;
                    } else if (readyPercentage < 35) {
                        text = `${prefix}. There are still a lot of posts that can be audiofied to allow your users to listen to your content.`;
                    } else if (readyPercentage > 35) {
                        text = `${prefix}. Well done and welcome to the audio revolution!`;
                    }

                    $('#trinity-admin .trinity-status-wrapper .progress .description').text(text);
                    trinityShowStatus(id, 'progress');

                    setTimeout(function () {
                        checkProgress();
                    }, 1000);
                } else {
                    bulkUpdateEnded();
                }
            }
        }).fail(function (response) {
            console.error('TRINITY_WP', response);
            trinityShowStatus(id, 'error');
        });
    }

    /**
     * Disable genders that are not supported for particular languages.
     * Switch back to default selected gender, when it's available.
     */
    function initLanguageSelect() {
        const defaultSelectedGender = $('#trinity_audio_gender_id').val();

        function callback() {
            const lang = this.value;
            const foundLang = TRINITY_WP_ADMIN.LANGUAGES.find(function (value) {
                return value.code === lang;
            });

            if (foundLang) {
                const genders = foundLang.genders;
                $('#trinity_audio_gender_id option').each(function (key, el) {
                    const isEnabled = genders.includes(el.value) || el.value === '';

                    $(el).attr('disabled', !isEnabled);
                    if (!isEnabled) $(el).removeAttr('selected');
                });

                const shouldSelectEl = $('#trinity_audio_gender_id option[value="' + defaultSelectedGender + '"]:not([disabled])');
                if (shouldSelectEl.length) {
                    shouldSelectEl.attr('selected', true);
                } else {
                    $('#trinity_audio_gender_id option:not([disabled]):first').attr('selected', true)
                }
            }
        }

        $('#trinity_audio_source_language').change(callback);
        $('#trinity_audio_source_language').change();
    }

    function initContactUs() {
        const id = '#trinity-admin-contact-us';
        const submitButton = $(`${id} form button`);

        $(`${id} form`).submit(function (e) {
            e.preventDefault();

            const formData = Object.fromEntries(new FormData(e.target).entries());
            formData.action = window.TRINITY_WP_ADMIN.TRINITY_AUDIO_CONTACT_US;

            $(submitButton).attr('disabled', true);
            trinityShowStatus(id, 'progress');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: formData,
                dataType: 'json',
                success: function () {
                    $(submitButton).attr('disabled', false);
                    trinityShowStatus(id, 'success');
                }
            }).fail(function (response) {
                console.error('TRINITY_WP', response);
                $(submitButton).attr('disabled', false);
                trinityShowStatus(id, 'error');
            });
        });
    }

    checkIfPostsBulkUpdateRequested();

    // check status for 7sec at least in the beginning
    setTimeout(() => { // wait for 3sec to make sync req to server, before checking status
        const t = setInterval(() => {
            checkProgress();
        }, 1000);

        setTimeout(() => {
            clearInterval(t);
        }, 7000);
    }, 3000);

    initLanguageSelect();
    initContactUs();
    $("#register-site").submit(trinityAudioOnRegisterFormSubmit);
    $(".use-account-key-button").click(trinityAudioOnPublisherTokenSubmit);

    $(".trinity-custom-select").click(function (e) {
        $('.trinity-custom-select').removeClass('opened');
        if (!e.target.matches('.line')) {
            $(e.target).closest('.trinity-custom-select').addClass('opened');
        }
        e.stopPropagation();
    });
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.trinity-custom-select')) {
            $('.trinity-custom-select').removeClass('opened');
        }
        if (event.target.matches('.trinity-notification .trinity-notification-close')) {
            event.target.parentElement.remove();
        }
    })

})(jQuery);

function trinityAudioCheckIfLocalStorageAvailable() {
    if (!window.localStorage) return console.error('localStorage is not available!');
    return true;
}

function updateCustomSelectValue(name, value, code) {
    const customSelect = document.forms.settings.elements[name].nextElementSibling;
    $(customSelect).find('.value-text').html(value);

    $(customSelect).find('.options').css('visibility', 'hidden');
    setTimeout(() => $(customSelect).find('.options').css('visibility', ''), 100)
    $(customSelect).find(`.line`).show();
    $(customSelect).find(`.line[value=${code}]`).hide();

    $('.trinity-custom-select').removeClass('opened');
    if (code !== undefined) document.forms.settings.elements[name].value = code;
    else document.forms.settings.elements[name].value = value;
}

function trinityAudioOnSettingsFormSubmit(form, changesWereSaved) {
    if (!trinityAudioCheckIfLocalStorageAvailable()) return;

    localStorage.setItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY, '1');

    if (!changesWereSaved) {
        jQuery.ajax({
            type: 'GET',
            url: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_ADMIN_POST,
            data: {
                action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_FIRST_CHANGES_SAVE
            },
        });
    }

}

function showRegistrationErrorMessage(message) {
    jQuery('.registration-error').append('<div class="notice notice-error"><p>' + message + '</p></div>');
}

function trinityAudioOnRegisterFormSubmit(e) {
    e.preventDefault();
    const terms = document.forms['register-site'].trinity_audio_terms_of_service;
    const privacy = document.forms['register-site'].trinity_audio_privacy_statement;

    if (!terms.checked)  $(terms).addClass('trinity-custom-required');
    if (!privacy.checked) $(privacy).addClass('trinity-custom-required');

    if (!terms.checked || !privacy.checked) return;

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REGISTER,
            recover_installkey: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_RECOVER_INSTALLKEY).val(),
            publisher_token: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN).val()
        },
        success: function (response) {
            if (response.code !== REGISTRATION_RESPONSE_CODE.SUCCESS) {
                showRegistrationErrorMessage(response.message);

                if (response.code === REGISTRATION_RESPONSE_CODE.ALREADY_REGISTERED) jQuery('.recover-install-key').show();

                return;
            }
            location.reload();
        }
    });
}

function trinityAudioOnPublisherTokenSubmit(e) {
    e.preventDefault();
    const button = e.target;
    $(button).off('click');
    $(button).addClass('trinity-loader');

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN_URL,
            publisher_token: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN).val(),
        },
        success: (response) => {
            if (response.code === REGISTRATION_RESPONSE_CODE.SUCCESS) {
                showPublisherTokenMessage('Successfully connected to Trinity Account');
                location.reload();
            } else {
                showPublisherTokenMessage(response.message, true);
            }
        },
        complete: () => {
            $(button).removeClass('trinity-loader');
            $(button).on('click', trinityAudioOnPublisherTokenSubmit);
        }
    });
}

function showPublisherTokenMessage(message, isError = false) {
    $cssClassSuffix = isError ? 'error' : 'success';
    jQuery('.publisher-token-notification').append(`<div class="notice notice-${$cssClassSuffix}"><p>${message}</p></div>`);
}
