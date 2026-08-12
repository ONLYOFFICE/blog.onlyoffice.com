<?php

namespace FluentForm\App\Modules\MCP;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\MCP\Support\PaymentDataProvider;
use FluentForm\App\Modules\MCP\Support\PermissionGate;
use FluentForm\App\Modules\MCP\Tools\ContextTools;

/**
 * Bootstrap for FluentForm's Model Context Protocol (MCP) integration.
 *
 * Wires the WordPress Abilities API (core 6.9+) + the WP MCP Adapter, which is
 * provided by FluentHub / Fluent Toolkit (bundled) or the standalone
 * mcp-adapter plugin — whichever is present. FluentForm bundles nothing; it
 * consumes whatever's loaded and surfaces an admin notice instead of failing
 * silently when nothing is.
 *
 * The whole surface is gated behind the enable option (default off): a site
 * owner turns it on in FluentForm → Settings → MCP and connects with an
 * application password. Even when on, the endpoint stays behind WP auth + a
 * FluentForm role (transport gate) + per-ability permission checks.
 *
 * Booted from boot/app.php.
 */
class MCPInit
{
    const SERVER_ID = 'fluentform';

    /**
     * Bootstrap entry point, called on every request from boot/app.php.
     *
     * Toolkit discovery runs unconditionally so FluentHub can list FluentForm
     * even while disabled. When MCP is off the front-end cost is two add_filter
     * calls plus one read of an autoloaded option — not literally zero, but
     * nothing that touches the database beyond that. The legacy-key prune adds a
     * second option read, and is confined to admin requests for that reason.
     */
    public static function boot()
    {
        self::registerWithToolkit();

        if (is_admin()) {
            PermissionGate::pruneLegacyKeys();
        }

        if (PermissionGate::isEnabled()) {
            (new self())->init();
        }
    }

    public function init()
    {
        add_action('wp_abilities_api_categories_init', [$this, 'registerCategory']);
        add_action('wp_abilities_api_init', [$this, 'registerAbilities']);

        add_action('mcp_adapter_init', [$this, 'registerCustomServer']);

        PaymentDataProvider::register();

        $invalidate = [ContextTools::class, 'invalidateCache'];
        foreach ([
            'fluentform/inserted_new_form',
            'fluentform/form_duplicated',
            // after_ only: bumping the cache version twice per deletion costs an
            // extra option write and buys nothing, since the bump is not
            // order-sensitive.
            'fluentform/after_form_deleted',
            // Permissions changed — drop the cached context so it can't briefly
            // advertise stale capabilities/visible-forms after a change. The
            // role-level hook covers the Role Manager; the per-user hook covers
            // an individual manager's assignment, which fires no role event.
            'fluentform/after_permission_set_assignment',
            'fluentform/after_user_permissions_attached',
        ] as $hook) {
            add_action($hook, $invalidate);
        }

        add_action('admin_notices', [$this, 'maybeShowAdapterNotice']);
    }

    public function registerCategory()
    {
        wp_register_ability_category('fluentform', [
            'label'       => __('FluentForm', 'fluentform'),
            'description' => __('Form abilities for FluentForm — forms, entries, analytics, and integrations.', 'fluentform'),
        ]);
    }

    public function registerAbilities()
    {
        AbilitiesRegistrar::register();

        /**
         * Fires after FluentForm registers its core MCP abilities. Pro and
         * extensions hook this to register their own abilities (payments,
         * advanced reports) under the same `fluentform/` namespace.
         *
         * @since 6.2.5
         */
        do_action('fluentform/mcp_loaded');
    }

    /**
     * Register the dedicated FluentForm MCP server. Endpoint defaults to
     * /wp-json/fluentform/mcp.
     *
     * @param object $adapter The \WP\MCP\Core\McpAdapter instance.
     */
    public function registerCustomServer($adapter)
    {
        if (!$adapter || !is_object($adapter) || !method_exists($adapter, 'create_server')) {
            return;
        }

        $abilityNames = array_keys(AbilitiesRegistrar::getDefinitions());

        /**
         * Filter the ability names exposed by the FluentForm MCP server. Pro and
         * extensions push their ability names here.
         *
         * @since 6.2.5
         *
         * @param array $abilityNames Fully-qualified ability names.
         */
        $abilityNames = apply_filters('fluentform/mcp_ability_names', $abilityNames);
        $abilityNames = array_values(array_unique(array_filter((array) $abilityNames)));

        $namespace = apply_filters('fluentform/mcp_server_namespace', 'fluentform');
        $route     = apply_filters('fluentform/mcp_server_route', 'mcp');

        $adapter->create_server(
            self::SERVER_ID,
            $namespace,
            $route,
            __('FluentForm MCP Server', 'fluentform'),
            __('AI agent tools for FluentForm forms, entries, analytics, and integrations.', 'fluentform'),
            defined('FLUENTFORM_VERSION') ? FLUENTFORM_VERSION : '1.0.0',
            ['\WP\MCP\Transport\HttpTransport'],
            '\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler',
            '\WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler',
            $abilityNames,
            [],
            [],
            [PermissionGate::class, 'transport']
        );
    }

    /**
     * Announce FluentForm to FluentHub's MCP page. FluentHub discovers products
     * through these filters; without them the server is fully functional yet
     * never appears in the Toolkit's list. Runs unconditionally (even when MCP is
     * off) so the operator can flip it on from the Toolkit. Both filters are
     * cheap no-ops unless the Toolkit applies them.
     */
    public static function registerWithToolkit()
    {
        add_filter('fluent_kit/mcp_products', function ($products) {
            if (!is_array($products)) {
                $products = [];
            }

            $products[] = [
                'slug'         => self::SERVER_ID,
                'name'         => __('FluentForm', 'fluentform'),
                'mcp_enabled'  => PermissionGate::isEnabled(),
                'tools_count'  => self::toolsCount(),
                'endpoint_url' => self::getEndpointUrl(),
                'status'       => self::toolkitStatus(),
            ];

            return $products;
        });

        add_filter('fluent_kit/mcp_toggle_handlers', function ($handlers) {
            if (!is_array($handlers)) {
                $handlers = [];
            }

            $handlers[self::SERVER_ID] = [
                'get_enabled' => [PermissionGate::class, 'isEnabled'],
                'set_enabled' => function ($enabled) {
                    return PermissionGate::setEnabled($enabled);
                },
            ];

            return $handlers;
        });
    }

    public static function toolsCount()
    {
        $names = array_keys(AbilitiesRegistrar::getDefinitions());
        $names = apply_filters('fluentform/mcp_ability_names', $names);

        return is_array($names) ? count(array_unique($names)) : 0;
    }

    public static function toolkitStatus()
    {
        if (!self::adapterAvailable()) {
            return 'adapter_required';
        }

        return PermissionGate::isEnabled() ? 'ready' : 'disabled';
    }

    public static function getEndpointUrl()
    {
        $namespace = apply_filters('fluentform/mcp_server_namespace', 'fluentform');
        $route     = apply_filters('fluentform/mcp_server_route', 'mcp');

        return get_rest_url(null, trailingslashit($namespace) . $route);
    }

    /** True when an MCP adapter + the Abilities API are both available. */
    public static function adapterAvailable()
    {
        return defined('WP_MCP_VERSION')
            && class_exists('\WP\MCP\Core\McpAdapter')
            && function_exists('wp_register_ability');
    }

    /**
     * Warn that MCP is on but no adapter is installed, so the endpoint is silently
     * dead. Scoped to FluentForm admin screens — the settings card carries the
     * richer prompt; this is only a cross-page reminder inside the plugin, never a
     * global dashboard nag.
     */
    public function maybeShowAdapterNotice()
    {
        if (self::adapterAvailable() || !current_user_can('manage_options')) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (0 !== strpos($page, 'fluent_forms')) {
            return;
        }

        // The MCP settings card renders its own richer adapter alert (with an
        // install/activate action), so skip this notice on the settings screen to
        // avoid double-reporting the same state.
        if ('fluent_forms_settings' === $page) {
            return;
        }

        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('FluentForm MCP is enabled but no MCP adapter was found. Install FluentHub (recommended) or the MCP Adapter plugin, on WordPress 6.9+.', 'fluentform');
        echo '</p></div>';
    }
}
