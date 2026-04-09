async function trinityMetaVoiceConfig() {
  // WP Save button will save our data since we already update the form, so the voice post-lvel new data will just be sent to backend
  const updateForm = async (formData) => {
    if (!originalConfig) return;
    if (JSON.stringify(originalConfig) === JSON.stringify(formData)) return;

    const {voiceId, code} = formData;

    const voiceIdInputEl = document.getElementById('trinity_audio_voice_id');
    voiceIdInputEl.value = voiceId; // set public voiceId

    // Good to save locale and not only voiceId, since if voiceId get removed, we have locale which we can rely on
    const languageInputEl = document.getElementById('trinity_audio_source_language');
    languageInputEl.value = code;

    console.debug(`Updating post-level voice config to voiceId: ${voiceId} and locale: ${code}`);
  };

  // keep the original config to avoid updating post-level config with the same data which is on unit's one, avoid spamming
  let originalConfig;
  waitForExpression(() => window.TRINITY_UNIT_CONFIGURATION?.getFormData).then(async () => {
    originalConfig = await window.TRINITY_UNIT_CONFIGURATION.getFormData();
    window.TRINITY_UNIT_CONFIGURATION.on('change', updateForm);
  });
}

function trinitySendMetricMeta(metric, additionalData) {
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

function waitForExpression(expressionFn) {
  return new Promise((resolve) => {
    const t = setInterval(() => {
      if (!!expressionFn()) {
        resolve();
        clearInterval(t);
      }
    }, 1000);
  });
}

(function ($) {
  const ERROR_GET_VALUE = 'Unable to retrieve value';

  const submitButton = $('#trinity-metabox .components-tab-panel__tab-content .content[data-id="advanced"] button');

  function initTabPanel() {
    const tabs = $('#trinity-metabox .components-tab-panel__tabs');
    tabs.click(function (e) {
      const target = e.target;
      const id = target.dataset.id;

      if (!id) return;

      $('#trinity-metabox .components-tab-panel__tabs button').removeClass('is-active');
      $(e.target).addClass('is-active');

      $('#trinity-metabox .components-tab-panel__tab-content .content').removeClass('is-active');
      $(`#trinity-metabox .components-tab-panel__tab-content .content[data-id='${id}']`).addClass('is-active');
    });

    submitButton.click(function () {
      regenerateTokens(window.TRINITY_WP_METABOX.postId);
    });
  }

  function regenerateTokens(postId) {
    const id = '#trinity-metabox';

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_REGENERATE_TOKENS,
        post_id: postId,
        [window.TRINITY_WP_ADMIN.TRINITY_AUDIO_AJAX_NONCE_NAME]: window.TRINITY_WP_ADMIN.TRINITY_AUDIO_NONCES.regenerate_tokens
      },
      dataType: 'json',
      beforeSend: function () {
        submitButton.prop('disabled', true);
        trinityShowStatus(id, 'progress');
      },
      complete: function () {
        submitButton.prop('disabled', false);
      },
      success: function (response) {
        if (!response || response.error) return trinityShowStatus(id, 'error');

        // update token labels
        const postMetaMap = window.TRINITY_WP_METABOX.TRINITY_AUDIO_POST_META_MAP;
        const titleContent = response[postMetaMap.title_content];

        $('.trinity-meta-title-content').text(titleContent || ERROR_GET_VALUE);

        if (!titleContent) return trinityShowStatus(id, 'error');

        trinityShowStatus(id, 'success');
      }
    }).fail(function (response) {
      console.error('TRINITY_WP', response);
      trinityShowStatus(id, 'error');
    });
  }

  initTabPanel();
})(jQuery);
