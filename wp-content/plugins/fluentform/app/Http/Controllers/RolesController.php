<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Roles\RolesService;

class RolesController extends Controller
{
    public function index(RolesService $rolesService)
    {
        $attributes = $this->request->all();
        
        $sanitizeMap = [
            'search' => 'sanitize_text_field',
        ];
        $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
        
        $result = $rolesService->getRoles($attributes);
        return $this->sendSuccess($result);
    }
    
    public function addCapability(RolesService $rolesService)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'role' => 'sanitize_text_field',
                'capability' => 'sanitize_text_field',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $result = $rolesService->setCapability($attributes);
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
}
