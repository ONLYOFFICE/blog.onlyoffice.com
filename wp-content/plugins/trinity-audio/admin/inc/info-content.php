<?php
  require_once __DIR__ . '/../../inc/templates.php';
  require_once __DIR__ . '/../../inc/common.php';
?>

<?php
    $package_data = trinity_get_package_data();
    notifications($package_data);
?>

<div class="wrap trinity-page" id="trinity-admin-info">
  <div class="wizard-progress-wrapper">
    <div class="trinity-head">Info</div>
    <?php require_once __DIR__ . '/../inc/progress.php'; ?>
  </div>
  <div class="flex-grid">
    <div class="row">
      <div class="column">
        <section>
          <div class="section-title">General Info</div>
          <div class="trinity-section-body">

            <div class="section-form-group">
              <div class="section-form-title">
                Recovery Token:
              </div>

              <div>
                <?php trinity_show_recovery_token(); ?>
              </div>

              <div class="section-form-title trinity-hide">
                Account key:
              </div>

              <div class="trinity-hide">
                <?= esc_html($package_data->package->account_key); ?>
              </div>

              <p class='description'>Your unique token bound to your domain. Keep it in secret. Using that token allows restoring your installation in a new environment. </p>
            </div>

            <div class="section-form-group">
              <?php trinity_show_articles_usage($package_data); ?>
            </div>

          </div>
        </section>
      </div>

      <div class="column">
        <section>
          <div class="section-title">Subscription</div>
          <div class="trinity-section-body plan-section">
            <?php trinity_current_package_info_template($package_data); ?>
          </div>
        </section>
      </div>
    </div>

    <div class="row trinity-hide">
      <div class="column">
        <section>
          <div class="section-title">Technical Info</div>
          <div class="trinity-section-body">

            <div class="table-title">PHP Configuration:</div>
            <table class="form-table">
              <tr>
                <td><?php trinity_info_tech_show_config(); ?></td>
              </tr>
            </table>

            <div class="table-title">Connection:</div>
            <table class="form-table">
              <tr>
                <td>
                  <table class="trinity-inner">
                    <thead>
                    <tr>
                      <td><span class="trinity-bold-text">Checking</span></td>
                      <td colspan="2"><span class="trinity-bold-text">Result</span></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                      <td>
                        DNS resolving hostname #1
                      </td>
                      <td colspan="2">
                        <?= esc_html(gethostbyname(TRINITY_AUDIO_TEST_HOST)); ?>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        DNS resolving hostname #2
                      </td>
                      <td colspan="2">
                        <?= esc_html(gethostbyname(TRINITY_AUDIO_SERVICE_HOST)); ?>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        DNS records
                      </td>
                      <td colspan="2" style="white-space: pre-line"><?= esc_html(trinity_init_checks_get_DNS_info()); ?></td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #1</strong> using wp_remote_get
                      </td>
                      <td colspan="2">
                        <?= esc_html(trinity_init_checks_speed_wp(TRINITY_AUDIO_TEST_SERVICE)); ?> ms
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #1</strong> using curl
                      </td>
                      <td colspan="2">
                        <?= esc_html(trinity_init_checks_speed_curl(TRINITY_AUDIO_TEST_SERVICE)); ?> ms
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </table>

          </div>
        </section>
      </div>
    </div>
  </div>
</div>
