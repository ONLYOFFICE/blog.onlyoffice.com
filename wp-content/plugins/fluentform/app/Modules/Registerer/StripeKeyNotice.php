<?php

namespace FluentForm\App\Modules\Registerer;

defined('ABSPATH') or die;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Http\Controllers\AdminNoticeController;

class StripeKeyNotice
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

        return get_option('fluentform_stripe_key_decrypt_failed') === 'yes';
    }

    public static function show()
    {
        $notice = new AdminNoticeController();
        $notice->addNotice(self::getMessage());
        $notice->showNotice();
    }

    private static function getMessage()
    {
        $message = __(
            'Fluent Forms could not read your saved Stripe keys. This can happen after a hosting or security change that rotates WordPress security keys. Please re-enter your Stripe keys to keep accepting payments.',
            'fluentform'
        );

        return [
            'name'    => 'stripe_key_decrypt_failed',
            'title'   => '',
            'message' => $message,
            'links'   => [
                [
                    'href'     => admin_url('admin.php?page=fluent_forms_settings#payments/payment_methods'),
                    'btn_text' => __('Fix Stripe Keys', 'fluentform'),
                    'btn_atts' => 'class="mr-1 el-button--success el-button--mini"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => __('Dismiss', 'fluentform'),
                    'btn_atts' => 'class="text-button el-button--mini ff_nag_cross" data-notice_type="temp" data-notice_name="stripe_key_decrypt_failed"',
                ],
            ],
        ];
    }
}
