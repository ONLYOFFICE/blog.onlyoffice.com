<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Submission\SubmissionService;
use FluentForm\Framework\Support\Arr;

class SubmissionController extends Controller
{
    public function index(SubmissionService $submissionService)
    {
        try {
            $attributes = $this->sanitizeSubmissionAttributes($this->request->all());

            return $this->sendSuccess(
                $submissionService->get($attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function find(SubmissionService $submissionService, $submissionId)
    {
        try {
            return $this->sendSuccess(
                $submissionService->find($submissionId)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function resources(SubmissionService $submissionService)
    {
        try {
            $attributes = $this->request->all();

            $sanitizeMap = [
                'form_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);

            // SECURITY (FINDING-02): this route has no {entry_id} placeholder, so SubmissionPolicy
            // authorizes the form owning the *request* entry_id, while resources() then reads a
            // separate form_id — letting a form-scoped user read another form's counts/labels/
            // fields and (via next/previous) submission rows. Re-verify the caller may view
            // entries of the form actually being queried.
            $formId = (int) Arr::get($attributes, 'form_id');
            if (!$formId || !Acl::hasPermission('fluentform_entries_viewer', $formId)) {
                return $this->sendError([
                    'message' => __('You do not have permission to view this form\'s entries.', 'fluentform'),
                ], 403);
            }

            return $this->sendSuccess(
                $submissionService->resources($attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateStatus(SubmissionService $submissionService, $submissionId)
    {
        try {
            $attributes = $this->request->all();
            $attributes['entry_id'] = intval($submissionId);
            $status = $submissionService->updateStatus($attributes);

            /* translators: %s is the submission status */
            $message = sprintf(__('The submission has been marked as %s', 'fluentform'), $status);

            return $this->sendSuccess([
                'message' => $message,
                'status'  => $status,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggleIsFavorite(SubmissionService $submissionService, $submissionId)
    {
        try {
            [$message, $isFavourite] = $submissionService->toggleIsFavorite(
                intval($submissionId)
            );

            return $this->sendSuccess([
                'message'      => $message,
                'is_favourite' => $isFavourite,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function handleBulkActions(SubmissionService $submissionService)
    {
        try {
            $message = $submissionService->handleBulkActions($this->request->all());

            return $this->sendSuccess(['message' => $message]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function remove(SubmissionService $submissionService, $submissionId)
    {
        try {
            $submission = Submission::findOrFail($submissionId);
            $submissionService->deleteEntries([$submissionId], $submission->form_id);
            do_action('fluentform/submission_deleted', $submissionId);

            return $this->sendSuccess([
                'message' => __('Selected submission successfully deleted Permanently', 'fluentform'),
            ]);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get user list for submission page
     *
     * @return \WP_REST_Response
     */
    public function submissionUsers()
    {
        // SECURITY (FINDING-21): don't let a lower-tier user enumerate the whole WP roster here.
        // Require WP's list_users OR the FF entries-manager permission this feature is built for —
        // a delegated non-admin manager holds fluentform_manage_entries (and the assign-user UI is
        // shown only to them) but NOT core list_users, so gating on list_users alone broke them.
        if (!current_user_can('list_users') && !current_user_can('fluentform_manage_entries')) {
            return $this->sendError(['message' => __('You do not have permission to list users.', 'fluentform')], 403);
        }
        $search = sanitize_text_field($this->request->get('search'));
        $users = get_users([
            'search' => "*{$search}*",
            'number' => 50,
        ]);

        $formattedUsers = [];
        foreach ($users as $user) {
            $formattedUsers[] = [
                'ID'    => $user->ID,
                'label' => $user->display_name . ' - ' . $user->user_email,
            ];
        }

        return $this->sendSuccess([
            'users' => $formattedUsers,
        ]);
    }

    /**
     * Update User of a submission
     *
     * @param SubmissionService $submissionService
     * @param int $submissionId
     * @return \WP_REST_Response
     */
    public function updateSubmissionUser(SubmissionService $submissionService, $submissionId)
    {
        try {
            $userId = intval($this->request->get('user_id'));
            $submissionId = intval($submissionId);
            $response = $submissionService->updateSubmissionUser($userId, $submissionId);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get All Submissions
     *
     * @param Submission $submission
     * @return \WP_REST_Response
     */
    public function all(Submission $submission)
    {
        try {
            $attributes = $this->sanitizeSubmissionAttributes($this->request->all());

            return $this->sendSuccess(
                $submission->allSubmissions($attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Get printable content
     *
     * @param SubmissionService $submissionService
     * @return \WP_REST_Response
     */
    public function print(SubmissionService $submissionService)
    {
        try {
            $attributes = $this->request->all();

            $sanitizeMap = [
                'submission_ids' => function ($value) {
                    if (is_array($value)) {
                        return array_map('intval', $value);
                    }
                    return [];
                },
                'form_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);

            // Preserve backward compatibility with any legacy callers that still
            // send entry_ids, while normalizing to the current submission_ids key.
            if (empty($attributes['submission_ids']) && isset($attributes['entry_ids'])) {
                $entryIds = $attributes['entry_ids'];
                $attributes['submission_ids'] = is_array($entryIds)
                    ? array_map('intval', $entryIds)
                    : [];
            }

            return $this->sendSuccess(
                $submissionService->getPrintContent($attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function sanitizeSubmissionAttributes($attributes)
    {
        $sanitizeMap = [
            'search'       => 'sanitize_text_field',
            'status'       => 'sanitize_text_field',
            'entry_type'   => 'sanitize_text_field',
            'form_id'      => 'intval',
            'per_page'     => 'intval',
            'page'         => 'intval',
            'is_favourite' => 'rest_sanitize_boolean',
        ];

        $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);

        if (isset($attributes['entry_type']) && !isset($attributes['status'])) {
            $attributes['status'] = $attributes['entry_type'];
        }

        if (isset($attributes['date_range']) && is_array($attributes['date_range'])) {
            $attributes['date_range'] = array_map('sanitize_text_field', $attributes['date_range']);
        }

        if (isset($attributes['payment_statuses']) && is_array($attributes['payment_statuses'])) {
            $attributes['payment_statuses'] = array_map('sanitize_text_field', $attributes['payment_statuses']);
        }

        return $attributes;
    }
}
