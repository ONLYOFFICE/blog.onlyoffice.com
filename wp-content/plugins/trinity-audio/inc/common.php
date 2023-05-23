<?php
  require_once __DIR__ . '/constants.php';

  function trinity_curl_get($url, $error_message = '', $die = true, $async = false, $http_args = []) {
    $start = microtime(true);

    $args = [
      'timeout'     => $async ? 0.01 : TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT,
      'blocking'    => !$async,
      'httpversion' => '1.1',
      'sslverify'   => false,
    ];

    $args = array_merge($args, $http_args);

    $response = wp_remote_get($url, $args);

    trinity_report_long_requests($start, $url);

    if ($async) {
      return;
    }
    trinity_handle_error($response, $url, '', $error_message, $die);

    return wp_remote_retrieve_body($response);
  }

  /**
   * function used to make post request, handle `long requests`
   * params description:
   * string error_message
   * boolean async - mode doesn't return anything
   * boolean die - enable die function if request failed
   * array http_args
   * boolean throw_exception - if `die` is false and there is request error throw exception *
   *
   * @param array $options options should contain 'postData' and 'url' params, other parameters error_message, die, async, http_args, throw_exception are optional
   * @return object|void
   * @throws Exception
   */
  function trinity_curl_post(array $options) {
    $start           = microtime(true);
    $postData        = $options['postData'];
    $url             = $options['url'];
    $error_message   = isset($options['error_message']) ? $options['error_message'] : '';
    $die             = isset($options['die']) ? $options['die'] : true;
    $async           = isset($options['async']) ? $options['async'] : false;
    $http_args       = isset($options['http_args']) ? $options['http_args'] : [];
    $throw_exception = isset($options['throw_exception']) ? $options['throw_exception'] : false;

    $args = [
      'body'        => $postData,
      'timeout'     => $async ? 0.01 : TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT,
      'blocking'    => !$async,
      'httpversion' => '1.1',
      'sslverify'   => false,
    ];

    $args = array_merge($args, $http_args);

    $response = wp_remote_post($url, $args);

    trinity_report_long_requests($start, $url);

    if ($async) {
      return;
    }
    $ok = trinity_handle_error($response, $url, $postData, $error_message, $die);
    if (!$ok && $throw_exception && !$die) {
      throw new Exception('Request failed');
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
  }

  function trinity_is_user_admin() {
    return current_user_can('manage_options');
  }

  function trinity_get_install_key() {
    return get_option(TRINITY_AUDIO_INSTALLKEY);
  }

  function trinity_get_view_key() {
    return get_option(TRINITY_AUDIO_VIEWKEY);
  }

  function trinity_get_db_plugin_version() {
    return get_option(TRINITY_AUDIO_PLUGIN_VERSION, []);
  }

  function trinity_get_plugin_migration() {
    return get_option(TRINITY_AUDIO_PLUGIN_MIGRATION, []);
  }

  function trinity_get_source_language() {
    return get_option(TRINITY_AUDIO_SOURCE_LANGUAGE);
  }

  function trinity_get_gender() {
    return get_option(TRINITY_AUDIO_GENDER_ID);
  }

  function trinity_get_new_posts_default() {
    return get_option(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT);
  }

  function trinity_get_player_position() {
    return get_option(TRINITY_AUDIO_PLAYER_POSITION);
  }

  function trinity_get_powered_by() {
    return !!get_option(TRINITY_AUDIO_POWERED_BY);
  }

  function trinity_get_preconnect() {
    return !!get_option(TRINITY_AUDIO_PRECONNECT);
  }

  function trinity_get_player_label() {
    return get_option(TRINITY_AUDIO_PLAYER_LABEL);
  }

  function trinity_get_add_post_title() {
    return get_option(TRINITY_AUDIO_ADD_POST_TITLE);
  }

  function trinity_get_add_post_excerpt() {
    return get_option(TRINITY_AUDIO_ADD_POST_EXCERPT);
  }

  function trinity_get_skip_tags() {
    return get_option(TRINITY_AUDIO_SKIP_TAGS, []);
  }

  function trinity_get_allowed_shortcodes() {
    return get_option(TRINITY_AUDIO_ALLOW_SHORTCODES, []);
  }

  function trinity_get_voice_id() {
    return get_option(TRINITY_AUDIO_VOICE_ID);
  }

  function trinity_get_is_bulk_updated() {
    return get_option(TRINITY_AUDIO_BULK_UPDATE_HEARTBEAT);
  }

  function trinity_get_is_first_changes_saved() {
    return get_option(TRINITY_AUDIO_FIRST_CHANGES_SAVE);
  }

  function trinity_get_check_for_loop() {
    return get_option(TRINITY_AUDIO_CHECK_FOR_LOOP);
  }

  function trinity_set_is_account_key_linked() {
    return add_option(TRINITY_AUDIO_IS_ACCOUNT_KEY_LINKED, true);
  }

  function trinity_get_is_account_key_linked() {
    return get_option(TRINITY_AUDIO_IS_ACCOUNT_KEY_LINKED);
  }

  function trinity_is_migration_v5_failed() {
    return get_option(TRINITY_AUDIO_CONFIGURATION_V5_FAILED);
  }

  function trinity_get_first_time_install() {
    return get_transient(TRINITY_AUDIO_FIRST_TIME_INSTALL);
  }

  function trinity_is_enabled_for_post($post_id) {
    return get_post_meta($post_id, TRINITY_AUDIO_ENABLED, true);
  }

  function trinity_get_plugin_version() {
    $plugin_data = get_plugin_data(__DIR__ . '/../trinity.php');
    return $plugin_data['Version'];
  }

  function trinity_get_voices($languages_url = TRINITY_AUDIO_STANDARD_VOICES_URL) {
    $result = trinity_curl_get($languages_url, trinity_can_not_connect_error_message('Can\'t get list of supported languages.'), false);

    if (!$result) return false;

    $languages = [];

    foreach (json_decode($result) as $lang) {
      $voiceIds = [];
      foreach ($lang->voices as $gender => $voice) {
        $voiceIds[$gender] = $voice->providerVoiceId;
      }

      $languageObj = (object)[
        'name' => $lang->languageName,
        'code' => $lang->code,
        'genders' => array_keys((array)$lang->voices),
        'voices' => $voiceIds
      ];

      if ($lang->country) $languageObj->name .= " ($lang->country)";

      array_push($languages, $languageObj);
    }

    $languages = array_values($languages);

    return $languages;
  }

  function trinity_get_audio_posthash($post_id) {
    if (!trinity_get_add_post_excerpt()) {
      if (trinity_get_add_post_title()) {
        $postHash_type = TRINITY_AUDIO_POST_HASH_CONTENT_TITLE;
      } else {
        $postHash_type = TRINITY_AUDIO_POST_HASH_CONTENT;
      }
    } elseif (trinity_get_add_post_title()) {
      $postHash_type = TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT_TITLE;
    } else {
      $postHash_type = TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT;
    }

    return get_post_meta($post_id, $postHash_type, true);
  }

  function trinity_include_audio_player() {
    $date = trinity_get_date();
    $post_id = $GLOBALS['post']->ID;

    $post_hash = trinity_get_audio_posthash($post_id);

    // TODO: remove after ensure that settings migration works properly. (add version instead or check version by installkey)
    $post_config = [];
    $gender = get_post_meta($post_id, TRINITY_AUDIO_GENDER_ID, true);
    if ($gender) array_push($post_config, 'gender');

    // TODO: remove after ensure that settings migration works properly. (add version instead or check version by installkey)
    $gender = $gender ? $gender : trinity_get_gender();

    $clean_text = trinity_get_clean_text($post_id, trinity_get_add_post_title(), trinity_get_add_post_excerpt());
    $is_no_text = (bool)trinity_is_text_empty($clean_text);

    if ($is_no_text) {
      return false;
    }

    $viewkey = trinity_get_view_key();

    $source_language = get_post_meta($post_id, TRINITY_AUDIO_SOURCE_LANGUAGE, true);

    if ($source_language) array_push($post_config, 'language');

    $post_config = implode(',', $post_config);

    // TODO: remove after ensure that settings migration works properly. (add version instead or check version by installkey)
    $source_language = $source_language ? $source_language : trinity_get_source_language();

    /*
    * Unfortunately AWS Polly and AWS Translate support different namings.
    *
    * @link https://docs.aws.amazon.com/polly/latest/dg/voicelist.html
    * @link https://docs.aws.amazon.com/translate/latest/dg/what-is.html
    */
    $language_code_map = [
      'arb'    => 'ar', // cmn-CN code is zh language (Arabic)
      'cmn-CN' => 'zh', // cmn-CN code is zh language (Chinese)
    ];

    if (isset($language_code_map[$source_language])) {
      $source_language = $language_code_map[$source_language];
    }

    $trinity_tts_wp_config = [
      'cleanText'     => $clean_text,
      'pluginVersion' => trinity_get_plugin_version(),
    ];

    $response = "<script nitro-exclude data-trinity-mount-date='$date'>var TRINITY_TTS_WP_CONFIG = " . json_encode($trinity_tts_wp_config) . ';</script>';

    $playerArgs = ['postHash'    => $post_hash,
                   'language'    => $source_language,
                   'voiceGender' => $gender,
                   'pageURL'     => get_permalink(),
                   'postConfig'  => $post_config
    ];

    $post_voice_id = get_post_meta($post_id, TRINITY_AUDIO_VOICE_ID, true);
    if ($post_voice_id) $playerArgs['voiceId'] = $post_voice_id;

    if (trinity_is_migration_v5_failed()) {
      unset($playerArgs['postConfig']);

      $poweredby = trinity_get_powered_by();
      $poweredby = $poweredby ? $poweredby : 0;

      $playerArgs['poweredby'] = $poweredby;
    }

    $response   .= "<div class='trinityAudioPlaceholder' data-trinity-mount-date='$date'></div>";

    $trinity_url = TRINITY_AUDIO_STARTUP . $viewkey . '/';

    add_filter( 'script_loader_tag', 'trinity_exclude_player_from_caching_plugins', 10, 2 );
    wp_enqueue_script('trinity-player', add_query_arg($playerArgs, $trinity_url));

    return $response;
  }

  function trinity_exclude_player_from_caching_plugins($tag, $handle) {
    $date = trinity_get_date();

    if ($handle === 'trinity-player') {
      remove_filter('script_loader_tag', 'trinity_exclude_player_from_caching_plugins', 10, 2);
      return str_replace('<script', "<script nitro-exclude data-wpfc-render='false' data-trinity-mount-date='$date'", $tag);
    }
    return $tag;
  }

  function trinity_get_clean_text($post_id, $with_title, $with_excerpt) {
    $article_text = '';

    if ($with_title) {
      $article_text = get_the_title($post_id) . TRINITY_AUDIO_DOT;
    }

    if ($with_excerpt) {
      $my_excerpt   = apply_filters('the_excerpt', get_post_field('post_excerpt', $post_id));
      $article_text = $article_text . $my_excerpt . TRINITY_AUDIO_DOT;
    }

    $article_text = $article_text . get_post_field('post_content', $post_id);

    $whitelist_shortcodes = trinity_get_allowed_shortcodes();

    global $shortcode_tags;
    $shortcode_tags_keys = array_keys($shortcode_tags);

    $result_shortcodes_tags = array_filter(
      $shortcode_tags_keys,
      function ($value) use ($whitelist_shortcodes) {
        return !in_array($value, $whitelist_shortcodes);
      }
    );

    $regex   = get_shortcode_regex($result_shortcodes_tags);
    $content = preg_replace("/$regex/", '', $article_text);

    $content = html_entity_decode($content);

    $content = trinity_remove_tags($content);
    $content = strip_tags($content, '<br>');  // strip all except <br>

    // in order to not read a dot in cases like "sample.text"
    $content = preg_replace('/\.[\n|\s\|\r]*<br>/', '. ', $content);
    // in order to not read a comma in cases like "sample,text"
    $content = preg_replace('/\,[\n|\s\|\r]*<br>/', ', ', $content);
    $content = str_replace('<br>', BREAK_MACRO, $content);

    return $content;
  }

  function trinity_remove_tags($text) {
    foreach (trinity_get_skip_tags() as $value) {
      $text = preg_replace('/<' . $value . '>(\s*?)(.*?)(\s*?)<\/' . $value . '>/', '', $text);
    }

    return $text;
  }

  function trinity_audio_ajax_regenerate_tokens() {
    $post_id = sanitize_text_field(wp_unslash($_POST['post_id']));

    trinity_save_post($post_id);

    header('Content-type: application/json');
    die(json_encode(trinity_get_posthashes_for_post_id($post_id)));
  }

  function trinity_save_post($post_id, $die = false, $throw_exception = false) {
    $installkey = trinity_get_install_key();

    if (empty($installkey)) return;

    $clean_text_with_title    = trinity_get_clean_text($post_id, true, false);
    $clean_text_without_title = trinity_get_clean_text($post_id, false, false);
    $clean_text_excerpt       = trinity_get_clean_text($post_id, false, true);
    $clean_text_excerpt_title = trinity_get_clean_text($post_id, true, true);

    trinity_update_audio_post_hash_with_content($clean_text_with_title, TRINITY_AUDIO_POST_HASH_CONTENT_TITLE, $installkey, $post_id, $die, $throw_exception);
    trinity_update_audio_post_hash_with_content($clean_text_without_title, TRINITY_AUDIO_POST_HASH_CONTENT, $installkey, $post_id, $die, $throw_exception);
    trinity_update_audio_post_hash_with_content($clean_text_excerpt, TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT, $installkey, $post_id, $die, $throw_exception);
    trinity_update_audio_post_hash_with_content($clean_text_excerpt_title, TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT_TITLE, $installkey, $post_id, $die, $throw_exception);

    return [
      TRINITY_AUDIO_CONTENT_TITLE         => $clean_text_with_title,
      TRINITY_AUDIO_CONTENT               => $clean_text_without_title,
      TRINITY_AUDIO_CONTENT_EXCERPT_TITLE => $clean_text_excerpt_title,
      TRINITY_AUDIO_CONTENT_EXCERPT       => $clean_text_excerpt,
    ];
  }

  function trinity_get_posthashes_for_post_id($post_id) {
    return [
      TRINITY_AUDIO_CONTENT_TITLE         => get_post_meta($post_id, TRINITY_AUDIO_POST_HASH_CONTENT_TITLE, true),
      TRINITY_AUDIO_CONTENT               => get_post_meta($post_id, TRINITY_AUDIO_POST_HASH_CONTENT, true),
      TRINITY_AUDIO_CONTENT_EXCERPT_TITLE => get_post_meta($post_id, TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT_TITLE, true),
      TRINITY_AUDIO_CONTENT_EXCERPT       => get_post_meta($post_id, TRINITY_AUDIO_POST_HASH_CONTENT_EXCERPT, true),
    ];
  }

  function trinity_update_audio_post_hash_with_content($text, $post_meta_field, $installkey, $post_id, $die, $throw_exception) {
    $postData      = [
      'text'       => $text,
      'installkey' => $installkey,
    ];
    $error_message = $die ? trinity_can_not_connect_error_message('Can\'t get hashes.') : 'Can\'t get hashes';

    $responseData = trinity_curl_post(
      [
        'postData'        => $postData,
        'url'             => TRINITY_AUDIO_POST_HASH_URL,
        'error_message'   => $error_message,
        'die'             => $die,
        'throw_exception' => $throw_exception,
      ]
    );
    update_post_meta($post_id, $post_meta_field, $responseData->postHash);
  }

  /**
   * update post_hash for posts, handle cases with unstable/bad connection
   *
   * @param array $post_ids
   * @return array
   */
  function trinity_update_posts($post_ids) {
    $num_of_success_posts  = 0;
    $num_of_posts          = sizeof($post_ids);
    $post_id_index         = 0;
    $failed_posts          = [];
    $post_retries          = 0;
    $failed_posts_in_a_row = 0;

    update_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED, $num_of_success_posts);
    update_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_FAILED, sizeof($failed_posts));

    while ($num_of_posts > $post_id_index) {
      try {
        update_option(TRINITY_AUDIO_BULK_UPDATE_HEARTBEAT, trinity_get_date());

        $post_id = $post_ids[$post_id_index];

        trinity_save_post($post_id, false, true);

        update_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED, ++$num_of_success_posts);
        update_option(TRINITY_AUDIO_BULK_UPDATE_HEARTBEAT, trinity_get_date());

        // after successful update clean reties
        $failed_posts_in_a_row = 0;
        $post_retries          = 0;
        ++$post_id_index;
      } catch (Exception $error) {
        ++$post_retries;
        $post_id = $post_ids[$post_id_index];

        // if there is a 10 fails in row that mean that API/connection is down
        if ($failed_posts_in_a_row >= TRINITY_AUDIO_MAX_REQUEST_RETRIES_IN_ROW) {
          $error_message = 'Bulk update failed due to bad/unstable connection';
          trinity_log($error_message . '. Please try again later. If you see this message few times in row contact ' . TRINITY_AUDIO_SUPPORT_MESSAGE, '', '', TRINITY_AUDIO_ERROR_TYPES::error);
          die($error_message);
        }

        if ($post_retries >= TRINITY_AUDIO_MAX_POST_REQUEST_RETRIES) {
          trinity_log('Fail to update post after ' . TRINITY_AUDIO_MAX_POST_REQUEST_RETRIES . ' retries', 'Unable to update post hash for post id: ' . $post_id, '', TRINITY_AUDIO_ERROR_TYPES::error);
          array_push($failed_posts, $post_id);
          update_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_FAILED, sizeof($failed_posts));
          $post_retries = 0;
          ++$failed_posts_in_a_row;
          ++$post_id_index;
        }
      }
    }

    return [
      'num_posts_success' => $num_of_success_posts,
      'failed_posts'      => $failed_posts,
    ];
  }

  function trinity_bulk_update() {
    if (trinity_is_bulk_update_in_progress()) {
      return;
    }

    update_option(TRINITY_AUDIO_BULK_UPDATE_HEARTBEAT, trinity_get_date());

    trinity_log('Bulk update started');

    trinity_send_stat(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'upgrade');

    $post_ids  = trinity_get_posts();
    $num_posts = sizeof($post_ids);

    $result = trinity_update_posts($post_ids);
    trinity_send_bulk_update_result($num_posts, $result['num_posts_success'], $result['failed_posts']);
  }

  function trinity_send_bulk_update_result($num_posts, $num_posts_success, $failed_posts) {
    $postData = [
      'installkey'        => trinity_get_install_key(),
      'num_posts'         => $num_posts,
      'num_posts_success' => $num_posts_success,
    ];

    trinity_curl_post(
      [
        'postData'      => $postData,
        'url'           => TRINITY_AUDIO_BULK_UPDATE_URL,
        'error_message' => trinity_can_not_connect_error_message('ERROR_UPDATE_ST1'),
      ]
    );
    trinity_log('Bulk update finished');

    if (sizeof($failed_posts) > 0) {
      trinity_log('Failed to update the following posts:', implode(',', $failed_posts), '', TRINITY_AUDIO_ERROR_TYPES::warn);
    }
  }

  function trinity_save_post_callback($post_id, $post, $updated) {
    // Check if this isn't an auto save.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Check to make sure this is not a new post creation.
    if (!$updated) {
      return;
    }

    // Validate if this post which is being saved is one of supported types. If not, return.
    $post_types_supported = ['post'];
    $post_type            = get_post_type($post_id);
    if (!in_array($post_type, $post_types_supported)) {
      return;
    }

    // If nonce is valid then update post meta
    // If it's not valid then this is probably a quick or bulk edit request in which case we won't update the post meta
    if (isset($_POST[TRINITY_AUDIO_NONCE_NAME]) && wp_verify_nonce($_POST[TRINITY_AUDIO_NONCE_NAME], 'trinity-audio-metabox')) {
      update_post_meta($post_id, TRINITY_AUDIO_ENABLED, (int)isset($_POST[TRINITY_AUDIO_ENABLED]));

      // Update post gender
      update_post_meta($post_id, TRINITY_AUDIO_GENDER_ID, sanitize_text_field($_POST[TRINITY_AUDIO_GENDER_ID]));

      // Update post source language
      update_post_meta($post_id, TRINITY_AUDIO_SOURCE_LANGUAGE, sanitize_text_field($_POST[TRINITY_AUDIO_SOURCE_LANGUAGE]));

      // Update post voice id
      update_post_meta($post_id, TRINITY_AUDIO_VOICE_ID, sanitize_text_field($_POST[TRINITY_AUDIO_VOICE_ID]));
    }

    trinity_save_post($post_id);
  }

  function trinity_save_publisher_token() {
    $postData = [
      'installkey'      => trinity_get_install_key(),
      'publisher_token' => $_POST['publisher_token']
    ];

    $response = trinity_curl_post(
      [
        'postData'  => $postData,
        'url'       => TRINITY_AUDIO_PUBLISHER_TOKEN_URL
      ]
    );

    if (!$response) send_response('ERROR_NETWORK');

    if ($response->code === 'SUCCESS' || $response->code === 'ALREADY_ASSIGNED_PUBLISHER_TOKEN') {
      trinity_set_is_account_key_linked();
    }

    send_response($response->code);
  }

  function trinity_is_bulk_update_in_progress() {
    $value = trinity_get_is_bulk_updated();
    if (!$value) {
      return false;
    }

    $differenceInSeconds = strtotime(trinity_get_date()) - strtotime($value);
    return ($differenceInSeconds < TRINITY_AUDIO_MAX_HEARTBEAT_TIMEOUT);
  }

  function trinity_audio_ajax_bulk_update_status() {
    header('Content-type: application/json');

    $in_progress         = false;
    $processed_posts     = 0;
    $total_posts         = 0;
    $num_of_failed_posts = 0;

    if (trinity_is_bulk_update_in_progress()) {
      $in_progress         = true;
      $processed_posts     = (int)get_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED);
      $num_of_failed_posts = (int)get_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_FAILED);
      $total_posts         = sizeof(trinity_get_posts());
    } else {
      delete_option(TRINITY_AUDIO_BULK_UPDATE_NUM_POSTS_UPDATED);
    }

    die(
    wp_json_encode(
      [
        'inProgress'       => $in_progress,
        'processedPosts'   => $processed_posts,
        'totalPosts'       => $total_posts,
        'numOfFailedPosts' => $num_of_failed_posts
      ]
    )
    );
  }

  function trinity_audio_ajax_contact_us() {
    header('Content-type: application/json');

    $is_include_log = isset($_POST['include_log']);

    $data = trinity_get_env_details();

    /*
      The code below uses cURL because we need to send 2 files and some data
      along with them as multipart/form-data for which there is not an option
      with wp_remote_post. The use-case of cURL in the plugin here was discussed
      with and approved by the WordPress Plugin Review Team in an email
    */
    if ($is_include_log && file_exists(TRINITY_AUDIO_LOG)) {
      $data['log'] = curl_file_create(TRINITY_AUDIO_LOG);
    }
    if ($is_include_log && file_exists(TRINITY_AUDIO_INFO_HTML)) {
      $data['info'] = curl_file_create(TRINITY_AUDIO_INFO_HTML);
    }

    $postData = array_merge($data, filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING));

    $ch = curl_init(TRINITY_AUDIO_CONTACT_US_URL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);

    $response     = curl_exec($ch);
    $responseData = json_decode($response);

    if (!isset($responseData->ok) || $ch === false) {
      http_response_code(500);
      echo 'Error Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE);
      echo 'Error Body: ' . curl_error($ch);
    }

    wp_die($response);
  }

  function trinity_get_posts() {
    return get_posts(
      [
        'fields'         => 'ids', // Only get post IDs
        'posts_per_page' => -1,
        'post_type'      => ['post'],
      ]
    );
  }

  function trinity_get_all_plugins_installed() {
    if (is_admin()) {
      if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }

      $all_plugins = get_plugins();
      $plugins     = [];

      foreach ($all_plugins as $plugin) {
        array_push($plugins, $plugin['Name'] . '-' . $plugin['Version']);
      }

      return implode(', ', $plugins);
    }

    return 'unknown';
  }

  function trinity_get_user_email() {
    $current_user = wp_get_current_user();
    if (!($current_user instanceof WP_User)) {
      return 'NOT_LOGIN';
    }

    return $current_user->user_email;
  }

  function trinity_get_user_name() {
    $current_user = wp_get_current_user();
    if (!($current_user instanceof WP_User)) {
      return '';
    }

    return $current_user->user_firstname . ' ' . $current_user->user_lastname;
  }

  function trinity_get_wp_version() {
    global $wp_version;

    return $wp_version;
  }

  function trinity_get_env_details() {
    return [
      'wp_version'         => trinity_get_wp_version(),
      'plugin_version'     => trinity_get_plugin_version(),
      'php_version'        => phpversion(),
      'all_plugins'        => trinity_get_all_plugins_installed(),
      'installkey'         => trinity_get_install_key(),
      'site_domain'        => parse_url(get_site_url(), PHP_URL_HOST),
      'email'              => trinity_get_user_email(),
      'details_event_type' => 'not_set',
    ];
  }

  function trinity_send_stat($url, $event_type = '', $die = true) {
    $data = trinity_get_env_details();
    if ($event_type) {
      $data['details_event_type'] = $event_type;
    }

    if ($event_type = 'activating' && empty($data['installkey']) && !empty($_POST['recover_installkey'])) {
      $data['installkey'] = $_POST['recover_installkey'];
    }

    if (!empty($_POST['publisher_token'])) $data['publisher_token'] = $_POST['publisher_token'];
    if (!empty($_POST['email_subscription'])) $data['email_subscription'] = $_POST['email_subscription'];

    return trinity_curl_post(
      [
        'postData'      => $data,
        'url'           => $url,
        'error_message' => trinity_can_not_connect_error_message('Can\'t update plugin details.'),
        'die'           => $die,
      ]
    );
  }

  function trinity_send_stat_update_settings() {
    $data = [
            'installkey'                => trinity_get_install_key(), // need for auth
            'powered_by'                => trinity_get_powered_by(),
            'voice_id'                  => trinity_get_voice_id(),
    ];

    return trinity_curl_post(
      [
        'postData'      => $data,
        'url'           => TRINITY_AUDIO_UPDATE_PLUGIN_CONFIG_URL,
        'error_message' => trinity_can_not_connect_error_message('Can\'t update plugin details.'),
        'die'           => false,
      ]
    );
  }

  function trinity_audio_ajax_update_unit_config() {
    $data = [
      'installkey' => trinity_get_install_key(),
      'speed' => $_GET['speed'],
      'language' => $_GET['language'],
      'voiceStyle' => $_GET['voiceStyle'],
      'engine' => $_GET['engine'],
      'themeId' => $_GET['themeId'],
      'voice' => $_GET['voice'],
      'fab' => $_GET['fab'],
      'powered_by' => $_GET['poweredBy'],
      'gender' => $_GET['gender']
    ];

    return trinity_curl_post(
      [
        'postData'      => $data,
        'url'           => TRINITY_AUDIO_UPDATE_FULL_UNIT_CONFIG_URL,
        'error_message' => trinity_can_not_connect_error_message('Can\'t update plugin details.'),
        'die'           => false,
      ]
    );
  }

  function trinity_send_stat_migrate_v5_settings() {
    $data = [
            'installkey'                => trinity_get_install_key(), // need for auth
            'source_language'           => trinity_get_source_language(),
            'powered_by'                => trinity_get_powered_by(),
            'gender'                    => trinity_get_gender(),
    ];

    return trinity_curl_post(
      [
        'postData'      => $data,
        'url'           => TRINITY_AUDIO_UPDATE_PLUGIN_MIGRATION_URL,
        'error_message' => trinity_can_not_connect_error_message('Can\'t migrate plugin details.'),
        'die'           => false,
      ]
    );
  }

  function trinity_send_stat_metrics() {
    $data = [
      'metric'  => $_POST['metric'],
      'additionalData' => $_POST['additionalData']
    ];

    return trinity_curl_post(
      [
        'postData'      => $data,
        'url'           => TRINITY_AUDIO_METRICS_URL,
        'die'           => false,
      ]
    );
  }

  function trinity_audio_ajax_remove_post_banner() {
    update_option(TRINITY_AUDIO_REMOVE_POST_BANNER, '0');
  }

  function send_response($code) {
    $env_details    = trinity_get_env_details();
    $site           = $env_details['site_domain'];

    $register_response = [
      'ERROR_NETWORK'         => 'Can\'t activate plugin. Please contact us ' . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . " and provide your domain: <strong>{$site}</strong>",

      'ERROR'                 => 'Can\'t activate plugin. Please contact us ' . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . " and provide your domain: <strong>{$site}</strong>",

      'ALREADY_REGISTERED'    => "It seems like your site <strong>{$site}</strong> is already registered to our services. In order to protect your assets, we do not allow duplicated registrations. <br/> If you've registered before and migrated into a new database/hosting service - please use the form below to insert the <span class='bold-text'>Install Key</span> if you have one, otherwise contact us at " . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . ' to help resolve this issue.',

      'WRONG_INSTALLKEY'      => 'We can see that the following value has changed in your database <strong>wp_options.trinity_audio_installkey</strong>. If you know how to revert the change, please do. If not, please write to us at ' . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . ' and we will respond as fast as we can.',

      'WRONG_PUBLISHER_TOKEN' => '<span class="bold-text">Account Key</span> is not found - please verify you have the correct one. If the problem persists, please write to us at ' . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . ' and we will respond as fast as we can.',

      'ALREADY_ASSIGNED_PUBLISHER_TOKEN' => "It seems like your installation is already assigned to your Trinity Account. If it's not reflected on the <a href='" . trinity_add_utm_to_url(TRINITY_AUDIO_DASHBOARD_URL) . "' target='_blank'>Trinity Dashboard</a> please contact us at " . TRINITY_AUDIO_SUPPORT_EMAIL_LINK . ' to help resolve this issue.',

      'SUCCESS'               => NULL
    ];

    die(json_encode(
      [
        'code'    => $code,
        'message' => $register_response[$code]
      ]));
  }

  function handle_register_response($response) {
    $response_code  = 'ERROR_NETWORK'; // if there is a `response`, $response_code should be overridden

    if (!$response) {
      send_response($response_code);
    }

    $response_code  = $response->code;

    if ($response_code === 'SUCCESS') {
      $response_install_key = $response->data->installkey;
      $response_view_key    = $response->data->viewkey;

      /*
      Check if response exists, so we don't override with empty values if smth went wrong.
      User can have installkey and could want to restore viewkey.
      */
      if ($response_install_key) {
        update_option(TRINITY_AUDIO_INSTALLKEY, $response_install_key);
      }
      if ($response_view_key) {
        update_option(TRINITY_AUDIO_VIEWKEY, $response_view_key);
      }

      set_transient(TRINITY_AUDIO_FIRST_TIME_INSTALL, true, 60);

      trinity_send_stat_update_settings();

      send_response($response_code);
    }

    send_response($response_code);
  }

  function trinity_register() {
    $install_key = trinity_get_install_key();
    $view_key    = trinity_get_view_key();

    if ($install_key && $view_key) {
      trinity_send_stat(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'reactivating', false);
      return true;
    }

    $response = trinity_send_stat(TRINITY_AUDIO_KEYS_URL, 'activating', false);

    handle_register_response($response);
  }

  function trinity_is_text_empty($text) {
    $text = str_replace('.', '', $text);
    return !trim(preg_replace('/\s+/', ' ', $text));
  }

  function trinity_hook_header() {
    if (trinity_get_preconnect()) {
      echo '<link href="https://trinitymedia.ai/" rel="preconnect" crossorigin="anonymous" />' . "\n";
      echo '<link href="https://vd.trinitymedia.ai/" rel="preconnect" crossorigin="anonymous" />' . "\n";
    }
  }

  function trinity_can_not_connect_error_message($error = '') {
    return $error . ' Can\'t connect to Trinity Audio! Please check <a href="/wp-admin/admin.php?page=trinity_audio_logs">logs</a> or contact ' . TRINITY_AUDIO_SUPPORT_MESSAGE;
  }

  function trinity_get_notice_error_message($error = '') {
    return trinity_can_not_connect_error_message("<div class='notice notice-error'><p>" . $error . "<br>") . "</p><p></p></div>";
  }

  function trinity_registered() {
    $install_key = trinity_get_install_key();
    $view_key    = trinity_get_view_key();
    if (empty($install_key) || empty($view_key)) {
      return false;
    }
    return true;
  }

  function trinity_get_package_data() {
    $error_msg = trinity_get_notice_error_message("Can't get plan data.");
    $result    = trinity_curl_get(TRINITY_AUDIO_CREDITS_URL . '?installkey=' . trinity_get_install_key(), $error_msg, false);

    if (!$result) die($error_msg);

    return json_decode($result);
  }

  function trinity_get_unit_config_from_trinity() {
    $error_msg = trinity_get_notice_error_message("Can't get plugin configuration.");
    $result    = trinity_curl_get(TRINITY_AUDIO_UPDATE_PLUGIN_CONFIG_URL . '?installkey=' . trinity_get_install_key(), $error_msg, false);

    if (!$result) die($error_msg);

    return json_decode($result);
  }

  function notifications($package_data) {
    $response = trinity_curl_get(TRINITY_AUDIO_NOTIFICATIONS_URL . '/?type=general', '', false);

    if ($response) {
        $notification = json_decode($response);
        if (property_exists($notification, 'message')) {
  	      $notification = json_decode($notification->message);
        }

  	    if ($notification && property_exists($notification, 'message_html')) {
  	      echo htmlspecialchars_decode($notification->message_html);
        }
    }

    if (!$package_data) return;

    if ($package_data->capType === 'articles'
        && is_numeric($package_data->used)
        && is_numeric($package_data->packageLimit)
        && $package_data->used >= $package_data->packageLimit)
      echo "<div class='trinity-notification'>
							<span>
								You have a maxed out your plan usage!
								<a class='bold-text' target='_blank' href='" . trinity_add_utm_to_url(TRINITY_AUDIO_PRICING_URL) . "'>Upgrade your plan</a>		
							</span>
							<span class='trinity-notification-close'></span>
						</div>";
  }

  function trinity_show_bulk_progress() {
    echo "<div class='trinity-bulk-update-wrapper trinity-notification'>
            <span class='trinity-bulk-update status error'>A problem occurred while updating articles values. Please try again later.</span>
  
            <span class='trinity-bulk-update status progress'>
              <span class='trinity-bulk-message'>We're updating your content settings. The plugin may experience issue while this is ongoing. You may navigate away from this page.</span>

              <div class='trinity-bulk-count-wrapper'>
                <span class='trinity-bulk-posts'></span>
                <span class='trinity-bulk-bar'>
                  <div class='trinity-bulk-bar-inner'></div>
                </span>
              </div>
            </span>
          </div>";
  }

  function trinity_add_utm_to_url($url, $utm_medium = 'wp_admin', $utm_campaign = '') {
    return add_query_arg(array(
      'utm_medium' => urlencode($utm_medium),
      'utm_source' => urlencode(get_site_url()),
      'utm_campaign' => urlencode($utm_campaign)
    ), $url);
  }

  function trinity_get_upgrade_url() {
    if (trinity_get_is_account_key_linked()) return TRINITY_AUDIO_UPGRADE_URL;
    return TRINITY_AUDIO_PRICING_URL;
  }

  function trinity_show_articles_usage($package_data) {
    $cap_type = $package_data->capType;

    if ($cap_type === 'chars') {
      ?>
      <div class="section-form-title">
        Credits left:
      </div>
      <?php
      echo "<p>$package_data->credits</p>";
      echo '<p class="description">Shows the amount of credits available to generate audio for new posts</p>';
    } else if ($cap_type === 'articles') {
      ?>
      <div class="section-form-title">
        Number of articles:
      </div>
      <?php
      echo "<p><span class='bold-text'>{$package_data->used}</span> / {$package_data->packageLimit}</p>";
      echo '<p class="description">Shows the amount of articles used</p>';
    } else if ($cap_type === 'no_limit') {
      echo '<p>Unlimited</p>';
      echo '<p class="description">Shows the amount of articles used</p>';
    } else  {
      echo '<p>N/A</p>';
      echo '<p class="description"></p>';
    }
  }

  function trinity_get_languages($is_premium = false, $is_package_known = false) {
    $cached_languages = get_transient(TRINITY_AUDIO_LANGUAGES_CACHE);
    $languages = json_decode($cached_languages);

    if ($languages) return $languages;

    if ($is_package_known === false) {
      $package_data = trinity_get_package_data();
      $is_premium = $package_data->package->isPremium;
    }

    $languages_url = $is_premium ? TRINITY_AUDIO_EXTENDED_VOICES_URL : TRINITY_AUDIO_STANDARD_VOICES_URL;

    $languages = trinity_get_voices($languages_url);

    set_transient(TRINITY_AUDIO_LANGUAGES_CACHE, json_encode($languages), 86400);

    return $languages;
  }

  function trinity_post_management_banner() {
    $messages = [
      "Get additional credits to convert more content into audio each month.",
      "Create and edit pronunciation rules for accuracy and the highest level of audio experience.",
      "Get access to premium and natural-sounding AI voices.",
      "Get access to your personal dashboard with usability analytics.",
      "Select the player’s theme that best suits your website’s branding elements."
    ];
    $message = array_rand($messages);
    $show_banner = get_option(TRINITY_AUDIO_REMOVE_POST_BANNER);

    if ($show_banner !== '0'): ?>
      <div class="container">
        <div class="header">
          <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 viewBox="0 0 44 44" style="enable-background:new 0 0 44 44;" xml:space="preserve"><g>
                <g>
                  <path d="M6,22c0-8.8,7.2-16,16-16s16,7.2,16,16s-7.2,16-16,16S6,30.8,6,22 M4,22c0,9.9,8.1,18,18,18s18-8.1,18-18S31.9,4,22,4S4,12.1,4,22L4,22z"/>
                </g>
                <g>
                  <path id="trinity-outer-triangle"
                        d="M13.3,35.9V8.1L38.2,22L13.3,35.9z M15.3,11.5v20.9L34.1,22L15.3,11.5z"/>
                  <path id="trinity-inner-triangle"
                        d="M17.6,28.6V15.4L29.5,22L17.6,28.6z M19.6,18.8v6.4l5.8-3.2L19.6,18.8z"/>
                </g>
              </g></svg>
          </div>
          <span class="header-text">
            <div>TRINITY</div>
            <div>AUDIO</div>
          </span>
          <span class="close-icon" onclick="trinityRemovePostBanner();trinitySendMetric('wordpress.post.banner.close');">
              <svg xmlns="http://www.w3.org/2000/svg" width="14.252" height="14.252" viewBox="0 0 14.252 14.252">
                <rect width="17.995" height="2.159" transform="translate(1.527) rotate(45)" fill="#c8c8c8"/>
                <rect width="17.995" height="2.159" transform="translate(14.252 1.527) rotate(135)" fill="#c8c8c8"/>
              </svg>
            </span>
        </div>

        <p class="message"><?= $messages[$message] ?></p>

        <div>
          <a onclick="trinitySendMetricMeta('wordpress.post.banner.visit', '<?= trinity_get_plugin_version() ?>');"
                href="<?= trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_post', 'upgrade_banner') ?>"
                class="upgrade-button" target="_blank">
            Upgrade to premium
          </a>
          <div class="footnote">30-days money back guarantee.</div>
        </div>
      </div>
  <?php endif;
  }
