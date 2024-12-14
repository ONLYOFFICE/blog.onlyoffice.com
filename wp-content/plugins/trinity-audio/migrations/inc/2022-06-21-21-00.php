<?php
  if (!trinity_get_install_key()) return;
  if (!trinity_should_migrate_for('4.2.4')) return;

  $response = trinity_send_stat_migrate_v5_settings();

  if ($response->STATUS === 'error') update_option(TRINITY_AUDIO_CONFIGURATION_V5_FAILED, 1);

