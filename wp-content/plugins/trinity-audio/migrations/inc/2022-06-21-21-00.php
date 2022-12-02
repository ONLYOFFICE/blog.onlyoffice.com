<?php
  if (!trinity_get_install_key()) return;

  $response = trinity_send_stat_migrate_v5_settings();

  if ($response->STATUS === 'error') update_option(TRINITY_AUDIO_CONFIGURATION_V5_FAILED, 1);

