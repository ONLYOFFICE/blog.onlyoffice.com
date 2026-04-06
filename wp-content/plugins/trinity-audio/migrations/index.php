<?php
  require_once __DIR__ . '/index.php';
  require_once __DIR__ . '/../utils.php';

  function trinity_should_migrate_for($version) {
    $db_plugin_data = trinity_get_db_plugin_version();

    $db_plugin_version = isset($db_plugin_data['version']) ? $db_plugin_data['version'] : '';

    $should_process = $db_plugin_version <= $version;

    if ($should_process) trinity_log("Current version: $db_plugin_version. Required version <= $version Processing...");
    else trinity_log("Skipping. Required version <= $version. Current version: $db_plugin_version...");

    return $should_process;
  }

  function trinity_get_migration_scripts() {
    $migrations = scandir(__DIR__ . '/inc');

    return array_diff($migrations, ['..', '.']);
  }

  function trinity_migration_init() {
    // we need that check, since ANY URL accessing in WP - triggers trinity.php of our plugin, which will trigger migration as well... That's how WP works
    if (trinity_get_transient_migration_in_progress()) return;
    trinity_set_transient_migration_in_progress();

    $plugin_version = trinity_get_plugin_version();

    $db_plugin_data = trinity_get_db_plugin_version();

    $db_plugin_version = isset($db_plugin_data['version']) ? $db_plugin_data['version'] : '';

    if ($db_plugin_version === $plugin_version) {
      trinity_remove_transient_migration_in_progress();
      return;
    }

    if ($db_plugin_version) {
      $db_plugin_migration = trinity_get_plugin_migration();
      $db_plugin_migration_version = isset($db_plugin_migration['version']) ? $db_plugin_migration['version'] : '';

      $migrations = trinity_get_migration_scripts();

      foreach ($migrations as $migration) {
        if (!str_ends_with($migration, '.php')) continue;

        $migration_version = str_replace('.php', '', $migration);

        if ($db_plugin_migration_version < $migration_version) {
          trinity_log("Running migration: $migration_version");
          trinity_set_transient_migration_in_progress();

          require_once __DIR__ . '/inc/' . $migration;

          update_option(
                  TRINITY_AUDIO_PLUGIN_MIGRATION,
                  [
                          'date' => trinity_get_date(),
                          'version' => $migration_version,
                  ]
          );
        }

        trinity_log("Migration $migration_version finished");
      }
    } else {
      trinity_log('Skipping migration for fresh new installation...');
    }

    update_option(
      TRINITY_AUDIO_PLUGIN_VERSION,
      [
        'date'    => trinity_get_date(),
        'version' => $plugin_version,
      ]
    );

    trinity_update_details(TRINITY_AUDIO_UPDATE_PLUGIN_DETAILS_URL, 'upgrade', false);

    trinity_remove_transient_migration_in_progress();
    trinity_log("Latest plugin version is written into DB as: $plugin_version");
  }

  function trinity_set_transient_migration_in_progress() {
    set_transient(TRINITY_AUDIO_MIGRATION_PROGRESS, true, 300); // 5 min
  }

  function trinity_get_transient_migration_in_progress() {
    return get_transient(TRINITY_AUDIO_MIGRATION_PROGRESS);
  }

  function trinity_remove_transient_migration_in_progress() {
    return delete_transient(TRINITY_AUDIO_MIGRATION_PROGRESS);
  }
