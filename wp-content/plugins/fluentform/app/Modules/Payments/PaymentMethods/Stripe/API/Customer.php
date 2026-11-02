<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

if (!defined('ABSPATH')) {
	exit;
}

class Customer
{
	use RequestProcessor;

	public static function createCustomer($customerArgs, $formId)
	{
		$errors = static::validate($customerArgs);

		if ($errors) {
			return static::errorHandler('validation_failed', __('Payment data validation failed', 'fluentform'), $errors);
		}

		try {
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

			$response = ApiRequest::request($customerArgs, 'customers');

			$response = static::processResponse($response);

            do_action_deprecated(
                'fluentform_stripe_customer_created',
                [
                    $response,
                    $customerArgs
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/stripe_customer_created',
                'Use fluentform/stripe_customer_created instead of fluentform_stripe_customer_created.'
            );

			do_action('fluentform/stripe_customer_created', $response, $customerArgs);

			return $response;
		} catch (\Exception $e) {
			// Something else happened, completely unrelated to Stripe
			return static::errorHandler('non_stripe', esc_html__('General Error', 'fluentform') . ': ' . $e->getMessage());
		}
	}

	public static function validate($args)
	{
		$errors = [];

		if (empty($args['source']) && empty($args['payment_method'])) {
			$errors['source'] = __('Stripe token/payment_method is required', 'fluentform');
		}

		return $errors;
	}
}
