<?php
  function trinity_is_any_post_enabled(): bool {
    $query_args = array(
      'post_type'   => 'post',
      'post_status' => 'publish',
      'meta_query'  => array([
        'key'   => TRINITY_AUDIO_ENABLED,
        'value' => '1'
      ]),
      'fields'      => 'ids',
    );

    $query = new WP_Query($query_args);

    return !!$query->found_posts;
  }

  function trinity_is_show_progress(): bool {
    return !trinity_get_is_first_changes_saved() || !trinity_is_any_post_enabled();
  }

  if (trinity_is_show_progress()) {
?>

<div class="wizard-progress">
    <div class="step complete">
        <span class="name">Install</span>
        <div class="node"></div>
    </div>
    <div class="step <?= trinity_get_is_first_changes_saved() ? 'complete' : '' ?>">
        <span class="name">
            <a href="<?= esc_url(admin_url('admin.php?page=trinity_audio')) ?>">Configure</a>
        </span>
        <div class="node"></div>
    </div>
    <div class="step <?= trinity_get_is_first_changes_saved() && trinity_is_any_post_enabled() ? 'complete' : '' ?>">
        <span class="name">
            <a href="<?= esc_url(admin_url('admin.php?page=trinity_audio_post_management')) ?>">Activate</a>
        </span>
        <div class="node"></div>
    </div>
</div>

<?php } ?>
