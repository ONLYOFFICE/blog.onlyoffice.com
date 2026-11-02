<?php
  /**
   * @link              trinityaudio.ai
   * @since             1.0.0
   * @package           TrinityAudio
   *
   * @wordpress-plugin
   * Plugin Name:       Trinity Audio
   * Plugin URI:        https://www.trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide
   * Description:       This plugin generates an audio version of the post, for absolutely FREE. You can choose the language and the gender of the voice reading your content. You also have the option to add Trinity Audio's player on select posts or have it audiofy all of your content. In both cases, it only takes a few simple clicks to get it done. The plugin is built through collaboration with the Amazon Polly team.
   * Version:           5.26.0
   * Author:            Trinity Audio
   * Author URI:        https://trinityaudio.ai/
   * License:           GPL-3.0 ONLY
   * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
   */

  require_once __DIR__ . '/inc/common.php';
  require_once __DIR__ . '/utils.php';
  require_once __DIR__ . '/inc/post-hashes.php';

  require_once __DIR__ . '/inc/post-hashes-cron.php'; // doesn't work when init later, e.g. after is_admin checking

  if (is_admin()) {
    require_once __DIR__ . '/admin/index.php';
  }

  // should be in main file, in order to fire up
  register_activation_hook( __FILE__ , 'trinity_audio_activation');
  register_deactivation_hook( __FILE__ , 'trinity_audio_deactivation');

  add_action('wp_head', 'trinity_hook_header');
  add_filter('the_content', 'trinity_content_filter', 99999);

  function trinity_content_filter($content) {
    $date = trinity_get_date();

    wp_enqueue_script("the_content-hook-script", plugin_dir_url(__FILE__) . 'js/the_content-hook-script.js');

    // Check if we're inside the main loop.
    $is_single   = is_single();
    $in_the_loop = in_the_loop() ?: !!trinity_get_check_for_loop();

    $main_post_id = get_queried_object_id();
    $renders_post_id = get_the_ID();
    $is_renders_main_post_id = $main_post_id === $renders_post_id;

    $is_main_query = is_main_query();

    if (!($is_single && $in_the_loop && $is_main_query && $is_renders_main_post_id)) {
      wp_add_inline_script("the_content-hook-script", "console.debug('TRINITY_WP', 'Skip player from rendering', 'is single: $is_single, is main loop: $in_the_loop, is main query: $is_main_query, is renders main post id: $is_renders_main_post_id. Main post ID: $main_post_id, renders post id: $renders_post_id', 'TS: $date');");

      if (strpos($content, TRINITY_AUDIO_STARTUP) !== false) {
        wp_add_inline_script("the_content-hook-script", "console.debug('TRINITY_WP', 'Post content contains trinity tag');");
      }

      return $content;
    }

    $post_id = $GLOBALS['post']->ID;

    $is_no_text = (bool)trinity_is_text_empty($content);
    $is_enabled = trinity_is_enabled_for_post($post_id);
    $posthash   = trinity_ph_get_audio_posthash($post_id);

    $is_bulk_update_done = trinity_phbu_is_bulk_update_done();
    $fist_time_install   = trinity_get_first_time_install();

    if ($is_enabled && !$is_no_text && is_singular()) {
      $player_label = trinity_get_player_label();

      // messages for admin only.
      if (trinity_is_user_admin()) {
//        if (!$is_bulk_update_done) {
//          $content = '
//          <div style="color: red; margin: 30px auto; font-size: 16px;">
//            It seems that you have yet to complete the plugin configuration. Please go to the plugin settings, choose the relevant options and click save in order to start using the player and audiofy your content!
//          </div>' . $content;
//        }

        if ($fist_time_install) {
          $content = '
            <div style="font-size: 14px;">
              <div style="margin: 10px 0;">We\'re excited that you\'ve decided to join the audio future! Please note that it might take a few minutes for the player to render properly. Almost there!
                minute to update before you can start using the plugin. Almost there!</div>
            </div>' . $content;
        }

        // leave it the last, so that messages goes first.
        if ($fist_time_install) { // !$is_bulk_update_done ||
          $content = '<div style="font-size: 14px;">That message only visible to you as administrator.</div>' . $content;
        }
      }

      if (!$fist_time_install) {
        $audio_part = trinity_include_audio_player($content);

        if (!$audio_part) {
          $content .= "<script>console.warn('TRINITY_WP', 'Do not include player for post ID: $post_id, no text for playback was found. TS: $date')</script>";
        } else {
          $player_content = '
        <table id="trinity-audio-table" style="width: 100%; display: table; border: none; margin: 0">
            <tr>
                <td id="trinity-audio-tab" style="border: none;">
                    <div id="trinity-audio-player-label">' . $player_label . '</div>
                    ' . $audio_part . '
                </td>
            </tr>
        </table>';

          $content = $player_content . $content;
        }
      } else {
        wp_add_inline_script("the_content-hook-script", "console.warn('TRINITY_WP', 'Hide player for post ID: $post_id, bulk update: $is_bulk_update_done, first time install: $fist_time_install', 'TS: $date')");
      }
    } else {
      wp_add_inline_script("the_content-hook-script", "console.warn('TRINITY_WP', 'Hide player for post ID: $post_id, enabled: $is_enabled, posthash: $posthash, is no text: $is_no_text', 'TS: $date')");
    }

    return $content;
  }
