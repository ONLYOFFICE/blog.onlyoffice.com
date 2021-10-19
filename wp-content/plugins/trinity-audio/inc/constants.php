<?php
  const TRINITY_AUDIO_SERVICE_HOST = 'audio.trinityaudio.ai';
  const TRINITY_AUDIO_SERVICE      = 'https://' . TRINITY_AUDIO_SERVICE_HOST;
  const TRINITY_AUDIO_STARTUP      = 'https://trinitymedia.ai/player/trinity.php';
  const TRINITY_AUDIO_TEST_HOST    = 'example.com';
  const TRINITY_AUDIO_TEST_SERVICE = 'https://example.com';
  const TRINITY_AUDIO_DASHBOARD_URL = 'https://dashboard.trinityaudio.ai/?utm_medium=wp_admin';
  const TRINITY_AUDIO_PRICING_URL   = 'https://trinityaudio.ai/pricing/?utm_medium=wp_admin';

  const TRINITY_AUDIO_LOG_DIR                             = '/tmp';
  const TRINITY_AUDIO_LOG_FILE_PART_NAME                  = 'trinity-wp-plugin';
  const TRINITY_AUDIO_INFO_FILE_PART_NAME                 = 'trinity-wp-plugin-info';
  const TRINITY_AUDIO_LOG                                 = TRINITY_AUDIO_LOG_DIR . '/' . TRINITY_AUDIO_LOG_FILE_PART_NAME . '.log';
  const TRINITY_AUDIO_INFO_HTML                           = TRINITY_AUDIO_LOG_DIR . '/' . TRINITY_AUDIO_INFO_FILE_PART_NAME . '.html';
  const TRINITY_AUDIO_LOG_MAX_SIZE_KB                     = 100;
  const TRINITY_AUDIO_LOG_MAX_FILES                       = 3;
  const TRINITY_AUDIO_REPORT_LONG_HTTP_REQUESTS_THRESHOLD = 1000; // 1 second
  const TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT                = 15; // in seconds
  const TRINITY_AUDIO_MAX_HEARTBEAT_TIMEOUT               = TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT + 5; // heartbeat timeout should be longer then request timeout
  const TRINITY_AUDIO_MAX_POST_REQUEST_RETRIES            = 3;
  const TRINITY_AUDIO_MAX_REQUEST_RETRIES_IN_ROW          = 10;

  const TRINITY_AUDIO_LABEL_DEFAULT = 'Default';

  abstract class TRINITY_AUDIO_ERROR_TYPES {
    const debug = 'debug';
    const info = 'info';
    const warn = 'warn';
    const error = 'error';
  }

  const TRINITY_AUDIO_GENDER_ARRAY = [
    'm' => 'Male',
    'f' => 'Female',
  ];

  const TRINITY_AUDIO_PLAYER_POSITION_ARRAY = [
    'before' => 'Before post',
    'after'  => 'After post',
    'none'   => 'Do not show',
  ];

  const TRINITY_AUDIO_INSTALLKEY                    = 'trinity_audio_installkey';
  const TRINITY_AUDIO_VIEWKEY                       = 'trinity_audio_viewkey';
  const TRINITY_AUDIO_PLUGIN_VERSION                = 'trinity_audio_plugin_version'; // array
  const TRINITY_AUDIO_PLUGIN_MIGRATION              = 'trinity_audio_plugin_migration'; // array
  const TRINITY_AUDIO_GENDER_ID                     = 'trinity_audio_gender_id';
  const TRINITY_AUDIO_SOURCE_LANGUAGE               = 'trinity_audio_source_language';
  const TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT      = 'trinity_audio_defconf';
  const TRINITY_AUDIO_PLAYER_POSITION               = 'trinity_audio_position';
  const TRINITY_AUDIO_PLAYER_LABEL                  = 'trinity_audio_player_label';
  const TRINITY_AUDIO_POWERED_BY                    = 'trinity_audio_poweredby';
  const TRINITY_AUDIO_PRECONNECT                    = 'trinity_audio_preconnect';
  const TRINITY_AUDIO_ADD_POST_TITLE                = 'trinity_audio_add_post_title';
  const TRINITY_AUDIO_ADD_POST_EXCERPT              = 'trinity_audio_add_post_excerpt';
  const TRINITY_AUDIO_SKIP_TAGS                     = 'trinity_audio_skip_tags';
  const TRINITY_AUDIO_ALLOW_SHORTCODES              = 'trinity_audio_allow_shortcodes';
  const TRINITY_AUDIO_PUBLISHER_TOKEN               = 'trinity_audio_publisher_token';
  const TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED = 'trinity_audio_bulk_update_num_posts_updated';
  const TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_FAILED  = 'trinity_audio_bulk_update_num_posts_failed';
  const TRINITY_AUDIO_BULK_UPDATE_HEARTBEAT         = 'trinity_audio_bulk_update_heartbeat';
  const TRINITY_AUDIO_FIRST_TIME_INSTALL            = 'trinity_audio_first_time_install';
  const TRINITY_AUDIO_CHECK_FOR_LOOP                = 'trinity_audio_check_for_loop';
  const TRINITY_AUDIO_TERMS_OF_SERVICE              = 'trinity_audio_terms_of_service';
  const TRINITY_AUDIO_PRIVACY_STATEMENT             = 'trinity_audio_privacy_statement';
  const TRINITY_AUDIO_RECOVER_INSTALLKEY            = 'TRINITY_AUDIO_RECOVER_INSTALLKEY';
  const TRINITY_AUDIO_FIRST_CHANGES_SAVE            = 'trinity_audio_first_changes_save';
  const TRINITY_AUDIO_IS_ACCOUNT_KEY_LINKED         = 'trinity_audio_is_account_linked';
  const TRINITY_AUDIO_TRANSLATE                     = 'trinity_audio_translate';

  const TRINITY_AUDIO_WP_SERVICE                 = TRINITY_AUDIO_SERVICE . '/wordpress';
  const TRINITY_AUDIO_BULK_UPDATE_URL            = TRINITY_AUDIO_WP_SERVICE . '/bulk_update';
  const TRINITY_AUDIO_POST_HASH_URL              = TRINITY_AUDIO_WP_SERVICE . '/posthash';
  const TRINITY_AUDIO_UPDATE_PLUGIN_SETTINGS_URL = TRINITY_AUDIO_WP_SERVICE . '/settings';
  const TRINITY_AUDIO_CREDITS_URL                = TRINITY_AUDIO_WP_SERVICE . '/credits';
  const TRINITY_AUDIO_CURRENT_PACKAGE            = TRINITY_AUDIO_WP_SERVICE . '/current_package';
  const TRINITY_AUDIO_KEYS_URL                   = TRINITY_AUDIO_WP_SERVICE . '/signup';
  const TRINITY_AUDIO_CONTACT_US_URL             = TRINITY_AUDIO_WP_SERVICE . '/contact-us';
  const TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL  = TRINITY_AUDIO_WP_SERVICE . '/update_plugin_details';
  const TRINITY_AUDIO_LANGUAGES_URL              = TRINITY_AUDIO_WP_SERVICE . '/languages';
  const TRINITY_AUDIO_PUBLISHER_TOKEN_URL        = TRINITY_AUDIO_WP_SERVICE . '/assign-unit-to-publisher';
  const TRINITY_AUDIO_NOTIFICATIONS_URL          = TRINITY_AUDIO_WP_SERVICE . '/notification';

  const TRINITY_AUDIO_ENABLED = 'trinity_audio_enable';

  const TRINITY_AUDIO_POST_HASH_CONTENT_TITLE         = 'trinity_audio_post_hash_content_title';
  const TRINITY_AUDIO_POST_HASH_CONTENT               = 'trinity_audio_post_hash_content';
  const TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT       = 'trinity_audio_post_hash_content_excerpt';
  const TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT_TITLE = 'trinity_audio_post_hash_content_excerpt_title';

  const TRINITY_AUDIO_NONCE_NAME = 'trinity-audio-post-nonce';

  const TRINITY_AUDIO = 'trinity_audio';

  const TRINITY_AUDIO_SUPPORT_EMAIL      = 'wp@trinityaudio.ai';
  const TRINITY_AUDIO_SUPPORT_EMAIL_LINK = '<a href="mailto:' . TRINITY_AUDIO_SUPPORT_EMAIL . '">' . TRINITY_AUDIO_SUPPORT_EMAIL . '</a>';
  const TRINITY_AUDIO_SUPPORT_MESSAGE    = 'Trinity Audio support: ' . TRINITY_AUDIO_SUPPORT_EMAIL_LINK;
  const TRINITY_AUDIO_DOT                = '. ';

  // SHARED VARIABLES
  const TRINITY_AUDIO_BULK_UPDATE_STATUS = 'trinity_audio_bulk_update_status';
  const TRINITY_AUDIO_BULK_UPDATE        = 'trinity_audio_bulk_update';
  const TRINITY_AUDIO_REGENERATE_TOKENS  = 'trinity_audio_regenerate_tokens';
  const TRINITY_AUDIO_CONTACT_US         = 'trinity_audio_contact_us';
  const TRINITY_AUDIO_REGISTER           = 'trinity_audio_register';

  const TRINITY_AUDIO_SENDER_EMAIL       = 'trinity_audio_sender_email';
  const TRINITY_AUDIO_SENDER_NAME        = 'trinity_audio_sender_name';
  const TRINITY_AUDIO_SENDER_MESSAGE     = 'trinity_audio_sender_message';
  const TRINITY_AUDIO_SENDER_INCLUDE_LOG = 'trinity_audio_sender_include_log';
  const TRINITY_AUDIO_SENDER_WEBSITE     = 'trinity_audio_sender_website';

  const TRINITY_AUDIO_CONTENT_TITLE         = 'content_title';
  const TRINITY_AUDIO_CONTENT               = 'content';
  const TRINITY_AUDIO_CONTENT_EXCERPT       = 'content_excerpt';
  const TRINITY_AUDIO_CONTENT_EXCERPT_TITLE = 'content_title_excerpt';

  const TRINITY_AUDIO_FEEDBACK_MESSAGE   = 'trinity_audio_feedback_message';

  const TRINITY_AUDIO_PACKAGES_DATA = array(
    'Free' => array(
        'translation' => 'No',
        'description' => 'For blog and content creators with up to 5 articles per month',
        'player_features' => 'No',
        'support' => 'No',
        'dashboard' => 'No'
    ),
    'Wordpress' => array(
        'translation' => 'No',
        'description' => 'For blog and content creators with up to 5 articles per month',
        'player_features' => 'No',
        'support' => 'No',
        'dashboard' => 'No'
    ),
    'Basic' => array(
        'translation' => 'Yes',
        'description' => 'Perfect for Blogs & Small publications',
        'player_features' => 'Basic',
        'support' => 'Up to 2 business days',
        'dashboard' => 'No'
    ),
    'Standard' => array(
        'translation' => 'Yes',
        'description' => 'Perfect for medium publications with larger content volume',
        'player_features' => 'Upgraded',
        'support' => 'Up to 1 business days',
        'dashboard' => 'Yes'
    ),
    'Premium' => array(
        'translation' => 'Yes',
        'description' => 'A custom solution for all publications',
        'player_features' => 'Custom',
        'support' => '24/7',
        'dashboard' => 'Yes'
    ),
);

