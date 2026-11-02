<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Manager\FormManagerService;
use FluentForm\Framework\Http\Request\Request;
use FluentForm\Framework\Foundation\Policy;
use FluentForm\Framework\Support\Arr;

class ReportPolicy extends Policy
{
    private function canAccessReportData(Request $request)
    {
        $formId = $this->resolveFormId($request);

        if ($formId) {
            return Acl::hasPermission('fluentform_entries_viewer', $formId);
        }

        return Acl::hasPermission('fluentform_entries_viewer');
    }

    private function canAccessDashboardReport(Request $request)
    {
        if (!Acl::hasPermission('fluentform_dashboard_access')) {
            return false;
        }

        return $this->canAccessReportData($request);
    }

    private function canAccessRequestedForm(Request $request)
    {
        return $this->canAccessReportData($request);
    }

    private function canAccessOptionalFormScopedReport(Request $request)
    {
        if (!Acl::hasPermission('fluentform_dashboard_access')) {
            return false;
        }

        $formId = $this->resolveFormId($request);

        if ($formId) {
            return Acl::hasPermission('fluentform_entries_viewer', $formId);
        }

        if (!Acl::hasPermission('fluentform_entries_viewer')) {
            return false;
        }

        $userId = get_current_user_id();

        return !$userId || !FormManagerService::hasSpecificFormsPermission($userId);
    }

    private function canAccessPaymentReport(Request $request)
    {
        // Payment/revenue/subscription data requires the dedicated, form-scoped
        // payment-view capability — the entry-view capability must not authorize
        // financial aggregates.
        $formId = $this->resolveFormId($request);

        if ($formId) {
            return Acl::hasPermission('fluentform_view_payments', $formId);
        }

        if (!Acl::hasPermission('fluentform_view_payments')) {
            return false;
        }

        // A specific-forms manager must NOT read the all-forms (form_id 0)
        // payment view — otherwise the per-form scope is bypassed by loading the
        // aggregate revenue/subscription report. Mirrors canAccessOptionalFormScopedReport.
        $userId = get_current_user_id();

        return !$userId || !FormManagerService::hasSpecificFormsPermission($userId);
    }

    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return Acl::hasPermission('fluentform_dashboard_access');
    }

    public function form(Request $request)
    {
        return $this->canAccessRequestedForm($request);
    }

    public function submissions(Request $request)
    {
        return $this->canAccessRequestedForm($request);
    }

    public function getOverviewChart(Request $request)
    {
        return $this->canAccessRequestedForm($request);
    }

    public function getRevenueChart(Request $request)
    {
        return $this->canAccessPaymentReport($request);
    }

    public function getFormStats(Request $request)
    {
        return $this->canAccessRequestedForm($request);
    }

    public function getApiLogs(Request $request)
    {
        return $this->canAccessRequestedForm($request);
    }

    public function getPaymentTypes(Request $request)
    {
        return $this->canAccessPaymentReport($request);
    }

    public function getCompletionRate(Request $request)
    {
        return $this->canAccessOptionalFormScopedReport($request);
    }

    public function getHeatmapData(Request $request)
    {
        return $this->canAccessOptionalFormScopedReport($request);
    }

    public function getCountryHeatmap(Request $request)
    {
        return $this->canAccessOptionalFormScopedReport($request);
    }

    public function getTopPerformingForms(Request $request)
    {
        return $this->canAccessDashboardReport($request);
    }

    public function getSubscriptions(Request $request)
    {
        return $this->canAccessPaymentReport($request);
    }

    public function getFormsDropdown(Request $request)
    {
        return $this->canAccessDashboardReport($request);
    }

    public function netRevenue(Request $request)
    {
        return $this->canAccessPaymentReport($request);
    }

    public function submissionsAnalysis(Request $request)
    {
        return $this->canAccessOptionalFormScopedReport($request);
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
