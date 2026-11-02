<?php
  require_once __DIR__ . '/post-hashes.php';

  const TRINITY_AUDIO_PHBU_BATCH_RETRY       = 'trinity_audio_phbu_batch_retry';
  const TRINITY_AUDIO_PHBU_HEARTBEAT         = 'trinity_audio_phbu_heartbeat';
  const TRINITY_AUDIO_PHBU_IN_PROGRESS       = 'trinity_audio_phbu_in_progress';
  const TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM   = 'trinity_audio_phbu_total_posts_num';
  const TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM = 'trinity_audio_phbu_updated_posts_num';
  const TRINITY_AUDIO_PHBU_BULK_SIZE         = 'trinity_audio_phbu_bulk_size';
  const TRINITY_AUDIO_PHBU_FAILED_POSTS      = 'trinity_audio_phbu_failed_posts';
  // for UI to show at the very end, since we will cleanup our values, so need to keep then for short period of time
  const TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM_UI   = 'trinity_audio_phbu_total_posts_num_ui';
  const TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM_UI = 'trinity_audio_phbu_updated_posts_num_ui';

  const TRINITY_AUDIO_PHBU_BULK_UPDATE_DONE = 'trinity_audio_phbu_bulk_update_done';

  const TRINITY_MAX_POSTS_PER_BATCH = 200;
  const TTL_FOR_UI                  = 4;

  function trinity_phbu_continue() {
    trinity_phbu_start(true);
  }

  // we have a separate function which has nonce checking, since we also run trinity_phbu_start() it via cron which doesn't require nonce checking
  function trinity_phbu_start_from_admin() {
    check_ajax_referer('audio_bulk_update_action', TRINITY_AUDIO_AJAX_NONCE_NAME);

    trinity_phbu_start();
  }

  /**
   * Manual triggering bulk update via pressing Save button (if skip HTML tags or shortcodes were changed)
   * or via init wp hook with is_continue flag as true
   * @return void
   */
  function trinity_phbu_start($is_continue = false) {
    // if there's already a running process, exit
    if (trinity_phbu_is_bulk_update_alive()) return;

    if ($is_continue) {
      // if there isn't one in progress, no need to recover
      if (!trinity_phbu_get_in_progress()) return;
      if (!trinity_phbu_is_iteration_allowed()) return;

      // set it ASAP
      trinity_phbu_set_process_in_progress();

      // we were interrupted - so this is a batch retry
      trinity_phbu_set_batch_retry(trinity_phbu_get_batch_retry() + 1);
      trinity_log('Triggered from wp_action to continue bulk update...', '', '', TRINITY_AUDIO_ERROR_TYPES::warn);
      trinity_send_graphite_metric('wordpress.bulk-update.v2.continue');
    } else {
      // set it ASAP
      trinity_phbu_set_process_in_progress();

      trinity_log('Triggered bulk update...', '', '');

      trinity_send_graphite_metric('wordpress.bulk-update.v2.start');
      trinity_phbu_reset();
    }

    // get posts that are remains to process
    $updated_posts = trinity_phbu_get_updated_posts_num();
    $batch_retry   = trinity_phbu_get_batch_retry();
    $bulk_size     = trinity_phbu_get_bulk_size_value($batch_retry);
    $post_ids      = trinity_get_posts($updated_posts, $bulk_size);

    $total_posts = trinity_phbu_get_total_posts();
    trinity_phbu_set_total_posts_num($total_posts);

    while (!empty($post_ids)) {
      trinity_phbu_update_heartbeat();

      $next_posts_count = sizeof($post_ids);
      trinity_log("Updated posts: $updated_posts/$total_posts. Updating next $next_posts_count posts... Batch retry: $batch_retry");

      // perform bulk-update for these posts
      $result_code = trinity_ph_update_bulk_update($post_ids, $total_posts, $updated_posts, $batch_retry);
      if ($result_code === -1) {
        trinity_phbu_stopped_by_server();
        return;
      }

      // update progress
      $updated_posts += count($post_ids);
      trinity_phbu_set_updated_posts_num($updated_posts);
      trinity_phbu_set_batch_retry(0);

      // next batch!
      $post_ids = trinity_get_posts($updated_posts, $bulk_size);
    }

    trinity_send_graphite_metric('wordpress.bulk-update.v2.success');
    trinity_phbu_done();
  }

  function trinity_phbu_stopped_by_server() {
    trinity_log('Stopped by server', '', '', TRINITY_AUDIO_ERROR_TYPES::warn);
    trinity_send_graphite_metric('wordpress.bulk-update.v2.stopped-by-server');
    trinity_phbu_cleanup();
  }

  function trinity_phbu_set_process_in_progress() {
    trinity_phbu_set_in_progress(true);

    // marking as running process to avoid multiple runs
    trinity_phbu_update_heartbeat();
  }

  function trinity_phbu_is_iteration_allowed(): bool {
    $retry               = trinity_phbu_get_batch_retry();
    $max_allowed_retries = 10;

    if ($retry > $max_allowed_retries) {
      trinity_log("Post hashes bulk update reached iteration to max ($max_allowed_retries). Stopping bulk update process", '', '', TRINITY_AUDIO_ERROR_TYPES::warn);
      return false;
    }

    return true;
  }

  function trinity_phbu_done() {
    trinity_log('Bulk update finished 🎉');

    // send success info to our end
    trinity_phbu_send_bulk_update_result();

    trinity_phbu_sync_data_for_ui();

    trinity_phbu_cleanup();

    // will be indicator later that we've done bulk-update at least once, need for UI
    trinity_phbu_set_bulk_update_done();
  }

  function trinity_phbu_is_bulk_update_in_progress(int $diff): bool {
    $heartbeat_ts = trinity_phbu_get_heartbeat();
    if (!$heartbeat_ts) return false;

    $difference_in_seconds = time() - $heartbeat_ts;

    return $difference_in_seconds < $diff;
  }

  function trinity_phbu_get_total_posts(): int {
    return count(trinity_get_posts());
  }

  /**
   * Send success info to our end
   * @return void
   * @throws Exception
   */
  function trinity_phbu_send_bulk_update_result() {
    $failed_posts = trinity_phbu_get_failed_posts();

    $post_data = [
      'installkey'        => trinity_get_install_key(),
      'num_posts'         => trinity_phbu_get_total_posts_num(),
      'num_posts_success' => trinity_phbu_get_updated_posts_num(),
    ];

    trinity_curl_post(
      [
        'post_data'     => $post_data,
        'url'           => TRINITY_AUDIO_BULK_UPDATE_URL,
        'error_message' => trinity_can_not_connect_error_message('ERROR_UPDATE_ST1'),
      ]
    );

    if (sizeof($failed_posts) > 0) {
      trinity_log('Failed to update the following posts:', implode(',', $failed_posts), '', TRINITY_AUDIO_ERROR_TYPES::warn);
    }
  }

  function trinity_phbu_is_bulk_update_alive(): bool {
    return trinity_phbu_is_bulk_update_in_progress(TRINITY_AUDIO_MAX_HEARTBEAT_TIMEOUT);
  }

  function trinity_phbu_get_status() {
    check_ajax_referer('audio_bulk_update_status_action', TRINITY_AUDIO_AJAX_NONCE_NAME);

    header('Content-type: application/json');

    die(wp_json_encode(trinity_phbu_get_status_data()));
  }

  function trinity_phbu_get_status_data(): array {
    $total_posts = get_transient(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM_UI);

    return [
      'inProgress'     => !!(trinity_phbu_is_bulk_update_in_progress(7) ?: $total_posts), // since heartbeat is removing right away after its done, we can rely on UI synced data
      'totalPosts'     => intval(trinity_phbu_get_total_posts_num() ?: $total_posts),
      'processedPosts' => intval(trinity_phbu_get_updated_posts_num() ?: get_transient(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM_UI))
    ];
  }

  function trinity_phbu_get_heartbeat(): int {
    return (int)get_option(TRINITY_AUDIO_PHBU_HEARTBEAT);
  }

  function trinity_phbu_update_heartbeat() {
    trinity_phbu_set_heartbeat(time());
  }

  function trinity_phbu_set_heartbeat($value) {
    update_option(TRINITY_AUDIO_PHBU_HEARTBEAT, $value);
  }

  function trinity_phbu_get_total_posts_num(): int {
    return (int)get_option(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM, 0);
  }

  function trinity_phbu_get_in_progress(): bool {
    return (bool)get_option(TRINITY_AUDIO_PHBU_IN_PROGRESS, false);
  }

  function trinity_phbu_set_in_progress($value) {
    update_option(TRINITY_AUDIO_PHBU_IN_PROGRESS, $value);
  }

  function trinity_phbu_set_total_posts_num($value) {
    update_option(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM, $value);
  }

  function trinity_phbu_get_updated_posts_num(): int {
    return (int)get_option(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM, 0);
  }

  function trinity_phbu_set_updated_posts_num($value) {
    return update_option(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM, $value);
  }

  function trinity_phbu_get_bulk_size_value($retry_count): int {
    $current_value = trinity_phbu_get_bulk_size();

    // this indicates some issue with progress - lets decrease the bulk size
    // why ">=2"? first retry could be after we've done several successful bulks in this iteration, so no need to reduce size (yet)
    if ($retry_count >= 2) {
      $current_value = max($current_value * 0.7, 1); //never less than 1
      trinity_phbu_set_bulk_size($current_value); // setting it for next time
    }

    return $current_value;
  }

  function trinity_phbu_get_bulk_size(): int {
    return (int)get_option(TRINITY_AUDIO_PHBU_BULK_SIZE, TRINITY_MAX_POSTS_PER_BATCH);
  }

  function trinity_phbu_set_bulk_size($value) {
    return update_option(TRINITY_AUDIO_PHBU_BULK_SIZE, $value);
  }

  function trinity_phbu_get_failed_posts(): array {
    return get_option(TRINITY_AUDIO_PHBU_FAILED_POSTS, []);
  }

  function trinity_phbu_set_batch_retry($number) {
    update_option(TRINITY_AUDIO_PHBU_BATCH_RETRY, $number);
  }

  function trinity_phbu_set_bulk_update_done() {
    update_option(TRINITY_AUDIO_PHBU_BULK_UPDATE_DONE, true);
  }

  function trinity_phbu_is_bulk_update_done(): bool {
    return get_option(TRINITY_AUDIO_PHBU_BULK_UPDATE_DONE, false);
  }

  function trinity_phbu_get_batch_retry(): int {
    return (int)get_option(TRINITY_AUDIO_PHBU_BATCH_RETRY, 0);
  }

  function trinity_phbu_sync_data_for_ui() {
    set_transient(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM_UI, trinity_phbu_get_total_posts_num(), TTL_FOR_UI);
    set_transient(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM_UI, trinity_phbu_get_updated_posts_num(), TTL_FOR_UI);
  }

  function trinity_phbu_reset() {
    trinity_log('Post hashes bulk update - reset');

    trinity_ph_cleanup();

    delete_option(TRINITY_AUDIO_PHBU_UPDATED_POSTS_NUM);
    delete_option(TRINITY_AUDIO_PHBU_FAILED_POSTS);
  }

  function trinity_phbu_cleanup() {
    trinity_log('Post hashes bulk update - cleanup... 🧹');
    trinity_phbu_reset();

    delete_option(TRINITY_AUDIO_PHBU_TOTAL_POSTS_NUM);
    delete_option(TRINITY_AUDIO_PHBU_IN_PROGRESS);
    delete_option(TRINITY_AUDIO_PHBU_BATCH_RETRY);
    delete_option(TRINITY_AUDIO_PHBU_BULK_SIZE);
    delete_option(TRINITY_AUDIO_PHBU_HEARTBEAT);
  }
