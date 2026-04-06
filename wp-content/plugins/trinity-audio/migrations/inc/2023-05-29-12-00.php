<?php
  if (!trinity_get_install_key()) return;
  if (!trinity_should_migrate_for('5.5.0')) return;

  trinity_phbu_start();
