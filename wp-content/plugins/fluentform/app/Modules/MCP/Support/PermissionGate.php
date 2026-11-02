<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Manager\FormManagerService;

/**
 * Maps MCP abilities onto FluentForm's existing capability model. The MCP user
 * IS a WordPress user with a FluentForm role, so we never invent a parallel
 * permission system — we reuse Acl, the same check the admin REST routes use.
 *
 * Two layers:
 *   - transport(): can this user reach the FluentForm MCP endpoint at all?
 *   - can()/canAny(): per-ability permission_callback gating, form-scoped.
 *
 * Annotations are UX hints only; THIS is the enforcement boundary.
 *
 * WHAT THE PER-ABILITY LAYER ACTUALLY ENFORCES — read before relying on it.
 * Acl::hasPermission() grants any FluentForm permission except
 * fluentform_full_access to a user whose WP role appears in the
 * _fluentform_form_permission option (see Acl::findUserCapability(); that option
 * stores role keys, and WP_User::has_cap('editor') is true for an Editor). So an
 * Editor granted FluentForm access passes the 'capability' gate on EVERY tool —
 * including delete-submission and bulk delete_permanently — regardless of which
 * capability that tool declares. Only per-user managers, who receive individual
 * caps via Acl::attachPermissions(), are gated the way the declarations read.
 *
 * That is long-standing Acl behaviour shared with the admin REST routes, not
 * something this module introduces, and it is deliberately NOT worked around
 * here: diverging from Acl would mean MCP silently refusing operations the same
 * user can perform in the admin UI. But it means the real boundary is
 * "authenticated WordPress user with FluentForm access, restricted to their form
 * scope" — NOT per-tool capability separation. Do not describe it as the latter.
 * Form scope (FormAccess) is the layer that genuinely constrains reach.
 */
class PermissionGate
{
    const OPTION = '_fluentform_mcp_settings';

    /**
     * Per-ability check. $formId scopes the check to a single form so a
     * "specific forms" manager can't reach data outside their assignment
     * (Acl::hasPermission defers form scoping to FormManagerService).
     */
    public static function can($permission, $formId = false)
    {
        return Acl::hasPermission($permission, $formId);
    }

    /** True if the user holds ANY of the given capabilities (no form scope). */
    public static function canAny(array $permissions, $formId = false)
    {
        foreach ($permissions as $permission) {
            if (Acl::hasPermission($permission, $formId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Transport gate for the `fluentform` server. Reaching the endpoint at all
     * requires (a) the feature is enabled and (b) the user holds at least one
     * FluentForm capability. A per-ability permission_callback still runs on top,
     * but see the class docblock: for role-granted users that callback does not
     * separate read from write, so treat this as an access gate, not a
     * privilege-separation one.
     */
    public static function transport($request = null)
    {
        if (!self::isEnabled()) {
            return new \WP_Error(
                'fluentform_mcp_disabled',
                __('The FluentForm MCP server is disabled. Enable it in FluentForm → Settings → MCP.', 'fluentform')
            );
        }

        if (!is_user_logged_in()) {
            return new \WP_Error(
                'fluentform_mcp_unauthorized',
                __('Authentication required to access the FluentForm MCP server.', 'fluentform')
            );
        }

        if (!Acl::hasAnyFormPermission()) {
            return new \WP_Error(
                'fluentform_mcp_forbidden',
                __('Your account does not have FluentForm access.', 'fluentform')
            );
        }

        return true;
    }

    /** Any one of these means "has at least a FluentForm role." */
    public static function readRoleCaps()
    {
        return [
            'fluentform_dashboard_access',
            'fluentform_forms_manager',
            'fluentform_entries_viewer',
            'fluentform_view_payments',
            'fluentform_settings_manager',
        ];
    }

    /**
     * Effective form scope for the current user: false = unrestricted,
     * [] = restricted to no forms, [ids] = restricted to those forms. Tools use
     * this to filter list results and to reject single-record access outside the
     * scope (IDOR-safe — never trust a form_id param alone).
     *
     * @return array<int>|false
     */
    public static function formScope()
    {
        return FormManagerService::getUserAllowedFormsScope();
    }

    /** True when the current user may access the given form. */
    public static function canAccessForm($formId)
    {
        return FormManagerService::hasFormPermission($formId);
    }

    /**
     * The master on/off switch. Ships OFF; enabled from Settings → MCP. Stored in
     * a dedicated autoloaded option so the boot guard costs no extra query.
     */
    public static function isEnabled()
    {
        $settings = get_option(self::OPTION, []);

        return is_array($settings) && isset($settings['enabled']) && 'yes' === $settings['enabled'];
    }

    /**
     * Persist the master switch. Enabling MCP opens the whole tool surface, so we
     * fail closed unless the caller can manage_options (defense in depth — the
     * REST route is gated too, but the toolkit toggle path delegates auth out).
     */
    public static function setEnabled($enabled)
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $settings = get_option(self::OPTION, []);
        if (!is_array($settings)) {
            $settings = [];
        }

        $settings['enabled'] = $enabled ? 'yes' : 'no';

        update_option(self::OPTION, $settings, 'yes');

        return (bool) $enabled;
    }

    /**
     * Drop settings keys from removed features so stale state can't linger. Writes
     * only when a legacy key is actually present, so it self-heals once and is a
     * no-op thereafter. Currently prunes 'new_tools_enabled' (the retired
     * advanced-tools opt-in). Runs unauthenticated: it removes dead data, never
     * changes the live 'enabled' switch.
     */
    public static function pruneLegacyKeys()
    {
        $settings = get_option(self::OPTION, []);
        if (!is_array($settings) || !array_key_exists('new_tools_enabled', $settings)) {
            return;
        }

        unset($settings['new_tools_enabled']);
        update_option(self::OPTION, $settings, 'yes');
    }
}
