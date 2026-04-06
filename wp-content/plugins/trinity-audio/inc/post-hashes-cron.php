<?php
  add_filter('cron_schedules', function($schedules) {
    $schedules['two_minutes'] = array(
      'interval' => 120,
      'display'  => esc_html__('Every 2 Minutes'),);
    return $schedules;
  });

  add_action('bl_cron_hook', 'trinity_phbu_continue');

  if (!wp_next_scheduled('bl_cron_hook')) {
    wp_schedule_event(time(), 'two_minutes', 'bl_cron_hook');
  }

  // add action hook here, so it triggers when another plugin executes wp_insert_post (especially using wp-cron)
  add_action('wp_insert_post', 'trinity_ph_update_save_post_callback', 2147483647, 3);
