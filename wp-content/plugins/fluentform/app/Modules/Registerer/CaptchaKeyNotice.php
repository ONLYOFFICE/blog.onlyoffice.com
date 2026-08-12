<?php

namespace FluentForm\App\Modules\Registerer;

defined('ABSPATH') or die;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Http\Controllers\AdminNoticeController;
use FluentForm\Framework\Helpers\ArrayHelper;

class CaptchaKeyNotice
{
    public static function register()
    {
        if (static::shouldRegister()) {
            add_action('fluentform/global_menu', [static::class, 'show'], 99);
        }
    }

    protected static function shouldRegister()
    {
        if (!Helper::isFluentAdminPage() || wp_doing_ajax()) {
            return false;
        }

        if (!Helper::isAutoloadCaptchaEnabled()) {
            return false;
        }

        $statusOption = ArrayHelper::get(static::statusOptionMap(), static::selectedType());

        return !$statusOption || !get_option($statusOption, false);
    }

    public static function show()
    {
        $notice = new AdminNoticeController();
        $notice->addNotice(self::getMessage());
        $notice->showNotice();
    }

    protected static function selectedType()
    {
        return ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.captcha_type');
    }

    protected static function statusOptionMap()
    {
        return [
            'recaptcha' => '_fluentform_reCaptcha_keys_status',
            'hcaptcha'  => '_fluentform_hCaptcha_keys_status',
            'turnstile' => '_fluentform_turnstile_keys_status',
        ];
    }

    protected static function settingsUrl()
    {
        $anchors = [
            'recaptcha' => '#re_captcha',
            'hcaptcha'  => '#h_captcha',
            'turnstile' => '#turnstile',
            'cleantalk' => '#cleantalk',
        ];

        $anchor = ArrayHelper::get($anchors, static::selectedType(), '');

        return admin_url('admin.php?page=fluent_forms_settings') . $anchor;
    }

    private static function getMessage()
    {
        $message = __(
            'Fluent Forms global captcha is enabled, but the selected captcha type has no valid keys. Until you add valid keys, your forms are not protected by captcha.',
            'fluentform'
        );

        return [
            'name'    => 'autoload_captcha_keys_missing',
            'title'   => '',
            'message' => $message,
            'links'   => [
                [
                    'href'     => static::settingsUrl(),
                    'btn_text' => __('Configure Captcha', 'fluentform'),
                    'btn_atts' => 'class="mr-1 el-button--success el-button--mini"',
                ],
            ],
        ];
    }
}
