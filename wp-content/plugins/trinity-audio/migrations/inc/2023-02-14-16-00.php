<?php
  if (!trinity_should_migrate_for('5.4.1')) return;

  delete_option('trinity_audio_check_for_loop'); // it was 1 by default
