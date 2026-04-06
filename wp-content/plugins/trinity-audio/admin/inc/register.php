<?php
  require_once __DIR__ . '/../../utils.php';
  require_once __DIR__ . '/../../inc/common.php';
  require_once __DIR__ . '/../../inc/constants.php';
?>

<div class="wrap trinity-page" id="trinity-admin">
  <div class="registration-error"></div>
  <h1 class="trinity-head">Trinity Audio - Registration</h1>

  <form method="post" id="register-site">
    <div class="flex-grid register">
      <div class="row">
        <div class="column">
          <section>
            <div class="section-title">Registration</div>
            <div class="trinity-section-body">
              <div class="section-form-group">
                <p class="description">In order to activate your Trinity audio player installation</p>

                <p class="description">Please complete your registration to Trinity audio services.</p>

                <div>
                  <label class='custom-checkbox'>
                    <input type='checkbox' name="<?= esc_attr(TRINITY_AUDIO_TERMS_OF_SERVICE); ?>"  />
                    <div class='custom-hitbox'></div>
                    <div class='text-label'>
                      I accept the <a href="<?= esc_url(trinity_add_utm_to_url('https://trinityaudio.ai/wp-plugin-terms')) ?>">Terms of Service</a>
                    </div>
                  </label>
                </div>

                <div>
                  <label class='custom-checkbox'>
                    <input type='checkbox' id="<?= esc_attr(TRINITY_AUDIO_EMAIL_SUBSCRIPTION); ?>" name="<?= esc_attr(TRINITY_AUDIO_EMAIL_SUBSCRIPTION); ?>"  />
                    <div class='custom-hitbox'></div>
                    <div class='text-label'>
                      I approve receiving occasional emails from Trinity Audio
                      <br>(we promise not to spam, and keep it professional)
                    </div>
                  </label>
                </div>

                <p class="description">
                  By clicking REGISTER, you agree that you have read our <a href="<?= esc_url(trinity_add_utm_to_url('https://trinityaudio.ai/privacy-policy/')) ?>">Privacy Policy</a>
                </p>

                <div class="recover-install-key" style="display: none">
                    <h4 class="site-migration">
                        Site migration/re-install
                    </h4>
                    <p class="description">
                        If you've registered before and are now migrating to a new database or hosting service, please insert your
                        previous <span class="trinity-bold-text">Install Key</span>.
                    </p>

                    <p class="description">
                        Your install key can be found in your previous
                        <span class="trinity-bold-text">admin panel</span>, under <span class="trinity-bold-text">Trinity Audio -> Info -> Install key</span>.
                    </p>

                    <div>
                        <label for="<?= esc_attr(TRINITY_AUDIO_RECOVER_INSTALLKEY); ?>">
                            <span>Install Key:</span>
                        </label>
                        <input class="custom-input" type="text" name="<?= esc_attr(TRINITY_AUDIO_RECOVER_INSTALLKEY); ?>"
                               id="<?= esc_attr(TRINITY_AUDIO_RECOVER_INSTALLKEY); ?>" style="width: 100%"
                               spellcheck="false" />
                    </div>
                </div>
              </div>

              <button class="save-button width-auto">Register</button>
            </div>
          </section>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  jQuery(document).ready(() => {
    trinitySendMetric('wordpress.signup.opened');
  })
</script>
