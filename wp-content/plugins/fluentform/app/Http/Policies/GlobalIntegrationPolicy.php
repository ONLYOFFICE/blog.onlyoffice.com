<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Foundation\Policy;
use FluentForm\Framework\Http\Request\Request;

class GlobalIntegrationPolicy extends Policy
{
    public function verifyRequest(Request $request)
    {
        return $this->canManageGlobalIntegrations();
    }

    public function index()
    {
        return $this->canManageGlobalIntegrations();
    }

    public function updateIntegration()
    {
        return $this->canManageGlobalIntegrations();
    }

    public function updateModuleStatus()
    {
        return $this->canManageGlobalIntegrations();
    }

    private function canManageGlobalIntegrations()
    {
        if (
            current_user_can('manage_options') ||
            current_user_can('fluentform_full_access') ||
            current_user_can('fluentform_settings_manager')
        ) {
            return true;
        }

        $roleCapability = Acl::getCurrentUserCapability();

        // Legacy role-level Fluent Forms access stores the WP role key here.
        // Granular Fluent Forms permissions must not imply settings access.
        return $roleCapability && !in_array($roleCapability, Acl::getPermissionSet(), true);
    }
}
