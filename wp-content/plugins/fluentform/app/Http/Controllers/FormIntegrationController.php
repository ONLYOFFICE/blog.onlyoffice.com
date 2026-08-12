<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Integrations\FormIntegrationService;

class FormIntegrationController extends Controller
{
    public function index(FormIntegrationService $integrationService, $formId)
    {
        try {
            $formId = (int) $formId;
            // SECURITY (FINDING-09): returns full integration feed configurations (webhook
            // URLs, headers, credential-like fields). The shared `index` method name resolved
            // to FormPolicy@index (dashboard_access); require forms-manager on this form.
            if (!Acl::hasPermission('fluentform_forms_manager', $formId)) {
                return $this->sendError([
                    'message' => __('You do not have permission to view these integrations.', 'fluentform'),
                ], 403);
            }
            return $this->sendSuccess(
                $integrationService->get($formId)
            );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function find(FormIntegrationService $integrationService, $formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;

            $integration = $integrationService->find($attributes);
            return $this->sendSuccess($integration);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function update(FormIntegrationService $integrationService, $formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;

            $integration = $integrationService->update($attributes);
            return $this->sendSuccess($integration);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    public function delete(FormIntegrationService $integrationService, $formId)
    {
        try {
            $formId = (int) $formId;
            $id = intval($this->request->get('integration_id'));
            $integrationService->delete($id, $formId);
            return $this->sendSuccess([
                'message' => __('Successfully deleted the Integration.', 'fluentform'),
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function integrationListComponent($formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;
            
            $sanitizeMap = [
                'integration_name' => 'sanitize_text_field',
                'list_id'          => 'sanitize_text_field',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $integrationName = $attributes['integration_name'];
            $formId = (int)$attributes['form_id'];
            $listId = $attributes['list_id'];
            $merge_fields = false;
            $merge_fields = apply_filters_deprecated(
                'fluentform_get_integration_merge_fields_' . $integrationName,
                [
                    $merge_fields,
                    $listId,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/get_integration_merge_fields_' . $integrationName,
                'Use fluentform/get_integration_merge_fields_' . $integrationName . ' instead of fluentform_get_integration_merge_fields_' . $integrationName
            );

            $merge_fields = apply_filters('fluentform/get_integration_merge_fields_' . $integrationName, $merge_fields, $listId, $formId);
            
            return $this->sendSuccess([
                'merge_fields' => $merge_fields,
            ]);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
}
