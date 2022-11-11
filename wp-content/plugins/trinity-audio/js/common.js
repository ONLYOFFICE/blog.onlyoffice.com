function trinityShowStatus(id, statusName) {
  jQuery(`${id} .trinity-status-wrapper .status`).removeClass('show');
  jQuery(`${id} .trinity-status-wrapper .status.${statusName}`).addClass('show');
}

function trinityUpdateBulkProgress({processedPosts, totalPosts, numOfFailedPosts, statusName}) {
  // has `display: none` in css by default
  jQuery('.trinity-bulk-update-wrapper').show();
  jQuery('.trinity-bulk-update-wrapper .status').hide();
  jQuery(`.trinity-bulk-update-wrapper .status.${statusName}`).show();

  if (Number(totalPosts) > 0 && Number(processedPosts) > 0) {
    const readyPercentage = (processedPosts / totalPosts) * 100;

    let countProcessedText = `${processedPosts}/${totalPosts} Posts`;

    if (Number(numOfFailedPosts) > 0) countProcessedText += ` (Failed: ${numOfFailedPosts})`;

    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-posts').text(countProcessedText);
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-bar .trinity-bulk-bar-inner').css('width', `${readyPercentage}%`);
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-count-wrapper').show();
  } else {
    jQuery('.trinity-bulk-update-wrapper .trinity-bulk-count-wrapper').hide();
  }
}

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
