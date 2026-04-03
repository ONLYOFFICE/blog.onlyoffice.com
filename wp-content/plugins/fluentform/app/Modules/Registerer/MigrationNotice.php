<?php

namespace FluentForm\App\Modules\Registerer;

defined('ABSPATH') or die;

use FluentForm\App\Models\Form;
use FluentForm\App\Http\Controllers\AdminNoticeController;
use FluentForm\App\Helpers\Helper;

class MigrationNotice
{
    public static function register()
    {
        if (static::shouldRegister()) {
            add_action('fluentform/global_menu', [static::class, 'show'], 99);
        }
    }

    protected static function shouldRegister()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking admin page parameter
        if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'fluent_forms_transfer') {
            return false;
        }

        if (Helper::isFluentAdminPage() && !wp_doing_ajax()) {
            return static::hasActiveCompatiblePlugins();
        }
        
        return false;
    }

    public static function show()
    {
        $notice = new AdminNoticeController();
        $msg = static::getMessage();
        $notice->addNotice($msg);
        $notice->showNotice();
    }

    private static function hasActiveCompatiblePlugins()
    {
        $activePlugins = static::getActiveCompatiblePlugins();
        return !empty($activePlugins);
    }

    private static function getActiveCompatiblePlugins()
    {
        $supportedPlugins = [
            'wpforms/wpforms.php' => 'WPForms',
            'wpforms-lite/wpforms.php' => 'WPForms',
            'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
            'ninja-forms/ninja-forms.php' => 'Ninja Forms',
            'gravityforms/gravityforms.php' => 'Gravity Forms',
            'caldera-forms/caldera-core.php' => 'Caldera Forms'
        ];

        $activePlugins = [];
        foreach ($supportedPlugins as $plugin => $name) {
            if (is_plugin_active($plugin)) {
                $activePlugins[] = $name;
            }
        }

        return $activePlugins;
    }

    private static function getMessage()
    {
        $activePlugins = static::getActiveCompatiblePlugins();
        $pluginNames = implode(', ', $activePlugins);
        
        $message = sprintf(
            'We noticed you have <strong>%s</strong> installed. You can easily migrate your existing forms to <strong>Fluent Forms</strong> with just a few clicks.',
            $pluginNames ?: 'compatible plugins'
        );

        return [
            'name'    => 'migration_notice', //ff_notice_migration_notice
            'title'   => '',
            'message' => $message,
            'links' => [
                [
                    'href'     => admin_url('admin.php?page=fluent_forms_transfer#migrator'),
                    'btn_text' => 'Migrate Now',
                    'btn_atts' => 'class="mr-1 el-button--success el-button--mini"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Maybe Later',
                    'btn_atts' => 'class="mr-1 el-button--info el-button--soft el-button--mini ff_nag_cross" data-notice_type="temp" data-notice_name="migration_notice"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Don\'t show again',
                    'btn_atts' => 'class="text-button el-button--mini ff_nag_cross" data-notice_type="permanent" data-notice_name="migration_notice"',
                ],
            ],
        ];
    }
}
