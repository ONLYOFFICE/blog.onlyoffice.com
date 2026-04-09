<?php
  function trinity_log($message, $detailed_message = '', $debug_info = '', $log_type = TRINITY_AUDIO_ERROR_TYPES::info) {
    if ($log_type === TRINITY_AUDIO_ERROR_TYPES::error || $log_type === TRINITY_AUDIO_ERROR_TYPES::warn) {
      error_log($detailed_message);
    }

    /*
     * Note, that in while cycle - filesize will return cached result of size. In order to avoid it - you have to use
     * clearstatcache(), but it might slow down OS
     */

    if (file_exists(TRINITY_AUDIO_LOG) && filesize(TRINITY_AUDIO_LOG) >= TRINITY_AUDIO_LOG_MAX_SIZE) {
      trinity_empty_log();
    }

    $output = trinity_log_prepare_output($message, $detailed_message, $debug_info, $log_type);
    trinity_log_to_file($output);

    // TODO: log to depart
  }

  function trinity_log_prepare_output($message, $detailed_message, $debug_info, $log_type) {
    return json_encode(
      [
        'date'             => trinity_get_date(),
        'message'          => $message,
        'detailed_message' => $detailed_message,
        'debug_info'       => $debug_info,
        'type'             => $log_type,
      ]
    );
  }

  function trinity_empty_log() {
    $rotating_log_file = TRINITY_AUDIO_LOG;

    $ok = @unlink($rotating_log_file);
    if (!$ok) {
      error_log("Can't remove log file: $rotating_log_file. Try to empty it then...");
      $fp = @fopen($rotating_log_file, 'w');
      if (!$fp) return error_log("Can't empty log file: $rotating_log_file");
      fclose($fp);
    }
  }

  function trinity_log_to_file($message, $file_path = TRINITY_AUDIO_LOG, $mode = 'a+') {
    try {
      $ok = $fp = @fopen($file_path, $mode);
      if (!$ok) {
        return error_log('Can\'t open file ' . $file_path . ' ' . error_get_last()['message']);
      }

      $ok = @fwrite($fp, $message . "\n");
      if (!$ok) {
        return error_log('Can\'t write to file ' . $file_path . ' ' . error_get_last()['message']);
      }

      fclose($fp);
    } catch (Exception $e) {
    }
  }
