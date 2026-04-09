<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\Components\PaymentMethods;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API\ApiRequest;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API\StripeListener;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API\Account;

class StripeHandler
{
    protected $key = 'stripe';

    public function init()
    {
        add_filter('fluentform/payment_settings_' . $this->key, function () {
            $settings = StripeSettings::getSettings();

            if ($settings['test_secret_key']) {
                $settings['test_secret_key'] = 'ENCRYPTED_KEY';
            }

            if ($settings['live_secret_key']) {
                $settings['live_secret_key'] = 'ENCRYPTED_KEY';
            }

            return $settings;

        });

        add_filter('fluentform/payment_method_settings_validation_' . $this->key, array($this, 'validateSettings'), 10, 2);

        add_filter('fluentform/payment_method_settings_save_' . $this->key, array($this, 'sanitizeGlobalSettings'), 10, 1);

        if (!$this->isEnabled()) {
            return;
        }

        add_filter('fluentform/available_payment_methods', array($this, 'pushPaymentMethodToForm'), 10, 1);

        add_action('fluentform/rendering_payment_method_' . $this->key, array($this, 'enqueueAssets'));

        add_filter('fluentform/transaction_data_' . $this->key, array($this, 'modifyTransaction'), 10, 1);

        add_action('fluentform/ipn_endpoint_' . $this->key, function () {
            (new StripeListener())->verifyIPN();
        });

        add_action('fluentform/process_payment_stripe', [$this, 'routeStripeProcessor'], 10, 6);

        add_filter('fluentform/payment_manager_class_' . $this->key, function ($class) {
            return new PaymentManager();
        });

        (new StripeProcessor())->init();
        (new StripeInlineProcessor())->init();
    }

    public function routeStripeProcessor($submissionId, $submissionData, $form, $methodSettings, $hasSubscriptions, $totalPayable = 0)
    {
        $processor = ArrayHelper::get($methodSettings, 'settings.embedded_checkout.value') === 'yes' ? 'inline' : 'hosted';

        do_action_deprecated(
            'fluentform_process_payment_stripe_' . $processor,
            [
                $submissionId,
                $submissionData,
                $form,
                $methodSettings,
                $hasSubscriptions,
                $totalPayable
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/process_payment_stripe_' . $processor,
            'Use fluentform/process_payment_stripe_' . $processor . ' instead of fluentform_process_payment_stripe_' . $processor
        );

        do_action('fluentform/process_payment_stripe_' . $processor, $submissionId, $submissionData, $form, $methodSettings, $hasSubscriptions, $totalPayable);
    }

    public function pushPaymentMethodToForm($methods)
    {
        if (!$this->isEnabled()) {
            return $methods;
        }

        $methods[$this->key] = [
            'title'        => __('Credit/Debit Card (Stripe)', 'fluentform'),
            'enabled'      => 'yes',
            'method_value' => $this->key,
            'settings'     => [
                'option_label'          => [
                    'type'     => 'text',
                    'template' => 'inputText',
                    'value'    => 'Pay with Card (Stripe)',
                    'label'    => __('Method Label', 'fluentform')
                ],
                'embedded_checkout'     => [
                    'type'     => 'checkbox',
                    'template' => 'inputYesNoCheckbox',
                    'value'    => 'yes',
                    'label'    => __('Embedded Checkout', 'fluentform')
                ],
                'require_billing_info'  => [
                    'type'       => 'checkbox',
                    'template'   => 'inputYesNoCheckbox',
                    'value'      => 'no',
                    'label'      => __('Require Billing info', 'fluentform'),
                    'dependency' => array(
                        'depends_on' => 'embedded_checkout/value',
                        'value'      => 'yes',
                        'operator'   => '!='
                    )
                ],
                'require_shipping_info' => [
                    'type'       => 'checkbox',
                    'template'   => 'inputYesNoCheckbox',
                    'value'      => 'no',
                    'label'      => __('Collect Shipping Info', 'fluentform'),
                    'dependency' => array(
                        'depends_on' => 'embedded_checkout/value',
                        'value'      => 'yes',
                        'operator'   => '!='
                    )
                ],
                'verify_zip_code'       => [
                    'type'     => 'checkbox',
                    'template' => 'inputYesNoCheckbox',
                    'value'    => 'no',
                    'label'    => __('Verify Zip/Postal Code', 'fluentform')
                ],
            ]
        ];

        return $methods;
    }

    public function enqueueAssets()
    {
        wp_enqueue_script('stripe_elements', 'https://js.stripe.com/v3/', array('jquery'), '3.0', true);
    }

    public function validateSettings($errors, $settings)
    {
        if (ArrayHelper::get($settings, 'is_active') != 'yes') {
            return [];
        }

        $mode = $settings['payment_mode'];

        if (empty($settings[$mode . '_publishable_key'])) {
            /* translators: %s is the mode (Live/Test) */
            $errors[$mode . '_publishable_key'] = sprintf(__('%s Publishable Key is required', 'fluentform'), ucfirst($mode));
        }

        if (empty($settings[$mode . '_secret_key'])) {
            /* translators: %s is the mode (Live/Test) */
            $errors[$mode . '_secret_key'] = sprintf(__('%s Secret Key is required', 'fluentform'), ucfirst($mode));
        }

        if (ArrayHelper::get($settings, 'provider') === 'connect' && count($errors)) {
            $errors = [
                'connect' => __('Connect with Stripe was not successful. Please try again!', 'fluentform')
            ];
        }

        return $errors;
    }

    public function modifyTransaction($transaction)
    {
        if ($transaction->charge_id) {
            $urlBase = 'https://dashboard.stripe.com/';
            if ($transaction->payment_mode != 'live') {
                $urlBase .= 'test/';
            }
            $transaction->action_url = $urlBase . 'payments/' . $transaction->charge_id;
        }

        if ($transaction->status == 'requires_capture') {
            $transaction->additional_note = sprintf(
                /* translators: %s is the URL to capture payment in Stripe dashboard */
                __('<b>Action Required: </b> The payment has been authorized but not captured yet. Please <a target="_blank" rel="noopener noreferrer" href="%s">Click here</a> to capture this payment in stripe.com', 'fluentform'),
                esc_url($transaction->action_url)
            );
        }

        return $transaction;
    }

    public function isEnabled()
    {
        $settings = StripeSettings::getSettings(false);
        return $settings['is_active'] == 'yes';
    }

    public function sanitizeGlobalSettings($settings)
    {
        if ($settings['is_active'] != 'yes') {
            return [
                'test_publishable_key' => '',
                'test_secret_key'      => '',
                'live_publishable_key' => '',
                'live_secret_key'      => '',
                'payment_mode'         => 'test',
                'is_active'            => 'no',
                'provider'             => 'connect' // api_keys
            ];
        }

        $prevSettings = StripeSettings::getSettings(true);

        $accountId = ArrayHelper::get($prevSettings, 'test_account_id');
        $token = ArrayHelper::get($prevSettings, 'test_secret_key');
        if (ArrayHelper::get($prevSettings, 'payment_mode') === 'live') {
            $accountId = ArrayHelper::get($prevSettings, 'live_account_id');
            $token = ArrayHelper::get($prevSettings, 'live_secret_key');
        }

        // Fetch connected account details
        $connectedAccountDetails = Account::retrive($accountId, $token);
        if ($connectedAccountDetails && isset($connectedAccountDetails->country)) {
            $settings['connected_account_country'] = $connectedAccountDetails->country;
        }

        // Fetch platform account details
        $platformAccountDetails = Account::retrive('', $token);
        if ($platformAccountDetails && isset($platformAccountDetails->country)) {
            $settings['platform_account_country'] = $platformAccountDetails->country;
        }

        if ($settings['test_secret_key'] == 'ENCRYPTED_KEY') {
            $settings['test_secret_key'] = $prevSettings['test_secret_key'];
        }

        if ($settings['live_secret_key'] == 'ENCRYPTED_KEY') {
            $settings['live_secret_key'] = $prevSettings['live_secret_key'];
        }

        return StripeSettings::encryptKeys($settings);
    }

}
