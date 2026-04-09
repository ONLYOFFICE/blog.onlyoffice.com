<?php

namespace FluentForm\App\Modules\Payments;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Services\Form\SubmissionHandlerService;

class PaymentHelper
{
    public static function getFormCurrency($formId)
    {
        $settings = self::getFormSettings($formId, 'public');
        return $settings['currency'];
    }

    public static function formatMoney($amountInCents, $currency)
    {
        $currencySettings = self::getCurrencyConfig(false, $currency);
        $symbol = \html_entity_decode($currencySettings['currency_sign']);
        $position = $currencySettings['currency_sign_position'];
        $decimalSeparator = '.';
        $thousandSeparator = ',';
        if ($currencySettings['currency_separator'] != 'dot_comma') {
            $decimalSeparator = ',';
            $thousandSeparator = '.';
        }
        $decimalPoints = 2;
        if ((int) round($amountInCents) % 100 == 0 && $currencySettings['decimal_points'] == 0) {
            $decimalPoints = 0;
        }

        $amount = number_format($amountInCents / 100, $decimalPoints, $decimalSeparator, $thousandSeparator);

        if ('left' === $position) {
            return $symbol . $amount;
        } elseif ('left_space' === $position) {
            return $symbol . ' ' . $amount;
        } elseif ('right' === $position) {
            return $amount . $symbol;
        } elseif ('right_space' === $position) {
            return $amount . ' ' . $symbol;
        }
        return $amount;
    }

    /**
     * Check payment settings available or not
     * @return bool
     */
    public static function hasPaymentSettings()
    {
        return !!get_option('__fluentform_payment_module_settings');
    }

    public static function getFormSettings($formId, $scope = 'public')
    {
        static $cachedSettings = [];

        if (isset($cachedSettings[$scope . '_' . $formId])) {
            return $cachedSettings[$scope . '_' . $formId];
        }
    
    
        $defaults = [
            'currency'                       => '',
            'push_meta_to_stripe'            => 'no',
            'receipt_email'                  => self::getFormInput($formId,'input_email'),
            'customer_name'                  => self::getFormInput($formId,'input_name'),
            'customer_address'               => self::getFormInput($formId,'address'),
            'transaction_type'               => 'product',
            'stripe_checkout_methods'        => ['card'],
            'stripe_meta_data'               => [
                [
                    'item_value' => '',
                    'label'      => ''
                ]
            ],
            'stripe_account_type'            => 'global',
            'disable_stripe_payment_receipt' => 'no',
            'stripe_custom_config'           => [
                'payment_mode'    => 'live',
                'publishable_key' => '',
                'secret_key'      => ''
            ],
            'custom_paypal_id'               => '',
            'custom_paypal_mode'             => 'live',
            'paypal_account_type'            => 'global'
        ];
        $settings = Helper::getFormMeta($formId, '_payment_settings', []);
        $settings = wp_parse_args($settings, $defaults);
        if (empty($settings['receipt_email'])) {
            $settings['receipt_email'] = self::getFormInput($formId, 'input_email');
        }
        if (empty($settings['customer_name'])) {
            $name = self::getFormInput($formId, 'input_name');
            if (!empty($name)) {
                $settings['customer_name'] = sprintf('{inputs.%s}', $name);
            }
        }
        if (empty($settings['customer_address'])) {
            $settings['customer_address'] = self::getFormInput($formId, 'address');
        }
        
        $globalSettings = self::getPaymentSettings();

        if (!$settings['currency']) {
            $settings['currency'] = $globalSettings['currency'];
        }

        if ($scope == 'public') {
            $settings = wp_parse_args($settings, $globalSettings);
        }


        $cachedSettings[$scope . '_' . $formId] = $settings;

        return $settings;

    }

    public static function getCurrencyConfig($formId = false, $currency = false)
    {
        if ($formId) {
            $settings = self::getFormSettings($formId, 'public');
        } else {
            $settings = self::getPaymentSettings();
        }

        if ($currency) {
            $settings['currency'] = $currency;
        }

        $settings = ArrayHelper::only($settings, ['currency', 'currency_sign_position', 'currency_separator', 'decimal_points']);

        $settings['currency_sign'] = self::getCurrencySymbol($settings['currency']);
        return $settings;
    }

    public static function getPaymentSettings()
    {
        static $paymentSettings;
        if ($paymentSettings) {
            return $paymentSettings;
        }

        $paymentSettings = get_option('__fluentform_payment_module_settings');
        $defaults = [
            'status'                       => 'no',
            'currency'                     => 'USD',
            'currency_sign_position'       => 'left',
            'currency_separator'           => 'dot_comma',
            'decimal_points'               => "2",
            'business_name'                => '',
            'business_logo'                => '',
            'business_address'             => '',
            'debug_log'                    => 'no',
            'all_payments_page_id'         => '',
            'receipt_page_id'              => '',
            'user_can_manage_subscription' => 'yes'
        ];

        $paymentSettings = wp_parse_args($paymentSettings, $defaults);

        return $paymentSettings;
    }

    public static function updatePaymentSettings($data)
    {
        $existingSettings = self::getPaymentSettings();
        $settings = wp_parse_args($data, $existingSettings);
        update_option('__fluentform_payment_module_settings', $settings, 'yes');

        return self::getPaymentSettings();
    }

    /**
     * https://support.stripe.com/questions/which-currencies-does-stripe-support
     */
    public static function getCurrencies()
    {
        $currencies = [
            'AED' => __('United Arab Emirates Dirham', 'fluentform'),
            'AFN' => __('Afghan Afghani', 'fluentform'),
            'ALL' => __('Albanian Lek', 'fluentform'),
            'AMD' => __('Armenian Dram', 'fluentform'),
            'ANG' => __('Netherlands Antillean Gulden', 'fluentform'),
            'AOA' => __('Angolan Kwanza', 'fluentform'),
            'ARS' => __('Argentine Peso','fluentform'), // non amex
            'AUD' => __('Australian Dollar', 'fluentform'),
            'AWG' => __('Aruban Florin', 'fluentform'),
            'AZN' => __('Azerbaijani Manat', 'fluentform'),
            'BAM' => __('Bosnia & Herzegovina Convertible Mark', 'fluentform'),
            'BBD' => __('Barbadian Dollar', 'fluentform'),
            'BDT' => __('Bangladeshi Taka', 'fluentform'),
            'BIF' => __('Burundian Franc', 'fluentform'),
            'BGN' => __('Bulgarian Lev', 'fluentform'),
            'BMD' => __('Bermudian Dollar', 'fluentform'),
            'BND' => __('Brunei Dollar', 'fluentform'),
            'BOB' => __('Bolivian Boliviano', 'fluentform'),
            'BRL' => __('Brazilian Real', 'fluentform'),
            'BSD' => __('Bahamian Dollar', 'fluentform'),
            'BWP' => __('Botswana Pula', 'fluentform'),
            'BZD' => __('Belize Dollar', 'fluentform'),
            'CAD' => __('Canadian Dollar', 'fluentform'),
            'CDF' => __('Congolese Franc', 'fluentform'),
            'CHF' => __('Swiss Franc', 'fluentform'),
            'CLP' => __('Chilean Peso', 'fluentform'),
            'CNY' => __('Chinese Renminbi Yuan', 'fluentform'),
            'COP' => __('Colombian Peso', 'fluentform'),
            'CRC' => __('Costa Rican Colón', 'fluentform'),
            'CVE' => __('Cape Verdean Escudo', 'fluentform'),
            'CZK' => __('Czech Koruna', 'fluentform'),
            'DJF' => __('Djiboutian Franc', 'fluentform'),
            'DKK' => __('Danish Krone', 'fluentform'),
            'DOP' => __('Dominican Peso', 'fluentform'),
            'DZD' => __('Algerian Dinar', 'fluentform'),
            'EGP' => __('Egyptian Pound', 'fluentform'),
            'ETB' => __('Ethiopian Birr', 'fluentform'),
            'EUR' => __('Euro', 'fluentform'),
            'FJD' => __('Fijian Dollar', 'fluentform'),
            'FKP' => __('Falkland Islands Pound', 'fluentform'),
            'GBP' => __('British Pound', 'fluentform'),
            'GEL' => __('Georgian Lari', 'fluentform'),
            'GHS' => __('Ghanaian Cedi', 'fluentform'),
            'GIP' => __('Gibraltar Pound', 'fluentform'),
            'GMD' => __('Gambian Dalasi', 'fluentform'),
            'GNF' => __('Guinean Franc', 'fluentform'),
            'GTQ' => __('Guatemalan Quetzal', 'fluentform'),
            'GYD' => __('Guyanese Dollar', 'fluentform'),
            'HKD' => __('Hong Kong Dollar', 'fluentform'),
            'HNL' => __('Honduran Lempira', 'fluentform'),
            'HRK' => __('Croatian Kuna', 'fluentform'),
            'HTG' => __('Haitian Gourde', 'fluentform'),
            'HUF' => __('Hungarian Forint', 'fluentform'),
            'IDR' => __('Indonesian Rupiah', 'fluentform'),
            'ILS' => __('Israeli New Sheqel', 'fluentform'),
            'INR' => __('Indian Rupee', 'fluentform'),
            'ISK' => __('Icelandic Króna', 'fluentform'),
            'JMD' => __('Jamaican Dollar', 'fluentform'),
            'JPY' => __('Japanese Yen', 'fluentform'),
            'KES' => __('Kenyan Shilling', 'fluentform'),
            'KGS' => __('Kyrgyzstani Som', 'fluentform'),
            'KHR' => __('Cambodian Riel', 'fluentform'),
            'KMF' => __('Comorian Franc', 'fluentform'),
            'KRW' => __('South Korean Won', 'fluentform'),
            'KYD' => __('Cayman Islands Dollar', 'fluentform'),
            'KZT' => __('Kazakhstani Tenge', 'fluentform'),
            'LAK' => __('Lao Kip', 'fluentform'),
            'LBP' => __('Lebanese Pound', 'fluentform'),
            'LKR' => __('Sri Lankan Rupee', 'fluentform'),
            'LRD' => __('Liberian Dollar', 'fluentform'),
            'LSL' => __('Lesotho Loti', 'fluentform'),
            'MAD' => __('Moroccan Dirham', 'fluentform'),
            'MDL' => __('Moldovan Leu', 'fluentform'),
            'MGA' => __('Malagasy Ariary', 'fluentform'),
            'MKD' => __('Macedonian Denar', 'fluentform'),
            'MNT' => __('Mongolian Tögrög', 'fluentform'),
            'MOP' => __('Macanese Pataca', 'fluentform'),
            'MRO' => __('Mauritanian Ouguiya', 'fluentform'),
            'MUR' => __('Mauritian Rupee', 'fluentform'),
            'MVR' => __('Maldivian Rufiyaa', 'fluentform'),
            'MWK' => __('Malawian Kwacha', 'fluentform'),
            'MXN' => __('Mexican Peso', 'fluentform'),
            'MYR' => __('Malaysian Ringgit', 'fluentform'),
            'MZN' => __('Mozambican Metical', 'fluentform'),
            'NAD' => __('Namibian Dollar', 'fluentform'),
            'NGN' => __('Nigerian Naira', 'fluentform'),
            'NIO' => __('Nicaraguan Córdoba', 'fluentform'),
            'NOK' => __('Norwegian Krone', 'fluentform'),
            'NPR' => __('Nepalese Rupee', 'fluentform'),
            'NZD' => __('New Zealand Dollar', 'fluentform'),
            'PAB' => __('Panamanian Balboa', 'fluentform'),
            'PEN' => __('Peruvian Nuevo Sol', 'fluentform'),
            'PGK' => __('Papua New Guinean Kina', 'fluentform'),
            'PHP' => __('Philippine Peso', 'fluentform'),
            'PKR' => __('Pakistani Rupee', 'fluentform'),
            'PLN' => __('Polish Złoty', 'fluentform'),
            'PYG' => __('Paraguayan Guaraní', 'fluentform'),
            'QAR' => __('Qatari Riyal', 'fluentform'),
            'RON' => __('Romanian Leu', 'fluentform'),
            'RSD' => __('Serbian Dinar', 'fluentform'),
            'RUB' => __('Russian Ruble', 'fluentform'),
            'RWF' => __('Rwandan Franc', 'fluentform'),
            'SAR' => __('Saudi Riyal', 'fluentform'),
            'SBD' => __('Solomon Islands Dollar', 'fluentform'),
            'SCR' => __('Seychellois Rupee', 'fluentform'),
            'SEK' => __('Swedish Krona', 'fluentform'),
            'SGD' => __('Singapore Dollar', 'fluentform'),
            'SHP' => __('Saint Helenian Pound', 'fluentform'),
            'SLL' => __('Sierra Leonean Leone', 'fluentform'),
            'SOS' => __('Somali Shilling', 'fluentform'),
            'SRD' => __('Surinamese Dollar', 'fluentform'),
            'STD' => __('São Tomé and Príncipe Dobra', 'fluentform'),
            'SVC' => __('Salvadoran Colón', 'fluentform'),
            'SZL' => __('Swazi Lilangeni', 'fluentform'),
            'THB' => __('Thai Baht', 'fluentform'),
            'TJS' => __('Tajikistani Somoni', 'fluentform'),
            'TOP' => __('Tongan Paʻanga', 'fluentform'),
            'TRY' => __('Turkish Lira', 'fluentform'),
            'TTD' => __('Trinidad and Tobago Dollar', 'fluentform'),
            'TWD' => __('New Taiwan Dollar', 'fluentform'),
            'TZS' => __('Tanzanian Shilling', 'fluentform'),
            'UAH' => __('Ukrainian Hryvnia', 'fluentform'),
            'UGX' => __('Ugandan Shilling', 'fluentform'),
            'USD' => __('United States Dollar', 'fluentform'),
            'UYU' => __('Uruguayan Peso', 'fluentform'),
            'UZS' => __('Uzbekistani Som', 'fluentform'),
            'VND' => __('Vietnamese Đồng', 'fluentform'),
            'VUV' => __('Vanuatu Vatu', 'fluentform'),
            'WST' => __('Samoan Tala', 'fluentform'),
            'XAF' => __('Central African Cfa Franc', 'fluentform'),
            'XCD' => __('East Caribbean Dollar', 'fluentform'),
            'XOF' => __('West African Cfa Franc', 'fluentform'),
            'XPF' => __('Cfp Franc', 'fluentform'),
            'YER' => __('Yemeni Rial', 'fluentform'),
            'ZAR' => __('South African Rand', 'fluentform'),
            'ZMW' => __('Zambian Kwacha', 'fluentform')
        ];
    
        $currencies = apply_filters_deprecated(
            'fluentform_accepted_currencies',
            [
                $currencies
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/accepted_currencies',
            'Use fluentform/accepted_currencies instead of fluentform_accepted_currencies.'
        );

        return apply_filters('fluentform/accepted_currencies', $currencies);
    }

    /**
     * Get a specific currency symbol
     *
     * https://support.stripe.com/questions/which-currencies-does-stripe-support
     */
    public static function getCurrencySymbol($currency = '')
    {
        if (!$currency) {
            // If no currency is passed then default it to USD
            $currency = 'USD';
        }
        $currency = strtoupper($currency);

        $symbols = self::getCurrencySymbols();
        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';
    
        $currency_symbol = apply_filters_deprecated(
            'fluentform_currency_symbol',
            [
                $currency_symbol,
                $currency
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/currency_symbol',
            'Use fluentform/currency_symbol instead of fluentform_currency_symbol.'
        );

        return apply_filters('fluentform/currency_symbol', $currency_symbol, $currency);
    }

    public static function getCurrencySymbols()
    {
        $symbols = [
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&fnof;',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'ISK' => 'Kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x43b;&#x432;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;. &#x645;.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '&#36;',
            'XOF' => 'Fr',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        ];

        $symbols = apply_filters_deprecated(
            'fluentform_currency_symbols',
            [
                $symbols
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/currencies_symbols',
            'Use fluentform/currencies_symbols instead of fluentform_currency_symbols.'
        );

        return apply_filters('fluentform/currencies_symbols', $symbols);
    }

    public static function zeroDecimalCurrencies()
    {
        $zeroDecimalCurrencies = [
            'BIF' => esc_html__('Burundian Franc', 'fluentform'),
            'CLP' => esc_html__('Chilean Peso', 'fluentform'),
            'DJF' => esc_html__('Djiboutian Franc', 'fluentform'),
            'GNF' => esc_html__('Guinean Franc', 'fluentform'),
            'JPY' => esc_html__('Japanese Yen', 'fluentform'),
            'KMF' => esc_html__('Comorian Franc', 'fluentform'),
            'KRW' => esc_html__('South Korean Won', 'fluentform'),
            'MGA' => esc_html__('Malagasy Ariary', 'fluentform'),
            'PYG' => esc_html__('Paraguayan Guaraní', 'fluentform'),
            'RWF' => esc_html__('Rwandan Franc', 'fluentform'),
            'VND' => esc_html__('Vietnamese Dong', 'fluentform'),
            'VUV' => esc_html__('Vanuatu Vatu', 'fluentform'),
            'XAF' => esc_html__('Central African Cfa Franc', 'fluentform'),
            'XOF' => esc_html__('West African Cfa Franc', 'fluentform'),
            'XPF' => esc_html__('Cfp Franc', 'fluentform'),
        ];

        $zeroDecimalCurrencies = apply_filters_deprecated(
            'fluentform_zero_decimal_currencies',
            [
                $zeroDecimalCurrencies
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/zero_decimal_currencies',
            'Use fluentform/zero_decimal_currencies instead of fluentform_zero_decimal_currencies.'
        );

        return apply_filters('fluentform/zero_decimal_currencies', $zeroDecimalCurrencies);
    }

    public static function isZeroDecimal($currencyCode)
    {
        $currencyCode = strtoupper($currencyCode);
        $zeroDecimals = self::zeroDecimalCurrencies();
        return isset($zeroDecimals[$currencyCode]);
    }

    public static function getPaymentStatuses()
    {
        $paymentStatuses = [
            'paid'               => __('Paid', 'fluentform'),
            'processing'         => __('Processing', 'fluentform'),
            'pending'            => __('Pending', 'fluentform'),
            'failed'             => __('Failed', 'fluentform'),
            'refunded'           => __('Refunded', 'fluentform'),
            'partially-refunded' => __('Partial Refunded', 'fluentform'),
            'cancelled'          => __('Cancelled', 'fluentform')
        ];

        $paymentStatuses = apply_filters_deprecated(
            'fluentform_available_payment_statuses',
            [
                $paymentStatuses
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/available_payment_statuses',
            'Use fluentform/available_payment_statuses instead of fluentform_available_payment_statuses.'
        );

        return apply_filters('fluentform/available_payment_statuses', $paymentStatuses);
    }

    public static function getFormPaymentMethods($formId)
    {
        $inputs = FormFieldsParser::getInputs($formId, ['element', 'settings']);
        foreach ($inputs as $field) {
            if ($field['element'] == 'payment_method') {
                $methods = ArrayHelper::get($field, 'settings.payment_methods') ?:  ArrayHelper::get($field, 'raw.settings.payment_methods');
                if (is_array($methods)) {
                    return array_filter($methods, function ($method) {
                        return $method['enabled'] == 'yes';
                    });
                }
            }
        }
        return [];
    }

    public static function getCustomerEmail($submission, $form = false)
    {

        $formSettings = PaymentHelper::getFormSettings($submission->form_id, 'admin');
        $customerEmailField = ArrayHelper::get($formSettings, 'receipt_email');

        if ($customerEmailField) {
            $email = ArrayHelper::get($submission->response, $customerEmailField);
            if ($email) {
                return $email;
            }
        }

        $user = get_user_by('ID', get_current_user_id());

        if ($user) {
            return $user->user_email;
        }

        if (!$form) {
            return '';
        }

        $emailFields = FormFieldsParser::getInputsByElementTypes($form, ['input_email'], ['attributes']);

        foreach ($emailFields as $field) {
            $fieldName = $field['attributes']['name'];
            if (!empty($submission->response[$fieldName])) {
                return $submission->response[$fieldName];
            }
        }

        return '';

    }

    static function getCustomerPhoneNumber($submission, $form) {
        $phoneFields = FormFieldsParser::getInputsByElementTypes($form, ['phone'], ['attributes']);

        foreach ($phoneFields as $field) {
            $fieldName = $field['attributes']['name'];
            if (!empty($submission->response[$fieldName])) {
                return $submission->response[$fieldName];
            }
        }
        return '';
    }

    /**
     * Trim a string and append a suffix.
     *
     * @param string $string String to trim.
     * @param integer $chars Amount of characters.
     *                         Defaults to 200.
     * @param string $suffix Suffix.
     *                         Defaults to '...'.
     * @return string
     */
    public static function formatPaymentItemString($string, $chars = 200, $suffix = '...')
    {
        $string = wp_strip_all_tags($string);
        if (strlen($string) > $chars) {
            if (function_exists('mb_substr')) {
                $string = mb_substr($string, 0, ($chars - mb_strlen($suffix))) . $suffix;
            } else {
                $string = substr($string, 0, ($chars - strlen($suffix))) . $suffix;
            }
        }

        return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Limit length of an arg.
     *
     * @param string $string Argument to limit.
     * @param integer $limit Limit size in characters.
     * @return string
     */
    public static function limitLength($string, $limit = 127)
    {
        $str_limit = $limit - 3;
        if (function_exists('mb_strimwidth')) {
            if (mb_strlen($string) > $limit) {
                $string = mb_strimwidth($string, 0, $str_limit) . '...';
            }
        } else {
            if (strlen($string) > $limit) {
                $string = substr($string, 0, $str_limit) . '...';
            }
        }
        return $string;
    }

    public static function floatToString($float)
    {
        if (!is_float($float)) {
            return $float;
        }

        $locale = localeconv();
        $string = strval($float);
        $string = str_replace($locale['decimal_point'], '.', $string);

        return $string;
    }

    public static function convertToCents($amount)
    {
        if (!$amount) {
            return 0;
        }

        $amount = floatval($amount);

        return intval(round($amount * 100));
    }

    public static function getCustomerName($submission, $form = false)
    {
        $formSettings = PaymentHelper::getFormSettings($submission->form_id, 'admin');
        $customerNameCode = ArrayHelper::get($formSettings, 'customer_name');
        if ($customerNameCode) {
            $customerName = ShortCodeParser::parse($customerNameCode, $submission->id, $submission->response);
            if ($customerName) {
                return $customerName;
            }
        }

        $user = get_user_by('ID', get_current_user_id());

        if ($user) {
            $customerName = trim($user->first_name . ' ' . $user->last_name);
            if (!$customerName) {
                $customerName = $user->display_name;
            }
            if ($customerName) {
                return $customerName;
            }
        }

        if (!$form) {
            return '';
        }

        $nameFields = FormFieldsParser::getInputsByElementTypes($form, ['input_name'], ['attributes']);

        $fieldName = false;
        foreach ($nameFields as $field) {
            if ($field['element'] === 'input_name') {
                $fieldName = $field['attributes']['name'];
                break;
            }
        }

        $name = '';
        if ($fieldName) {
            if (!empty($submission->response[$fieldName])) {
                $names = array_filter($submission->response[$fieldName]);
                return trim(implode(' ', $names));
            }
        }

        return $name;
    }

    public static function getStripeInlineConfig($formId)
    {
        $methods = static::getFormPaymentMethods($formId);

        $stripe = ArrayHelper::get($methods, 'stripe');
        $stripeInlineStyles = ArrayHelper::get(Helper::getFormMeta($formId, '_ff_form_styles', []), 'stripe_inline_element_style', false);
        if ($stripe) {
            return apply_filters(
                'fluentform/stripe_inline_config',
                [
                    'is_inline'     => ArrayHelper::get($stripe, 'settings.embedded_checkout.value') == 'yes',
                    'inline_styles' => $stripeInlineStyles,
                    'verifyZip'     => ArrayHelper::get($methods['stripe'], 'settings.verify_zip_code.value') === 'yes',
                    'disable_link'  => false
                ],
                $formId
            );
        }

        return [];
    }

    public static function log($data, $submission = false, $forceInsert = false)
    {
        if (!$forceInsert) {
            static $paymentSettings;
            if (!$paymentSettings) {
                $paymentSettings = self::getPaymentSettings();
            }

            if (!isset($paymentSettings['debug_log']) || $paymentSettings['debug_log'] != 'yes') {
                return false;
            }
        }

        $defaults = [
            'component'  => 'Payment',
            'status'     => 'info',
            'created_at' => current_time('mysql')
        ];

        if ($submission) {
            $defaults['parent_source_id'] = $submission->form_id;
            $defaults['source_type'] = 'submission_item';
            $defaults['source_id'] = $submission->id;
        } else {
            $defaults['source_type'] = 'system_log';
        }

        $data = wp_parse_args($data, $defaults);

        return \FluentForm\App\Models\Log::create($data)->id;

    }

    public static function maybeFireSubmissionActionHok($submission)
    {
        if (Helper::getSubmissionMeta($submission->id, 'is_form_action_fired') == 'yes') {
            return false;
        }

        $form = \FluentForm\App\Models\Form::find($submission->form_id);

        $formData = $submission->response;
        if (!is_array($formData)) {
            $formData = json_decode($formData, true);
        }

        (new SubmissionHandlerService())->processSubmissionData(
            $submission->id, $formData, $form
        );
        Helper::setSubmissionMeta($submission->id, 'is_form_action_fired', 'yes');
        return true;
    }

    public static function loadView($fileName, $data)
    {
        // normalize the filename
        $fileName = str_replace(array('../', './'), '', $fileName);
    
        $basePath = FLUENTFORM_DIR_PATH . 'app/Views/receipt/';
        $basePath = apply_filters_deprecated(
            'fluentform_payment_receipt_template_base_path',
            [
                $basePath,
                $fileName,
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_template_base_path',
            'Use fluentform/payment_receipt_template_base_path instead of fluentform_payment_receipt_template_base_path.'
        );

        $basePath = apply_filters('fluentform/payment_receipt_template_base_path', $basePath, $fileName, $data);

        $filePath = $basePath . $fileName . '.php';
        extract($data);
        ob_start();
        include $filePath;
        return ob_get_clean();
    }

    public static function recordSubscriptionCancelled($subscription, $vendorData, $logData = [])
    {
        \FluentForm\App\Models\Subscription::where('id', $subscription->id)
            ->update([
                'status'     => 'cancelled',
                'updated_at' => current_time('mysql')
            ]);

        $subscription = \FluentForm\App\Models\Subscription::find($subscription->id);

        $submission = \FluentForm\App\Models\Submission::find($subscription->submission_id);

        $logDefaults = [
            'parent_source_id' => $subscription->form_id,
            'source_type'      => 'submission_item',
            'source_id'        => $subscription->submission_id,
            'component'        => 'Payment',
            'status'           => 'info',
            'title'            => __('Subscription has been cancelled', 'fluentform'),
            'description'      => __('Subscription has been cancelled from ', 'fluentform') . $submission->payment_method
        ];

        $logs = wp_parse_args($logData, $logDefaults);

        do_action('fluentform/log_data', $logs);

        do_action_deprecated(
            'fluentform_subscription_payment_canceled',
            [
                $subscription,
                $submission,
                $vendorData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subscription_payment_canceled',
            'Use fluentform/subscription_payment_canceled instead of fluentform_subscription_payment_canceled.'
        );
        // New Payment Made so we have to fire some events here
        do_action('fluentform/subscription_payment_canceled', $subscription, $submission, $vendorData);

        do_action_deprecated(
            'fluentform_subscription_payment_canceled_' . $submission->payment_method,
            [
                $subscription,
                $submission,
                $vendorData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subscription_payment_canceled_' . $submission->payment_method,
            'Use fluentform/subscription_payment_canceled_' . $submission->payment_method . ' instead of fluentform_subscription_payment_canceled_' . $submission->payment_method
        );
        do_action('fluentform/subscription_payment_canceled_' . $submission->payment_method, $subscription, $submission, $vendorData);
    }

    public static function getPaymentSummaryText($plan, $formId, $currency, $withMarkup = true)
    {
        $paymentSummaryText = [
            'has_signup_fee' => __('{first_interval_total} for first {billing_interval} then {subscription_amount} for each {billing_interval}', 'fluentform'),
            'has_trial'      => __('Free for {trial_days} days then {subscription_amount} for each {billing_interval}', 'fluentform'),
            'onetime_only'   => __('One time payment of {first_interval_total}', 'fluentform'),
            'normal'         => __('{subscription_amount} for each {billing_interval}', 'fluentform'),
            'bill_times'     => __(', for {bill_times} installments', 'fluentform'),
            'single_trial'   => __('Free for {trial_days} days then {subscription_amount} one time', 'fluentform')
        ];

        $paymentSummaryText = apply_filters_deprecated(
            'fluentform_recurring_payment_summary_texts',
            [
                $paymentSummaryText,
                $plan,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/recurring_payment_summary_texts',
            'Use fluentform/recurring_payment_summary_texts instead of fluentform_recurring_payment_summary_texts.'
        );

        $cases = apply_filters('fluentform/recurring_payment_summary_texts', $paymentSummaryText, $plan, $formId);

        // if is trial
        $hasTrial = ArrayHelper::get($plan, 'has_trial_days') == 'yes' && ArrayHelper::get($plan, 'trial_days');
        if ($hasTrial) {
            $plan['signup_fee'] = 0;
        }

        $signupFee = 0;
        $hasSignupFee = ArrayHelper::get($plan, 'has_signup_fee') == 'yes' && ArrayHelper::get($plan, 'signup_fee');
        if ($hasSignupFee) {
            $plan['trial_days'] = 0;
            $signupFee = ArrayHelper::get($plan, 'signup_fee');
        }

        $firstIntervalTotal = PaymentHelper::formatMoney(
            PaymentHelper::convertToCents($signupFee + ArrayHelper::get($plan, 'subscription_amount')),
            $currency
        );

        if ($signupFee) {
            $signupFee = PaymentHelper::formatMoney(
                PaymentHelper::convertToCents($signupFee),
                $currency
            );
        }

        $subscriptionAmount = PaymentHelper::formatMoney(
            PaymentHelper::convertToCents(ArrayHelper::get($plan, 'subscription_amount')),
            $currency
        );

        $billingInterval = $plan['billing_interval'];
        $billingInterval = ArrayHelper::get(self::getBillingIntervals(), $billingInterval, $billingInterval);
        $replaces = array(
            '{signup_fee}'           => '<span class="ff_bs ffbs_signup_fee">' . $signupFee . '</span>',
            '{first_interval_total}' => '<span class="ff_bs ffbs_first_interval_total">' . $firstIntervalTotal . '</span>',
            '{subscription_amount}'  => '<span class="ff_bs ffbs_subscription_amount">' . $subscriptionAmount . '</span>',
            '{billing_interval}'     => '<span class="ff_bs ffbs_billing_interval">' . $billingInterval . '</span>',
            '{trial_days}'           => '<span class="ff_bs ffbs_trial_days">' . $plan['trial_days'] . '</span>',
            '{bill_times}'           => '<span class="ff_bs ffbs_bill_times">' . ArrayHelper::get($plan, 'bill_times') . '</span>'
        );

        if (ArrayHelper::get($plan, 'user_input') == 'yes') {
            $cases['{subscription_amount}'] = '<span class="ff_dynamic_input_amount">' . $subscriptionAmount . '</span>';
        }

        foreach ($cases as $textKey => $text) {
            $cases[$textKey] = str_replace(array_keys($replaces), array_values($replaces), $text);
        }

        $customText = '';
        if ($hasSignupFee) {
            $customText = $cases['has_signup_fee'];
        } else if ($hasTrial) {
            if (ArrayHelper::get($plan, 'bill_times') == 1) {
                $customText = $cases['single_trial'];
            } else {
                $customText = $cases['has_trial'];
            }
        } else if (isset($plan['bill_times']) && $plan['bill_times'] == 1) {
            $customText = $cases['onetime_only'];
        } else {
            $customText = $cases['normal'];
        }

        if (isset($plan['bill_times']) && $plan['bill_times'] > 1) {
            $customText .= $cases['bill_times'];
        }
        if($withMarkup) {
            $class = $plan['is_default'] === 'yes' ? '' : 'hidden_field';
            return '<div class="ff_summary_container ff_summary_container_' . $plan['index'] . ' ' . $class . '">' . $customText . '</div>';
        }
        return $customText;
    }

	public static function getCustomerAddress($submission)
	{
		$formSettings = PaymentHelper::getFormSettings($submission->form_id, 'admin');
		$customerAddressField = ArrayHelper::get($formSettings, 'customer_address');

		if ($customerAddressField) {
            return ArrayHelper::get($submission->response, $customerAddressField);
		}

		return null;
	}

    public static function getBillingIntervals()
    {
        return [
            'day'   => __('day', 'fluentform'),
            'week'  => __('week', 'fluentform'),
            'month' => __('month', 'fluentform'),
            'year'  => __('year', 'fluentform')
        ];
    }

    public static function getSubscriptionStatuses()
    {
        return [
            'active'    => __('active', 'fluentform'),
            'trialling' => __('trialling', 'fluentform'),
            'failing'   => __('failing', 'fluentform'),
            'cancelled' => __('cancelled', 'fluentform')
        ];
    }
    
    public static function getFormInput($formId,$inputType)
    {
        $form = fluentFormApi()->form($formId);
        $fields = FormFieldsParser::getInputsByElementTypes($form, [$inputType], ['attributes']);
        if (!empty($fields)) {
            $field = array_shift($fields);
            return ArrayHelper::get($field, 'attributes.name');
        }
        return '';
    }

    public static function encryptKey($value)
    {
        if(!$value) {
            return $value;
        }

        if ( ! extension_loaded( 'openssl' ) ) {
            return $value;
        }

        $salt = (defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';
        $key = ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = openssl_random_pseudo_bytes( $ivlen );

        $raw_value = openssl_encrypt( $value . $salt, $method, $key, 0, $iv );
        if ( ! $raw_value ) {
            return false;
        }

        return base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    public static function decryptKey( $raw_value ) {

        if(!$raw_value) {
            return $raw_value;
        }

        if ( ! extension_loaded( 'openssl' ) ) {
            return $raw_value;
        }

        $raw_value = base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = substr( $raw_value, 0, $ivlen );

        $raw_value = substr( $raw_value, $ivlen );

        $salt = (defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';
        $key = ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';

        $value = openssl_decrypt( $raw_value, $method, $key, 0, $iv );
        if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
            return false;
        }

        return substr( $value, 0, - strlen( $salt ) );
    }

    public static function isPlanExpiredAndHidden($plan)
    {
        if (ArrayHelper::get($plan, 'has_end_date') !== 'yes') {
            return false;
        }
        if (ArrayHelper::get($plan, 'expire_behavior') !== 'hide') {
            return false;
        }
        $endDateStr = ArrayHelper::get($plan, 'subscription_end_date');
        if (!$endDateStr) {
            return false;
        }
        $endDate = strtotime($endDateStr . ' +1 day');
        return !$endDate || $endDate <= current_time('timestamp');
    }
}
