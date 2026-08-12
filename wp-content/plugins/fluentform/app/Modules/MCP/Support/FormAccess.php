<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;

/**
 * Shared form/entry access + scoping for the MCP tools.
 *
 * Centralizes the three things every form-touching tool was repeating:
 *   - resolve a form_id / entry_id to a model with the right not_found /
 *     forbidden / missing_identifier errors,
 *   - apply the current user's form scope to a query,
 *   - the "real form field?" test that keeps internal response keys
 *     (nonces, referers, embed ids) out of agent-facing output.
 */
class FormAccess
{
    // Non-underscore response keys that are never real form fields.
    const SYSTEM_KEYS = [
        'g-recaptcha-response',
        'h-captcha-response',
        'cf-turnstile-response',
    ];

    /**
     * Validate + access-check + load a form. Accepts the raw tool params
     * (form_id is read from them) or a bare id, so every tool shares one
     * resolve line instead of repeating the extraction.
     *
     * @param array|int|string $params
     *
     * @return Form|\WP_Error
     */
    public static function resolveForm($params)
    {
        $formId = is_array($params) ? (isset($params['form_id']) ? $params['form_id'] : 0) : $params;
        $formId = (int) $formId;
        if (!$formId) {
            return MCPHelper::error(ErrorCodes::MISSING_IDENTIFIER, __('form_id is required.', 'fluentform'), ['fields' => ['form_id']]);
        }
        if (!PermissionGate::canAccessForm($formId)) {
            return MCPHelper::error(ErrorCodes::FORBIDDEN, __('You do not have access to this form.', 'fluentform'));
        }
        $form = Form::query()->find($formId);
        if (!$form) {
            return MCPHelper::error(ErrorCodes::NOT_FOUND, __('No form found for the given form_id.', 'fluentform'));
        }

        return $form;
    }

    /**
     * Validate + load an entry, then check its (DB-resolved) form against the
     * user's form scope — never trusting a caller-supplied form_id (IDOR-safe).
     * Accepts the raw tool params (entry_id is read from them) or a bare id.
     *
     * @param array|int|string $params
     *
     * @return Submission|\WP_Error
     */
    public static function resolveSubmission($params)
    {
        $entryId = is_array($params) ? (isset($params['entry_id']) ? $params['entry_id'] : 0) : $params;
        $entryId = (int) $entryId;
        if (!$entryId) {
            return MCPHelper::error(ErrorCodes::MISSING_IDENTIFIER, __('entry_id is required.', 'fluentform'), ['fields' => ['entry_id']]);
        }
        $submission = Submission::query()->find($entryId);
        if (!$submission) {
            return MCPHelper::error(ErrorCodes::NOT_FOUND, __('No entry found for the given entry_id.', 'fluentform'));
        }
        if (!PermissionGate::canAccessForm($submission->form_id)) {
            return MCPHelper::error(ErrorCodes::FORBIDDEN, __('You do not have access to this entry\'s form.', 'fluentform'));
        }

        return $submission;
    }

    /**
     * Apply the current user's form scope to a query. $column is the form-id
     * column on the queried table ('id' for forms, 'form_id' for submissions,
     * or a table-qualified name when the query joins).
     */
    public static function applyScope($query, $column = 'form_id')
    {
        $scope = PermissionGate::formScope();
        if (false !== $scope) {
            $query->whereIn($column, $scope ? $scope : [0]);
        }

        return $query;
    }

    /** Count non-trashed entries for a form, or null on failure. */
    public static function entryCount($formId)
    {
        try {
            return (int) Submission::query()
                ->where('form_id', $formId)
                ->where('status', '!=', 'trashed')
                ->count();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Non-trashed entry counts for many forms in one grouped query, so list
     * tools never run a COUNT per row. Returns [formId => int] covering every
     * requested id (0 when absent), or nulls on failure.
     *
     * @return array<int, int|null>
     */
    public static function entryCounts(array $formIds)
    {
        $formIds = array_values(array_unique(array_map('intval', $formIds)));
        if (!$formIds) {
            return [];
        }

        try {
            $rows = Submission::query()
                ->whereIn('form_id', $formIds)
                ->where('status', '!=', 'trashed')
                ->selectRaw('form_id, COUNT(*) as total')
                ->groupBy('form_id')
                ->get();

            $out = array_fill_keys($formIds, 0);
            foreach ($rows as $row) {
                $out[(int) $row->form_id] = (int) $row->total;
            }

            return $out;
        } catch (\Throwable $e) {
            return array_fill_keys($formIds, null);
        }
    }

    /**
     * True for response keys that are framework internals, not user-entered
     * fields: anything prefixed with "_" (nonces, _wp_http_referer,
     * __fluent_form_embded_post_id) plus a few named captcha tokens.
     */
    public static function isInternalKey($key)
    {
        if (!is_string($key) || '' === $key) {
            return false;
        }
        if ('_' === $key[0]) {
            return true;
        }

        return in_array($key, self::SYSTEM_KEYS, true);
    }
}
