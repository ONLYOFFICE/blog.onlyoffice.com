<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

if (!defined('ABSPATH')) {
	exit;
}

class Invoice
{
	use RequestProcessor;

	public static function createItem($item, $formId)
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

		$response = ApiRequest::request($item, 'invoiceitems', 'POST');

		return static::processResponse($response);
	}

	public static function retrieve($invoiceId, $formId, $args = [])
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

		$response = ApiRequest::request($args, 'invoices/' . $invoiceId, 'POST');

		return static::processResponse($response);
	}
}
