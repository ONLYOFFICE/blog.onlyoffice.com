<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle Stripe Checkout Session
 * @since 1.0.0
 */
class CheckoutSession
{
    public static function create($args)
    {
        $argsDefault = [
            'locale' => 'auto'
        ];

        $args = wp_parse_args($args, $argsDefault);

        $formId = ArrayHelper::get($args, 'metadata.form_id');
        $secretKey = apply_filters_deprecated(
            'fluentform-payment_stripe_secret_key',
            [
                StripeSettings::getSecretKey($formId),
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_stripe_secret_key',
            'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
        );
        $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $formId);
        ApiRequest::set_secret_key($secretKey);
        return ApiRequest::request($args, 'checkout/sessions');
    }

    public static function retrieve($sessionId, $args = [], $formId = false)
    {
        $secretKey = apply_filters_deprecated(
            'fluentform-payment_stripe_secret_key',
            [
                StripeSettings::getSecretKey($formId),
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_stripe_secret_key',
            'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
        );
        $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $formId);
        ApiRequest::set_secret_key($secretKey);
        return ApiRequest::request($args, 'checkout/sessions/' . $sessionId, 'GET');
    }
}
