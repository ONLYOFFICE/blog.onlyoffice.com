<?php
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
  require_once __DIR__ . '/../inc/constants.php';
  require_once __DIR__ . '/../inc/common.php';
  require_once __DIR__ . '/../inc/templates.php';
  require_once __DIR__ . '/../metaboxes.php';

  add_action('admin_enqueue_scripts', 'trinity_admin_scripts');

  function trinity_admin_scripts() {
    wp_enqueue_script('trinity_audio_common', plugin_dir_url(__FILE__) . '../js/common.js', [], wp_rand(), true);
    wp_enqueue_script('trinity_audio_admin', plugin_dir_url(__FILE__) . '../js/admin.js', [], wp_rand(), true);
    wp_enqueue_style('trinity_audio_styles', plugin_dir_url(__FILE__) . 'dist/styles.css', [], wp_rand());

    $bulk_progress = [];

    if (trinity_is_bulk_update_in_progress()) {
      $bulk_progress['inProgress']        = true;
      $bulk_progress['processedPosts']    = (int)get_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED);
      $bulk_progress['numOfFailedPosts']  = (int)get_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_FAILED);
      $bulk_progress['totalPosts']        = sizeof(trinity_get_posts());
    }

    wp_localize_script(
      'trinity_audio_admin',
      'TRINITY_WP_ADMIN',
      [
        'TRINITY_AUDIO_ADMIN_POST'                  => admin_url('admin-post.php'),
        'TRINITY_AUDIO_BULK_UPDATE_STATUS'          => TRINITY_AUDIO_BULK_UPDATE_STATUS,
        'TRINITY_AUDIO_BULK_UPDATE'                 => TRINITY_AUDIO_BULK_UPDATE,
        'TRINITY_AUDIO_CONTACT_US'                  => TRINITY_AUDIO_CONTACT_US,
        'TRINITY_AUDIO_INSTALLKEY'                  => TRINITY_AUDIO_INSTALLKEY,
        'TRINITY_AUDIO_PUBLISHER_TOKEN'             => TRINITY_AUDIO_PUBLISHER_TOKEN,
        'TRINITY_AUDIO_PUBLISHER_TOKEN_URL'         => TRINITY_AUDIO_PUBLISHER_TOKEN_URL,
        'TRINITY_AUDIO_REGENERATE_TOKENS'           => TRINITY_AUDIO_REGENERATE_TOKENS,
        'TRINITY_AUDIO_REGISTER'                    => TRINITY_AUDIO_REGISTER,
        'TRINITY_AUDIO_RECOVER_INSTALLKEY'          => TRINITY_AUDIO_RECOVER_INSTALLKEY,
        'TRINITY_AUDIO_FIRST_CHANGES_SAVE'          => TRINITY_AUDIO_FIRST_CHANGES_SAVE,
        'LANGUAGES'                                 => trinity_get_voices(),
        'TRINITY_AUDIO_BULK_UPDATE_PROGRESS'        => $bulk_progress,
        TRINITY_AUDIO_SKIP_TAGS                     => implode(',', trinity_get_skip_tags()),
        TRINITY_AUDIO_ALLOW_SHORTCODES              => implode(',', trinity_get_allowed_shortcodes()),
        'TRINITY_AUDIO_EMAIL_SUBSCRIPTION'          => TRINITY_AUDIO_EMAIL_SUBSCRIPTION,
        'TRINITY_AUDIO_UPDATE_UNIT_CONFIG'          => TRINITY_AUDIO_UPDATE_UNIT_CONFIG,
        'TRINITY_AUDIO_SEND_METRIC'                 => TRINITY_AUDIO_SEND_METRIC,
        'TRINITY_AUDIO_REMOVE_POST_BANNER'          => TRINITY_AUDIO_REMOVE_POST_BANNER,
        'TRINITY_AUDIO_PACKAGE_INFO'                => TRINITY_AUDIO_PACKAGE_INFO
      ]
    );
  }

  add_action('admin_init', 'trinity_admin_create_page');
  add_action('admin_menu', 'trinity_admin_create_menu');
  add_action('wp_ajax_' . TRINITY_AUDIO_REGENERATE_TOKENS, 'trinity_audio_ajax_regenerate_tokens');
  add_action('wp_ajax_' . TRINITY_AUDIO_BULK_UPDATE_STATUS, 'trinity_audio_ajax_bulk_update_status');
  add_action('wp_ajax_' . TRINITY_AUDIO_CONTACT_US, 'trinity_audio_ajax_contact_us');
  add_action('save_post', 'trinity_save_post_callback', 2147483647, 3);
  add_action('wp_ajax_' . TRINITY_AUDIO_REGISTER, 'trinity_audio_ajax_register');
  add_action('wp_ajax_' . TRINITY_AUDIO_PUBLISHER_TOKEN_URL, 'trinity_save_publisher_token');
  add_action('wp_ajax_' . TRINITY_AUDIO_UPDATE_UNIT_CONFIG, 'trinity_audio_ajax_update_unit_config');
  add_action('wp_ajax_' . TRINITY_AUDIO_SEND_METRIC, 'trinity_send_stat_metrics');
  add_action('wp_ajax_' . TRINITY_AUDIO_REMOVE_POST_BANNER, 'trinity_audio_ajax_remove_post_banner');
  add_action('wp_ajax_' . TRINITY_AUDIO_PACKAGE_INFO, 'trinity_get_and_render_package');

  function trinity_admin_create_page() {
    // add our page to whitelist, so we can POST to options.php.
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_SOURCE_LANGUAGE);

    // TODO: remove this setting after drop $_GET[postConfig]
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_GENDER_ID);

    // allow to save to DB.
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_VOICE_ID);

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_PLAYER_POSITION);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_PLAYER_LABEL);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_POWERED_BY);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_PRECONNECT);

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_ADD_POST_TITLE);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_ADD_POST_EXCERPT);

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_TRANSLATE);

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_FIRST_CHANGES_SAVE);

    register_setting(
      TRINITY_AUDIO,
      TRINITY_AUDIO_SKIP_TAGS,
      [
        'sanitize_callback' => function ($value) {
          // save into DB as array.
          return array_map('trim', explode(',', $value));
        },
      ]
    );

    register_setting(
      TRINITY_AUDIO,
      TRINITY_AUDIO_ALLOW_SHORTCODES,
      [
        'sanitize_callback' => function ($value) {
          // save into DB as array.
          return array_map('trim', explode(',', $value));
        },
      ]
    );

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_CHECK_FOR_LOOP);
  }

  function trinity_admin_create_menu() {
    add_menu_page('Trinity Audio', 'Trinity Audio', 'manage_options', 'trinity_audio', 'trinity_admin_setting_page', plugins_url('../assets/images/play-button.svg', __FILE__));

    if (!trinity_registered()) {
      return;
    }
    add_submenu_page('trinity_audio', 'Info', 'Info', 'manage_options', 'trinity_audio_info', 'trinity_admin_settings_info');
    add_submenu_page('trinity_audio', 'Logs', 'Logs', 'manage_options', 'trinity_audio_logs', 'trinity_admin_settings_submenu_logs');
    add_submenu_page('trinity_audio', 'Contact us', 'Contact us', 'manage_options', 'trinity_audio_contact_us', 'trinity_admin_settings_contact_us');
  }

  function trinity_admin_setting_page() {
    if (trinity_registered()) {
      require_once __DIR__ . '/inc/settings.php';
    } else {
      require_once __DIR__ . '/inc/register.php';
    }
  }

  function trinity_admin_settings_info() {
    require_once __DIR__ . '/inc/info.php';
  }

  function trinity_admin_settings_submenu_logs() {
    require_once __DIR__ . '/inc/logs.php';
  }

  function trinity_admin_settings_contact_us() {
    require_once __DIR__ . '/inc/contact.php';
  }

  function trinity_audio_ajax_register() {
    trinity_register();
    wp_die();
  }
