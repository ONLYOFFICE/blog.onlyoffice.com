<?php

defined('ABSPATH') or die;

?>
<div class="ffp_submission_header">
    <div class="ffp_submission_message">
        <?php echo fluentform_sanitize_html($header_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
</div>
