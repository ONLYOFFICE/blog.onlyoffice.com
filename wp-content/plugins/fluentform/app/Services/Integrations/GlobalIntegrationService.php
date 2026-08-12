<?php

namespace FluentForm\App\Services\Integrations;

use Exception;
use FluentForm\Framework\Support\Arr;
class GlobalIntegrationService
{
    // Sentinel written over connected credential values on read (FINDING-16) and
    // detected again on save so a re-posted mask never overwrites a live secret.
    const REDACTION_MASK = '********';

    public function get($attr)
    {
        $settingsKey = sanitize_text_field(Arr::get($attr, 'settings_key'));
        $settings = apply_filters_deprecated(
            'fluentform_global_integration_settings_' . $settingsKey,
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_integration_settings_' . $settingsKey,
            'Use fluentform/global_integration_settings_' . $settingsKey . ' instead of fluentform_global_integration_settings_' . $settingsKey
        );
        $settings = apply_filters('fluentform/global_integration_settings_' . $settingsKey, $settings);
        $fieldSettings = apply_filters_deprecated(
            'fluentform_global_integration_fields_' . $settingsKey,
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_integration_fields_' . $settingsKey,
            'Use fluentform/global_integration_fields_' . $settingsKey . ' instead of fluentform_global_integration_fields_' . $settingsKey
        );
        $fieldSettings = apply_filters('fluentform/global_integration_fields_' . $settingsKey, $fieldSettings);
        if (!$fieldSettings) {
            $message = __('Sorry! No integration failed found with: ', 'fluentform').$settingsKey;
           return [
               'status' => false,
               'message'=> $message,
               'integration' => $settings,
               'settings'    => $fieldSettings,
           ];
        }
        
        if (!Arr::exists($fieldSettings,'save_button_text')) {
            $fieldSettings['save_button_text'] = __('Save Settings', 'fluentform');
        }
        
        if (!Arr::exists($fieldSettings,'valid_message')) {
            $fieldSettings['valid_message'] = __('Your API Key is valid', 'fluentform');
        }
        
        if (!Arr::exists($fieldSettings,'invalid_message')) {
            $fieldSettings['invalid_message'] = __('Your API Key is not valid', 'fluentform');
        }

        // SECURITY (FINDING-16): the stored settings carry long-lived third-party credentials
        // (API keys, tokens). This read endpoint is reachable by delegated roles and the payload
        // returned them verbatim — hide_on_valid only hid the field in the Vue UI, not in the REST
        // response. When the integration is connected and its credential fields are hidden anyway
        // (hide_on_valid), redact the declared field values so the browser never receives the
        // secret. The save path restores any field posted back still masked (see
        // unmaskCredentials), so a re-saved connected integration — e.g. the "Verify Connection
        // Again" button, which re-POSTs the loaded payload — can never overwrite a live credential
        // with the mask.
        if (is_array($settings) && !empty($fieldSettings['hide_on_valid']) && !empty($settings['status'])) {
            foreach (array_keys((array) Arr::get($fieldSettings, 'fields', [])) as $credentialKey) {
                if (!empty($settings[$credentialKey]) && is_string($settings[$credentialKey])) {
                    $settings[$credentialKey] = self::REDACTION_MASK;
                }
            }
        }

        return [
            'status' => true,
            'integration' => $settings,
            'settings'    => $fieldSettings,
        ];
    }
    
    /**
     * Restore any credential the browser posted back still masked (REDACTION_MASK)
     * to its real stored value before it is persisted. get() redacts connected
     * credentials on read; without this, re-saving a connected integration — the
     * "Verify Connection Again" button re-POSTs the loaded payload verbatim, and
     * several handlers persist BEFORE their auth test — would overwrite a live key
     * with '********'. Stored settings come from the same filter get() reads.
     */
    public function unmaskCredentials($settingsKey, $integration)
    {
        if (!is_array($integration) || !in_array(self::REDACTION_MASK, $integration, true)) {
            return $integration;
        }

        $stored = apply_filters('fluentform/global_integration_settings_' . $settingsKey, []);
        if (!is_array($stored)) {
            return $integration;
        }

        foreach ($integration as $key => $value) {
            if (self::REDACTION_MASK === $value && isset($stored[$key]) && is_string($stored[$key])) {
                $integration[$key] = $stored[$key];
            }
        }

        return $integration;
    }

    public function isEnabled($integrationKey)
    {
        $globalModules = get_option('fluentform_global_modules_status');
        $isEnabled = $globalModules && isset($globalModules[$integrationKey]) && 'yes' == $globalModules[$integrationKey];

        return apply_filters('fluentform/is_integration_enabled_'.$integrationKey, $isEnabled);
    }

    /**
     * @param $args - key value pair array
     * @throws Exception
     * @return void
     */
    public function updateModuleStatus($args) {
        $moduleKey = sanitize_text_field(Arr::get($args, 'module_key'));
        $moduleStatus = sanitize_text_field(Arr::get($args, 'module_status'));
        if (!$moduleKey || !in_array($moduleStatus, ['yes', 'no'])) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output
            throw new Exception(__('Status update failed. Not valid module or status', 'fluentform'));
        }
        try {
            $modules = (array)get_option('fluentform_global_modules_status');
            $modules[$moduleKey] = $moduleStatus;
            update_option('fluentform_global_modules_status', $modules, 'no');
        } catch (Exception $e) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output
            throw new Exception($e->getMessage());
        }
    }
}
