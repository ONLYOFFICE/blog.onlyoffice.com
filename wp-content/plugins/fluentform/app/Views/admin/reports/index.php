<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
do_action('fluentform/global_menu');
?>
    <div class="ff_form_wrap">
        <div class="ff_form_wrap_area">
            <?php
            do_action('fluentform/before_form_reports_render');
            ?>
            <div class="ff_all_forms" id="ff_all_forms_app">
                <div id="ff_reports">
                    <ff-reports></ff-reports>
                    <global-search></global-search>
                </div>
            </div>
            <?php
            do_action('fluentform/after_form_reports_render');
            ?>
        </div>
    </div>
<?php
$notices = apply_filters('fluentform/dashboard_notices', []);

if ($notices) {
    echo '<div class="ff_global_notices">';
    foreach ($notices as $noticeKey => $notice) :
        ?>
        <div class="ff_global_notice ff_notice_<?php
        echo esc_attr($notice['type']); ?>">
            <?php
            echo esc_html($notice['message']); ?>
        </div>
    <?php
    endforeach;
    echo '</div>';
}