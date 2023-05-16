<?php
  // Use hardcoded values only, don't rely on consts, as they can change in time

  if (!trinity_should_migrate_for('2.1.0')) return;

  $new_map = [
    'en'     => 'en-US',
    'de'     => 'de-DE',
    'it'     => 'it-IT',
    'fr'     => 'fr-FR',
    'es'     => 'es-ES',
    'pt'     => 'pt-PT',
    'hi'     => 'hi-IN',
    'zh'     => 'cmn-CN',

    // if somebody will delete that plugin and run everything again, we have to match those values
    'en-US'  => 'en-US',
    'de-DE'  => 'de-DE',
    'it-IT'  => 'it-IT',
    'fr-FR'  => 'fr-FR',
    'es-ES'  => 'es-ES',
    'pt-PT'  => 'pt-PT',
    'hi-IN'  => 'hi-IN',
    'cmn-CN' => 'cmn-CN',
  ];

  // update language code
  $saved  = get_option('trinity_audio_source_language');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : 'en-US';
  if ($saved !== $result) {
    update_option('trinity_audio_source_language', $result);
    trinity_log("Migrated language code from `$saved` to `$result`");
  }

  if (($result === 'en-IN' || $result === 'hi-IN') && get_option('trinity_audio_gender_id') === 'm') {
    update_option('trinity_audio_gender_id', 'f');
    trinity_log("Set gender to female for $result, as don't support male voice yet");
  }

  // update old values to new one
  global $wpdb;
  foreach ($new_map as $old => $new) {
    $wpdb->update(
      $wpdb->postmeta,
      [
        'meta_value' => $new,
      ],
      [
        'meta_key'   => 'trinity_audio_source_language',
        'meta_value' => $old,
      ]
    );
  }

  trinity_log('Migrated genders and languages for postmeta');

  // set for all en-IN and hi-IN gender to Female
  $rows = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='trinity_audio_gender_id' AND meta_value='m' and post_id in (SELECT DISTINCT post_id from wp_postmeta WHERE meta_key='trinity_audio_source_language' and meta_value IN ('en-IN', 'hi-IN'))");

  foreach ($rows as $row) {
    $wpdb->update(
      $wpdb->postmeta,
      [
        'meta_value' => 'f',
      ],
      [
        'post_id'  => $row->post_id,
        'meta_key' => 'trinity_audio_gender_id',
      ]
    );
  }
