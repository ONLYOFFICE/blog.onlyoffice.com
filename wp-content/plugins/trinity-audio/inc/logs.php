<?php
  function trinity_log($message, $detailed_message = '', $debug_info = '', $log_type = TRINITY_AUDIO_ERROR_TYPES::info) {
    if ($log_type === TRINITY_AUDIO_ERROR_TYPES::error || $log_type === TRINITY_AUDIO_ERROR_TYPES::warn) {
      error_log($detailed_message);
    }

    // rotate file when size is exceeded TRINITY_AUDIO_LOG_MAX_SIZE_KB
    if (file_exists(TRINITY_AUDIO_LOG) && filesize(TRINITY_AUDIO_LOG) >= TRINITY_AUDIO_LOG_MAX_SIZE_KB * 1024) {
      trinity_rotate_log();
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

  function trinity_rotate_log() {
    $dir_list = scandir(TRINITY_AUDIO_LOG_DIR);

    $log_files = array_filter($dir_list, 'trinity_start_with');
    sort($log_files);
    array_splice($log_files, count($log_files) - TRINITY_AUDIO_LOG_MAX_FILES);

    foreach ($log_files as $log_file) {
      $full_path_log_file = TRINITY_AUDIO_LOG_DIR . '/' . $log_file;
      $ok                 = @unlink($full_path_log_file);

      if (!$ok) {
        $output = trinity_log_prepare_output('Can\'t delete log file ' . $log_file, error_get_last()['message'], '', TRINITY_AUDIO_ERROR_TYPES::warn);
        trinity_log_to_file($output);
      } else {
        $output = trinity_log_prepare_output('Rotated log file' . $log_file, '', '', TRINITY_AUDIO_ERROR_TYPES::info);
        trinity_log_to_file($output);
      }
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
