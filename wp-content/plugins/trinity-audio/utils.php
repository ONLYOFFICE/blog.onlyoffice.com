<?php
  require_once __DIR__ . '/inc/logs.php';
  require_once __DIR__ . '/inc/constants.php';

  function trinity_handle_error($response, $url, $body = '', $user_error_message = '', $die = true) {
    if (is_wp_error($response)) {
      $error_code    = $response->get_error_code();
      $error_message = $response->get_error_message();
      $error_data    = $response->get_error_data();

      $error = '';
      if ($error_code) {
        $error = $error . 'Code: ' . $error_code . ".\n";
      }
      if ($error_message) {
        $error = $error . 'Message: ' . $error_message . ".\n";
      }
      if ($error_data) {
        $error = $error . 'Error data: ' . $error_data . ".\n";
      }

      $debug_data = [
        'url' => $url,
      ];

      if ($body) {
        $debug_data['body'] = var_export($body, true);
      }

      trinity_log($user_error_message, $error, trinity_dump_object($debug_data), TRINITY_AUDIO_ERROR_TYPES::error);
      if ($die) {
        die($user_error_message ? $user_error_message : $error);
      } else {
        return false;
      }
    } elseif ($response['response']['code'] !== 200) {
      $error = $response['response']['message'] . ' (' . $response['response']['code'] . '). URL: ' . $url;

      $debug_data = [
        'url' => $url,
      ];

      // TODO: truncate data if it's too long.
      if ($body) {
        $debug_data['body'] = var_export($body, true);
      }

      trinity_log($user_error_message, $error, trinity_dump_object($debug_data), TRINITY_AUDIO_ERROR_TYPES::error);
      if ($die) {
        die($user_error_message ? $user_error_message : $error);
      } else {
        return false;
      }
    }

    return true;
  }

  function trinity_get_date() {
    return date('Y-m-d H:i:s');
  }

  function trinity_dump_object($object) {
    return bin2hex(strrev(base64_encode(var_export($object, true))));
  }

  function trinity_start_with($str) {
    return substr($str, 0, strlen(TRINITY_AUDIO_LOG_FILE_PART_NAME)) === TRINITY_AUDIO_LOG_FILE_PART_NAME;
  }

  /**
   * Return time diff in ms
   *
   * @param $start
   * @return float
   */
  function trinity_get_time_diff($start) {
    return round((microtime(true) - $start) * 1000, 0);
  }

  function trinity_report_long_requests($start, $url) {
    $diff = trinity_get_time_diff($start);
    if ($diff < TRINITY_AUDIO_REPORT_LONG_HTTP_REQUESTS_THRESHOLD) {
      return false;
    }

    trinity_log("Request time to $url took $diff ms", '', '', TRINITY_AUDIO_ERROR_TYPES::warn);

    return true;
  }

  function trinity_is_dev_env() {
    return TRINITY_AUDIO_SERVICE !== 'https://audio.trinityaudio.ai';
  }
