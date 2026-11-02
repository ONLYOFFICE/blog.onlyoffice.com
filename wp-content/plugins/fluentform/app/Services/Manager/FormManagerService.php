<?php

namespace FluentForm\App\Services\Manager;



class FormManagerService
{
    private static function normalizeFormIds($formIds)
    {
        $formIds = is_array($formIds) ? $formIds : [$formIds];
        $resolvedFormIds = [];

        foreach ($formIds as $formId) {
            if (is_string($formId)) {
                $formId = trim($formId);
            }

            if (!is_int($formId) && (!is_string($formId) || !ctype_digit($formId))) {
                return [];
            }

            $formId = (int) $formId;

            if ($formId <= 0) {
                return [];
            }

            $resolvedFormIds[] = $formId;
        }

        return array_values(array_unique($resolvedFormIds));
    }

    public static function maybeAddUserAllowedFormIds($formId)
    {
        if (false !== ($allowFormIds = self::getUserAllowedFormsScope())) {
            $allowFormIds[] = $formId;
            self::addUserAllowedForms($allowFormIds);
        }
    }

    public static function addUserAllowedForms($formIds, $userId = false)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }
        if ($userId) {
            $formIds = array_filter(array_map('intval', $formIds));
            update_user_meta($userId, '_fluent_forms_allowed_forms', $formIds);
        }
    }

    public static function deleteUserAllowedForms($userId = false)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        if ($userId) {
            delete_user_meta($userId, '_fluent_forms_allowed_forms');
        }
    }

    public static function updateHasSpecificFormsPermission($userId, $status)
    {
        if (in_array($status, ['no', 'yes'])) {
            update_user_meta($userId, '_fluent_forms_has_specific_forms_permission', $status);
        }
    }

    public static function hasSpecificFormsPermission($userId)
    {
        $hasFormsPermission = get_user_meta($userId, '_fluent_forms_has_specific_forms_permission', true);
        return 'yes' === $hasFormsPermission;
    }

    /**
     * Return the raw stored allowed-form ids for a user.
     *
     * This legacy helper returns `false` for unrestricted users and the stored
     * ids for specific-form managers. Because an empty array is falsey in PHP,
     * callers that need to distinguish "unrestricted" from "restricted to no
     * forms" should use getUserAllowedFormsScope() instead.
     *
     * @param string|int|false $userId Optional. Current user is used when omitted.
     * @return array<int>|false
     */
    public static function getUserAllowedForms($userId = false)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }
        if ($userId && self::hasSpecificFormsPermission($userId)) {
            $formIds = get_user_meta($userId, '_fluent_forms_allowed_forms', true);
            if (is_array($formIds)) {
                return array_filter(array_map('intval', $formIds));
            }
        }
        return false;
    }

    /**
     * Return the effective form scope for the current user's permissions.
     *
     * - `false`: the user is unrestricted
     * - `[]`: the user is restricted to specific forms but none are assigned
     * - `[ids...]`: the user is restricted to the listed forms
     *
     * Use this helper for ACL-sensitive queries so zero assigned forms produce
     * an empty result set instead of falling back to unrestricted access.
     *
     * @param string|int|false $userId Optional. Current user is used when omitted.
     * @return array<int>|false
     */
    public static function getUserAllowedFormsScope($userId = false)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        if (!$userId || !self::hasSpecificFormsPermission($userId)) {
            return false;
        }

        $formIds = self::getUserAllowedForms($userId);

        return is_array($formIds) ? array_values($formIds) : [];
    }

    public static function hasFormPermission($formId)
    {
        // Use the scoped helper here so "specific forms" managers with zero
        // assignments do not accidentally pass this permission check.
        if ($formId && false !== ($allowedForm = self::getUserAllowedFormsScope())) {
            $formIds = self::normalizeFormIds($formId);

            if (!$formIds) {
                return false;
            }

            return !array_diff($formIds, $allowedForm);
        }

        return true;
    }
}
