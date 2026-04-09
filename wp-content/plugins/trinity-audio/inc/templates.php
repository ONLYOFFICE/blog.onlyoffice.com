<?php
  require_once __DIR__ . '/constants.php';
  require_once __DIR__ . '/common.php';

  function trinity_get_package_template($package_data, $retry_number = 0) {
    $result = [
      'html'   => '',
      'status' => 'loading'
    ];

    if (!$package_data || !$package_data->package) {
      $max_retries   = 10;
      $retry_timeout = 10000;

      if (($retry_number + 1) >= $max_retries) {
        $result['html']   = "Error getting subscription details. Please retry later or <a href='/wp-admin/admin.php?page=trinity_audio_contact_us' target='_blank'>Contact Support</a>.";
        $result['status'] = 'fail';

        return $result;
      }

      $result['html'] = "<div class='loader-container'>
        <span class='loader'></span>
          <script>
            const timeout = $retry_timeout;
            const maxRetries = $max_retries;
            let counter = $retry_number;

            const intervalId = setInterval(() => {
              window.trinityGrabPackageInfo(counter);
              counter++;

              if (counter === maxRetries) clearInterval(intervalId);
            }, timeout);
          </script>
      </div>";

      return $result;
    }

    $package_name = $package_data->package->package_name;
    $account_key  = $package_data->package->account_key;

    if (!empty($package_data->nextRefreshAt)) {
      try {
        $next_refresh_at           = $package_data->nextRefreshAt;
        $date                      = new DateTime($next_refresh_at);
        $next_refresh_at_formatted = $date->format('M d, Y');
      } catch (Exception $error) {
        trinity_log("Error while parsing next refresh credits date: $next_refresh_at", '', '', TRINITY_AUDIO_ERROR_TYPES::error);
      }
    }

    if ($package_name === 'Wordpress') $package_name = 'Free';

    $cap_type = $package_data->capType;

    $result['html'] .= "<div class='plan-banner-wrapper'>
          <div class='current-plan-wrapper'>
            <div class='curr-plan'>Current plan:</div>";

    if ($package_name === 'Premium') {
      $articles_per_month = '<span class="bright">Unlimited</span>';
    } else {
      $package_articles_used  = $package_data->used ?? 0;
      $package_articles_total = $package_data->packageLimit ?? 0;
      $articles_per_month     = "<span class='bright'>" . esc_html($package_articles_used) . "</span><span class='articles-limit'> / " . esc_html($package_articles_total) . "</span>";
    }

    $result['html'] .= "<div class='plan-name'>" . esc_html($package_name) . "</div>";

    if ($cap_type === 'chars') {
      $formatted_credits = number_format($package_data->credits);
      $result['html']    .= "<div class='credits-used feature-title large-title'>Credits left: <span class='bright'>" . esc_html($formatted_credits) . "</span></div>
            <div class='feature-description bottom-space-10'></div>";
    } else if ($cap_type === 'articles' || $cap_type === 'audios') {
      $result['html'] .= "<div class='section-form-title'>Credits used:</div>";
      $result['html'] .= "<div class='credits-used feature-title large-title'>" . wp_kses_post($articles_per_month) . "</div>";
    }

    if (!empty($next_refresh_at_formatted)) $result['html'] .= "<div class='next-refresh-at'><span class='renew-at-label'>Renew at </span><span class='renew'>" . esc_html($next_refresh_at_formatted) . "</span></div>";

    if ($cap_type !== 'no_limit' && $package_name !== 'Premium') {
      $result['html'] .= "<div>Need more articles? <a href='" . esc_url(trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_admin', 'subscription_panel')) . "' target='_blank'>Try a different plan</a></div>";
    }

    $result['html'] .= "</div></div>";

    if (trinity_get_is_account_key_linked()) {
      $result['html'] .= "<div class='token-label'>Account key:</div>";
      $result['html'] .= "<div class='verified-message'>Account key Validated</div>";

//      if ($package_name !== 'Free') {
      $result['html'] .= "<div class='custom-input-disabled'>
                <div class='edit-icon'>
                  <span>✎</span>
                </div>
                <input placeholder='Enter new Account key' class='custom-input description' type='text' value='" . esc_attr($account_key) . "' name='" . esc_attr(TRINITY_AUDIO_PUBLISHER_TOKEN) . "' id='" . esc_attr(TRINITY_AUDIO_PUBLISHER_TOKEN) . "' disabled>
                <div class='publisher-token-notification'></div>
                <div class='trinity-save-account trinity-hide'>
                  <div class='use-account-key-button'>Save key</div>
                  <p class='description'>For <span class='underline'>subscribed</span> clients, please insert the account key received from the Trinity Audio dashboard.</p>
                </div>
              </div>";
//      }

      $result['html'] .= "<div class='advanced-features'><a href='" . esc_url(trinity_add_utm_to_url(TRINITY_AUDIO_DASHBOARD_URL)) . "' target='_blank'>Manage Advanced Features</a></div>";
    } else {
      $result['html'] .= "<div class='token-label'>Account key:</div>
            <input spellcheck='false' placeholder='Enter Account key' type='text' class='custom-input inline-block' value='' name='" . esc_attr(TRINITY_AUDIO_PUBLISHER_TOKEN) . "' id='" . esc_attr(TRINITY_AUDIO_PUBLISHER_TOKEN) . "' />
            <div class='publisher-token-notification'></div>
            <div class='trinity-save-account'>
              <p class='description'>For <span class='underline'>subscribed</span> clients, please insert the account key received from the Trinity Audio dashboard.</p>
              <div class='use-account-key-button'>Save key</div>
            </div>";
    }

    $result['status'] = 'success';

    return $result;
  }

  function trinity_current_package_info_template($package_data) {
    $result = trinity_get_package_template($package_data);

      // all the HTML is escaped in the function trinity_get_package_template()
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo $result['html'];
  }

  function trinity_get_and_render_package() {
    check_ajax_referer('get_package_info_action', TRINITY_AUDIO_AJAX_NONCE_NAME);

    $package_data = trinity_get_package_data();
    $result       = trinity_get_package_template($package_data, $_GET['retryNumber']);

    wp_send_json(json_encode($result));
  }

  function trinity_premium_banner() {
    ?>

      <div class="premium-banner" target="_blank">
          <div class="upgrade-plan">Upgrade your Trinity Audio plan</div>
          <div class="upgrade-odds">
              <ul>
                  <li>Get more credits</li>
                  <li>GenAI voices</li>
                  <li>Create podcasts</li>
                  <li>Monetize your content</li>
              </ul>
          </div>
          <a href="<?= esc_url(trinity_add_utm_to_url(trinity_get_upgrade_url()), 'wp_admin', 'upgrade_banner') ?>"
             target="_blank" class="upgrade-button">Upgrade to premium</a>
      </div>
    <?php
  }

  function trinity_show_recovery_token() {
    $installkey = trinity_get_install_key();

    echo "
        <p class='info-text install-key trinity-show-recovery-token-button'>
          <a>Get my token</a>
        </p>
        <p class='info-text install-key hidden'>" . esc_html($installkey) . "</p>";
  }

  function trinity_show_recovery_token_inline() {
    $installkey = trinity_get_install_key();

    echo "
        <span class='info-text install-key trinity-show-recovery-token-button inline'>
          <a>Get my token</a>.
        </span>
        <span class='info-text install-key hidden'>" . esc_html($installkey) . "</span>";
  }

  function trinity_post_management_banner() {
    $messages = [
      "Convert unlimited articles with premium voices and advanced features.",
      "Get detailed analytics and insights about your audio engagement.",
      "Access voice cloning and custom pronunciation controls.",
      "Distribute your audio content to Spotify, Apple, and Google Podcasts.",
      "Access AI-powered content summaries and background music.",
      "Clone your own voice for consistent brand narration.",
      "Add background music and create professional podcast episodes."
    ];
    $message = array_rand($messages);
    
    $banner_dismissed_time = get_option(TRINITY_AUDIO_REMOVE_POST_BANNER);
    $should_show_banner = true;
    
    if ($banner_dismissed_time) {
      $should_show_banner = (time() - intval($banner_dismissed_time)) > TRINITY_AUDIO_BANNER_TTL_SECONDS;
    }

    if ($should_show_banner): ?>
        <div class="container">
            <div class="header">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
                         y="0px"
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
                <span class="close-icon"
                      onclick="trinityRemovePostBanner();trinitySendMetric('wordpress.post.banner.close');">
              <svg xmlns="http://www.w3.org/2000/svg" width="14.252" height="14.252" viewBox="0 0 14.252 14.252">
                <rect width="17.995" height="2.159" transform="translate(1.527) rotate(45)" fill="#c8c8c8"/>
                <rect width="17.995" height="2.159" transform="translate(14.252 1.527) rotate(135)" fill="#c8c8c8"/>
              </svg>
            </span>
            </div>

            <p class="message"><?= esc_html($messages[$message]) ?></p>

            <div>
                <a onclick="trinitySendMetricMeta('wordpress.post.banner.visit', '<?= esc_attr(trinity_get_plugin_version()) ?>');"
                   href="<?= esc_url(trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_post', 'upgrade_banner')) ?>"
                   class="upgrade-button" target="_blank">
                    Upgrade to premium
                </a>
                <div class="footnote">30-days money back guarantee.</div>
            </div>
        </div>
    <?php endif;
  }

  function trinity_show_articles_usage($package_data) {
    $cap_type = $package_data->capType;

    if ($cap_type === 'chars') {
      ?>
        <div class="section-form-title">
            Credits left:
        </div>
      <?php
      echo "<p>" . esc_html($package_data->credits) . "</p>";
      echo '<p class="description">Shows the amount of credits available to generate audio for new posts</p>';
    } else if ($cap_type === 'articles') {
      ?>
        <div class="section-form-title">
            Number of articles:
        </div>
      <?php
      echo "<p><span class='trinity-bold-text'>" . esc_html($package_data->used) . "</span> / " . esc_html($package_data->packageLimit) . "</p>";
      echo '<p class="description">Shows the amount of articles used</p>';
    } else if ($cap_type === 'no_limit') {
      echo '<p>Unlimited</p>';
      echo '<p class="description">Shows the amount of articles used</p>';
    } else {
      echo '<p>N/A</p>';
      echo '<p class="description"></p>';
    }
  }

  function trinity_show_bulk_progress() {
    echo "<div class='trinity-bulk-update-wrapper trinity-notification'>
            <span class='trinity-bulk-update status error'>A problem occurred while updating articles values. Please try again later.</span>
  
            <span class='trinity-bulk-update status progress'>
              <div class='trinity-bulk-message'>We're applying your settings to your posts. The plugin may experience issue while this is ongoing. You may navigate away from this page.</div>

              <div class='trinity-bulk-count-wrapper'>
                <span class='trinity-bulk-posts-numbers'></span>
                <span class='trinity-bulk-posts-posts'>Posts</span>
                <span class='trinity-bulk-posts-stage'></span>
                <span class='trinity-bulk-bar'>
                  <div class='trinity-bulk-bar-inner'></div>
                </span>
              </div>
            </span>
          </div>";
  }
