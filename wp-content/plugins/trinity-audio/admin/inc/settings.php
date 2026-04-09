<?php
  require_once __DIR__ . '/../../utils.php';
  require_once __DIR__ . '/../../inc/common.php';
  require_once __DIR__ . '/../../inc/constants.php';
  require_once __DIR__ . '/../../inc/templates.php';

  $unit_config = trinity_get_unit_config_from_trinity();

  $initial_save = !trinity_get_is_first_changes_saved();

  trinity_audio_first_time_install_notice();

  if ($initial_save) {
    trinity_show_warning_need_to_activate();
  }

  $package_data = trinity_get_package_data();

  notifications($package_data);
  trinity_show_bulk_progress();
?>

<form class="trinity-page-wrapper" action="options.php" name="settings" method="post" onsubmit="trinityAudioOnSettingsFormSubmit(this, <?=(int)$initial_save?>);return false;">
  <div class="wrap trinity-page" id="trinity-admin">
    <?php
      settings_errors();
      settings_fields('trinity_audio');
    ?>
    <div class="wizard-progress-wrapper">
        <div class="trinity-head">Trinity Audio</div>
        <?php require_once __DIR__ . '/../inc/progress.php'; ?>
    </div>
    <div class="flex-grid">
      <div class="column column-left">
        <section>
          <?php if ($package_data->package->package_name !== 'Premium') { ?>
          <div class="section-title row lock-description">
            <span>General Configuration</span>
            <span class="upgrade-description">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" data-v-f5d98984=""><g data-name="Ellipse 31" fill="#333" stroke="#f9f9f9" stroke-width="2"><circle cx="11" cy="11" r="11" stroke="none"></circle><circle cx="11" cy="11" r="10" fill="none"></circle></g><path data-name="Icon ionic-ios-lock" d="M14.034 9.382h-.607V8.201a2.427 2.427 0 10-4.853-.034v1.215h-.607a1.014 1.014 0 00-1.011 1.011v4.854a1.014 1.014 0 001.011 1.011h6.067a1.014 1.014 0 001.012-1.011v-4.854a1.014 1.014 0 00-1.012-1.011zm-2.68 3.155v1.739a.362.362 0 01-.336.364.354.354 0 01-.372-.354v-1.749a.809.809 0 11.708 0zm1.365-3.155H9.282V8.167a1.719 1.719 0 013.438 0z" fill="#fff"></path></svg>
              <a target="_blank" href="<?= esc_url(trinity_add_utm_to_url(trinity_get_upgrade_url(), 'wp_admin', 'top_configuration')) ?>">Upgrade</a>
              <span>to Activate Locked Features</span>
            </span>
          </div>
          <?php } ?>
          <div class="trinity-section-body">
            <script defer src="<?= esc_url(TRINITY_DASHBOARD_SERVICE . 'backend/v1/apps/unit-configuration/wp/' . trinity_get_install_key()) ?>" onload="trinityDashboardComponentLoaded()" onerror="trinityDashboardComponentFailed()"></script>
          </div>
        </section>
        <section>
          <div class="section-title">Textual Configuration</div>
          <div class="trinity-section-body">
            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SKIP_TAGS); ?>">
                Skip HTML tags:
              </label>

              <?php trinity_skip_tags(); ?>
            </div>

            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_ALLOW_SHORTCODES); ?>">
                Allow shortcodes:
              </label>

              <?php trinity_allow_shortcodes(); ?>
            </div>

          </div>
        </section>
        <section>
          <div class="section-title">Player Settings</div>
          <div class="trinity-section-body">
            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_PLAYER_LABEL); ?>">
                Player label:
              </label>

              <?php trinity_player_label(); ?>
            </div>

            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_POWERED_BY); ?>">
                Help us reach new users:
              </label>

              <div></div>

              <?php trinity_display_powered_by($unit_config); ?>
            </div>

            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_PRECONNECT); ?>">
                Resource Preconnect:
              </label>

              <?php trinity_preconnect(); ?>
            </div>

            <div class="section-form-group">

              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT); ?>">
                New post default:
              </label>

              <?php trinity_new_post_default(); ?>
            </div>

          </div>
        </section>
        <section>
          <div class="section-title">Advanced Settings</div>
          <div class="trinity-section-body">
            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_CHECK_FOR_LOOP); ?>">
                Render player with 3rd party theme posts, e.g. Divi, Bespoke, etc.
              </label>

              <?php trinity_check_for_loop(); ?>
            </div>
            <div class="section-form-group">
              <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION); ?>">
                  Enable Trinity Player on API post creation
              </label>

              <?php trinity_activate_on_api(); ?>
            </div>
          </div>
        </section>
      </div>

      <div class="column column-right">
        <section class="trinity-guide">
          <div class="trinity-section-body">
            <div>
              Get started with your TrinityAudio Player on WordPress
            </div>
            <div>
              <a target="_blank" href="<?= esc_url(trinity_add_utm_to_url('https://www.trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide')) ?>">WordPress Installation Guide <svg xmlns="http://www.w3.org/2000/svg" width="14.002" height="14.002" viewBox="0 0 14.002 14.002">
                  <path d="M16.946,16.946H6.056V6.056H11.5V4.5H6.056A1.555,1.555,0,0,0,4.5,6.056v10.89A1.555,1.555,0,0,0,6.056,18.5h10.89A1.56,1.56,0,0,0,18.5,16.946V11.5H16.946ZM13.057,4.5V6.056h2.793L8.2,13.7l1.1,1.1,7.647-7.647V9.945H18.5V4.5Z" transform="translate(-4.5 -4.5)" fill="#07f"/>
                </svg></a>
            </div>
          </div>
        </section>

        <section>
          <div class="section-title">Subscription</div>
          <div class="trinity-section-body plan-section">
            <?php trinity_current_package_info_template($package_data); ?>
          </div>
        </section>

        <section class="save-and-odds">
          <div class="save-and-odds-positioning">
            <?php trinity_premium_banner(); ?>
            <button class="save-button">Save Changes</button>
          </div>
        </section>
      </div>
    </div>
  </div>
  <input type="hidden" name="<?= esc_attr(TRINITY_AUDIO_FIRST_CHANGES_SAVE); ?>" value="1">
  <script>
    jQuery(document).ready(() => {
      trinitySendMetric('wordpress.settings.opened');
    })
  </script>
</form>

<?php

  function trinity_new_post_default() {
    $checked = trinity_get_new_posts_default() ? 'checked' : '';
    echo "<label for='" . esc_attr(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT) . "' class='custom-checkbox'>
            <div class='text-label'>
            Add to all new posts
            </div>
            <input type='checkbox' name='" . esc_attr(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT) . "' id='" . esc_attr(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT) . "'" . esc_html($checked) . " />
            <div class='custom-hitbox'></div>
          </label>";

    echo '<p class="description">Enable this to make sure that the Trinity Audio player will be added to each new post you publish.</p>';
  }

  function trinity_player_label() {
    $value = trinity_get_player_label();
    echo "<input placeholder='Enter label' type='text' value='" . esc_attr($value) . "' name='" . esc_attr(TRINITY_AUDIO_PLAYER_LABEL) . "' id='" . esc_attr(TRINITY_AUDIO_PLAYER_LABEL) . "' class='custom-input' />";
    echo "<p class='description'>Set optional text to be visible within the player, above the audio progress bar (HTML tags are supported with this label)</p>";
  }

  function trinity_display_powered_by($unit_config) {
    $checked = $unit_config->powered_by ? 'checked' : '';

    echo "<label  for='" . esc_attr(TRINITY_AUDIO_POWERED_BY) . "' class='custom-checkbox powered-by-inline-flex'>
            <div class='text-label'>
            Display Powered by Trinity Audio
            </div>
            <input type='checkbox' name='" . esc_attr(TRINITY_AUDIO_POWERED_BY) . "' id='" . esc_attr(TRINITY_AUDIO_POWERED_BY)  . "'" . esc_html($checked) . " />
            <div class='custom-hitbox'></div>
          </label>";
    echo '<p class="description">76% of our users decided to show their support by enabling the (very small) "Powered by Trinity Audio" credit text to appear on the player</p>';
  }

  function trinity_preconnect() {
    $checked = trinity_get_preconnect() ? 'checked' : '';

    echo "<label for='" . esc_attr(TRINITY_AUDIO_PRECONNECT) . "' class='custom-checkbox'>
            <div class='text-label'>
            Pre connect ON
            </div>
            <input type='checkbox' name='" . esc_attr(TRINITY_AUDIO_PRECONNECT) . "' id='" . esc_attr(TRINITY_AUDIO_PRECONNECT)  . "'" . esc_html($checked) . " />
            <div class='custom-hitbox'></div>
          </label>";

    echo '<p class="description">This option let you to choose if you want to improve player loading speed by using preconnect</p>';
  }

  function trinity_skip_tags() {
    $value = implode(',', trinity_get_skip_tags());

    echo "<input type='text' placeholder='Example: htmltag1, htmltag2' class='custom-input' oninput='trinityCheckFieldDirty(this)' value='" . esc_attr($value) . "' name='" . esc_attr(TRINITY_AUDIO_SKIP_TAGS) . "' id='" .  esc_attr(TRINITY_AUDIO_SKIP_TAGS) . "' />";

    trinity_bulk_update_dirty_warning();
    trinity_bulk_update_field_notify();
    echo '<p class="description">
            Enter HTML tags that should be ignored while reading the text using comma delimiter, e.g. img, i, footer.
          </p>
          <p class="description">
            Please note - changing the value of this setting affects the text being read by the player. This requires re-processing the current articles, and might take some time.
         </p>';
  }

  function trinity_allow_shortcodes() {
    $value = implode(',', trinity_get_allowed_shortcodes());

    echo "<input type='text' placeholder='Example: vc_row,vc_column,vc_column_text' class='custom-input' oninput='trinityCheckFieldDirty(this)' value='" . esc_attr($value) . "' name='" .  esc_attr(TRINITY_AUDIO_ALLOW_SHORTCODES) . "' id='" .  esc_attr(TRINITY_AUDIO_ALLOW_SHORTCODES) . "' />";

    trinity_bulk_update_dirty_warning();
    trinity_bulk_update_field_notify();
    echo '<p class="description">
            By default, no shortcodes are executed. Only add shortcodes required for article text. <br/>
            Use comma delimited for multiple values, e.g. vc_row,vc_column,su_heading <br/><br/>

            Please Note: Themes such as Divi, WPBakery, Avada etc. use shortcodes for article text. Please see 
            <span class="guide-hints" data-url="https://www.youtube.com/embed/6MsW3OUxgt8?si=RG708r1-_uKW9-Yc" data-title="Configuring Trinity Audio WordPress plugin to work with Divi, WPBakery, Avada..." data-id="guide-wp">this guide</span> on how to get the correct values. 
          </p>';
  }

  function trinity_check_for_loop() {
    $checked = trinity_get_check_for_loop() ? 'checked' : '';

    echo "<label for='" . esc_attr(TRINITY_AUDIO_CHECK_FOR_LOOP) . "' class='custom-checkbox'>
        <div class='text-label'>Enable</div>
        <input type='checkbox' name='" . esc_attr(TRINITY_AUDIO_CHECK_FOR_LOOP) . "' id='" . esc_attr(TRINITY_AUDIO_CHECK_FOR_LOOP)  . "'" . esc_html($checked) . " />
        <div class='custom-hitbox'></div>
      </label>";

    $email = TRINITY_AUDIO_SUPPORT_EMAIL;
    echo "<p class='description' style='color: red'>
Enable this checkbox when using 3rd party themes such as Divi.
Note! - Please verify that the player appears as you expect it. In case you are not sure, reach out to our <a href='mailto:" . esc_attr($email) . "'>support</a>
        </p>";
  }

  function trinity_activate_on_api() {
    $checked = trinity_get_enable_for_api() ? 'checked' : '';

    echo "<label for='" . esc_attr(TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION) . "' class='custom-checkbox'>
        <div class='text-label'>Enable</div>
        <input type='checkbox' name='" . esc_attr(TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION) . "' id='" . esc_attr(TRINITY_AUDIO_ACTIVATE_ON_API_POST_CREATION)  . "'" . esc_html($checked) . " />
        <div class='custom-hitbox'></div>
      </label>";

    $email = TRINITY_AUDIO_SUPPORT_EMAIL;
    echo "<p class='description'>
Use this setting to enable Trinity Player on posts created by Wordpress APIs.
Please note that the primary setting for 'New post default' Should also be set to 'On' for this setting to take effect.
In case you are not sure, reach out to our <a href='mailto:" . esc_attr($email) . "'>support</a>
        </p>";
  }

  function trinity_show_warning_need_to_activate() {
    echo '
        <div class="notice notice-warning trinity-activate-plugin">
          <p class="message">Your Trinity Audio player is not functional yet! Please review the settings and click <strong>Save Changes</strong> at the bottom of the page to activate it.</p>
          <p><a href="https://trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide/" target="_blank">Click here for further details</a></p>
        </div>
    ';
  }

  function trinity_audio_first_time_install_notice() {
    if (trinity_get_first_time_install()) {
      ?>
      <div class="notice notice-info">
        <p>
          <?php trinity_show_recovery_token_inline(); ?> Please save your secret recovery token in a safe place. This token is unique and bound to your domain. It is required for restoring your installation (a new environment of any sort).
        </p>
      </div>
      <?php
    }
  }

  function trinity_bulk_update_dirty_warning() {
    echo '<p class="trinity-warning trinity-warning-dirty description">
            <span class="icon warning-icon"></span>
            <span>During re-processing time audio player might experience errors. You can track the progress of the update at the top of the screen</span>
          </p>';
  }

  function trinity_bulk_update_field_notify() {
    echo '<p class="trinity-warning trinity-warning-bulk-notify description">
            <span class="icon warning-icon"></span>
            <span>This values can not be changed while re-processing is under going</span>
          </p>';
  }
