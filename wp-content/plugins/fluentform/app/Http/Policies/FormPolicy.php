<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Http\Request\Request;
use FluentForm\Framework\Foundation\Policy;
use FluentForm\Framework\Support\Arr;

class FormPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return Acl::hasPermission('fluentform_forms_manager', $this->resolveFormId($request));
    }

    public function index(Request $request)
    {
        return Acl::hasPermission('fluentform_dashboard_access', $this->resolveFormId($request));
    }

    public function templates(Request $request)
    {
        return Acl::hasAnyFormPermission($this->resolveFormId($request));
    }

    public function find(Request $request)
    {
        return Acl::hasPermission('fluentform_forms_manager', $this->resolveFormId($request));
    }

    public function delete(Request $request)
    {
        return Acl::hasPermission('fluentform_forms_manager', $this->resolveFormId($request));
    }

    public function integrationListComponent(Request $request)
    {
        return Acl::hasPermission('fluentform_forms_manager', $this->resolveFormId($request));
    }

    public function updateModuleStatus(Request $request)
    {
        return Acl::hasPermission('fluentform_settings_manager', $this->resolveFormId($request));
    }

    public function updateIntegration(Request $request)
    {
        return Acl::hasPermission('fluentform_settings_manager', $this->resolveFormId($request));
    }

    public function ping()
    {
        return Acl::hasAnyFormPermission();
    }

    private function resolveFormId(Request $request)
    {
        $route = $request->route();
        $routeFormId = $route ? Arr::get($route->getParameter(), 'form_id') : null;
        $routeFormId = Acl::normalizeFormId($routeFormId);

        if ($routeFormId) {
            return $routeFormId;
        }

        return Acl::normalizeFormId($request->get('form_id'));
    }
}
