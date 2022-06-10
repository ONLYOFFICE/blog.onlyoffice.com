<?php
  require_once __DIR__ . '/../../inc/templates.php';
  require_once __DIR__ . '/../../inc/common.php';
?>

<!-- keep styles here, as they will be sent as logs -->
<style>
    .trinity-phpinfo iframe {
        width: 100%;
        height: 700px;
    }

    .trinity-monospace {
        white-space: pre-line;
    }

    table.trinity-inner td {
        padding: 5px 10px;
    }
</style>

<?php
    $package_data = trinity_get_package_data();
    notifications($package_data);
?>

<div class="wrap trinity-page" id="trinity-admin-info">
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
                <?php echo $package_data->package->account_key; ?>
              </div>

              <p class='description'>Your unique token bound to your domain. Keep it in secret. Using that token allows restoring your installation in a new environment. </p>
            </div>

            <div class="section-form-group">
              <?php show_articles_usage($package_data); ?>
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
                      <td><span class="bold-text">Checking</span></td>
                      <td colspan="2"><span class="bold-text">Result</span></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                      <td>
                        DNS resolving hostname #1
                      </td>
                      <td colspan="2">
                        <?php echo gethostbyname(TRINITY_AUDIO_TEST_HOST); ?>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        DNS resolving hostname #2
                      </td>
                      <td colspan="2">
                        <?php echo gethostbyname(TRINITY_AUDIO_SERVICE_HOST); ?>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        DNS records
                      </td>
                      <td colspan="2" class="trinity-monospace"><?php echo trinity_init_checks_get_DNS_info(); ?></td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #1</strong> using wp_remote_get
                      </td>
                      <td colspan="2">
                        <?php echo trinity_init_checks_speed_wp(TRINITY_AUDIO_TEST_SERVICE); ?> ms
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #1</strong> using curl
                      </td>
                      <td colspan="2">
                        <?php echo trinity_init_checks_speed_curl(TRINITY_AUDIO_TEST_SERVICE); ?> ms
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #2</strong> using wp_remote_get
                      </td>
                      <td colspan="2">
                        <?php echo trinity_init_checks_speed_wp(TRINITY_AUDIO_LANGUAGES_URL); ?> ms
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Speed and ability to connect to <strong>endpoint #2</strong> using curl
                      </td>
                      <td colspan="2">
                        <?php echo trinity_init_checks_speed_curl(TRINITY_AUDIO_LANGUAGES_URL); ?> ms
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr class="trinity-phpinfo trinity-hide">
                <th>
                  <label>PHP Info:</label>
                </th>
                <td>
                  <?php
                    ob_start();
                    require_once __DIR__ . '/phpinfo.php';
                    $contents = ob_get_contents();
                    ob_end_clean();
                    echo "<iframe srcdoc='$contents'></iframe>";
                  ?>
                </td>
              </tr>
            </table>

          </div>
        </section>
      </div>
    </div>
  </div>
</div>
