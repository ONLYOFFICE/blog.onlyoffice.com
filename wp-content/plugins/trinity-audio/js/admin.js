const TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY = 'bulk-update';
// check status for 10sec at least in the beginning, that server have a time to set heartbeat
const TRINITY_BULK_POLLING_TIMEOUT = 10000;

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
const trinityPageIds = '#trinity-admin, #trinity-admin-info, #trinity-admin-logs, #trinity-admin-contact-us';

function dashboardComponentLoaded() {
  trinitySendMetric('wordpress.component.load.success');

  // TODO: check if TRINITY_UNIT_CONFIGURATION.getFormData() return all fields
}
function dashboardComponentFailed() {
  trinitySendMetric('wordpress.component.load.failed');
}

(function ($) {
    const id = '#trinity-admin';
    let isBulkTriggered = false;

    if (!jQuery(trinityPageIds)[0]) return;

    function checkIsBulkUpdateInProgress() {
      return window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_PROGRESS?.inProgress;
    }

    function checkIfPostsBulkUpdateRequested() {

        if (!trinityAudioCheckIfLocalStorageAvailable()) return;

        if (!localStorage.getItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY)) return;

        $.ajax({
            type: 'GET',
            url: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_ADMIN_POST,
            data: {
                action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE
            },
        });

        trinityUpdateBulkProgress({
          totalPosts: 0,
          processedPosts: 0,
          statusName: 'progress'
        });
        trinityDisableFieldsWhichProduceBulkUpdate();

        localStorage.removeItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY);
    }

    function checkProgress(allowRerunCheckProgrees) {
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_STATUS
            },
            dataType: 'json',
            success: function (bulkUpdateResponse) {
                const processedPosts = bulkUpdateResponse.processedPosts;
                const totalPosts = bulkUpdateResponse.totalPosts;
                const numOfFailedPosts = bulkUpdateResponse.numOfFailedPosts;

                if (bulkUpdateResponse.inProgress && totalPosts) {
                    isBulkTriggered = true;

                    trinityUpdateBulkProgress({
                      totalPosts,
                      processedPosts,
                      numOfFailedPosts,
                      statusName: 'progress'
                    });
                    trinityDisableFieldsWhichProduceBulkUpdate();

                    if (allowRerunCheckProgrees) setTimeout(checkProgress, 1000, allowRerunCheckProgrees);
                }
                // Hide progress bar only after inProgress was true
                else if (bulkUpdateResponse.inProgress === false && isBulkTriggered) {
                  trinityHideBulkProgress();
                  trinityEnableFieldsWhichProduceBulkUpdate();
                }
            }
        }).fail(function (response) {
            console.error('TRINITY_WP', response);
            trinityUpdateBulkProgress({statusName: 'error'});
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

    if (checkIsBulkUpdateInProgress()) {
      const {
        totalPosts,
        processedPosts,
        numOfFailedPosts
      } = window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_PROGRESS;

      trinityUpdateBulkProgress({
        totalPosts,
        processedPosts,
        numOfFailedPosts,
        statusName: 'progress'
      });
      trinityDisableFieldsWhichProduceBulkUpdate();
    }

    checkIfPostsBulkUpdateRequested();

    const t = setInterval(checkProgress, 1000);

    // need to give a time for the backend to set `bulkInProgress: true`
    setTimeout(() => {
      clearInterval(t);
      checkProgress(true);
    }, TRINITY_BULK_POLLING_TIMEOUT);

    initLanguageSelect();
    initContactUs();
    $("#register-site").submit(trinityAudioOnRegisterFormSubmit);
    $(".use-account-key-button").click(trinityAudioOnPublisherTokenSubmit);
    $(".trinity-show-recovery-token-button a").click(showRecoveryToken);
    $(".custom-input-disabled .edit-icon span").click(enableInput);

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
    });

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

// update value for hidden input for voiceId
// to be called when language changes
function updateVoiceId(voices) {
  const GENDERS = {
    'm': 'Male',
    'f': 'Female'
  };
  const gender = document.forms.settings.elements['trinity_audio_gender_id'].value;

  if (voices[gender]) {
    document.forms.settings.elements['trinity_audio_voice_id'].value = voices[gender];
  } else {
    const availableGender = Object.keys(voices)[0];

    updateCustomSelectValue('trinity_audio_gender_id', GENDERS[availableGender], availableGender);

    document.forms.settings.elements['trinity_audio_voice_id'].value = voices[availableGender];
  }
}

function trinityAudioOnSettingsFormSubmit(form, isInitialSave) {
    trinitySendMetric('wordpress.settings.submit');

    try {
      const {
        voice,
        voiceStyle,
        engine,
        theme,
        language,
        speed,
        fab,
        gender
      } = TRINITY_UNIT_CONFIGURATION.getFormData();
      const saveButton = $('.trinity-page .save-button');

      saveButton.addClass('disabled');

      $.ajax({
        type: 'GET',
        url: ajaxurl,
        data: {
          action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_UPDATE_UNIT_CONFIG,
          speed,
          gender,
          language,
          voiceStyle,
          engine,
          themeId: theme,
          voice,
          poweredBy: Number(form.elements.trinity_audio_poweredby.checked),
          fab: Number(fab)
        },
        complete() {
          form.submit();
        }
      });
    } catch(e) {
      trinitySendMetric('wordpress.settings.error');
    }

    if (!trinityAudioCheckIfLocalStorageAvailable()) return;

    const shouldBulkUpdate = isInitialSave
      || isFormValueChanged('trinity_audio_skip_tags', form['trinity_audio_skip_tags'].value)
      || isFormValueChanged('trinity_audio_allow_shortcodes', form['trinity_audio_allow_shortcodes'].value);

    if (shouldBulkUpdate) localStorage.setItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY, '1');
}

function isFormValueChanged(field, formValue) {
  return window.TRINITY_WP_ADMIN[field] !== formValue;
}

function showRegistrationErrorMessage(message) {
    jQuery('.registration-error').append('<div class="notice notice-error"><p>' + message + '</p></div>');
}

function trinityAudioOnRegisterFormSubmit(e) {
    e.preventDefault();
    const terms = document.forms['register-site'].trinity_audio_terms_of_service;

    if (!terms.checked) return $(terms).addClass('trinity-custom-required');

    trinitySendMetric('wordpress.signup.clicked');

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REGISTER,
            recover_installkey: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_RECOVER_INSTALLKEY).val(),
            publisher_token: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN).val(),
            email_subscription: Number(jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_EMAIL_SUBSCRIPTION)[0].checked)
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
            if (response.code === REGISTRATION_RESPONSE_CODE.SUCCESS || response.code === REGISTRATION_RESPONSE_CODE.ALREADY_ASSIGNED_PUBLISHER_TOKEN) {
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
    jQuery('.publisher-token-notification').html(`<div class="notice notice-${$cssClassSuffix}"><p>${message}</p></div>`);
}

function showRecoveryToken(e) {
    e.preventDefault();
    e.target.parentElement.classList.toggle('hidden');
    e.target.parentElement.nextElementSibling.classList.toggle('hidden');
}

function enableInput(e) {
    const iconWrapper = e.target.parentElement;
    const input = iconWrapper.nextElementSibling;
    const submitSection = input.nextElementSibling.nextElementSibling;
    const verifiedMessage = document.querySelector('.verified-message');

    iconWrapper.classList.toggle('trinity-hide')
    input.toggleAttribute('disabled');
    input.focus();
    submitSection.classList.toggle('trinity-hide');
    verifiedMessage.remove();
}

function trinitySendMetric(metric, additionalData) {
  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      metric,
      additionalData,
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_SEND_METRIC
    }
  });
}

function trinityRemovePostBanner() {
  $('.trinity-meta-upgrade-banner').remove();

  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REMOVE_POST_BANNER
    }
  });
}

function grabPackageInfo(retryNumber) {
  $.ajax({
    type: 'GET',
    url: ajaxurl,
    data: {
      retryNumber,
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PACKAGE_INFO
    }
  }).then((result) => {
    const el = document.querySelector('.trinity-section-body.plan-section');

    if (el && result) {
      try {
        result = JSON.parse(result);

        if (['success', 'fail'].includes(result.status)) el.innerHTML = result.html;
      } catch (error) {
        console.error('TRINITY_WP', error);
      }
    }
  });
}

function checkFieldDirty(input) {
  if (isFormValueChanged(input.name, input.value)) return input.classList.add('dirty');

  input.classList.remove('dirty');
}
