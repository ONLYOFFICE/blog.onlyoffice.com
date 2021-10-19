<?php
  require_once __DIR__ . '/constants.php';
  require_once __DIR__ . '/common.php';

  function trinity_current_package_info_template($package_data) {
    $error_msg = trinity_can_not_connect_error_message('Can\'t get current plan.');
    $package_name = $package_data->package->package_name;
    if ($package_name === 'Wordpress') $package_name = 'Free';
    $packageInfo = TRINITY_AUDIO_PACKAGES_DATA[$package_name];
    $cap_type = $package_data->capType;

    if (!$package_data || !$package_data->package) {
      echo $error_msg;
      return;
    }

    if ($package_name === 'Premium') {
        $articles_per_month = '<span class="bright">Unlimited</span>';
    } else {
        $package_articles_used = $package_data->used ?? 0;
        $package_articles_total = $package_data->packageLimit ?? 0;
        $articles_per_month = "<span class='bright'>$package_articles_used</span><span class='articles-limit'> / $package_articles_total</span>";
    }


    echo "<div class='plan-name'>{$package_name}</div>
            <div class='description'>{$packageInfo['description']}</div>";

    if ($cap_type === 'chars') {
      $formatted_credits = number_format($package_data->credits);
      echo "<div class='feature-title large-title'>Credits left: <span class='bright'>$formatted_credits</span></div>
            <div class='feature-description bottom-space-10'></div>";
    } else if ($cap_type === 'articles') {
      echo "<div class='section-form-title'>Articles used:</div>";
      echo "<div class='feature-title large-title'>$articles_per_month</div>";

      if ($cap_type !== 'no_limit' && $package_name !== 'Premium')
          echo "<div>Need more articles? <a href='" . TRINITY_AUDIO_PRICING_URL . "&installkey=" . trinity_get_install_key() . "' target='_blank'>Try a different plan</a></div>";
    }

    if (trinity_get_is_account_key_linked()) {
      echo "<div class='token-label'>Account key:</div>
            <div class='verified-message'>Account key Validated</div>
            <div>Advanced features are managed from <a href='" . TRINITY_AUDIO_DASHBOARD_URL . "' target='_blank'>here</a></div>";
    } else {
      echo "<div class='token-label'>Account key:</div>
            <input spellcheck='false' type='text' class='custom-input inline-block' value='' name='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' id='" . TRINITY_AUDIO_PUBLISHER_TOKEN . "' />
            <div class='publisher-token-notification'></div>
            <div class='trinity-save-account'>
              <div class='use-account-key-button'>Save key</div>
              <p class='description'>For existing clients, please insert the token received from the Trinity Audio dashboard.</p>
            </div>";
    }
  }

  function trinity_premium_banner() {
    $bannerLink = TRINITY_AUDIO_PRICING_URL . '&installkey=' . trinity_get_install_key();
    ?>

    <div class="premium-banner" target="_blank">
      <div class="upgrade-plan">Upgrade your Trinity Audio plan</div>
      <div class="upgrade-odds">
        <ul>
          <li>Convert more article</li>
          <li>Natural voices & accents</li>
          <li>Edit & customize your audio</li>
        </ul>
        <ul>
          <li>Create playlists & distribute </li>
          <li>Usability reports & dashboard</li>
          <li>AMP & SDK support</li>
        </ul>
      </div>
      <a href="<?php echo $bannerLink; ?>" target="_blank" class="upgrade-button">Upgrade to premium</a>
    </div>
    <?php
  }
