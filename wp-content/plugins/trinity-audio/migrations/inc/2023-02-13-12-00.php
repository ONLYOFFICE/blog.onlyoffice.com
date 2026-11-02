<?php
  if (!trinity_get_install_key()) return;
  if (!trinity_should_migrate_for('5.3.12')) return;

//  trinity_update_posts_bulk_update();
