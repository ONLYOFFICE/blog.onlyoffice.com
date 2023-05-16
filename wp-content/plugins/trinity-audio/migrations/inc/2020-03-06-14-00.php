<?php
  // Use hardcoded values only, don't rely on consts, as they can change in time

  if (!trinity_should_migrate_for('1.2.5')) return;

  // in order to show checkbox for "Activate for all posts"
  delete_option('trinity_audio_bulk_update_heartbeat');
  delete_option('trinity_audio_bulk_update_num_posts_updated');
  add_option('trinity_audio_bulk_update_heartbeat', '', '', true);

  $new_map = [
    'Female'                 => 'f',
    'Male'                   => 'm',
    'on'                     => 1,
    'Trinity Audio enabled'  => 1,
    'Trinity Audio disabled' => 0,
    'After post'             => 'after',
    'Before post'            => 'before',
    'Do not show'            => 'none',
    'Default'                => '',
  ];

  // update gender
  $saved  = get_option('trinity_audio_gender_id');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : 'f';
  if ($saved !== $result) {
    update_option('trinity_audio_gender_id', $result);
    trinity_log("Migrated gender from `$saved` to `$result`");
  }

  // update default enabled
  $saved  = get_option('trinity_audio_defconf');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : 1;
  if ($saved !== $result) {
    update_option('trinity_audio_defconf', $result);
    trinity_log("Migrated default enabled from `$saved` to `$result`");
  }

  // update add post title
  $saved  = get_option('trinity_audio_add_post_title');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : '';
  if ($saved !== $result) {
    update_option('trinity_audio_add_post_title', $result);
    trinity_log("Migrated add post title from `$saved` to `$result`");
  }

  // update add post excerpt
  $saved  = get_option('trinity_audio_add_post_excerpt');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : '';
  if ($saved !== $result) {
    update_option('trinity_audio_add_post_excerpt', $result);
    trinity_log("Migrated add post excerpt from `$saved` to `$result`");
  }

  // update position
  $saved  = get_option('trinity_audio_position');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : 'before';
  if ($saved !== $result) {
    update_option('trinity_audio_position', $result);
    trinity_log("Migrated audio position from `$saved` to `$result`");
  }

  // update powered by
  $saved  = get_option('trinity_audio_poweredby');
  $result = isset($new_map[$saved]) ? $new_map[$saved] : 1;
  if ($saved !== $result) {
    update_option('trinity_audio_poweredby', $result);
    trinity_log("Migrated audio power by from `$saved` to `$result`");
  }

  // update skip tags
  $saved = get_option('trinity_audio_skip_tags', '');

  if (is_string($saved)) {
    try {
      $saved_array = explode(',', $saved);
      update_option('trinity_audio_skip_tags', $saved_array);
    } catch (Exception $e) {
      delete_option('trinity_audio_skip_tags');
      add_option(TRINITY_AUDIO_SKIP_TAGS, [], '', true);
    }
  }

  // post meta

  // remove unneeded fields
  global $wpdb;
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_saved_title']);
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_saved_body']);
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_saved_excerpt']);

  // update old values to new one

  foreach (['trinity_audio_gender_id', 'trinity_audio_source_language'] as $key) {
    foreach ($new_map as $old => $new) {
      $wpdb->update(
        $wpdb->postmeta,
        [
          'meta_value' => $new,
        ],
        [
          'meta_key'   => $key,
          'meta_value' => $old,
        ]
      );
    }
  }

  // delete unneeded options
  delete_option('trinity_audio');
  delete_option('trinity_audio_update_all');
