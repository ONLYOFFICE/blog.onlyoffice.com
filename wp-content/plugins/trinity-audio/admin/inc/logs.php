<div class="wrap" id="trinity-admin-logs">
  <div class="header-wrapper">
    <div class="h1">Logs</div>
    <a href="admin.php?page=trinity_audio_contact_us&from=logs">Send log file to Trinity Audio support
      team</a>
  </div>

  <table>
    <thead>
    <tr>
      <td class="date">Date</td>
      <td class="user-error">Message</td>
      <td class="detail-error">Detailed message</td>
    </tr>
    </thead>
    <tbody>
    <?php
      if (!file_exists(TRINITY_AUDIO_LOG)) {
        return;
      }

      try {
        $file = new SplFileObject(TRINITY_AUDIO_LOG, 'r');

        $file->seek(PHP_INT_MAX);
        $last_line = $file->key();

        $read_lines = 1000;
        $max_lines  = $last_line < $read_lines ? $last_line : $read_lines;

        for ($i = 0; $i <= $max_lines; ++$i) {
          $file->seek($last_line - $i);
          $line = $file->current();

          if (!empty($line)) {
            $result = json_decode($line);

            $type             = isset($result->type) ? $result->type : '-';
            $date             = isset($result->date) ? $result->date : '-';
            $message          = isset($result->message) ? $result->message : '-';
            $detailed_message = isset($result->detailed_message) ? $result->detailed_message : '-';

            echo "<tr class='" . esc_attr($type) . "'>
                  <td>" . esc_html($date) . '</td>
                  <td>' . esc_html($message) . '</td>
                  <td>' . esc_html($detailed_message) . '</td>
                </tr>';
          }
        }
      } catch (Exception $e) {
        error_log('Can\'t read log file: ' . $e);

        echo "<div class='error notice'>
                <p>Can't read log file. Please check your system logs</p>
                <p>" . esc_html($e) . '</p>
              </div>';
      }
    ?>
    </tbody>
  </table>

</div>
