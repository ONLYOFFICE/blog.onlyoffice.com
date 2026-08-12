<?php

defined('ABSPATH') or die;

use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Hooks\Handlers\ActivationHandler;
use FluentForm\App\Hooks\Handlers\DeactivationHandler;
use FluentForm\App\Services\Migrator\Bootstrap as FormsMigrator;
use FluentForm\App\Services\FluentConversational\Classes\Form as FluentConversational;
use FluentForm\Database\Migrations\LegacyManagerScopes;
use FluentForm\App\Helpers\Helper;

return function ($file) {
    add_action('plugins_loaded', function () {
        $isNotCompatible = defined('FLUENTFORMPRO')
            && version_compare(FLUENTFORMPRO_VERSION, '6.2.0', '<');
        if ($isNotCompatible) {
            add_action('admin_init', function () {
                $message = '<b>' . __('Action Required: ', 'fluentform') . '</b>'
                    . __('Fluent Forms Pro is not compatible with this version of Fluent Forms. Please update Fluent Forms Pro to version ', 'fluentform')
                    . '6.2.0' . __(' or later.', 'fluentform')
                    . ' <a href="' . admin_url('plugins.php?s=fluentformpro&plugin_status=all&force-check=1') . '">'
                    . __('Update Now', 'fluentform') . '</a>';
                $renderNotice = function () use ($message) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin notice with HTML links
                    printf('<div class="fluentform-admin-notice notice notice-error"><div style="padding: 15px 10px;">%1$s</div></div>', $message);
                };
                add_action('fluentform/global_menu', $renderNotice);
                add_action('fluentform/after_form_menu', $renderNotice);
            });
        }

        fluentform_maybe_disable_contaminated_pro();
    });

    $app = new Application($file);

    // Boot the MCP server (Abilities API tools + endpoint). Self-guards: ships
    // off, and its Abilities/adapter hooks fire only when those systems exist.
    \FluentForm\App\Modules\MCP\MCPInit::boot();

    register_activation_hook($file, function ($network_wide) use ($app) {
        ($app->make(ActivationHandler::class))->handle($network_wide);
    });

    $initializeNewSite = function ($blogId) use ($app) {
        if (!is_plugin_active_for_network('fluentform/fluentform.php')) {
            return;
        }

        switch_to_blog($blogId);
        ($app->make(ActivationHandler::class))->handle(false);
        restore_current_blog();
    };

    if (function_exists('wp_initialize_site')) {
        add_action('wp_initialize_site', function ($newSite) use ($initializeNewSite) {
            $initializeNewSite($newSite->id);
        }, 20, 1);
    } else {
        add_action('wpmu_new_blog', function ($blogId) use ($initializeNewSite) {
            $initializeNewSite($blogId);
        }, 10, 1);
    }

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    add_action('admin_init', function () {
        if (!wp_doing_ajax()) {
            LegacyManagerScopes::migrate();
        }
    });

    add_action('plugins_loaded', function () use ($app) {
        do_action_deprecated(
            'fluentform_loaded',
            [
                $app
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/loaded',
            'Use fluentform/loaded instead of fluentform_loaded.'
        );

        do_action_deprecated(
            'fluentform-loaded',
            [
                $app
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/loaded',
            'Use fluentform/loaded instead of fluentform-loaded.'
        );
        do_action('fluentform/loaded', $app);
        
    });

    fluentformLoadFile('Services/FluentConversational/plugin.php');
    fluentformLoadFile('Services/Libraries/action-scheduler/action-scheduler.php');
    
    (new FluentConversational)->boot();
    (new FormsMigrator())->boot();
    
    /* Plugin Meta Links — registered on init to avoid early textdomain loading (WP 6.7+) */

    add_action('init', function () {
        add_filter('plugin_row_meta', 'fluentform_plugin_row_meta', 10, 2);
    });

    function fluentform_plugin_row_meta($links, $file)
    {
        if ('fluentform/fluentform.php' == $file) {
            $row_meta = [
                'docs'    => '<a rel="noopener" href="https://fluentforms.com/docs" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr__('View FluentForms Documentation', 'fluentform') . '" target="_blank">' . esc_html__('Docs', 'fluentform') . '</a>',
                'support' => '<a rel="noopener" href="https://wpmanageninja.com/support-tickets/#/" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr__('Get Support', 'fluentform') . '" target="_blank">' . esc_html__('Support', 'fluentform') . '</a>',
                'developer_docs' => '<a rel="noopener" href="https://developers.fluentforms.com" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr__('Developer Docs', 'fluentform') . '" target="_blank">' . esc_html__('Developer Docs', 'fluentform') . '</a>',
            ];
            if (!defined('FLUENTFORMPRO')) {
                $row_meta['pro'] = '<a rel="noopener" href="' . esc_url(fluentform_upgrade_url('plugin_row_meta')) . '" style="color: #7742e6;font-weight: bold;" aria-label="' . esc_attr__('Upgrade to Pro', 'fluentform') . '" target="_blank">' . esc_html__('Upgrade to Pro', 'fluentform') . '</a>';
            }
            return array_merge($links, $row_meta);
        }
        return (array)$links;
    }
};
