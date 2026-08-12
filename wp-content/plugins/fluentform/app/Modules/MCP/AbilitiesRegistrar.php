<?php

namespace FluentForm\App\Modules\MCP;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\MCP\Support\ErrorCodes;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Modules\MCP\Support\PermissionGate;
use FluentForm\App\Modules\MCP\Tools\ContextTools;
use FluentForm\App\Modules\MCP\Tools\FormTools;
use FluentForm\App\Modules\MCP\Tools\SubmissionTools;
use FluentForm\App\Modules\MCP\Tools\ReportTools;
use FluentForm\App\Modules\MCP\Tools\IntegrationTools;
use FluentForm\App\Modules\MCP\Tools\StylingTools;
use FluentForm\App\Modules\MCP\Tools\FieldTools;
use FluentForm\App\Modules\MCP\Tools\NotificationTools;

/**
 * Single source of truth for every FluentForm MCP ability.
 *
 * Each tool class owns its own definitions() slice (schema next to code); this
 * class merges them, wraps every execute_callback so unhandled exceptions become
 * structured WP_Errors the agent can read (instead of the adapter's generic
 * "Tool execution failed"), and registers each as a WP ability.
 *
 * Pro tools are NOT listed here — they push abilities via the
 * fluentform/mcp_loaded action + fluentform/mcp_ability_names filter.
 */
class AbilitiesRegistrar
{
    private static function toolClasses()
    {
        return [
            ContextTools::class,
            FormTools::class,
            SubmissionTools::class,
            ReportTools::class,
            IntegrationTools::class,
            StylingTools::class,
            FieldTools::class,
            NotificationTools::class,
        ];
    }

    public static function getDefinitions()
    {
        $defs = [];

        foreach (self::toolClasses() as $class) {
            if (class_exists($class) && method_exists($class, 'definitions')) {
                $defs = array_merge($defs, (array) $class::definitions());
            }
        }

        /**
         * Filter the full MCP tool-definition map (name => definition). The one
         * unified seam for FluentForm Pro to inject a new tool or override an
         * existing definition; must return the map array.
         *
         * @since 6.2.5
         *
         * @param array $defs Map of ability name to definition.
         */
        $filtered = apply_filters('fluentform/mcp_tool_definitions', $defs);

        return is_array($filtered) ? $filtered : $defs;
    }

    /**
     * The agent-facing catalogue: each ability's display metadata, projected
     * from its own definition (group declared inline; read/write derived from
     * the readonly annotation). The single source the settings card reads, so
     * the UI can never drift from what the server actually exposes.
     */
    public static function catalogue()
    {
        $out = [];
        $present = [];
        foreach (self::getDefinitions() as $name => $def) {
            $present[$name] = true;
            $out[] = [
                'name'        => $name,
                'label'       => isset($def['label']) ? $def['label'] : $name,
                'description' => isset($def['description']) ? $def['description'] : '',
                'group'       => isset($def['group']) ? $def['group'] : __('General', 'fluentform'),
                'write'       => empty($def['annotations']['readonly']),
                'pro'         => !empty($def['pro']),
                'available'   => true,
            ];
        }

        // When Pro is inactive its advanced-report abilities are not registered;
        // surface them as greyed "Pro" teasers so admins see what upgrading adds.
        // Settings-card only (this catalogue), never the agent-facing server list.
        foreach (self::advancedToolTeasers() as $teaser) {
            if (isset($present[$teaser['name']])) {
                continue;
            }
            $out[] = [
                'name'        => $teaser['name'],
                'label'       => $teaser['label'],
                'description' => isset($teaser['description']) ? $teaser['description'] : '',
                'group'       => $teaser['group'],
                'write'       => false,
                'pro'         => true,
                'available'   => false,
            ];
        }

        return $out;
    }

    /**
     * Display-only teasers for the Pro Advanced Reporting tools, listed (greyed,
     * "Pro") in the settings card when Pro is inactive. NEVER registered as
     * abilities — they must not reach getDefinitions()/wp_register_ability or the
     * agent server. Names MUST match fluentformpro McpReportTools::definitions()
     * (kept in lockstep by a test), so the live tool takes over the same row once
     * Pro activates.
     */
    public static function advancedToolTeasers()
    {
        return [
            ['name' => 'fluentform/get-revenue-analysis', 'label' => __('Get Revenue Analysis', 'fluentform'), 'group' => __('Reports', 'fluentform')],
            ['name' => 'fluentform/get-completion-rate', 'label' => __('Get Completion Rate', 'fluentform'), 'group' => __('Reports', 'fluentform')],
            ['name' => 'fluentform/get-subscription-report', 'label' => __('Get Subscription Report', 'fluentform'), 'group' => __('Reports', 'fluentform')],
        ];
    }

    public static function register()
    {
        foreach (self::getDefinitions() as $name => $definition) {
            $permissionCallback = self::permissionCallback($definition);

            // Filter-injected definitions are untrusted shape-wise: without an
            // execute callback and a permission source the ability is
            // uncallable or ungated — skip, don't fatal.
            if (empty($definition['execute_callback']) || !$permissionCallback) {
                continue;
            }

            $args = [
                'label'               => isset($definition['label']) ? $definition['label'] : $name,
                'description'         => isset($definition['description']) ? $definition['description'] : '',
                'category'            => 'fluentform',
                'execute_callback'    => self::wrapExecuteCallback($name, $definition['execute_callback']),
                'permission_callback' => $permissionCallback,
                'meta'                => [
                    // OFF by default. show_in_rest opts an ability into WP core's
                    // own surface — POST /wp-json/wp-abilities/v1/abilities/{name}/run
                    // — which is a second entry path that never runs
                    // PermissionGate::transport(). It is not a privilege
                    // escalation (each ability's permission_callback still runs,
                    // and it is narrower than the transport check), but it means
                    // enabling MCP would also expose all of these tools to any
                    // authenticated browser session, which is not what the
                    // settings card advertises.
                    //
                    // The MCP adapter itself reads only the 'mcp' meta below, so
                    // turning this off costs the MCP endpoint nothing.
                    'show_in_rest' => (bool) apply_filters('fluentform/mcp_show_in_rest', false),
                    'mcp'          => ['public' => true],
                ],
            ];

            if (!empty($definition['input_schema'])) {
                $args['input_schema'] = $definition['input_schema'];
            }

            if (!empty($definition['output_schema'])) {
                $args['output_schema'] = $definition['output_schema'];
            }

            if (!empty($definition['annotations'])) {
                $args['meta']['annotations'] = $definition['annotations'];
            }

            wp_register_ability($name, $args);
        }
    }

    /**
     * The gate for one ability: an explicit permission_callback wins (the seam
     * for filter-injected tools with custom logic); otherwise a 'capability'
     * key — one cap or an any-of list — is wrapped in the standard
     * PermissionGate check. Null means the definition declared neither.
     *
     * @return callable|null
     */
    public static function permissionCallback($definition)
    {
        if (!empty($definition['permission_callback'])) {
            return $definition['permission_callback'];
        }

        if (empty($definition['capability'])) {
            return null;
        }

        $capabilities = (array) $definition['capability'];

        return function () use ($capabilities) {
            return PermissionGate::canAny($capabilities);
        };
    }

    /**
     * Convert any unhandled \Throwable from a tool into a structured WP_Error
     * carrying the real message (and, under WP_DEBUG, the file + a short trace).
     * Without this the agent only sees the adapter's generic failure surface and
     * retries blindly against tools that may have partially succeeded.
     */
    private static function wrapExecuteCallback($toolName, $callback)
    {
        return function ($params) use ($toolName, $callback) {
            try {
                return call_user_func($callback, $params);
            } catch (\Throwable $e) {
                /**
                 * Fires when an MCP tool throws. Lets sites log/alert before the
                 * structured error reaches the agent.
                 *
                 * @since 6.2.5
                 *
                 * @param array $context { exception: \Throwable, tool: string, params: mixed }
                 */
                do_action('fluentform/mcp_tool_exception', [
                    'exception' => $e,
                    'tool'      => $toolName,
                    'params'    => $params,
                ]);

                $details = ['tool' => $toolName, 'exception' => get_class($e), 'retryable' => true];

                // File/line/trace help an operator but this payload reaches the
                // remote agent — raw paths would leak the server layout. Off by
                // default, opt-in, and reduced to a basename when on.
                $exposeDetails = apply_filters('fluentform/mcp_expose_error_details', false);
                if ($exposeDetails) {
                    $details['file']  = basename($e->getFile()) . ':' . $e->getLine();
                    $details['trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 5);
                }

                return MCPHelper::error(ErrorCodes::TOOL_FAILED, $e->getMessage(), $details);
            }
        };
    }
}
