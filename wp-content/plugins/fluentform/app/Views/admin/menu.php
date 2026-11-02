<?php

defined('ABSPATH') or die;

?>
<div class="fluent-forms" id="fluent-forms-app"></div>

<script>
    window.FormApp = <?php echo wp_json_encode([
        'plugin' => $plugin
    ]); ?>
</script>