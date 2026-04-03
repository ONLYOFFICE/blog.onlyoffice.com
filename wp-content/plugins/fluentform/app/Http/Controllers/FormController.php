<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Form\FormService;
use FluentForm\App\Services\Form\HistoryService;
use FluentForm\Framework\Support\Arr;

class FormController extends Controller
{
    /**
     * Get the paginated forms matching search criteria.
     *
     * @param \FluentForm\App\Services\Form\FormService $formService
     *
     * @return \WP_REST_Response
     */
    public function index(FormService $formService)
    {
        $attributes = $this->request->all();
        
        return $this->sendSuccess(
            $formService->get($attributes)
        );
    }
    
    /**
     * Create a form from backend/editor
     *
     * @param \FluentForm\App\Services\Form\FormService $formService
     *
     * @return \WP_REST_Response
     */
    public function store(FormService $formService)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'title'       => 'sanitize_text_field',
                'template_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $form = $formService->store($attributes);
            
            return $this->sendSuccess([
                'formId'       => $form->id,
                'redirect_url' => admin_url(
                    'admin.php?page=fluent_forms&form_id=' . $form->id . '&route=editor'
                ),
                'message'      => __('Successfully created a form.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function duplicate(FormService $formService)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'form_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $form = $formService->duplicate($attributes);
            
            return $this->sendSuccess([
                'message'  => __('Form has been successfully duplicated.', 'fluentform'),
                'form_id'  => $form->id,
                'redirect' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $form->id),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function find(FormService $formService)
    {
        try {
            $id = (int)$this->request->get('form_id');
            
            $form = $formService->find($id);
            
            return $this->sendSuccess($form, 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function delete(FormService $formService)
    {
        try {
            $id = (int)$this->request->get('form_id');
            
            $formService->delete($id);
            
            return $this->sendSuccess([
                'message' => __('Successfully deleted the form.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function update(FormService $formService)
    {
        try {
            // Sanitization handled in Updater::update() — only title, status, form_id, formFields are extracted
            $attributes = $this->request->all();

            $formService->update($attributes);
            
            return $this->sendSuccess([
                'message' => __('The form is successfully updated.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function convert(FormService $formService)
    {
        try {
            $formId = (int)$this->request->get('form_id');
            $formService->convert($formId);
            
            return $this->sendSuccess([
                'message' => __('The form is successfully converted.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function templates(FormService $formService)
    {
        try {
            return $this->sendSuccess($formService->templates(), 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function resources(FormService $formService, $formId)
    {
        $components = $formService->components($formId);
        
        $disabledComponents = $formService->getDisabledComponents();
        
        return $this->sendSuccess([
            'components'          => $components,
            'disabled_components' => $disabledComponents,
            'shortcodes'          => fluentFormEditorShortCodes(),
            'edit_history'        => HistoryService::get($formId)
        ]);
    }
    
    public function fields(FormService $formService, $formId)
    {
        return $this->sendSuccess($formService->fields($formId));
    }
    
    public function shortcodes(FormService $formService, $formId)
    {
        return $this->sendSuccess($formService->shortcodes($formId));
    }
    
    public function pages(FormService $formService)
    {
        return $this->sendSuccess($formService->pages());
    }
    
    public function findShortCodePage(FormService $formService, $formId)
    {
        return $this->sendSuccess($formService->findShortCodePage($formId));
    }
    
    public function formEditHistory(HistoryService $historyService, $formId)
    {
        return $this->sendSuccess($historyService::get($formId));
    }
    
    public function clearEditHistory(HistoryService $historyService)
    {
        try {
            $id = (int)$this->request->get('form_id');
            
            $historyService->delete($id);
            return $this->sendSuccess([
                'message' => __('Successfully deleted edit history.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function ping()
    {
        return ['message' => 'pong'];
    }
}
