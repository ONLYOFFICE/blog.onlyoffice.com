<?php

namespace FluentForm\App\Http\Controllers;



class SuggestedPluginsController extends Controller
{
    /**
     * Check the installation and activation status of multiple plugins.
     */
    public function checkPluginStatuses()
    {
        if (!current_user_can('install_plugins')) {
            return $this->sendError([
                'message' => __('You do not have permission to manage plugins.', 'fluentform')
            ], 403);
        }

        $plugins = wpFluentForm('request')->get('plugins', []);
        
        if (empty($plugins) || !is_array($plugins)) {
            return $this->sendError([
                'message' => __('No plugins specified.', 'fluentform')
            ], 400);
        }

        $statuses = [];
        
        foreach ($plugins as $pluginSlug) {
            $statuses[$pluginSlug] = $this->getPluginStatus($pluginSlug);
        }

        return $this->sendSuccess([
            'statuses' => $statuses
        ]);
    }

    /**
     * Install a plugin from WordPress.org.
     */
    public function installPlugin()
    {
        if (!current_user_can('install_plugins')) {
            return $this->sendError([
                'message' => __('You do not have permission to install plugins.', 'fluentform')
            ], 403);
        }

        $pluginSlug = wpFluentForm('request')->get('plugin_slug');
        
        if (empty($pluginSlug)) {
            return $this->sendError([
                'message' => __('Plugin slug is required.', 'fluentform')
            ], 400);
        }

        // Load WordPress plugin installer
        if (!class_exists('Plugin_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_tempnam')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
        }
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $api = plugins_api('plugin_information', [
            'slug'   => sanitize_key($pluginSlug),
            'fields' => [
                'sections' => false,
            ],
        ]);

        if (is_wp_error($api)) {
            return $this->sendError([
                'message' => $api->get_error_message()
            ], 500);
        }

        // Install the plugin
        $upgrader = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message()
            ], 500);
        }

        if ($result === false) {
            return $this->sendError([
                'message' => __('Plugin installation failed.', 'fluentform')
            ], 500);
        }

        return $this->sendSuccess([
            'message' => __('Plugin installed successfully.', 'fluentform')
        ]);
    }

    /**
     * Activate an installed plugin.
     */
    public function activatePlugin()
    {
        if (!current_user_can('activate_plugins')) {
            return $this->sendError([
                'message' => __('You do not have permission to activate plugins.', 'fluentform')
            ], 403);
        }

        $pluginSlug = sanitize_text_field(wpFluentForm('request')->get('plugin_slug'));

        if (empty($pluginSlug)) {
            return $this->sendError([
                'message' => __('Plugin slug is required.', 'fluentform')
            ], 400);
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!array_key_exists($pluginSlug, get_plugins())) {
            return $this->sendError([
                'message' => __('Invalid plugin.', 'fluentform')
            ], 400);
        }

        $result = activate_plugin($pluginSlug);

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message()
            ], 500);
        }

        return $this->sendSuccess([
            'message' => __('Plugin activated successfully.', 'fluentform')
        ]);
    }

    /**
     * Get the status of a plugin (not_installed, inactive, or active).
     *
     * @param string $pluginSlug The plugin slug (e.g., 'fluent-crm/fluent-crm.php')
     * @return string
     */
    private function getPluginStatus($pluginSlug)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $allPlugins = get_plugins();

        // Check if plugin is installed
        if (!isset($allPlugins[$pluginSlug])) {
            return 'not_installed';
        }

        // Check if plugin is active
        if (is_plugin_active($pluginSlug)) {
            return 'active';
        }

        return 'inactive';
    }
}
