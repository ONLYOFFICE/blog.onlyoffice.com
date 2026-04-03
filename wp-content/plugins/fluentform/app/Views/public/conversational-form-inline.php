<?php

defined('ABSPATH') or die;

?>
<style>
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is sanitized via fluentformSanitizeCSS()
    echo $generated_css;
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is sanitized via fluentformSanitizeCSS()
    echo $submit_css;
    ?>
</style>
<div class="ffc_conv_wrapper ffc_inline_form">
    <div class="frm-fluent-form ff_conv_app fluent_form_<?php echo esc_attr($form_id); ?> ff_conv_app_frame ff_conv_app_<?php echo esc_attr($form_id); ?> ffc_media_hide_mob_<?php echo esc_attr($design['hide_media_on_mobile']); ?>" data-form_id="<?php echo esc_attr($form_id) ?>">
        <div data-var_name="<?php echo esc_attr($global_var_name); ?>" class="ffc_conv_form" style="width: 100%" id="ffc_app_instance_<?php echo esc_attr($instance_id); ?>"></div>
    </div>
</div>
