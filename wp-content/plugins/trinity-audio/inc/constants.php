<?php
  const TRINITY_AUDIO_SERVICE_HOST = 'audio.trinityaudio.ai';

  define('TRINITY_AUDIO_SERVICE', empty(getenv('PLUGIN_AUDIO_SERVICE_URL')) ? 'https://' . TRINITY_AUDIO_SERVICE_HOST : getenv('PLUGIN_AUDIO_SERVICE_URL'));

  define('TRINITY_AUDIO_STARTUP', empty(getenv('PLUGIN_TTS_PLAYER_URL')) ? 'https://trinitymedia.ai/player/trinity/' : getenv('PLUGIN_TTS_PLAYER_URL')); // v4.1.(4,5,6) works with trinity/_/?viewkey...

  define('TRINITY_DASHBOARD_SERVICE', empty(getenv('TRINITY_DASHBOARD_SERVICE')) ? 'https://dashboard.trinityaudio.ai/' : getenv('TRINITY_DASHBOARD_SERVICE')); // v4.1.(4,5,6) works with trinity/_/?viewkey...

  define('TRINITY_IS_TEST', !!getenv('TRINITY_IS_TEST'));

  const TRINITY_AUDIO_TEST_HOST     = 'example.com';
  const TRINITY_AUDIO_TEST_SERVICE  = 'https://example.com';
  const TRINITY_AUDIO_DASHBOARD_URL = 'https://dashboard.trinityaudio.ai';
  const TRINITY_AUDIO_UPGRADE_URL   = 'https://dashboard.trinityaudio.ai/upgrade-plan';

  define('TRINITY_AUDIO_PRICING_URL', 'https://www.trinityaudio.ai/pricing-trinity-audio?installkey=' . get_option('trinity_audio_installkey'));
  define('TRINITY_AUDIO_LOG_DIR', sys_get_temp_dir());

  const TRINITY_AUDIO_LOG_FILE_PART_NAME                  = 'trinity-wp-plugin';
  const TRINITY_AUDIO_INFO_FILE_PART_NAME                 = 'trinity-wp-plugin-info';
  const TRINITY_AUDIO_LOG_MAX_SIZE                        = 500 * 1024; // in bytes, 500 KB
  const TRINITY_AUDIO_REPORT_LONG_HTTP_REQUESTS_THRESHOLD = 1000; // 1 second
  const TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT                = 15; // in seconds
  const TRINITY_AUDIO_MAX_HEARTBEAT_TIMEOUT               = 60;  // in seconds

  define('TRINITY_AUDIO_LOG', TRINITY_AUDIO_LOG_DIR . '/' . TRINITY_AUDIO_LOG_FILE_PART_NAME . '-' . get_log_prefix() . '.log');
  define('TRINITY_AUDIO_INFO_HTML', TRINITY_AUDIO_LOG_DIR . '/' . TRINITY_AUDIO_INFO_FILE_PART_NAME . '-' . get_log_prefix() . '.html');

  const BREAK_MACRO = '⏸';
  const TRINITY_AUDIO_BANNER_TTL_SECONDS = 7 * 24 * 60 * 60;
  const BLOCK_MACRO = '▇';

  abstract class TRINITY_AUDIO_ERROR_TYPES {
    const debug = 'debug';
    const info = 'info';
    const warn = 'warn';
    const error = 'error';
  }

  const TRINITY_AUDIO_INSTALLKEY                    = 'trinity_audio_installkey';
  const TRINITY_AUDIO_VIEWKEY                       = 'trinity_audio_viewkey';
  const TRINITY_AUDIO_PLUGIN_VERSION                = 'trinity_audio_plugin_version'; // array
  const TRINITY_AUDIO_PLUGIN_MIGRATION              = 'trinity_audio_plugin_migration'; // array
  const TRINITY_AUDIO_SOURCE_LANGUAGE               = 'trinity_audio_source_language';
  const TRINITY_AUDIO_VOICE_ID                      = 'trinity_audio_voice_id';
  const TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT      = 'trinity_audio_defconf';
  const TRINITY_AUDIO_PLAYER_LABEL                  = 'trinity_audio_player_label';
  const TRINITY_AUDIO_POWERED_BY                    = 'trinity_audio_poweredby';
  const TRINITY_AUDIO_PRECONNECT                    = 'trinity_audio_preconnect';
  const TRINITY_AUDIO_SKIP_TAGS                     = 'trinity_audio_skip_tags';
  const TRINITY_AUDIO_ALLOW_SHORTCODES              = 'trinity_audio_allow_shortcodes';
  const TRINITY_AUDIO_PUBLISHER_TOKEN               = 'trinity_audio_publisher_token';
  const TRINITY_AUDIO_FIRST_TIME_INSTALL            = 'trinity_audio_first_time_install';
  const TRINITY_AUDIO_MIGRATION_PROGRESS            = 'trinity_audio_migration_progress';
  const TRINITY_AUDIO_CHECK_FOR_LOOP                = 'trinity_audio_check_for_loop';
  const TRINITY_AUDIO_TERMS_OF_SERVICE              = 'trinity_audio_terms_of_service';
  const TRINITY_AUDIO_EMAIL_SUBSCRIPTION            = 'trinity_audio_email_subscription';
  const TRINITY_AUDIO_RECOVER_INSTALLKEY            = 'trinity_audio_recover_installkey';
  const TRINITY_AUDIO_FIRST_CHANGES_SAVE            = 'trinity_audio_first_changes_save';
  const TRINITY_AUDIO_IS_ACCOUNT_KEY_LINKED         = 'trinity_audio_is_account_linked';
  const TRINITY_AUDIO_UPDATE_UNIT_CONFIG            = 'trinity_audio_update_unit_config';
  const TRINITY_AUDIO_SEND_METRIC                   = 'trinity_audio_send_metric';
  const TRINITY_AUDIO_REMOVE_POST_BANNER            = 'trinity_audio_remove_post_banner';
  const TRINITY_AUDIO_PACKAGE_INFO                  = 'trinity_audio_package_info';
  const TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION = 'trinity_audio_activate_on_api_post_creation';

  const TRINITY_AUDIO_WP_SERVICE                    = TRINITY_AUDIO_SERVICE . '/wordpress';
  const TRINITY_AUDIO_BULK_UPDATE_URL               = TRINITY_AUDIO_WP_SERVICE . '/bulk_update';
  const TRINITY_AUDIO_POST_HASH_URL_V2              = TRINITY_AUDIO_WP_SERVICE . '/v2/posthash';
  const TRINITY_AUDIO_UPDATE_PLUGIN_CONFIG_URL      = TRINITY_AUDIO_WP_SERVICE . '/config';
  const TRINITY_AUDIO_METRICS_URL                   = TRINITY_AUDIO_WP_SERVICE . '/metrics';

  const TRINITY_AUDIO_CREDITS_URL                   = TRINITY_AUDIO_WP_SERVICE . '/credits';
  const TRINITY_AUDIO_KEYS_URL                      = TRINITY_AUDIO_WP_SERVICE . '/signup';
  const TRINITY_AUDIO_CONTACT_US_URL                = TRINITY_AUDIO_WP_SERVICE . '/contact-us';
  const TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL     = TRINITY_AUDIO_WP_SERVICE . '/update_plugin_details';
  const TRINITY_AUDIO_PUBLISHER_TOKEN_URL           = TRINITY_AUDIO_WP_SERVICE . '/assign-unit-to-publisher';
  const TRINITY_AUDIO_UPDATE_FULL_UNIT_CONFIG_URL   = TRINITY_AUDIO_WP_SERVICE . '/unit-config';
  const TRINITY_AUDIO_NOTIFICATIONS_URL             = TRINITY_AUDIO_WP_SERVICE . '/notification';

  const TRINITY_AUDIO_ENABLED = 'trinity_audio_enable';

  const TRINITY_AUDIO_POST_HASH = 'trinity_audio_post_hash';

  const TRINITY_AUDIO_NONCE_NAME = '_trinity_wp_nonce';
  const TRINITY_AUDIO_AJAX_NONCE_NAME = '_trinity_ajax_nonce';

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

  const TRINITY_AUDIO_TITLE_CONTENT = 'title_content';

  const TRINITY_AUDIO_POST_META_MAP = [
    'title_content'         => 'tc'
  ];

  const TRINITY_AUDIO_FEEDBACK_MESSAGE = 'trinity_audio_feedback_message';

  const TRINITY_AUDIO_POST_MANAGEMENT_SUCCESS_MESSAGES = [
    'activate-all-posts'       => 'Trinity Audio Player enabled for all ##AMOUNT## posts',
    'deactivate-all-posts'     => 'Trinity Audio Player disabled for all ##AMOUNT## posts',
    'activate-all-posts-range' => 'Trinity Audio Player enabled for ##AMOUNT## posts which were published ##BEFORE-AFTER## ##DATE##',
  ];

  function get_log_prefix() {
    return str_replace([':', '/', '.'], '_', get_site_url());
  }
