<?php

namespace FluentForm\App\Services\GlobalSettings;

use FluentForm\Framework\Support\Arr;

class GlobalSettingsService
{
    private function isAllowedOptionKey($key)
    {
        $allowedPrefixes = [
            'fluentform_',
            '_fluentform_',
            'fluentform-',
            '_fluentform-',
        ];
        foreach ($allowedPrefixes as $prefix) {
            if (strpos($key, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    public function get($attributes = [])
    {
        $values = [];
        $key = Arr::get($attributes, 'key');

        if (is_array($key)) {
            foreach ($key as $key_item) {
                $sanitizedKey = sanitize_text_field($key_item);
                if (!$this->isAllowedOptionKey($sanitizedKey)) {
                    continue;
                }
                $values[$key_item] = get_option($sanitizedKey);
            }
        } else {
            $sanitizedKey = sanitize_text_field($key);
            if (!$this->isAllowedOptionKey($sanitizedKey)) {
                return $values;
            }
            $values[$key] = get_option($sanitizedKey);
        }
    
        $values = apply_filters_deprecated(
            'fluentform_get_global_settings_values',
            [
                $values,
                $key
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_global_settings_values',
            'Use fluentform/get_global_settings_values instead of fluentform_get_global_settings_values.'
        );

        return apply_filters('fluentform/get_global_settings_values', $values, $key);
    }

    public function store($attributes = [])
    {
        $key = Arr::get($attributes, 'key');
        if (is_array($key)) {
            $key = array_map('sanitize_text_field', $key);
        } else {
            $key = sanitize_text_field($key);
        }

        $globalSettingsHelper = new GlobalSettingsHelper();

        $allowedMethods = [
            'storeReCaptcha',
            'storeHCaptcha',
            'storeTurnstile',
            'storeCleantalk',
            'storeSaveGlobalLayoutSettings',
            'storeMailChimpSettings',
            'storeEmailSummarySettings',
            'storeAutosaveSettings',
            'storeDefaultStyleTemplate',
        ];

        $method = '';
        $container = [];
        if (is_array($key)) {
            foreach ($key as $item) {
                $method = 'store' . ucwords($item);
                if (in_array($method, $allowedMethods)) {
                    $container[] = $globalSettingsHelper->{$method}($attributes);
                }
            }
            return $container;
        } else {
            $method = 'store' . ucwords($key);
        }

        do_action_deprecated(
            'fluentform_saving_global_settings_with_key_method',
            [
                $attributes
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/saving_global_settings_with_key_method',
            'Use fluentform/saving_global_settings_with_key_method instead of fluentform_saving_global_settings_with_key_method.'
        );

        do_action('fluentform/saving_global_settings_with_key_method', $attributes);

        if (in_array($method, $allowedMethods)) {
            return $globalSettingsHelper->{$method}($attributes);
        }
    }
}
