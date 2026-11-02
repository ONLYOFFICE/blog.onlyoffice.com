const TRINITY_REGISTRATION_RESPONSE_CODE = {
  ERROR_NETWORK: 'ERROR_NETWORK',
  ERROR: 'ERROR',
  ALREADY_REGISTERED: 'ALREADY_REGISTERED',
  ALREADY_ASSIGNED_PUBLISHER_TOKEN: 'ALREADY_ASSIGNED_PUBLISHER_TOKEN',
  WRONG_INSTALLKEY: 'WRONG_INSTALLKEY',
  WRONG_PUBLISHER_TOKEN: 'WRONG_PUBLISHER_TOKEN',
  SUCCESS: 'SUCCESS'
};

const TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY = 'bulk-update';
const TRINITY_LOCAL_STORAGE_IS_INITIAL_SAVE = 'is-initial-save';

const $ = jQuery;

(function ($) {
  // check status for 10sec at least in the beginning, that server have a time to set heartbeat
  const TRINITY_BULK_POLLING_TIMEOUT = 10000;

  const trinityPageIds = '#trinity-admin, #trinity-admin-info, #trinity-admin-logs, #trinity-admin-contact-us, #trinity-admin-contact-us, #trinity-admin-post-management';

  let isBulkTriggered = false;

  if (!jQuery(trinityPageIds)[0]) return;

  function trinityHideBulkProgress() {
    jQuery('.trinity-bulk-update-wrapper').hide();
  }

  function trinityEnableFieldsWhichProduceBulkUpdate() {
    const fields = jQuery('#trinity_audio_skip_tags, #trinity_audio_allow_shortcodes');

    fields.removeClass('disabled dirty bulk-notify');
    fields.removeAttr('readonly');
  }

  function trinityDisableFieldsWhichProduceBulkUpdate() {
    const fields = jQuery('#trinity_audio_skip_tags, #trinity_audio_allow_shortcodes');

    fields.addClass('disabled bulk-notify');
    fields.attr('readonly', 'readonly');
  }

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
        action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE,
        [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.audio_bulk_update
      }
    });

    trinityUpdateBulkProgress({
      totalPosts: 0,
      processedPosts: 0
    });
    trinityDisableFieldsWhichProduceBulkUpdate();

    localStorage.removeItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY);
  }

  let failedCounter = 0;

  function checkProgress(allowRerunCheckProgress) {
    // reduce unneeded polling
    if (!trinityIsAllowedForPageName('trinity_audio') && !trinityIsAllowedForPageName('trinity_audio_post_management')) return;

    $.ajax({
      type: 'GET',
      url: ajaxurl,
      data: {
        action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_STATUS,
        [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.audio_bulk_update_status
      },
      dataType: 'json',
      success: function (bulkUpdateResponse) {
        const processedPosts = bulkUpdateResponse.processedPosts;
        const totalPosts = bulkUpdateResponse.totalPosts;
        const numOfFailedPosts = bulkUpdateResponse.numOfFailedPosts;
        const isInProgress = bulkUpdateResponse.inProgress;

        if (isInProgress) {
          isBulkTriggered = true;

          trinityUpdateBulkProgress({
            totalPosts,
            processedPosts,
            numOfFailedPosts,
            isInProgress
          });
          trinityDisableFieldsWhichProduceBulkUpdate();

          if (allowRerunCheckProgress) setTimeout(checkProgress, 1000, allowRerunCheckProgress);
        }
        // Hide progress bar only after inProgress was true
        else if (bulkUpdateResponse.inProgress === false && isBulkTriggered) {
          trinityHideBulkProgress();
          trinityEnableFieldsWhichProduceBulkUpdate();
        }
      }
    }).fail(function (response) {
      console.error('TRINITY_WP', response);

      // don't stop and show error right away, give some attempts to get results from API
      if (failedCounter++ < 5 && allowRerunCheckProgress) setTimeout(checkProgress, 1000, allowRerunCheckProgress);
      else trinityUpdateBulkProgress({statusName: 'error'});
    });
  }

  function initContactUs() {
    const id = '#trinity-admin-contact-us';
    const submitButton = $(`${id} form button`);

    $(`${id} form`).submit(function (e) {
      e.preventDefault();

      const formData = Object.fromEntries(new FormData(e.target).entries());
      formData.action = window.TRINITY_WP_ADMIN.TRINITY_AUDIO_CONTACT_US;
      formData[window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME] = window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.contact_us;

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
    trinityUpdateBulkProgress(window.TRINITY_WP_ADMIN.TRINITY_AUDIO_BULK_UPDATE_PROGRESS);
    trinityDisableFieldsWhichProduceBulkUpdate();
  }

  checkIfPostsBulkUpdateRequested();

  const t = setInterval(checkProgress, 2000);

  // need to give a time for the backend to set `bulkInProgress: true`
  setTimeout(() => {
    clearInterval(t);
    checkProgress(true);
  }, TRINITY_BULK_POLLING_TIMEOUT);

  initContactUs();
  $("#register-site").submit(trinityAudioOnRegisterFormSubmit);
  $("form[name=trinity_audio_post_management]").submit(trinityAudioOnPostManagementSubmit);
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
  window.addEventListener('click', function (event) {
    if (!event.target.matches('.trinity-custom-select')) {
      $('.trinity-custom-select').removeClass('opened');
    }
    if (event.target.matches('.trinity-notification .trinity-notification-close')) {
      event.target.parentElement.remove();
    }
  });

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
})(jQuery);

function trinityDashboardComponentLoaded() {
  trinitySendMetric('wordpress.component.load.success');

  // TODO: check if TRINITY_UNIT_CONFIGURATION.getFormData() return all fields
}

function trinityDashboardComponentFailed() {
  trinitySendMetric('wordpress.component.load.failed');
}

function trinityIsAllowedForPageName(name) {
  const searchParams = new URLSearchParams(location.search);
  const pageName = searchParams.get('page');

  return pageName === name;
}

function trinityAudioCheckIfLocalStorageAvailable() {
  if (!window.localStorage) return console.error('localStorage is not available!');
  return true;
}

function trinityUpdateCustomSelectValue(name, value, code) {
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

async function isFormValid() {
  return (await window.TRINITY_UNIT_CONFIGURATION.validate()).isValid;
}

// check if we need to disable/enable save button
setInterval(async () => {
  if (!window.TRINITY_UNIT_CONFIGURATION?.validate) return;

  const isValid = await isFormValid();
  if ($('.trinity-page .save-button').hasClass('submitted')) return; // don't remove disable class when form is submitted
  $('.trinity-page .save-button').toggleClass('disabled', !isValid);
}, 1000);

async function trinityAudioOnSettingsFormSubmit(form, isInitialSave) {
  const isValid = await isFormValid();
  if (!isValid) { // should not go here, since button should be disabled when a form is not valid...
    console.error('Can not submit a form, since it is not valid');
    return;
  }

  trinitySendMetric('wordpress.settings.submit');

  try {
    localStorage.setItem(TRINITY_LOCAL_STORAGE_IS_INITIAL_SAVE, isInitialSave);

    const {
      voice,
      voiceStyle,
      theme,
      language,
      speed,
      fab,
      showSettings,
      shareEnabled,
      audioVariantId
    } = TRINITY_UNIT_CONFIGURATION.getFormData();
    const saveButton = $('.trinity-page .save-button');

    saveButton.addClass('disabled submitted');

    $.ajax({
      type: 'GET',
      url: ajaxurl,
      data: {
        action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_UPDATE_UNIT_CONFIG,
        speed,
        language,
        voiceStyle,
        themeId: theme,
        voice,
        poweredBy: Number(form.elements.trinity_audio_poweredby.checked),
        fab,
        showSettings: Number(showSettings),
        shareEnabled: Number(shareEnabled),
        audioVariantId: audioVariantId ? Number(audioVariantId) : '',
        [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.update_unit_config
      },
      complete() {
        form.submit();
      }
    });
  } catch (e) {
    trinitySendMetric('wordpress.settings.error');
  }

  if (!trinityAudioCheckIfLocalStorageAvailable()) return;

  const shouldBulkUpdate = isInitialSave
    || trinityIsFormValueChanged('trinity_audio_skip_tags', form['trinity_audio_skip_tags'].value)
    || trinityIsFormValueChanged('trinity_audio_allow_shortcodes', form['trinity_audio_allow_shortcodes'].value);

  if (shouldBulkUpdate) localStorage.setItem(TRINITY_LOCAL_STORAGE_POSTS_BULK_UPDATE_KEY, '1');
}

function trinityIsFormValueChanged(field, formValue) {
  return window.TRINITY_WP_ADMIN[field] !== formValue;
}

function trinityShowRegistrationErrorMessage(message) {
  jQuery('.registration-error').append('<div class="notice notice-error"><p>' + message + '</p></div>');
}

function trinityAudioOnRegisterFormSubmit(e) {
  e.preventDefault();
  const terms = document.forms['register-site'].trinity_audio_terms_of_service;

  if (!terms.checked) return $(terms).addClass('trinity-custom-required');

  const registerButtonEl = $('.button-primary');
  registerButtonEl.addClass('disabled');

  trinitySendMetric('wordpress.signup.clicked');

  jQuery.ajax({
    type: 'POST',
    url: ajaxurl,
    dataType: 'json',
    data: {
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REGISTER,
      recover_installkey: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_RECOVER_INSTALLKEY).val(),
      publisher_token: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN).val(),
      email_subscription: Number(jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_EMAIL_SUBSCRIPTION)[0].checked),
      [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.register
    },
    success: function (response) {
      if (response.code !== TRINITY_REGISTRATION_RESPONSE_CODE.SUCCESS) {
        trinityShowRegistrationErrorMessage(response.message);

        if (response.code === TRINITY_REGISTRATION_RESPONSE_CODE.ALREADY_REGISTERED) jQuery('.recover-install-key').show();

        return;
      }
      location.reload();
    },
    complete: () => {
      registerButtonEl.removeClass('disabled');
    }
  });
}

function trinityAudioOnPostManagementSubmit(e) {
  const saveButtonEl = e.target.getElementsByClassName('save-button')[0];
  saveButtonEl.classList.add('disabled');

  const formData = Object.fromEntries(new FormData(e.target).entries());

  const action = formData['post-management-action'];
  if (action === 'manual') {
    e.preventDefault();

    trinitySendMetric('wordpress.post-management.manual');
    const url = new URL(TRINITY_WP_ADMIN.TRINITY_AUDIO_POST_EDIT);
    url.hash = 'show_activate_modal';

    window.location.href = url.toString();
  }

  if (action === 'activate-all-posts-range') {
    const dateRangeEl = e.target.querySelector('[name="range-date"]');

    if (!formData['range-date']) {
      dateRangeEl.classList.add('field-error');
      saveButtonEl.classList.remove('disabled');
      e.preventDefault();
    }

    e.target.querySelector('[name="range-date"]').onchange = () => {
      dateRangeEl.classList.remove('field-error');
    }
  }
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
      publisher_token: jQuery('#' + window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PUBLISHER_TOKEN).val().trim(),
      [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.assign_account_key
    },
    success: (response) => {
      if (response.code === TRINITY_REGISTRATION_RESPONSE_CODE.SUCCESS || response.code === TRINITY_REGISTRATION_RESPONSE_CODE.ALREADY_ASSIGNED_PUBLISHER_TOKEN) {
        trinityShowPublisherTokenMessage('Successfully connected to Trinity Account');
        location.reload();
      } else {
        trinityShowPublisherTokenMessage(response.message, true);
      }
    },
    complete: () => {
      $(button).removeClass('trinity-loader');
      $(button).on('click', trinityAudioOnPublisherTokenSubmit);
    }
  });
}

function trinityShowPublisherTokenMessage(message, isError = false) {
  $cssClassSuffix = isError ? 'error' : 'success';
  jQuery('.publisher-token-notification').html(`<div class="notice notice-${$cssClassSuffix}"><p>${message}</p></div>`);
}

function trinitySendMetric(metric, additionalData) {
  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      metric,
      additionalData,
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_SEND_METRIC,
      [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.send_metric
    }
  });
}

function trinityRemovePostBanner() {
  $('.trinity-meta-upgrade-banner').remove();

  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REMOVE_POST_BANNER,
      [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.remove_post_banner
    }
  });
}

function trinityGrabPackageInfo(retryNumber) {
  $.ajax({
    type: 'GET',
    url: ajaxurl,
    data: {
      retryNumber,
      action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_PACKAGE_INFO,
      [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.get_package_info
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

function trinityCheckFieldDirty(input) {
  if (trinityIsFormValueChanged(input.name, input.value)) return input.classList.add('dirty');

  input.classList.remove('dirty');
}

var trinityTotalPostsStore;
var processedPostsStore;

function trinityUpdateBulkProgress({
                                     totalPosts,
                                     processedPosts,
                                     numOfFailedPosts,
                                     isInProgress,
                                     statusName = 'progress'
                                   }) {
  if (totalPosts) trinityTotalPostsStore = totalPosts;
  if (processedPosts) processedPostsStore = processedPosts;

  // has `display: none` in css by default
  jQuery('.trinity-bulk-update-wrapper').show();
  jQuery('.trinity-bulk-update-wrapper .status').hide();
  jQuery(`.trinity-bulk-update-wrapper .status.${statusName}`).show();

  if (processedPostsStore >= trinityTotalPostsStore) { // should be equal, but in case we have a bug in a code
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-posts-numbers').text(trinityTotalPostsStore);
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-posts-stage').text('Done!');
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-bar .trinity-bulk-bar-inner').css('width', '100%');
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-count-wrapper').show();
  } else if (isInProgress) {
    let readyPercentage = (processedPosts / trinityTotalPostsStore) * 100;

    let countProcessedText = `${processedPosts}/${totalPosts}`;

    if (Number(numOfFailedPosts) > 0) countProcessedText += ` (Failed: ${numOfFailedPosts})`;

    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-posts-numbers').text(countProcessedText);
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-posts-stage').text('Processed');
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-bar .trinity-bulk-bar-inner').css('width', `${readyPercentage}%`);
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-count-wrapper').show();
  } else {
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-count-wrapper').hide();
  }
}

function redirectToPostManagementPage() {
  const url = new URL(window.location.href);
  if (url.searchParams.get('page') === 'trinity_audio_post_management') return; // to avoid refreshing itself

  if (localStorage.getItem(TRINITY_LOCAL_STORAGE_IS_INITIAL_SAVE) === '1') { // redirect to post-management page on initial save
    localStorage.removeItem(TRINITY_LOCAL_STORAGE_IS_INITIAL_SAVE);

    const url = new URL(TRINITY_WP_ADMIN.TRINITY_AUDIO_ADMIN);
    url.searchParams.set('page', 'trinity_audio_post_management');

    setTimeout(() => {
      window.location.href = url.toString();
    }, 2000);
  }
}

redirectToPostManagementPage();

function showPostActivationModal() {
  if (window.location.hash === '#show_activate_modal') {
    const wrapperEl = document.createElement('div');
    wrapperEl.id = 'show-modal-activate-posts';
    wrapperEl.style = 'display: none;';
    wrapperEl.innerHTML = `<img src="${TRINITY_WP_ADMIN.TRINITY_AUDIO_ASSETS}/media/how-to-activate-post-edit.gif" alt="" width="800" height="428" />`;

    document.body.appendChild(wrapperEl);

    jQuery('#show-modal-activate-posts').dialog({
      autoOpen: true,
      modal: true,
      width: 800 + 20,
      height: 428 + 50,
      title: 'How to activate Trinity Player for posts',
      beforeClose: function () {
        window.location.hash = '';
      }
    });
  }
}

showPostActivationModal();

function showVideoGuideModal({id, url, title}) {
  const idSelector = `#${id}`;

  if (!document.querySelector(idSelector)) {
    const wrapperEl = document.createElement('div');
    wrapperEl.id = id;

    wrapperEl.innerHTML = `<iframe
    width="790"
    height="400"
    src="${url}"
    title="${title}"
    frameBorder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    allowFullScreen></iframe>`;

    document.body.appendChild(wrapperEl);
  }

  jQuery(idSelector).dialog({
    autoOpen: true,
    modal: true,
    width: 800 + 20,
    height: 428 + 50,
    title
  });
}

jQuery('.guide-hints').on('click', undefined, (event) => {
  const element = jQuery(event.target);

  const id = element.attr('data-id');
  const url = element.attr('data-url');
  const title = element.attr('data-title');

  showVideoGuideModal({
    id,
    url,
    title
  });
});
