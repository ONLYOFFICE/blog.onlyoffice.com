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
        post_id: postId
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
        const contentTitle = response[window.TRINITY_WP_METABOX.TRINITY_AUDIO_CONTENT_TITLE];
        const content = response[window.TRINITY_WP_METABOX.TRINITY_AUDIO_CONTENT];
        const contentTitleExcerpt = response[window.TRINITY_WP_METABOX.TRINITY_AUDIO_CONTENT_EXCERPT_TITLE];
        const contentExcerpt = response[window.TRINITY_WP_METABOX.TRINITY_AUDIO_CONTENT_EXCERPT];

        $('.trinity-meta-content-title').text(contentTitle || ERROR_GET_VALUE);
        $('.trinity-meta-content').text(content || ERROR_GET_VALUE);
        $('.trinity-meta-content-title-excerpt').text(contentTitleExcerpt || ERROR_GET_VALUE);
        $('.trinity-meta-content-excerpt').text(contentExcerpt || ERROR_GET_VALUE);

        if (!contentTitle || !content || !contentTitleExcerpt || !contentExcerpt) return trinityShowStatus(id, 'error');

        trinityShowStatus(id, 'success');
      }
    }).fail(function (response) {
      console.error('TRINITY_WP', response);
      trinityShowStatus(id, 'error');
    });
  }

  initTabPanel();
})(jQuery);
