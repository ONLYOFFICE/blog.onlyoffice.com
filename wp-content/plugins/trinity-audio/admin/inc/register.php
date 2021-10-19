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
                <p class="description">
                  In order to activate your Trinity audio player installation<br />Please complete your registration to Trinity audio services.
                </p>

                <div>
                  <label  class='custom-checkbox'>
                    <input type='checkbox' name="<?php echo TRINITY_AUDIO_TERMS_OF_SERVICE; ?>"  />
                    <div class='custom-hitbox'></div>
                    <div class='text-label'>
                      I accept the <a href="https://trinityaudio.ai/wp-plugin-terms/?utm_medium=wp_admin">Terms of Service</a>
                    </div>
                  </label>
                </div>

                <div>
                  <label  class='custom-checkbox'>
                    <input type='checkbox' name="<?php echo TRINITY_AUDIO_PRIVACY_STATEMENT; ?>"  />
                    <div class='custom-hitbox'></div>
                    <div class='text-label'>
                      I accept the <a href="https://trinityaudio.ai/privacy-policy/?utm_medium=wp_admin">Privacy Statement</a>
                    </div>
                  </label>
                </div>

                <div style="display: none">
                  <p style="margin-top: 10px" class="description">
                    If you've registered on <a href="<?php echo TRINITY_AUDIO_DASHBOARD_URL; ?>" target="_blank">Trinity Dashboard</a> and bought a plan in order to connect your Wordpress installation please provide an Account Key.
                  </p>
                  <label for="<?php echo TRINITY_AUDIO_PUBLISHER_TOKEN; ?>">
                    <span>Account Key (optional):</span>
                  </label>
                  <input type="text" class="custom-input"
                    name="<?php echo TRINITY_AUDIO_PUBLISHER_TOKEN; ?>"
                    id="<?php echo TRINITY_AUDIO_PUBLISHER_TOKEN; ?>"
                    spellcheck="false" style="width: 100%"/>
                </div>

                <div class="recover-install-key" style="display: none">
                    <h4 class="site-migration">
                        Site migration/re-install
                    </h4>
                    <p class="description">
                        If you've registered before and are now migrating to a new database or hosting service, please insert your
                        previous <span class="bold-text">Install Key</span>.
                    </p>
                    <br />
                    <p class="description">
                        Your install key can be found in your previous
                        <span class="bold-text">admin panel</span>, under <span class="bold-text">Trinity Audio -> Info -> Install key</span>.
                    </p>
                    <br />
                    <div>
                        <label for="<?php echo TRINITY_AUDIO_RECOVER_INSTALLKEY; ?>">
                            <span>Install Key:</span>
                        </label>
                        <input class="custom-input" type="text" name="<?php echo TRINITY_AUDIO_RECOVER_INSTALLKEY; ?>"
                               id="<?php echo TRINITY_AUDIO_RECOVER_INSTALLKEY; ?>" style="width: 100%"
                               spellcheck="false" />
                    </div>
                </div>
              </div>

              <button class="button button-primary">Register</button>
            </div>
          </section>
        </div>
      </div>
    </div>
  </form>
</div>
