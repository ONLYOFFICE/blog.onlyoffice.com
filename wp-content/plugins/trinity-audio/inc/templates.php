<?php
  require_once __DIR__ . '/constants.php';
  require_once __DIR__ . '/common.php';

  function trinity_get_package_template($package_data, $retry_number = 0) {
    $result = [
      'html' => '',
      'status' => 'loading'
    ];

    if (!$package_data || !$package_data->package) {
      $max_retries = 10;
      $retry_timeout = 10000;

      if (($retry_number + 1) >= $max_retries) {
        $result['html'] = "Error getting subscription details. Please retry later or <a href='/wp-admin/admin.php?page=trinity_audio_contact_us' target='_blank'>Contact Support</a>.";
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
              window.grabPackageInfo(counter);
              counter++;

              if (counter === maxRetries) clearInterval(intervalId);
            }, timeout);
          </script>
      </div>";

      return $result;
    }

    $package_name = $package_data->package->package_name;
    $account_key = $package_data->package->account_key;

    if ($package_name === 'Wordpress') $package_name = 'Free';

    $packageInfo = TRINITY_AUDIO_PACKAGES_DATA[$package_name];
    $cap_type = $package_data->capType;

    $result['html'] .= "<div class='plan-banner-wrapper'>
          <div class='current-plan-wrapper'>
            <div class='curr-plan'>Current plan:</div>";

    if ($package_name === 'Premium') {
        $articles_per_month = '<span class="bright">Unlimited</span>';
    } else {
        $package_articles_used = $package_data->used ?? 0;
        $package_articles_total = $package_data->packageLimit ?? 0;
        $articles_per_month = "<span class='bright'>$package_articles_used</span><span class='articles-limit'> / $package_articles_total</span>";
    }

    $result['html'] .= "<div class='plan-name'>{$package_name}</div>
            <div class='description'>{$packageInfo['description']}</div>";

    if ($cap_type === 'chars') {
      $formatted_credits = number_format($package_data->credits);
      $result['html'] .= "<div class='feature-title large-title'>Credits left: <span class='bright'>$formatted_credits</span></div>
            <div class='feature-description bottom-space-10'></div>";
    } else if ($cap_type === 'articles') {
      $result['html'] .= "<div class='section-form-title'>Articles used:</div>";
      $result['html'] .= "<div class='feature-title large-title'>$articles_per_month</div>";
    }

    if ($cap_type !== 'no_limit' && $package_name !== 'Premium') {
        $result['html'] .= "<div>Need more articles? <a href='" . trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_admin', 'subscription_panel') . "' target='_blank'>Try a different plan</a></div>";
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
                <input placeholder='Enter new Account key' class='custom-input description' type='text' value='$account_key' name='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' id='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' disabled>
                <div class='publisher-token-notification'></div>
                <div class='trinity-save-account trinity-hide'>
                  <div class='use-account-key-button'>Save key</div>
                  <p class='description'>For <span class='underline'>subscribed</span> clients, please insert the account key received from the Trinity Audio dashboard.</p>
                </div>
              </div>";
//      }

      $result['html'] .= "<div class='advanced-features'><a href='" . trinity_add_utm_to_url(TRINITY_AUDIO_DASHBOARD_URL) . "' target='_blank'>Manage Advanced Features</a></div>";
    } else {
      $result['html'] .= "<div class='token-label'>Account key:</div>
            <input spellcheck='false' placeholder='Enter Account key' type='text' class='custom-input inline-block' value='' name='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' id='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' />
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

    echo $result['html'];
  }

  function trinity_get_and_render_package() {
    $package_data = trinity_get_package_data();
    $result = trinity_get_package_template($package_data, $_GET['retryNumber']);

    wp_send_json(json_encode($result));
  }

  function trinity_premium_banner() {
    ?>

    <div class="premium-banner" target="_blank">
      <div class="upgrade-plan">Upgrade your Trinity Audio plan</div>
      <div class="upgrade-odds">
        <ul>
          <li>Convert more article</li>
          <li>Natural voices & accents</li>
          <li>Edit & customize your audio</li>
        </ul>
      </div>
      <a href="<?= trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_admin', 'upgrade_banner') ?>" target="_blank" class="upgrade-button">Upgrade to premium</a>
    </div>
    <?php
  }

  function trinity_show_recovery_token() {
    $installkey = trinity_get_install_key();

    echo "
        <p class='info-text install-key trinity-show-recovery-token-button'>
          <a>Get my token</a>
        </p>
        <p class='info-text install-key hidden'>$installkey</p>";
  }

  function trinity_show_recovery_token_inline() {
    $installkey = trinity_get_install_key();

    echo "
        <span class='info-text install-key trinity-show-recovery-token-button inline'>
          <a>Get my token</a>.
        </span>
        <span class='info-text install-key hidden'>$installkey</span>";
  }
