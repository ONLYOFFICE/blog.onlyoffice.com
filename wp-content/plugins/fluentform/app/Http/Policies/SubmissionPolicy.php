<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Http\Request\Request;
use FluentForm\Framework\Foundation\Policy;
use FluentForm\Framework\Support\Arr;

class SubmissionPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        $formId = $this->resolveFormId($request);
        return Acl::hasPermission('fluentform_entries_viewer', $formId);
    }

    public function handleBulkActions(Request $request)
    {
        $formId = $this->resolveFormId($request);
        return Acl::hasPermission('fluentform_manage_entries', $formId);
    }

    public function store(Request $request)
    {
        return $this->handleBulkActions($request);
    }

    public function updateStatus(Request $request)
    {
        return $this->handleBulkActions($request);
    }

    public function toggleIsFavorite(Request $request)
    {
        return $this->handleBulkActions($request);
    }

    public function remove(Request $request)
    {
        return $this->handleBulkActions($request);
    }

    public function print(Request $request)
    {
        return $this->handleBulkActions($request);
    }

    public function updateSubmissionUser(Request $request)
    {
        // Controller now uses entry_id from route as the mutation target,
        // so authorization and mutation are always the same record.
        $formId = $this->resolveFormId($request);
        return Acl::hasPermission('fluentform_manage_entries', $formId);
    }

    public function submissionUsers(Request $request)
    {
        return $this->updateSubmissionUser($request);
    }

    /**
     * Resolve the form_id for authorization.
     *
     * entry_id is read from the URL route parameter first so that a JSON body
     * {"entry_id": X} cannot shadow the URL placeholder and cause the policy to
     * authorize against a different record than the controller acts on (IDOR).
     */
    private function resolveFormId(Request $request)
    {
        $route   = $request->route();
        $entryId = $route ? Arr::get($route->getParameter(), 'entry_id') : null;

        if (!$entryId) {
            $entryId = $request->get('entry_id');
        }

        if ($entryId) {
            $submission = Submission::select('form_id')->find(intval($entryId));
            if ($submission) {
                return $submission->form_id;
            }
        }

        $formId = $route ? Arr::get($route->getParameter(), 'form_id') : null;
        if (!$formId) {
            $formId = $request->get('form_id');
        }

        return $formId ? intval($formId) : null;
    }
}
