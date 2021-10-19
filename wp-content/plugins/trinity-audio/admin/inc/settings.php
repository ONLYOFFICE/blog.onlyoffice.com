<?php
  require_once __DIR__ . '/../../utils.php';
  require_once __DIR__ . '/../../inc/common.php';
  require_once __DIR__ . '/../../inc/constants.php';
  require_once __DIR__ . '/../../inc/templates.php';

  trinity_audio_first_time_install_notice();
  if (!trinity_get_is_first_changes_saved()) {
    trinity_show_warning_need_to_activate();
  }
  $package_data = trinity_get_package_data();
  notifications($package_data);
?>

<form action="options.php" name="settings" method="post"
  onsubmit="trinityAudioOnSettingsFormSubmit(this, <?php echo trinity_get_is_first_changes_saved() ?>)">
  <?php
    settings_errors();
    settings_fields('trinity_audio');
  ?>
  <div class="wrap trinity-page" id="trinity-admin">
    <h1 class="trinity-head">Trinity Audio</h1>
    <div class="flex-grid">
      <div class="row">
        <div class="column">
          <section>
            <div class="section-title">General Configuration</div>
            <div class="trinity-section-body">
              <div class="section-form-group">

                <div class="section-form-title">
                  Default language:
                </div>

                <?php  trinity_source_language(); ?>
              </div>

              <div class="section-form-group">

                <div class="section-form-title">
                  Default gender:
                </div>

                <?php trinity_source_gender(); ?>
              </div>

              <div class="section-form-group">

                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT; ?>">
                  New post default:
                </label>

                <?php trinity_new_post_default(); ?>
              </div>

            </div>
          </section>
        </div>

        <div class="column">
          <section>
            <div class="section-title">Subscription</div>
            <div class="trinity-section-body plan-section">
              <div class="curr-plan">Current plan:</div>

              <?php trinity_current_package_info_template($package_data); ?>
            </div>
          </section>
        </div>

      </div>

      <div class="row">
        <div class="column">
          <section>
            <div class="section-title">Textual Configuration</div>
            <div class="trinity-section-body">

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_ADD_POST_TITLE; ?>">
                  Add post title to audio:
                </label>

                <?php trinity_add_post_title(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_ADD_POST_EXCERPT; ?>">
                  Add post excerpt to audio:
                </label>

                <?php trinity_add_post_excerpt(); ?>
              </div>


              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_SKIP_TAGS; ?>">
                  Skip HTML tags:
                </label>

                <?php trinity_skip_tags(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_ALLOW_SHORTCODES; ?>">
                  Allow shortcodes:
                </label>

                <?php trinity_allow_shortcodes(); ?>
              </div>

            </div>
          </section>
        </div>

        <div class="column">
          <section>
            <div class="section-title">Player Settings</div>
            <div class="trinity-section-body">

              <div class="section-form-group">
                <div class="section-form-title">
                  Player position:
                </div>

                <?php trinity_player_position(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_PLAYER_LABEL; ?>">
                  Player label:
                </label>

                <?php trinity_player_label(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_POWERED_BY; ?>">
                  Help us reach new users:
                </label> <br />

                <?php trinity_display_powered_by(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_PRECONNECT; ?>">
                  Resource Preconnect:
                </label>

                <?php trinity_preconnect(); ?>
              </div>

              <div class="section-form-group" style="display: none">
                <label class="section-form-title" for="<?php echo TRINITY_AUDIO_PRECONNECT; ?>">
                  Translate:
                </label>

                <?php trinity_translate(); ?>
              </div>

            </div>
          </section>
        </div>
      </div>

      <div class="row">
	      <div class="column"></div>
        <div class="column">
          <section class="save-and-odds">
            <div class="section-title">SAVE & ACTIVATE</div>

            <div class="save-and-odds-positioning">
              <button class="save-button">Save Changes
              </button>
              <?php trinity_premium_banner(); ?>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</form>

<?php

  function trinity_source_language() {
    $languages = trinity_get_languages(); // keep here, is error is occur, it will display error via die().
    if (!$languages) {
      die(trinity_can_not_connect_error_message('Can\'t get list of supported languages.'));
    }
    $current_language = trinity_get_source_language();

    echo "<input value='$current_language' class='hidden' name='" . TRINITY_AUDIO_SOURCE_LANGUAGE . "' id='" . TRINITY_AUDIO_SOURCE_LANGUAGE . "' />
          <div class='trinity-custom-select'>
          <div class='value-text'>";

    foreach ($languages as $lang) {
      if ($current_language === $lang->code) echo esc_html($lang->name);
    }

    echo "</div><div class='options'>";

    foreach ($languages as $lang) {
      $language_code = $lang->code;
      $language_name = $lang->name;

      $hidden = $current_language === $language_code ? "hidden" : "";

      echo "<div value='$language_code' onclick='updateCustomSelectValue(\"" . TRINITY_AUDIO_SOURCE_LANGUAGE . "\", \"$language_name\", \"$language_code\")' class='line $hidden' value='$language_code'>$language_name</div>";
    }
    echo "</div>
          <div class='custom-select-arrow'></div></div>
          <p class='description'>Use this to configure the default language that is used for your textual content in your site. In case you have more than one, please declare the main language used and use the individual post menu to define the language for it</p>";
  }

  function trinity_player_themes() {
    // Mock for a while
//    $themes = [['id' => 1, "name" => "White"], ['id' => 2, "name" => "Black"]];
//
//    echo "<input value='1' class='hidden' name='" . TRINITY_AUDIO_THEME_ID . "' id='" . TRINITY_AUDIO_THEME_ID . "' />
//          <div class='trinity-custom-select'>
//          <div class='value-text'>";
//
//    foreach ($themes as $theme) {
//      if ($current_theme_id === $theme->id) echo esc_html($theme->name);
//    }
//    echo "</div><div class='options'>";
//
//    foreach ($themes as $theme) {
//      $theme_id = $theme->id;
//      $theme_name = $theme->name;
//
//      echo "<div value='$theme_id' onclick='updateCustomSelectValue(\"" . TRINITY_AUDIO_THEME_ID . "\", \"$theme_name\", \"$theme_id\")' class='line' value='$theme_id'>$theme_name</div>";
//    }
//    echo "</div>
//          <div class='custom-select-arrow'></div></div>";
//
//    echo "<p class='description'>Use this to configure the default language that is used for your textual content in your site. In case you have more than one, please declare the main language used and use the individual post menu to define the language for it</p>";
  }

  function trinity_source_gender() {
    $current_gender = trinity_get_gender();

    echo "<input value='$current_gender' class='hidden' name='" . TRINITY_AUDIO_GENDER_ID . "' id='" . TRINITY_AUDIO_GENDER_ID . "'>";
    echo "<div class='trinity-custom-select'>
          <div class='value-text'>";
    foreach (TRINITY_AUDIO_GENDER_ARRAY as $key => $value) {
      if ($current_gender === $key) echo esc_html($value);
    }
    echo "</div><div class='options'>";

    foreach (TRINITY_AUDIO_GENDER_ARRAY as $key => $value) {
      $hidden = $key === $current_gender ? "hidden" : "";
      echo "<div value='$key' class='line $hidden' onclick='updateCustomSelectValue(\"" . TRINITY_AUDIO_GENDER_ID . "\", \"$value\", \"$key\")'>$value</div>";
    }

    echo "</div><div class='custom-select-arrow'></div></div>
          <p class='description'>Use this to choose the voice gender you prefer. You could also choose different gender per post in the post menu</p>";
  }

  function trinity_new_post_default() {
    $checked = trinity_get_new_posts_default() ? 'checked' : '';
    echo "<label for='" . TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT . "' class='custom-checkbox'>
            <div class='text-label'>
            Add to all new posts
            </div>
            <input type='checkbox' name='" . TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT . "' id='" . TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT . "' $checked />
            <div class='custom-hitbox'></div>
          </label>";

    echo '<p class="description">Check this inbox to make sure that each new post will have the Trinity Audio player enabled by default</p>';
  }

  function trinity_player_position() {
    $current_player_position = trinity_get_player_position();
    echo "<input class='hidden' value='$current_player_position' name='" . TRINITY_AUDIO_PLAYER_POSITION . "' id='" . TRINITY_AUDIO_PLAYER_POSITION . "' />";
    echo "<div class='trinity-custom-select'>
            <div class='value-text'>";

    foreach (TRINITY_AUDIO_PLAYER_POSITION_ARRAY as $key => $value) {
      if ($current_player_position === $key) echo esc_html($value);
    }

    echo "</div>";
    echo "<div class='options'>";
    foreach (TRINITY_AUDIO_PLAYER_POSITION_ARRAY as $key => $value) {
      $hidden = $current_player_position === $key ? "hidden" : "";
      echo "<div onclick='updateCustomSelectValue(\"" . TRINITY_AUDIO_PLAYER_POSITION . "\", \"$value\", \"$key\")' class='line $hidden' value='$key'>$value</div>";
    }
    echo "</div>";
    echo "<div class='custom-select-arrow'></div></div>";

    echo '<p class="description">Choose the position on the page you would like the player to appear on. We recommend placing it above the fold for best user experience</p>';
  }

  function trinity_player_label() {
    $value = trinity_get_player_label();
    echo "<input placeholder='Enter label' type='text' value='$value' name='" . TRINITY_AUDIO_PLAYER_LABEL . "' id='" . TRINITY_AUDIO_PLAYER_LABEL . "' class='custom-input' />";
  }

  function trinity_display_powered_by() {
    $checked = trinity_get_powered_by() ? 'checked' : '';

    echo "<label  for='" . TRINITY_AUDIO_POWERED_BY . "' class='custom-checkbox powered-by-inline-flex'>
            <div class='text-label'>
            Display Powered by Trinity Audio
            </div>
            <input type='checkbox' name='" . TRINITY_AUDIO_POWERED_BY . "' id='" . TRINITY_AUDIO_POWERED_BY . "' $checked>
            <div class='custom-hitbox'></div>
          </label>";
    echo '<p class="description">Select this option if you would like to give us credit and help other content creators reach out to us easily once they see the player on your site</p>';
  }

  function trinity_preconnect() {
    $checked = trinity_get_preconnect() ? 'checked' : '';

    echo "<label for='" . TRINITY_AUDIO_PRECONNECT . "' class='custom-checkbox'>
            <div class='text-label'>
            Pre connect ON
            </div>
            <input type='checkbox' name='" . TRINITY_AUDIO_PRECONNECT . "' id='" . TRINITY_AUDIO_PRECONNECT . "' $checked>
            <div class='custom-hitbox'></div>
          </label>";

    echo '<p class="description">This option let you to choose if you want to improve player loading speed by using preconnect</p>';
  }

  function trinity_translate() {
    echo "<label for='" . TRINITY_AUDIO_TRANSLATE . "' class='custom-checkbox'>
            <div class='text-label'>
            Translate
            </div>
            <input type='checkbox' name='" . TRINITY_AUDIO_TRANSLATE . "' id='" . TRINITY_AUDIO_TRANSLATE . "'>
            <div class='custom-hitbox'></div>
          </label>";
  }

  function trinity_add_post_title() {
    $checked = trinity_get_add_post_title() ? 'checked' : '';

    echo "<label for='" . TRINITY_AUDIO_ADD_POST_TITLE . "' class='custom-checkbox'>
        <div class='text-label'>Include title</div>
        <input type='checkbox' name='" . TRINITY_AUDIO_ADD_POST_TITLE . "' id='" . TRINITY_AUDIO_ADD_POST_TITLE . "' $checked>
        <div class='custom-hitbox'></div>
      </label>";

    echo '<p class="description">If enabled, each audio file will start by reading the post title. If not, the player will start reading from the main text</p>';
  }

  function trinity_add_post_excerpt() {
    $checked = trinity_get_add_post_excerpt() ? 'checked' : '';

    echo "<label for='" . TRINITY_AUDIO_ADD_POST_EXCERPT . "' class='custom-checkbox'>
            <div class='text-label'> Include excerpt </div>
            <input type='checkbox' name='" . TRINITY_AUDIO_ADD_POST_EXCERPT . "' id='" . TRINITY_AUDIO_ADD_POST_EXCERPT . "' $checked>
            <div class='custom-hitbox'></div>
          </label>";
  }

  function trinity_skip_tags() {
    $value = implode(',', trinity_get_skip_tags());

    echo "<input type='text' class='custom-input' value='$value' name='" . TRINITY_AUDIO_SKIP_TAGS . "' id='" . TRINITY_AUDIO_SKIP_TAGS . "' />";

    echo '<p class="description">Enter HTML tags that should be ignored while reading a text, using comma delimiter, e.g. img, i, footer. <br /></p>';
  }

  function trinity_allow_shortcodes() {
    $value = implode(',', trinity_get_allowed_shortcodes());

    echo "<input type='text' class='custom-input' value='$value' name='" . TRINITY_AUDIO_ALLOW_SHORTCODES . "' id='" . TRINITY_AUDIO_ALLOW_SHORTCODES . "' />";
    echo '<p class="description">Enter shortcodes that should not be filtered out, using comma delimiter, e.g. gallery, myshortcode. By default all shortcodes are filtered out while reading text. <br /></p>';
  }

  function trinity_check_for_loop() {
    $checked = trinity_get_check_for_loop() ? 'checked' : '';
    echo "<input type='checkbox' name='" . TRINITY_AUDIO_CHECK_FOR_LOOP . "' id='" . TRINITY_AUDIO_CHECK_FOR_LOOP . "' $checked value='1'>";

    echo '<p class="description">Render player if <strong>in_the_loop()</strong> is true. Can help publishers, using posts injected by other services in not standard WordPress way</p>';
  }

  function trinity_activate_for_all_posts() {
    $is_checked = !!trinity_get_is_bulk_updated();
    $checked    = $is_checked ? '' : 'checked';
    $disabled   = $checked ? 'disabled=disabled' : '';

    echo "<div>
            <input type='checkbox' name='trinity_audio_activate_for_all_posts' id='trinity_audio_activate_for_all_posts' " . $checked . ' ' . $disabled . " value='1'>
            <span class='trinity-status-wrapper'>
              <span class='status error'>
                  <span class='dashicons dashicons-dismiss'
                        style='color: red'></span>
                  <span>A problem occurred while activating. Please try again later</span>
              </span>
              <span class='status progress'>
                  <span class='dashicons dashicons-update'></span>
                  <span class='description'></span>
              </span>
            </span>
          </div>
         ";
  }

  function trinity_show_warning_need_to_activate() {
    echo '
        <div class="notice notice-warning activate-plugin">
          <p class="message">Your Trinity Audio player is not functional yet! Please review the settings and click <strong>Save Changes</strong> at the bottom of the page to activate it.</p>
          <p><a href="https://trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide/" target="_blank">Click here for further details</a></p>
        </div>
    ';
  }

  function trinity_audio_first_time_install_notice() {
    if (trinity_get_first_time_install()) {
      ?>
      <div class="notice notice-info">
        <p>We're excited that you've decided to join the audio future! Please note that our servers might take up to 5
          minutes to update before you can start using the plugin. Almost there!</p>
        <p>
          Please save your secret recovery key <span class="bold-text"> (Install Key): <?php echo trinity_get_install_key(); ?> </span> in a safe
          place.
          This key is unique and bound to your domain. It is required for restoring your installation (a new environment of
          any sort).
        </p>
      </div>
      <?php
    }
  }
