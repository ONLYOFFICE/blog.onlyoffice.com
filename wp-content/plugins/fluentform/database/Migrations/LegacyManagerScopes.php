<?php

namespace FluentForm\Database\Migrations;

defined('ABSPATH') or die;

/**
 * Temporary compatibility migration for legacy manager scope records.
 *
 * Older installs may have "specific forms = yes" with an empty allowed-forms
 * list, which historically behaved like unrestricted access. The newer scoped
 * ACL logic treats that state as "no forms allowed", so we normalize those
 * legacy records once during upgrade.
 *
 * This can be removed after a few stable release cycles, once older installs
 * have had enough time to pass through the one-time normalization.
 */
class LegacyManagerScopes
{
    const BATCH_SIZE = 500;
    const COMPLETED_OPTION = 'fluentform_empty_manager_scopes_normalized';
    const CURSOR_OPTION = 'fluentform_empty_manager_scopes_normalization_cursor';

    public static function migrate()
    {
        if (get_option(self::COMPLETED_OPTION)) {
            return;
        }

        global $wpdb;

        $allowedFormsMetaKey = '_fluent_forms_allowed_forms';
        $specificFormsMetaKey = '_fluent_forms_has_specific_forms_permission';
        $lastProcessedUserId = max(0, intval(get_option(self::CURSOR_OPTION, 0)));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time upgrade cleanup for legacy manager scope data.
        $scopedUserIds = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id
            FROM {$wpdb->usermeta}
            WHERE meta_key = %s
                AND meta_value = %s
                AND user_id > %d
            ORDER BY user_id ASC
            LIMIT %d",
            $specificFormsMetaKey,
            'yes',
            $lastProcessedUserId,
            self::BATCH_SIZE
        ));

        if (false === $scopedUserIds) {
            return;
        }

        $scopedUserIds = array_values(array_filter(array_map('intval', (array) $scopedUserIds)));

        if (!$scopedUserIds) {
            self::markComplete();
            return;
        }

        if (!self::normalizeLegacyBatch($scopedUserIds, $specificFormsMetaKey, $allowedFormsMetaKey)) {
            return;
        }

        $lastProcessedUserId = (int) end($scopedUserIds);

        if (count($scopedUserIds) < self::BATCH_SIZE) {
            self::markComplete();
            return;
        }

        update_option(self::CURSOR_OPTION, $lastProcessedUserId, 'no');
    }

    protected static function normalizeLegacyBatch($userIds, $specificFormsMetaKey, $allowedFormsMetaKey)
    {
        global $wpdb;

        $placeholders = implode(', ', array_fill(0, count($userIds), '%d'));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Batched read for one-time upgrade cleanup.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, meta_value
            FROM {$wpdb->usermeta}
            WHERE meta_key = %s
                AND user_id IN ({$placeholders})",
            array_merge([$allowedFormsMetaKey], $userIds)
        ));

        if (false === $rows) {
            return false;
        }

        $allowedFormsByUser = [];

        foreach ((array) $rows as $row) {
            $allowedFormsByUser[intval($row->user_id)][] = $row->meta_value;
        }

        $legacyUserIds = [];

        foreach ($userIds as $userId) {
            $metaValues = isset($allowedFormsByUser[$userId]) ? $allowedFormsByUser[$userId] : [];
            $hasAssignedForms = false;

            foreach ($metaValues as $metaValue) {
                $allowedForms = maybe_unserialize($metaValue);
                $allowedForms = is_array($allowedForms)
                    ? array_values(array_filter(array_map('intval', $allowedForms)))
                    : [];

                if ($allowedForms) {
                    $hasAssignedForms = true;
                    break;
                }
            }

            if (!$hasAssignedForms) {
                $legacyUserIds[] = $userId;
            }
        }

        if (!$legacyUserIds) {
            return true;
        }

        $legacyPlaceholders = implode(', ', array_fill(0, count($legacyUserIds), '%d'));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Batched meta update for one-time upgrade cleanup.
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->usermeta}
            SET meta_value = %s
            WHERE meta_key = %s
                AND user_id IN ({$legacyPlaceholders})",
            array_merge(['no', $specificFormsMetaKey], $legacyUserIds)
        ));

        if (false === $updated) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Batched meta delete for one-time upgrade cleanup.
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta}
            WHERE meta_key = %s
                AND user_id IN ({$legacyPlaceholders})",
            array_merge([$allowedFormsMetaKey], $legacyUserIds)
        ));

        return false !== $deleted;
    }

    protected static function markComplete()
    {
        delete_option(self::CURSOR_OPTION);
        update_option(self::COMPLETED_OPTION, FLUENTFORM_VERSION, 'no');
    }
}
