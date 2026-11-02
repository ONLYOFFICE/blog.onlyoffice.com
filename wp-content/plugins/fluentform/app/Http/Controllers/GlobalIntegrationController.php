<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\App\Services\Integrations\GlobalIntegrationService;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use Exception;

class GlobalIntegrationController extends Controller
{
  
    public function index(GlobalIntegrationService $globalIntegrationService)
    {
        try {
            $attributes = $this->request->all();
            $returnData = $globalIntegrationService->get($attributes);
            if (Arr::isTrue($returnData, 'status')) {
                return $this->sendSuccess($returnData);
            }
            // No settings_key provided is a list-style call, not an error; return 200 with empty payload.
            if (!Arr::get($attributes, 'settings_key')) {
                return $this->sendSuccess([
                    'status'      => false,
                    'integration' => [],
                    'settings'    => [],
                ]);
            }
            return $this->sendError($returnData);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function updateIntegration()
    {
        try {
            $settingsKey = sanitize_text_field($this->request->get('settings_key'));
            $integration = wp_unslash($this->request->get('integration'));

            // SECURITY (FINDING-16): connected credentials are redacted on read; restore any field
            // the browser posted back still masked so a re-save (e.g. "Verify Connection Again")
            // cannot overwrite a live credential with the '********' mask.
            $integration = (new GlobalIntegrationService())->unmaskCredentials($settingsKey, $integration);

            do_action_deprecated(
                'fluentform_save_global_integration_settings_' . $settingsKey,
                [
                    $integration
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/save_global_integration_settings_' . $settingsKey,
                'Use fluentform/save_global_integration_settings_' . $settingsKey . ' instead of fluentform_save_global_integration_settings_' . $settingsKey
            );

            do_action('fluentform/save_global_integration_settings_' . $settingsKey, $integration);
            
            // Someone should catch that above action and send response
            return $this->sendError([
                'message' => __('Sorry, no Integration found. Please make sure that latest version of Fluent Forms pro installed',
                    'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateModuleStatus(GlobalIntegrationService $globalIntegrationService)
    {
        try {
            $globalIntegrationService->updateModuleStatus($this->request->get());
            return $this->sendSuccess([
                'message' => __('Status successfully updated', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    
}
