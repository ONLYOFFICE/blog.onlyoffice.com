<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Settings\Customizer;
use FluentForm\App\Services\Settings\SettingsService;
use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Submission\SubmissionService;

class FormSettingsController extends Controller
{
    public function index(SettingsService $settingsService, $formId)
    {
        $formId = (int) $formId;

        // SECURITY (FINDING-09): this endpoint returns arbitrary form_meta by meta_key —
        // including integration feeds that hold webhook Authorization headers/credentials.
        // Because the method name collides with the forms-list controller, it resolved to
        // FormPolicy@index (fluentform_dashboard_access, the lowest tier). Require the
        // forms-manager capability, scoped to this form, to read its settings/meta.
        if (!Acl::hasPermission('fluentform_forms_manager', $formId)) {
            return $this->sendError([
                'message' => __('You do not have permission to view these settings.', 'fluentform'),
            ], 403);
        }

        $attributes = $this->request->all();
        $attributes['form_id'] = $formId;

        $result = $settingsService->get($attributes);

        return $this->sendSuccess($result);
    }

    public function general(SettingsService $settingsService, $formId)
    {
        $result = $settingsService->general($formId);

        return $this->sendSuccess($result);
    }

    public function saveGeneral(SettingsService $settingsService, $formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;

            $settingsService->saveGeneral($attributes);

            return $this->sendSuccess([
                'message' => __('Settings has been saved.', 'fluentform'),
            ]);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }

    public function store(SettingsService $settingsService, $formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;

            [$settingsId, $settings] = $settingsService->store($attributes);

            return $this->sendSuccess([
                'message'  => __('Settings has been saved.', 'fluentform'),
                'id'       => $settingsId,
                'settings' => $settings,
            ]);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }

    public function remove(SettingsService $settingsService, $formId)
    {
        $attributes = $this->request->all();
        $attributes['form_id'] = (int) $formId;

        $settingsService->remove($attributes);

        return $this->sendSuccess([]);
    }

    public function customizer(Customizer $customizer, $id)
    {
        $metaKeys = [
            '_custom_form_css',
            '_custom_form_js',
            '_ff_selected_style',
            '_ff_form_styles'
        ];
        
        return $this->sendSuccess($customizer->get($id, $metaKeys));
    }

    public function storeCustomizer(Customizer $customizer, $id)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $id;

            $customizer->store($attributes);

            return $this->sendSuccess([
                'message' => __('Custom CSS & JS successfully saved.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function storeEntryColumns(SubmissionService $submissionService, $id)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $id;

            $submissionService->storeColumnSettings($attributes);

            return $this->sendSuccess([
                'message' => __('The column display order has been saved.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function conversationalDesign(SettingsService $settingsService, $formId)
    {
        try {
            return $this->sendSuccess($settingsService->conversationalDesign($formId));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function storeConversationalDesign(SettingsService $settingsService, $formId)
    {
        try {
            return $this->sendSuccess($settingsService->storeConversationalDesign($this->request->all(), $formId));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function getPreset(SettingsService $settingsService, $formId)
    {
        try {
            return $this->sendSuccess($settingsService->getPreset($formId));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function savePreset(SettingsService $settingsService, $formId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['form_id'] = (int) $formId;

            return $this->sendSuccess($settingsService->savePreset($attributes));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }
}
