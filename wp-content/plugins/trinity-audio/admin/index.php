<?php
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
  require_once __DIR__ . '/../inc/templates.php';
  require_once __DIR__ . '/../metaboxes.php';
  require_once __DIR__ . '/../migrations/index.php';

  if (trinity_is_dev_env()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // for testing
    $max_exec_time = get_option('trinity_audio_max_exec_time');
    if ($max_exec_time) {
      set_time_limit($max_exec_time);
    }
  }

  add_action('admin_enqueue_scripts', 'trinity_admin_scripts');
  add_action('admin_menu', function() {
    trinity_migration_init();
  });

  function trinity_admin_scripts() {
    // TODO: filter out only our pages

    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');

    wp_enqueue_script('trinity_audio_common', plugin_dir_url(__FILE__) . '../js/common.js', [], wp_rand(), true);
    wp_enqueue_script('trinity_audio_admin', plugin_dir_url(__FILE__) . '../js/admin.js', [], wp_rand(), true);
    wp_enqueue_style('trinity_audio_styles', plugin_dir_url(__FILE__) . 'dist/styles.css', [], wp_rand());

    $bulk_progress = [];

    if (trinity_phbu_is_bulk_update_alive()) {
      $bulk_progress = trinity_phbu_get_status_data();
    }

    wp_localize_script(
      'trinity_audio_admin',
      'TRINITY_WP_ADMIN',
      [
        'TRINITY_AUDIO_POST_EDIT'            => admin_url('edit.php'),
        'TRINITY_AUDIO_ADMIN_POST'           => admin_url('admin-post.php'),
        'TRINITY_AUDIO_ADMIN'                => admin_url('admin.php'),
        'TRINITY_AUDIO_ASSETS'               => plugins_url('assets', dirname(__FILE__)),
        'TRINITY_AUDIO_BULK_UPDATE_STATUS'   => TRINITY_AUDIO_BULK_UPDATE_STATUS,
        'TRINITY_AUDIO_BULK_UPDATE'          => TRINITY_AUDIO_BULK_UPDATE,
        'TRINITY_AUDIO_CONTACT_US'           => TRINITY_AUDIO_CONTACT_US,
        'TRINITY_AUDIO_INSTALLKEY'           => TRINITY_AUDIO_INSTALLKEY,
        'TRINITY_AUDIO_PUBLISHER_TOKEN'      => TRINITY_AUDIO_PUBLISHER_TOKEN,
        'TRINITY_AUDIO_PUBLISHER_TOKEN_URL'  => TRINITY_AUDIO_PUBLISHER_TOKEN_URL,
        'TRINITY_AUDIO_REGENERATE_TOKENS'    => TRINITY_AUDIO_REGENERATE_TOKENS,
        'TRINITY_AUDIO_REGISTER'             => TRINITY_AUDIO_REGISTER,
        'TRINITY_AUDIO_RECOVER_INSTALLKEY'   => TRINITY_AUDIO_RECOVER_INSTALLKEY,
        'TRINITY_AUDIO_FIRST_CHANGES_SAVE'   => TRINITY_AUDIO_FIRST_CHANGES_SAVE,
        'TRINITY_AUDIO_BULK_UPDATE_PROGRESS' => $bulk_progress,
        TRINITY_AUDIO_SKIP_TAGS              => implode(',', trinity_get_skip_tags()),
        TRINITY_AUDIO_ALLOW_SHORTCODES       => implode(',', trinity_get_allowed_shortcodes()),
        'TRINITY_AUDIO_EMAIL_SUBSCRIPTION'   => TRINITY_AUDIO_EMAIL_SUBSCRIPTION,
        'TRINITY_AUDIO_UPDATE_UNIT_CONFIG'   => TRINITY_AUDIO_UPDATE_UNIT_CONFIG,
        'TRINITY_AUDIO_SEND_METRIC'          => TRINITY_AUDIO_SEND_METRIC,
        'TRINITY_AUDIO_REMOVE_POST_BANNER'   => TRINITY_AUDIO_REMOVE_POST_BANNER,
        'TRINITY_AUDIO_PACKAGE_INFO'         => TRINITY_AUDIO_PACKAGE_INFO,
        'TRINITY_AUDIO_AJAX_NONCE_NAME'      => TRINITY_AUDIO_AJAX_NONCE_NAME,
        'TRINITY_AUDIO_NONCES' => [
              'register' => wp_create_nonce('register_action'),
              'update_unit_config' => wp_create_nonce('update_unit_config_action'),
              'assign_account_key' => wp_create_nonce('assign_account_key_action'),
              'contact_us' => wp_create_nonce('contact_us_action'),
              'regenerate_tokens' => wp_create_nonce('regenerate_tokens_action'),
              'send_metric' => wp_create_nonce('send_metric_action'),
              'remove_post_banner' => wp_create_nonce('remove_post_banner_action'),
              'get_package_info' => wp_create_nonce('get_package_info_action'),

              'audio_bulk_update' => wp_create_nonce('audio_bulk_update_action'),
              'audio_bulk_update_status' => wp_create_nonce('audio_bulk_update_status_action'),
        ]
      ]
    );
  }

  add_filter('plugin_row_meta', 'trinity_audio_plugin_links', 9999, 4);

  add_action('admin_init', 'trinity_admin_create_page');
  add_action('admin_menu', 'trinity_admin_create_menu');
  add_action('wp_ajax_' . TRINITY_AUDIO_REGENERATE_TOKENS, 'trinity_ph_update_regenerate_tokens');
  add_action('wp_ajax_' . TRINITY_AUDIO_BULK_UPDATE_STATUS, 'trinity_phbu_get_status');
  add_action('wp_ajax_' . TRINITY_AUDIO_CONTACT_US, 'trinity_audio_ajax_contact_us');
  add_action('wp_ajax_' . TRINITY_AUDIO_REGISTER, 'trinity_audio_ajax_register');
  add_action('wp_ajax_' . TRINITY_AUDIO_PUBLISHER_TOKEN_URL, 'trinity_save_publisher_token');
  add_action('wp_ajax_' . TRINITY_AUDIO_UPDATE_UNIT_CONFIG, 'trinity_audio_ajax_update_unit_config');
  add_action('wp_ajax_' . TRINITY_AUDIO_SEND_METRIC, 'trinity_send_stat_metrics');
  add_action('wp_ajax_' . TRINITY_AUDIO_REMOVE_POST_BANNER, 'trinity_audio_ajax_remove_post_banner');
  add_action('wp_ajax_' . TRINITY_AUDIO_PACKAGE_INFO, 'trinity_get_and_render_package');
  add_action('update_option', 'trinity_audio_enable_for_last_20_posts'); // updated_option will not work, since at that point trinity_get_is_first_changes_saved() will be 1

  // triggers by admin.js checkIfPostsBulkUpdateRequested only after cleaning shortcodes or skip HTML tags
  add_action('admin_post_' . TRINITY_AUDIO_BULK_UPDATE, 'trinity_phbu_start_from_admin');

  register_deactivation_hook(__FILE__, 'trinity_audio_deactivation');

  function trinity_audio_activation() {
    trinity_update_details(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'activated', false);
  }

  function trinity_audio_deactivation() {
    trinity_update_details(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'deactivated', false);
  }

  trinity_init_default_settings();

  function trinity_init_default_settings() {
    // lets add keys as well, so if user has them from previous setup - can find them by name and edit them.
    add_option(TRINITY_AUDIO_INSTALLKEY, '', '', true);
    add_option(TRINITY_AUDIO_VIEWKEY, '', '', true);

    add_option(TRINITY_AUDIO_PLUGIN_VERSION, [], '', true);
    add_option(TRINITY_AUDIO_PLUGIN_MIGRATION, [], '', true);
    add_option(TRINITY_AUDIO_SOURCE_LANGUAGE, 'en-US', '', true);
    add_option(TRINITY_AUDIO_POWERED_BY, 1, '', true);
    add_option(TRINITY_AUDIO_PRECONNECT, 1, '', true);
    add_option(TRINITY_AUDIO_PLAYER_LABEL, '', '', true);
    add_option(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT, 1, '', true);
    add_option(TRINITY_AUDIO_SKIP_TAGS, [], '', true);
    add_option(TRINITY_AUDIO_ALLOW_SHORTCODES, [], '', true);
    add_option(TRINITY_AUDIO_CHECK_FOR_LOOP, 0, '', true);
    add_option(TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION, 0, '', true);
    add_option(TRINITY_AUDIO_FIRST_CHANGES_SAVE, 0, '', true);
  }

  if (trinity_get_is_first_changes_saved() && trinity_get_install_key() && trinity_get_view_key()) {
    add_filter('bulk_actions-edit-post', function($bulk_actions) {
      $bulk_actions['enable-trinity-audio']  = 'Enable Trinity Audio';
      $bulk_actions['disable-trinity-audio'] = 'Disable Trinity Audio';
      return $bulk_actions;
    });

    add_filter('handle_bulk_actions-edit-post', function($redirect_url, $action, $post_ids) {
      if ($action == 'enable-trinity-audio') {
        foreach ($post_ids as $post_id) {
          update_post_meta($post_id, TRINITY_AUDIO_ENABLED, 1);
        }
      }

      if ($action == 'disable-trinity-audio') {
        foreach ($post_ids as $post_id) {
          update_post_meta($post_id, TRINITY_AUDIO_ENABLED, 0);
        }
      }

      return $redirect_url;
    }, 9999, 3);

    add_action('restrict_manage_posts', function() {
      $values = [
        'Trinity Audio enabled'  => '1',
        'Trinity Audio disabled' => '0'
      ];
      ?>
      <select name="trinity-audio-bulk-filter">
        <option value="">All posts</option>
        <?php
          $is_filtered = isset($_GET['trinity-audio-bulk-filter']) ? $_GET['trinity-audio-bulk-filter'] : '';

          foreach ($values as $label => $value) {
            $is_selected = $value == $is_filtered ? ' selected="selected"' : '';
            echo "<option value='" . esc_attr($value) . "'" . esc_attr($is_selected) . ">" . esc_html($label) . "</option>";
          }
        ?>
      </select>
      <?php
    });

    add_filter('parse_query', function($query) {
      global $pagenow;

      if (is_admin() && $pagenow == 'edit.php' && isset($_GET['trinity-audio-bulk-filter']) && $_GET['trinity-audio-bulk-filter'] != '') {
        $value = $_GET['trinity-audio-bulk-filter'];

        if ($value === '0') {
          $query->query_vars['meta_query'] = [
            'relation' => 'OR',
            // when no posts activated, we don't have any record in wp_postmeta at all
            [
              'key'     => TRINITY_AUDIO_ENABLED,
              'compare' => 'NOT EXISTS'
            ],
            [
              'key'     => TRINITY_AUDIO_ENABLED,
              'value' => '0',
              'compare' => '='
            ]
          ];
        } else {
          $query->query_vars['meta_key']     = TRINITY_AUDIO_ENABLED;
          $query->query_vars['meta_value']   = $value;
          $query->query_vars['meta_compare'] = '=';
        }
      }
    });
  }

  function trinity_admin_create_page() {
    // add our page to whitelist, so we can POST to options.php.
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_SOURCE_LANGUAGE);

    // allow to save to DB.
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_PLAYER_LABEL);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_POWERED_BY);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_PRECONNECT);

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_FIRST_CHANGES_SAVE);

    register_setting(
      TRINITY_AUDIO,
      TRINITY_AUDIO_SKIP_TAGS,
      [
        'sanitize_callback' => 'trinity_admin_save_as_array'
      ]
    );

    register_setting(
      TRINITY_AUDIO,
      TRINITY_AUDIO_ALLOW_SHORTCODES,
      [
        'sanitize_callback' => 'trinity_admin_save_as_array'
      ]
    );

    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_CHECK_FOR_LOOP);
    register_setting(TRINITY_AUDIO, TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION);
  }

  function trinity_admin_save_as_array($value) {
    // return an empty array if the input is empty
    if (empty(trim($value))) return [];

    // save into DB as array
    return array_map('trim', explode(',', $value));
  }

  function trinity_admin_create_menu() {
    add_menu_page('Trinity Audio', 'Trinity Audio', 'manage_options', 'trinity_audio', 'trinity_admin_setting_page', plugins_url('../assets/images/play-button.svg', __FILE__));

    if (!trinity_registered()) {
      return;
    }
    add_submenu_page('trinity_audio', 'Post Management', 'Post Management', 'manage_options', 'trinity_audio_post_management', 'trinity_admin_post_management');
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

  function trinity_admin_post_management() {
    require_once __DIR__ . '/inc/post-management.php';
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
    check_ajax_referer('register_action', TRINITY_AUDIO_AJAX_NONCE_NAME);

    trinity_register();
    wp_die();
  }

  function trinity_audio_enable_for_last_20_posts($property) {
    if ($property !== TRINITY_AUDIO) return; // since it will trigger for each property, need to filter out for main one
    if (trinity_get_is_first_changes_saved()) return; // we only care when it's for the first time, so it means that record will be 0 in DB

    $posts = trinity_get_posts(0, 20);

    foreach ($posts as $key => $val) {
      update_metadata('post', $val, TRINITY_AUDIO_ENABLED, 1);
    }
  }

  function trinity_audio_plugin_links($plugin_meta, $plugin_file) {
    if (plugin_basename(__FILE__) == $plugin_file) {
      $row_meta = array(
        'guide'   => '<a href="https://www.trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide" target="_blank" aria-label="Trinity Audio implementation guide">Implementation guide</a>',
        'rate us' => '<a href="https://wordpress.org/support/plugin/trinity-audio/reviews/#new-post" target="_blank" aria-label="Rate Trinity Audio">Rate us</a>'
      );
      return array_merge($plugin_meta, $row_meta);
    }
    return (array)$plugin_meta;
  }

