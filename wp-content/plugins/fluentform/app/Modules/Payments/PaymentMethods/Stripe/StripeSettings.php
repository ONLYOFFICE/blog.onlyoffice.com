<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe;

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class StripeSettings
{
    public static function getSettings($decrypted = true)
    {
        $defaults = [
            'test_publishable_key' => '',
            'test_secret_key'      => '',
            'live_publishable_key' => '',
            'live_secret_key'      => '',
            'payment_mode'         => 'test',
            'is_active'            => 'no',
            'provider'             => 'api_keys',
            'test_account_id'      => '',
            'live_account_id'      => '',
            'is_encrypted'         => 'no'
        ];

        $settings = get_option('fluentform_payment_settings_stripe', []);

        if (!$settings) {
            $defaults['provider'] = 'connect';
        }

        $settings = wp_parse_args($settings, $defaults);

        if ($settings['provider'] == 'connect' && apply_filters('fluentform/disable_stripe_connect', false)) {
            $settings['provider'] = 'api_keys';
        }

        if ($decrypted) {
            $settings = self::maybeDecryptKeys($settings);
        }

        return $settings;
    }

    public static function updateSettings($data)
    {
        $settings = self::getSettings();
        $settings = wp_parse_args($data, $settings);

        $settings = self::encryptKeys($settings);
        update_option('fluentform_payment_settings_stripe', $settings);
        return self::getSettings();
    }

    public static function encryptKeys($settings)
    {
        $settings['live_secret_key'] = PaymentHelper::encryptKey($settings['live_secret_key']);
        $settings['test_secret_key'] = PaymentHelper::encryptKey($settings['test_secret_key']);
        $settings['is_encrypted'] = 'yes';

        return $settings;
    }

    public static function maybeDecryptKeys($settings)
    {
        if (ArrayHelper::get($settings, 'is_encrypted') == 'yes') {
            if (!empty($settings['live_secret_key'])) {
                $settings['live_secret_key'] = PaymentHelper::decryptKey($settings['live_secret_key']);
            }

            if (!empty($settings['test_secret_key'])) {
                $settings['test_secret_key'] = PaymentHelper::decryptKey($settings['test_secret_key']);
            }
        } else {
            $encrypted = self::encryptKeys($settings);
            update_option('fluentform_payment_settings_stripe', $encrypted);
        }

        $settings['is_encrypted'] = 'yes';

        return $settings;
    }

    public static function getSecretKey($formId = false)
    {
        if ($formId) {
            $formPaymentSettings = PaymentHelper::getFormSettings($formId, 'admin');
            if (ArrayHelper::get($formPaymentSettings, 'stripe_account_type') == 'custom') {
                return ArrayHelper::get($formPaymentSettings, 'stripe_custom_config.secret_key');
            }
        }

        $settings = self::getSettings();

        if ($settings['payment_mode'] == 'live') {
            return $settings['live_secret_key'];
        }

        return $settings['test_secret_key'];
    }

    public static function getPublishableKey($formId = false)
    {
        if ($formId) {
            $formPaymentSettings = PaymentHelper::getFormSettings($formId, 'admin');
            if (ArrayHelper::get($formPaymentSettings, 'stripe_account_type') == 'custom') {
                return ArrayHelper::get($formPaymentSettings, 'stripe_custom_config.publishable_key');
            }
        }

        $settings = self::getSettings();
        if ($settings['payment_mode'] == 'live') {
            return $settings['live_publishable_key'];
        }

        return $settings['test_publishable_key'];
    }

    public static function isLive($formId = false)
    {
        if ($formId) {
            $formPaymentSettings = PaymentHelper::getFormSettings($formId, 'admin');
            if (ArrayHelper::get($formPaymentSettings, 'stripe_account_type') == 'custom') {
                return ArrayHelper::get($formPaymentSettings, 'stripe_custom_config.payment_mode') == 'live';
            }
        }

        $settings = self::getSettings();
        return $settings['payment_mode'] == 'live';
    }

    public static function getMode($formId = false)
    {
        return static::isLive($formId) ? 'live' : 'test';
    }

    public static function supportedShippingCountries()
    {
        $countries = [
            'AC', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AT', 'AU', 'AW', 'AX', 'AZ',
            'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS',
            'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO',
            'CR', 'CV', 'CW', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER',
            'ES', 'ET', 'FI', 'FJ', 'FK', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL',
            'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HN', 'HR', 'HT', 'HU', 'ID',
            'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI',
            'KM', 'KN', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV',
            'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MM', 'MN', 'MO', 'MQ', 'MR', 'MS', 'MT',
            'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU',
            'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PY', 'QA',
            'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM',
            'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SZ', 'TA', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK',
            'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'US', 'UY', 'UZ', 'VA', 'VC',
            'VE', 'VG', 'VN', 'VU', 'WF', 'WS', 'XK', 'YE', 'YT', 'ZA', 'ZM', 'ZW', 'ZZ'
        ];

        return apply_filters('fluentform/stripe_supported_shipping_countries', $countries);
    }

    public static function getClientId($paymentMode = 'live')
    {
        if ($paymentMode == 'live') {
            return 'ca_GwAPNUlKAW6EgeTqERQieU3DXLAX3k7N';
        } else {
            return 'ca_GwAPhxHwszjhsFRdC4j5DqKHud7JLQ8C';
        }
    }

    public static function guessFormIdFromEvent($event)
    {
        $eventType = $event->type;

        $metaDataEvents = [
            'checkout.session.completed',
            'charge.refunded',
            'charge.succeeded'
        ];

        if (in_array($eventType, $metaDataEvents)) {
            $data = $event->data->object;
            $metaData = (array)$data->metadata;
            return ArrayHelper::get($metaData, 'form_id');
        }

        return false;
    }

    public static function getPaymentDescriptor($form)
    {
        $title = $form->title;
        $paymentSettings = PaymentHelper::getFormSettings($form->id, 'admin');
        if ($providedDescriptor = ArrayHelper::get($paymentSettings, 'stripe_descriptor')) {
            $title = $providedDescriptor;
        } else {
            $globalSettings = PaymentHelper::getPaymentSettings();
            if (!empty($globalSettings['business_name'])) {
                $title = $globalSettings['business_name'];
            }
        }

        $illegal = array('<', '>', '"', "'");
        // Remove slashes
        $descriptor = stripslashes($title);
        // Remove illegal characters
        $descriptor = str_replace($illegal, '', $descriptor);
        // Trim to 22 characters max
        $descriptor = substr($descriptor, 0, 22);

        if (!$descriptor || strlen($descriptor) < 5) {
            $descriptor = 'FluentForm';
        }

        return $descriptor;

    }
    
    public static function isConnectedAndPlatformCountrySame()
    {
        $settings = static::getSettings();
        return ArrayHelper::get($settings, 'connected_account_country') === ArrayHelper::get($settings, 'platform_account_country');
    }

    public static function getAccountId()
    {
        $settings = self::getSettings();
        $isLive = self::isLive();
        
        if ($isLive) {
            return ArrayHelper::get($settings, 'live_account_id');
        }
        return ArrayHelper::get($settings, 'test_account_id');
    }

}
