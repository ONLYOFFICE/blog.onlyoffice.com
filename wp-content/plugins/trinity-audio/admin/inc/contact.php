<?php
    require_once __DIR__ . '/../../inc/constants.php';
?>

<div class="wrap trinity-page" id="trinity-admin-contact-us">
  <!-- render iframe so we can generate report -->
  <iframe src="admin.php?page=trinity_audio_info" style="display: none"></iframe>

  <div class="wizard-progress-wrapper">
    <div class="trinity-head">Contact us</div>
    <?php require_once __DIR__ . '/../inc/progress.php'; ?>
  </div>

  <div class="flex-grid">
    <div class="row">

      <div class="column">
        <section>
          <form method="post">
            <div class="section-title">Contact us</div>
            <div class="trinity-section-body">

              <div class="section-form-group">
                <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SENDER_NAME); ?>">
                  Full name
                </label>
                <?php trinity_sender_name(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SENDER_EMAIL); ?>">
                  Email
                </label>
                <?php trinity_sender_email(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SENDER_WEBSITE); ?>">
                  Website
                </label>
                <?php trinity_sender_website(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SENDER_MESSAGE); ?>">
                  Message
                </label>
                <?php trinity_sender_message(); ?>
              </div>

              <div class="section-form-group">
                <label class="section-form-title" for="<?= esc_attr(TRINITY_AUDIO_SENDER_INCLUDE_LOG); ?>">
                  Include logs:
                </label>
                <?php trinity_sender_include_logs(); ?>
              </div>

              <div class="trinity-send-wrapper">
                <div class="trinity-submit-wrapper">
                  <span class='trinity-status-wrapper'>
                    <span class="status success">
                      <span class="dashicons dashicons-yes" style="color: green"></span>
                      <span>Message successfully sent!</span>
                    </span>
                    <span class='status error'>
                      <span class='dashicons dashicons-dismiss' style='color: red'></span>
                      <span>A problem occurred while sending. Please try again later</span>
                    </span>
                    <span class='status progress'>
                      <span class='dashicons dashicons-update'></span>
                      <span class='description'>Sending...</span>
                    </span>
                  </span>
                </div>
                <button class="trinity-contact-us-button">Send</button>
              </div>
            </div>
          </form>
        </section>
      </div>

      <div class="column">
        <section style="display: none;">
          <div class="section-title">Tell us what you think</div>
          <div class="trinity-section-body trinity-feedback-background">
            <form action="#">
              <div class="feature-title">
                Hey you, do you find our WordPress app useful?
              </div>
              <p class="description">
                It would help us a lot if you could take a moment to rate us
              </p>

              <div class="trinity-stars">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
              </div>

              <div class="feature-title bottom-space-10">
                Also, we'd love to hear you out, Leave us a feedback :)
              </div>

              <div class="section-form-group">
                <div>
                  Title
                </div>
                <?php trinity_feedback_title(); ?>
              </div>

              <div class="section-form-group">
                <label for="<?= esc_attr(TRINITY_AUDIO_FEEDBACK_MESSAGE); ?>">
                  Feedback
                </label>
                <?php  trinity_feedback_message(); ?>
                <div style="width: 66%">
                  <button class="trinity-feedback-button">Send</button>
                </div>

              </div>

            </form>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>

<?php
  function trinity_came_from_logs() {
    return isset($_GET['from']) && $_GET['from'] === 'logs';
  }

  function trinity_sender_email() {
    $value = trinity_get_user_email();
    echo "<input placeholder='Please enter a valid email address' class='trinity-custom-contact-input' type='text' required value='" . esc_attr($value) . "' name='email' id='" . esc_attr(TRINITY_AUDIO_SENDER_EMAIL) . "' />";
  }

  function trinity_sender_name() {
    $value = trinity_get_user_name();
    echo "<input placeholder='Please enter your full name' class='trinity-custom-contact-input' type='text' value='" . esc_attr($value) . "' name='name' id='" . esc_attr(TRINITY_AUDIO_SENDER_NAME) . "' required />";
  }

  function trinity_sender_website() {
    echo "<input placeholder='Please enter a valid URL address' class='trinity-custom-contact-input' type='text' value='' name='website' id='" . esc_attr(TRINITY_AUDIO_SENDER_WEBSITE) . "' required />";
  }

  function trinity_sender_message() {
    $value = trinity_came_from_logs() ? "Hi!\n\nHave some issues with .... [PLEASE DESCRIBE WHAT ISSUE DO YOU HAVE]" : '';

    echo "<textarea 
        placeholder='Tell us more...' class='custom-textarea' 
        required rows='5' name='message' 
        id='" . esc_attr(TRINITY_AUDIO_SENDER_MESSAGE) . "' class='large-text'>" . esc_html($value) . '</textarea>';
  }

  function trinity_feedback_title() {
    echo "<input placeholder='Recommended: Best product ever!!!' class='custom-input' type='text' value='' name='name' />";
  }

  function trinity_feedback_message() {
    echo "<textarea 
        placeholder='Tell us more...' autocomplete='off' 
        class='trinity-custom-contact-textarea large-text' 
        required rows='5' name='message' id='" . esc_attr(TRINITY_AUDIO_FEEDBACK_MESSAGE) . "'></textarea>";
  }

  function trinity_sender_include_logs() {
    echo "<label for='" . esc_attr(TRINITY_AUDIO_SENDER_INCLUDE_LOG) . "' class='custom-checkbox'>
      <div class='text-label'>Include logs</div>
      <input type='checkbox' name='include_log' id='" . esc_attr(TRINITY_AUDIO_SENDER_INCLUDE_LOG) . "' checked value='1'>
      <div class='custom-hitbox'></div>
    </label>";

    echo "<p class='description'>Select this option if you have some issues, so we can check your plugin logs</p>";
  }
