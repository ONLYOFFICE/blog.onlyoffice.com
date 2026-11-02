<?php
  // we have to allow people to uninstall plugin no matter what happened.
  try {
    require_once __DIR__ . '/inc/constants.php';
    require_once __DIR__ . '/inc/common.php';
    require_once __DIR__ . '/utils.php';
    require_once __DIR__ . '/inc/post-hashes-bulk-update.php';

    if (!defined('WP_UNINSTALL_PLUGIN')) {
      exit();
    }

    // keep those options, as if we delete them - client will not be able to reactivate later on.
    //
    // delete_option(TRINITY_AUDIO_INSTALLKEY);
    // delete_option(TRINITY_AUDIO_VIEWKEY);

    // TODO: delete all postmeta as well.

    delete_option(TRINITY_AUDIO_PLUGIN_VERSION);
    delete_option(TRINITY_AUDIO_PLUGIN_MIGRATION);
    delete_option(TRINITY_AUDIO_SOURCE_LANGUAGE);
    delete_option(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT);
    delete_option(TRINITY_AUDIO_PLAYER_LABEL);
    delete_option(TRINITY_AUDIO_POWERED_BY);
    delete_option(TRINITY_AUDIO_SKIP_TAGS);
    delete_option(TRINITY_AUDIO_ALLOW_SHORTCODES);
    delete_option(TRINITY_AUDIO_PHBU_HEARTBEAT);
    delete_option(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM);
    delete_option(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM);
    delete_option(TRINITY_AUDIO_PHBU_FAILED_POSTS);
    delete_option(TRINITY_AUDIO_PHBU_BULK_UPDATE_DONE);
    delete_option(TRINITY_AUDIO_FIRST_CHANGES_SAVE);
    delete_option(TRINITY_AUDIO_VOICE_ID);
    delete_option(TRINITY_AUDIO_MIGRATION_PROGRESS);

    trinity_update_details(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'deleted', false);
  } catch (Exception $e) {
    error_log($e->getMessage());
  }
