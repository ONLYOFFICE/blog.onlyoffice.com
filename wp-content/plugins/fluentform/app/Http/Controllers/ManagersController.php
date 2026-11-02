<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Manager\ManagerService;

class ManagersController extends Controller
{
    public function index(ManagerService $managerService)
    {
        $attributes = $this->request->all();
        
        $sanitizeMap = [
            'search' => 'sanitize_text_field',
        ];
        $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
        
        $result = $managerService->getManagers($attributes);
        return $this->sendSuccess($result);
    }
    
    public function addManager(ManagerService $managerService)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'user_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $result = $managerService->addManager($attributes);
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
    
    public function removeManager(ManagerService $managerService)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'user_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            $result = $managerService->removeManager($attributes);
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }

    public function getUsers(ManagerService $managerService)
    {
        $search = sanitize_text_field($this->request->get('search', ''));
        
        $users = get_users([
            'search' => "*{$search}*",
            'number' => 50,
            'fields'  => ['ID', 'display_name', 'user_email']
        ]);
        
        $formattedUsers = [];
        foreach ($users as $user) {
            $formattedUsers[] = [
                'ID'           => $user->ID,
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
                'label'        => $user->display_name . ' - ' . $user->user_email,
            ];
        }

        return $this->sendSuccess([
            'users' => $formattedUsers,
        ]);
    }
}
