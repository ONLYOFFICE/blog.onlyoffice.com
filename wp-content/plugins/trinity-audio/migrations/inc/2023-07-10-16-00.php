<?php
  if (!trinity_should_migrate_for('5.6.2')) return;

  delete_option('trinity_player_position');
  delete_option('trinity_audio_add_post_title');
  delete_option('trinity_audio_add_post_excerpt');

  // remove legacy fields
  global $wpdb;
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_post_hash_content_title']);
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_post_hash_content']);
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_post_hash_content_excerpt']);
  $wpdb->delete($wpdb->postmeta, ['meta_key' => 'trinity_audio_post_hash_content_excerpt_title']);
