<?php
  function trinity_init_checks_get_ini() {
    $min_required_params = [
      'memory_limit'        => '256M',
      'post_max_size'       => '8M',
      'upload_max_filesize' => '2M',
      'max_execution_time'  => '30',
      'max_input_time'      => '5',
    ];

    $output = [];

    foreach ($min_required_params as $key => $value) {
      $output[$key] = [
        'min_required' => $min_required_params[$key],
        'actual'       => ini_get($key),
      ];
    }

    return $output;
  }

  function trinity_init_checks_speed_wp($url) {
    $start = microtime(true);

    $response = wp_remote_get(
      $url,
      [
        'timeout'     => TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT,
        'httpversion' => '1.1',
        'sslverify'   => false,
      ]
    );

    $is_ok = trinity_handle_error($response, $url, '', '', false);
    if (!$is_ok) {
      return trinity_can_not_connect_error_message('Can\'t connect to testing endpoint');
    }

    return trinity_get_time_diff($start);
  }

  function trinity_init_checks_speed_curl($url) {
        $start = microtime(true);
        $response = wp_remote_get(
          $url,
          [
            'timeout'     => TRINITY_AUDIO_MAX_HTTP_REQ_TIMEOUT,
            'httpversion' => '1.1',
            'sslverify'   => false,
          ]
        );
        if(  is_wp_error( $response ) ){
                return trinity_can_not_connect_error_message('Can\'t connect to testing endpoint');
        }

        return trinity_get_time_diff($start);
  }

  function trinity_init_checks_get_DNS_info() {
    $result = dns_get_record(TRINITY_AUDIO_SERVICE_HOST);
    $output = [];

    foreach ($result as $record) {
      $str = '';
      foreach ($record as $value) {
        $str .= $value . ' ';
      }

      $output[] = $str;
    }

    return implode("\n", $output);
  }
