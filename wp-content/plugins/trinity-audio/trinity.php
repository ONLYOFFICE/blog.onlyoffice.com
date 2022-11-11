<?php
  /**
   * @link              trinityaudio.ai
   * @since             1.0.0
   * @package           TrinityAudio
   *
   * @wordpress-plugin
   * Plugin Name:       Trinity Audio
   * Plugin URI:        https://wordpress.org/plugins/trinity-audio/
   * Description:       This plugin generates an audio version of the post, for absolutely FREE. You can choose the language and the gender of the voice reading your content. You also have the option to add Trinity Audio's player on select posts or have it audiofy all of your content. In both cases, it only takes a few simple clicks to get it done. The plugin is built through collaboration with the Amazon Polly team.
   * Version:           4.2.4
   * Author:            Trinity Audio
   * Author URI:        https://trinityaudio.ai/
   * License:           GPL-3.0 ONLY
   * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
   */


  require_once __DIR__ . '/admin/index.php';
  require_once __DIR__ . '/inc/common.php';
  require_once __DIR__ . '/migrations/index.php';
  require_once __DIR__ . '/initial_checking.php';
  require_once __DIR__ . '/utils.php';

  if (trinity_is_dev_env()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
  }

  add_action('wp_head', 'trinity_hook_header');

  add_action('plugins_loaded', 'trinity_plugin_loaded');

  add_action('admin_post_' . TRINITY_AUDIO_BULK_UPDATE, 'trinity_bulk_update');

  add_filter('the_content', 'trinity_content_filter', 99999);

  trinity_init_default_settings();

  register_deactivation_hook(__FILE__, 'trinity_audio_deactivation');

  function trinity_audio_deactivation() {
    trinity_send_stat(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'deactivating', false);
  }

  function trinity_init_default_settings() {
    // lets add keys as well, so if user has them from previous setup - can find them by name and edit them.
    add_option(TRINITY_AUDIO_INSTALLKEY, '', '', true);
    add_option(TRINITY_AUDIO_VIEWKEY, '', '', true);

    add_option(TRINITY_AUDIO_PLUGIN_VERSION, [], '', true);
    add_option(TRINITY_AUDIO_PLUGIN_MIGRATION, [], '', true);
    add_option(TRINITY_AUDIO_SOURCE_LANGUAGE, 'en-US', '', true);
    add_option(TRINITY_AUDIO_POWERED_BY, 1, '', true);
    add_option(TRINITY_AUDIO_PRECONNECT, 1, '', true);
    add_option(TRINITY_AUDIO_GENDER_ID, 'f', '', true);
    add_option(TRINITY_AUDIO_PLAYER_POSITION, 'before', '', true);
    add_option(TRINITY_AUDIO_PLAYER_LABEL, '', '', true);
    add_option(TRINITY_AUDIO_SOURCE_NEW_POSTS_DEFAULT, 1, '', true);
    add_option(TRINITY_AUDIO_ADD_POST_TITLE, 1, '', true);
    add_option(TRINITY_AUDIO_ADD_POST_EXCERPT, '', '', true);
    add_option(TRINITY_AUDIO_SKIP_TAGS, [], '', true);
    add_option(TRINITY_AUDIO_ALLOW_SHORTCODES, [], '', true);
    add_option(TRINITY_AUDIO_CHECK_FOR_LOOP, 1, '', true);
    add_option(TRINITY_AUDIO_TRANSLATE, 0, '', true);
    add_option(TRINITY_AUDIO_FIRST_CHANGES_SAVE, 0, '', true);
  }

  function trinity_content_filter($content) {
    $date = trinity_get_date();

    wp_enqueue_script("the_content-hook-script", plugin_dir_url(__FILE__) . 'js/the_content-hook-script.js');

    // Check if we're inside the main loop.
    $is_single     = is_single();
    $in_the_loop   = in_the_loop(); // $in_the_loop   = trinity_get_check_for_loop() ? in_the_loop() : true;.
    $is_main_query = is_main_query();
    if (!($is_single && $in_the_loop && $is_main_query)) {
      wp_add_inline_script("the_content-hook-script", "console.debug('TRINITY_WP', 'Skip player from rendering', 'is single: $is_single, is main loop: $in_the_loop, is main query: $is_main_query', 'TS: $date');");

      if (strpos($content, TRINITY_AUDIO_STARTUP) !== false) {
        wp_add_inline_script("the_content-hook-script", "console.debug('TRINITY_WP', 'Post content contains trinity tag');");
      }

      return $content;
    }

    $post_id = $GLOBALS['post']->ID;

    $is_no_text = (bool)trinity_is_text_empty($content);
    $is_enabled = trinity_is_enabled_for_post($post_id);
    $posthash   = trinity_get_audio_posthash($post_id);

    $bulk_update       = trinity_get_is_bulk_updated();
    $fist_time_install = trinity_get_first_time_install();

    if ($is_enabled && $posthash && !$is_no_text && is_singular()) {
      $player_label = trinity_get_player_label();

      $audio_part = trinity_include_audio_player();

      // messages for admin only.
      if (trinity_is_user_admin()) {
        if (!$bulk_update) {
          $content = '
          <div style="color: red; margin: 30px auto; font-size: 16px;">
            It seems that you have yet to complete the plugin configuration. Please go to the plugin settings, choose the relevant options and click save in order to start using the player and audiofy your content!
          </div>' . $content;
        }

        if ($fist_time_install) {
          $content = '
            <div style="font-size: 14px;">
              <div style="margin: 10px 0;">We\'re excited that you\'ve decided to join the audio future! Please note that our servers might take up to 5
                minutes to update before you can start using the plugin. Almost there!</div>
            </div>' . $content;
        }

        // leave it the last, so that messages goes first.
        if (!$bulk_update || $fist_time_install) {
          $content = '<div style="font-size: 14px;">That message only visible to you as administrator.</div>' . $content;
        }
      }

      if ($bulk_update && !$fist_time_install) {
        if (!$audio_part) {
          $content .= "<script>console.warn('TRINITY_WP', 'Do not include player for post ID: $post_id, no text for playback was found. TS: $date')</script>";
        } else {
          $player_content = '
        <table id="trinity-audio-table" style="width:100%; display: table;">
            <tr>
                <td id="trinity-audio-tab" style="border: none;">
                    <div id="trinity-audio-player-label">' . $player_label . '</div>
                    ' . $audio_part . '
                </td>
            </tr>
        </table>';

          $player_position = trinity_get_player_position();

          if ('before' === $player_position) {
            $content = $player_content . $content;
          } elseif ('after' === $player_position) {
            $content .= $player_content;
          }
        }
      } else {
        wp_add_inline_script("the_content-hook-script", "console.warn('TRINITY_WP', 'Hide player in for post ID: $post_id, bulk update: $bulk_update, first time install: $fist_time_install', 'TS: $date')");
      }
    } else {
      wp_add_inline_script("the_content-hook-script", "console.warn('TRINITY_WP', 'Hide player in for post ID: $post_id, enabled: $is_enabled, posthash: $posthash, is no text: $is_no_text', 'TS: $date')");
    }

    return $content;
  }

  function trinity_plugin_loaded() {
    trinity_migration_init();
  }
