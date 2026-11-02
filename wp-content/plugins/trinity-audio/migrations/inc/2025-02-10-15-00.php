<?php
  if (!trinity_should_migrate_for('5.20.0')) return;

  // drop cache since we don't need it anymore
  delete_transient('trinity_audio_languages_cache');

  delete_option('trinity_audio_configuration_v5_failed');
  delete_option('trinity_audio_gender_id');
  delete_option('trinity_audio_voice_id');
  delete_option('trinity_audio_translate');

  global $wpdb;

  $meta_key = 'trinity_audio_gender_id';

  $wpdb->query($wpdb->prepare(
  "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
    $meta_key
  ));

  // migrate voices from standard to neural
  $voices_migration_file = '2025-02-10-15-00-standard-to-neural-map.json';
  $voices_migration_file_path = __DIR__ . '/' . $voices_migration_file;
  if (!file_exists($voices_migration_file_path )) {
    trinity_log("Missing $voices_migration_file file");
    return;
  }

  $voice_mapping = json_decode(file_get_contents($voices_migration_file_path), true);
  if (!$voice_mapping) {
    trinity_log("Invalid JSON in $voices_migration_file");
    return;
  }

  // Migrate post-specific voice IDs
  foreach ($voice_mapping as $standard_id => $neural_id) {
    trinity_log("migrate voice $standard_id to $neural_id");

    $wpdb->query($wpdb->prepare(
      "UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_key = 'trinity_audio_voice_id' AND meta_value = %s",
      $neural_id,
      $standard_id
    ));
  }
