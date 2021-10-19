<?php
  require_once __DIR__ . '/index.php';
  require_once __DIR__ . '/../utils.php';

  function trinity_get_migration_scripts() {
    $migrations = scandir(__DIR__ . '/inc');

    return array_diff($migrations, ['..', '.']);
  }

  function trinity_migration_init() {
    $plugin_data    = get_plugin_data(__DIR__ . '/../trinity.php');
    $plugin_version = $plugin_data['Version'];

    $db_plugin_data = trinity_get_db_plugin_version();

    $db_plugin_version = isset($db_plugin_data['version']) ? $db_plugin_data['version'] : '';

    if ($db_plugin_version === $plugin_version) {
      return;
    }

    $db_plugin_migration         = trinity_get_plugin_migration();
    $db_plugin_migration_version = isset($db_plugin_migration['version']) ? $db_plugin_migration['version'] : '';

    $migrations = trinity_get_migration_scripts();

    foreach ($migrations as $migration) {
      $migration_version = str_replace('.php', '', $migration);

      if ($db_plugin_migration_version < $migration_version) {
        trinity_log("Running migration: $migration_version");

        require_once __DIR__ . '/inc/' . $migration;

        update_option(
          TRINITY_AUDIO_PLUGIN_MIGRATION,
          [
            'date'    => trinity_get_date(),
            'version' => $migration_version,
          ]
        );
      }

      trinity_log("Migration $migration_version finished");
    }

    update_option(
      TRINITY_AUDIO_PLUGIN_VERSION,
      [
        'date'    => trinity_get_date(),
        'version' => $plugin_version,
      ]
    );
  }

