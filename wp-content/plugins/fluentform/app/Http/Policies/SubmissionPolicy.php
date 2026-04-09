<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Http\Request\Request;
use FluentForm\Framework\Foundation\Policy;

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

    /**
     * Resolve the form_id for authorization.
     * For entry-scoped routes, always derive from the entry record to prevent
     * attackers from passing an allowed form_id while targeting another form's entry.
     */
    private function resolveFormId(Request $request)
    {
        $entryId = $request->get('entry_id');
        if ($entryId) {
            $submission = Submission::select('form_id')->find(intval($entryId));
            if ($submission) {
                return $submission->form_id;
            }
        }

        $formId = $request->get('form_id');
        return $formId ? intval($formId) : null;
    }
}
