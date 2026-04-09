<?php
  require_once __DIR__ . '/../../inc/constants.php';

  trinity_send_graphite_metric('wordpress.post-management.opened');

  // Check if the form is submitted
  if (isset($_POST['action'])) {
    trinity_check_post_nonce('trinity-audio-post-management');

    $action = $_POST['post-management-action'];

    $posts = [];
    if ($action === 'activate-all-posts' || $action === 'deactivate-all-posts') $posts = trinity_get_posts();
    if ($action === 'activate-all-posts-range') {
      $posts = get_posts(
        [
          'fields'         => 'ids', // Only get post IDs
          'orderby'        => 'ID',
          'order'          => 'DESC',
          'post_type'      => ['post'],
          'posts_per_page' => -1,
          'date_query'     => [[
            $_POST['range-before-after'] => sanitize_text_field($_POST['range-date']),
          ]]
        ]
      );
    }

    $posts_num = count($posts);

    $value = $action === 'deactivate-all-posts' ? 0 : 1;

    foreach ($posts as $post_id) {
      update_post_meta($post_id, TRINITY_AUDIO_ENABLED, $value);
    }

    if ($posts_num === 0) {
      echo "<div class='notice notice-warning'><p>No posts were affected</p></div>";
    } else {
      $success_message = str_replace('##AMOUNT##', $posts_num, TRINITY_AUDIO_POST_MANAGEMENT_SUCCESS_MESSAGES[$action]);
      if ($action === 'activate-all-posts-range') {
        $success_message = str_replace('##BEFORE-AFTER##', $_POST['range-before-after'], $success_message);
        $success_message = str_replace('##DATE##', $_POST['range-date'], $success_message);
      }

      echo "<div class='notice notice-success'><p>" . esc_html($success_message) . "</p></div>";
    }

    trinity_send_graphite_metric('wordpress.post-management.' . $action);
  }

  trinity_show_bulk_progress();
?>
<div class="wrap trinity-page" id="trinity-admin-post-management">
    <div class="wizard-progress-wrapper">
        <div class="trinity-head">Post Management</div>
      <?php require_once __DIR__ . '/../inc/progress.php'; ?>
    </div>

    <form action="<?= esc_url($_SERVER['REQUEST_URI']); ?>" method="post"
          name="trinity_audio_post_management">
        <input type="hidden" name="action" value="trinity_audio_post_management">
        <?php wp_nonce_field('trinity-audio-post-management', TRINITY_AUDIO_NONCE_NAME); ?>

        <div class="flex-grid">
            <div class="row">
                <div class="column">
                    <section style="width: 100%">
                        <form method="post">
                            <div class="section-title">Manage posts</div>
                            <div class="trinity-section-body">
                                <div class="section-form-group">
                                    <label class="section-form-title">
                                        <input type="radio"
                                               name="post-management-action"
                                               value="activate-all-posts"
                                               required
                                        />
                                        <span>Enable on all posts</span>
                                    </label>
                                    <p class="description">Enable Trinity Audio Player on all posts</p>
                                </div>

                                <div class="section-form-group">
                                    <label class="section-form-title">
                                        <input type="radio"
                                               name="post-management-action"
                                               value="activate-all-posts-range"
                                               required
                                        />
                                        <span>
                                        Enable on all posts published
                                        <select name="range-before-after">
                                            <option value="after">After</option>
                                            <option value="before">Before</option>
                                        </select>
                                        <input type="date" name="range-date"/>
                                    </span>
                                    </label>
                                    <p class="description">Enable Trinity Audio Player on all posts for selected date
                                        range</p>
                                </div>

                                <div class="section-form-group">
                                    <label class="section-form-title">
                                        <input type="radio"
                                               name="post-management-action"
                                               value="manual"
                                               required
                                        />
                                        <span>Manually choose the posts to enable the player on</span>
                                    </label>
                                    <p class="description">Will redirect to Posts edit page where you can do it by
                                        yourself</p>
                                </div>

                                <div class="hl"></div>

                                <div class="section-form-group">
                                    <label class="section-form-title">
                                        <input type="radio"
                                               name="post-management-action"
                                               value="deactivate-all-posts"
                                               required
                                        />
                                        <span>Disable on all posts</span>
                                    </label>
                                    <p class="description">Disable Trinity Audio Player for all posts</p>
                                </div>

                                <button class="save-button">Submit</button>
                            </div>
                        </form>
                    </section>
                    <section style="width: 100%" class="guides">
                        <div class="section-title">Guides</div>
                        <div class="trinity-section-body">
                            <div class="section-form-group">
                                <a href="https://www.trinityaudio.ai/wordpress-plugin-manage-posts-in-bulk" target="_blank">How to manage active posts</a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </form>
</div>
