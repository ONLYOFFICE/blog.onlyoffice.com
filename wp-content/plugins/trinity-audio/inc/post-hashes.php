<?php
  /**
   * Post hashes update functionality.
   *
   * Functionality for generating post hashes that are required for player to playback audio.
   * Generating hashes consists from 3 parts:
   * 1. Get text from DB that are going to be played and get sha1 hashes
   *    - The format is an object, where key is postId and values if object which consists with the following feidls:
   *      - content
   *      - title + content
   *      - excerpt + content
   *      - title + excerpt + content
   * 2. Send that data as one request to Trinity service with private installkey in order to get final post hashes which will be used in player
   * 3. Write them into DB
   *
   * During each stage (calculating_hashes and writing_hashes) current stage is written into DB with total posts updated,
   * failed and current performance (ability to process posts per second). Later these numbers are used in post hashes batch functionality.
   *
   * Pay attention, that functionality is used by Post hashes bulk update, in case we update all posts hashes.
   * In case we update only 1 particular post - functionality here is used directly.
   */

  require_once __DIR__ . '/common.php';
  require_once __DIR__ . '/post-hashes-bulk-update.php';
  require_once __DIR__ . '/text.php';

  /**
   * Start posts hashes bulk update
   * @param array $post_ids posts ids to be processed
   * @return void
   */
  function trinity_ph_update_bulk_update(array $post_ids, $total_posts, $updated_posts, $batch_retry): int {
    $total_posts_to_process = count($post_ids);
    trinity_log("Bulk update - processing $total_posts_to_process posts...");

    // main function to process posts and get hashes
    return trinity_ph_update($post_ids, $total_posts, $updated_posts, $batch_retry, true);
  }

  /**
   * Main post hashes function that runs 3 phases like calculating, requesting and writing into DB
   * @param $post_ids
   * @return number
   */
  function trinity_ph_update($post_ids, $total_posts = 1, $updated_posts = 0, $batch_retry = 0, $is_bulk = false): int {
    $posts_amount = count($post_ids);
    trinity_log("Post hashes updating for $posts_amount post(s) ⚙️");

    // stage #1 - calculating hashes
    $calculated_hashes = trinity_ph_update_calculate_hashes($post_ids);

    // stage #2 - requesting hashes
    $requested_hashes = trinity_ph_update_requesting_hashes($calculated_hashes, $total_posts, $updated_posts, $batch_retry, $is_bulk);

    if ($requested_hashes === -1) return -1; // isStop

    if (!$requested_hashes) {
      trinity_log("Can't get hashes from server", '', '', TRINITY_AUDIO_ERROR_TYPES::error);
      return 0;
    }

    // stage #3 - writing hashes
    trinity_ph_update_writing_hashes($requested_hashes['hashes']);

    trinity_log("Post hashes updated for $posts_amount post(s) ✅");

    return 1; // OK
  }

  /**
   * First phase of post hashes - calculate hashes from posts texts
   * @param array $post_ids
   * @return array
   */
  function trinity_ph_update_calculate_hashes(array $post_ids): array {
    trinity_log('Post hashes update - calculation hashes...');

    $posts_total_amount   = sizeof($post_ids);
    $posts_success_amount = 0;
    $post_id_index        = 0;

    $whitelist_shortcodes = trinity_get_allowed_shortcodes();

    $hashes = [];

    while ($posts_total_amount > $post_id_index) {
      try {
        $post_id = $post_ids[$post_id_index];

        $title   = get_the_title($post_id);
        $content = get_post_field('post_content', $post_id);

        $result_text_title_content = trinity_get_clean_text($title, $content, $whitelist_shortcodes);

        $hashes[$post_id] = [
          TRINITY_AUDIO_POST_META_MAP[TRINITY_AUDIO_TITLE_CONTENT] => sha1($result_text_title_content)
        ];

        ++$post_id_index;
        ++$posts_success_amount;
      } catch (Exception $error) {
        trinity_log("Error while calculating hashes, post hash id: $post_ids[$post_id_index]", '', '', TRINITY_AUDIO_ERROR_TYPES::error);
      }
    }

    trinity_log('Post hashes update - calculation hashes done');

    return $hashes;
  }

  /**
   * Request final hashes from Trinity service endpoint
   * @param $hashes
   * @return null
   * @throws Exception
   */
  function trinity_ph_update_requesting_hashes($hashes, $total_posts, $updated_posts, $batch_retry, $is_bulk) {
    trinity_log('Post hashes update - requesting hashes...');

    $env_details = trinity_get_env_details();
    $installkey  = trinity_get_install_key();

    $post_data = [
      'hashes'        => $hashes,
      'totalPosts'    => $total_posts,
      'updatedPosts'  => $updated_posts,
      'batchRetry'    => $batch_retry,
      'isBulk'        => $is_bulk,
      'installkey'    => $installkey,
      'pluginVersion' => $env_details['plugin_version']
    ];

    $response = trinity_curl_post(
      [
        'post_data'       => gzencode(json_encode($post_data)), // compress with gzip, for 2k of posts - 450kb => 225kb
        'url'             => TRINITY_AUDIO_POST_HASH_URL_V2,
        'error_message'   => "Can't get hashes from server",
        'die'             => false,
        'throw_exception' => false,
        'http_args'       => [
          'headers' => [
            'Content-Encoding' => 'gzip',
            'Content-Type'     => 'application/json'
          ]
        ]
      ]
    );

    if (!$response) return trinity_ph_update_requesting_hashes_report_error('Can\'t POST to ' . TRINITY_AUDIO_POST_HASH_URL_V2, $post_data);
    if (isset($response['isStop']) && $is_bulk) return -1;
    if (!isset($response['hashes'])) return trinity_ph_update_requesting_hashes_report_error('Did not get hashes back!', $post_data);

    trinity_log('Post hashes update - requesting hashes done');

    return $response;
  }

  function trinity_ph_update_requesting_hashes_report_error($error_message, $post_data) {
    trinity_log($error_message, '', trinity_dump_object($post_data), TRINITY_AUDIO_ERROR_TYPES::error);
  }

  /**
   * Writing final post hashes into DB
   * @param $hashes
   * @return void
   */
  function trinity_ph_update_writing_hashes($hashes) {
    trinity_log('Post hashes update - writing hashes...');

    $hashes_amount = count($hashes);

    // calculate how many times we have to update state (in order to reflect on UI). Should be >= 1 < 10
    $to_update_times = round($hashes_amount / 10);
    if ($to_update_times < 1) $to_update_times = 1;

    $posts_success_amount = 0;
    foreach ($hashes as $key => $value) {
      ++$posts_success_amount;
      update_post_meta($key, 'trinity_audio_post_hash', $value); // we can't rely on return value false here, since it will return false when data was not change
    }

    trinity_log('Post hashes update - writing hashes done');
  }

  function trinity_ph_update_regenerate_tokens() {
    check_ajax_referer('regenerate_tokens_action', TRINITY_AUDIO_AJAX_NONCE_NAME);

    $post_id = sanitize_text_field(wp_unslash($_POST['post_id']));

    if (!trinity_ph_update([$post_id])) die(json_encode([], JSON_FORCE_OBJECT));

    header('Content-type: application/json');
    die(json_encode(trinity_ph_get_posthashes_for_post_id($post_id)));
  }

  function trinity_ph_get_posthashes_for_post_id($post_id) {
    return get_post_meta($post_id, TRINITY_AUDIO_POST_HASH, true);
  }

  function trinity_ph_update_save_post_callback($post_id, $post, $updated) {
    // Check if this isn't an auto save.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // avoid doing something when post moved to Trash or was just created and in draft (before save)
    if ($post->post_type === 'revision' || $post->post_status === 'auto-draft') {
      return;
    }

    $is_enable = (int)isset($_POST[TRINITY_AUDIO_ENABLED]);

    // if it's a new post
    if (!$updated) {
      // in case we have enabled option to enable player on post API creation and have default new posts enabled - then do it
      if (trinity_get_enable_for_api() && trinity_get_new_posts_default()) {
        trinity_audio_enable_player_for_post_id($post_id, 1);
        trinity_ph_update([$post_id]);
      }

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
      trinity_audio_enable_player_for_post_id($post_id, $is_enable);

      // Update post source language
      update_post_meta($post_id, TRINITY_AUDIO_SOURCE_LANGUAGE, sanitize_text_field($_POST[TRINITY_AUDIO_SOURCE_LANGUAGE]));

      // Update post voice id
      update_post_meta($post_id, TRINITY_AUDIO_VOICE_ID, sanitize_text_field($_POST[TRINITY_AUDIO_VOICE_ID]));
    }

    trinity_ph_update([$post_id]);
  }

  function trinity_audio_enable_player_for_post_id($post_id, $is_enable) {
    update_post_meta($post_id, TRINITY_AUDIO_ENABLED, $is_enable);
  }

  /** Return post hash for postId
   * @param $post_id
   * @return mixed|void
   */
  function trinity_ph_get_audio_posthash($post_id) {
    $post_hashes = trinity_ph_get_posthashes_for_post_id($post_id);
    if (!$post_hashes) return;

    return $post_hashes[TRINITY_AUDIO_POST_META_MAP[TRINITY_AUDIO_TITLE_CONTENT]];
  }

  function trinity_ph_cleanup() {
    trinity_phbu_set_batch_retry(0);
  }
