<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2017 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

require_once 'AddThisAmp.php';
require_once 'AddThisFeature.php';
require_once 'AddThisGlobalOptionsTool.php';
require_once 'AddThisGlobalOptionsCustomHtmlTool.php';

if (!class_exists('AddThisGlobalOptionsFeature')) {
    /**
     * Class for adding AddThis global options to WordPress, such as whether to
     * load addthis_widget.js asyncronously, in the header or footer, custom
     * settings to addthis_config and addthis_share, etc.
     *
     * @category   GlobalOptions
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisGlobalOptionsFeature extends AddThisFeature
    {
        protected $oldConfigVariableName = 'addthis_settings';
        protected $settingsVariableName = 'addthis_shared_settings';
        protected $settingsPageId = 'addthis_advanced_settings';
        protected $GlobalOptionsToolObject = null;
        protected $GlobalOptionsCustomHtmlToolObject = null;
        protected $name = 'Advanced Settings';
        public $publicJavaScriptAction = 'addthis_global_options_settings';
        protected $filterNamePrefix = 'addthis_custom_html_';
        protected $enableAboveContent = true;
        protected $enableBelowContent = true;
        public static $anonymousProfileIdPrefix = 'wp';

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array(
            // general
            'addthis_anonymous_profile',
            'addthis_asynchronous_loading',
            'addthis_per_post_enabled',
            'addthis_profile',
            'addthis_rate_us',
            'addthis_rate_us_timestamp',
            'profile_edition',
            'api_key',
            'ajax_support',
            'credential_validation_status',
            'filter_get_the_excerpt',
            'filter_the_excerpt',
            'filter_wp_trim_excerpt',
            'script_location',
            'startUpgradeAt',
            'wpfooter',
            'xmlns_attrs',
            'follow_buttons_feature_enabled',
            'recommended_content_feature_enabled',
            'sharing_buttons_feature_enabled',
            'html',
            'enqueue_client',
            'enqueue_local_settings',
            // debug settings
            'debug_enable',
            'addthis_plugin_controls',
            'addthis_environment',
            'darkseid_environment',
            'settings_ui_base_url',
            // addthis_share
            'addthis_twitter_template',
            'addthis_bitly',
            'addthis_share_json',
            'addthis_share_follow_json',
            'addthis_share_recommended_json',
            'addthis_share_trending_json',
            'addthis_share_welcome_json',
            // addthis_layers
            'addthis_layers_json',
            'addthis_layers_follow_json',
            'addthis_layers_recommended_json',
            'addthis_layers_trending_json',
            'addthis_layers_welcome_json',
            'smart_layers_bad_json',
            // addthis_config
            'data_ga_property',
            'addthis_language',
            'atversion',
            'addthis_append_data',
            'addthis_addressbar',
            'addthis_508',
            'addthis_config_json',
            'addthis_config_follow_json',
            'addthis_config_recommended_json',
            'addthis_config_trending_json',
            'addthis_config_welcome_json',
            // AMP options
            'amp_disable',
            'amp_inline_share_width',
            'amp_inline_share_height',
        );

        protected $defaultConfigs = array(
            // general
            'addthis_asynchronous_loading'          => false,
            'addthis_per_post_enabled'              => true,
            'addthis_profile'                       => '',
            'addthis_rate_us'                       => '',
            'profile_edition'                       => 'anonymous',
            'api_key'                               => '',
            'ajax_support'                          => false,
            'credential_validation_status'          => 0,
            'filter_get_the_excerpt'                => true,
            'filter_the_excerpt'                    => true,
            'filter_wp_trim_excerpt'                => false,
            'wpfooter'                              => true,
            'xmlns_attrs'                           => true,
            'follow_buttons_feature_enabled'        => false,
            'recommended_content_feature_enabled'   => false,
            'sharing_buttons_feature_enabled'       => false,
            'enqueue_client'                        => true,
            'enqueue_local_settings'                => false,
            // debug settings
            'debug_enable'                          => false,
            'addthis_environment'                   => '',
            'addthis_plugin_controls'               => 'WordPress',
            'darkseid_environment'                  => '',
            'settings_ui_base_url'                  => '',
            // addthis_share
            'addthis_twitter_template'              => '',
            'addthis_bitly'                         => false,
            'addthis_share_json'                    => '',
            'addthis_share_follow_json'             => '',
            'addthis_share_recommended_json'        => '',
            'addthis_share_trending_json'           => '',
            'addthis_share_welcome_json'            => '',
            // addthis_layers
            'addthis_layers_json'                   => '',
            'addthis_layers_follow_json'            => '',
            'addthis_layers_recommended_json'       => '',
            'addthis_layers_trending_json'          => '',
            'addthis_layers_welcome_json'           => '',
            // addthis_config
            'data_ga_property'                      => '',
            'addthis_language'                      => '',
            'atversion'                             => 300,
            'addthis_append_data'                   => true,
            'addthis_addressbar'                    => false,
            'addthis_508'                           => '',
            'addthis_config_json'                   => '',
            'addthis_config_follow_json'            => '',
            'addthis_config_recommended_json'       => '',
            'addthis_config_trending_json'          => '',
            'addthis_config_welcome_json'           => '',
            // AMP options
            'amp_disable'                           => false,
            'amp_inline_share_width'                => AddThisAmp::TOOL_DEF_WIDTH,
            'amp_inline_share_height'               => AddThisAmp::TOOL_DEF_HEIGHT,
        );

        // require the files with the tool and widget classes at the top of this
        // file for each tool
        // make a protected variable matching the name for each
        // tool + ToolObject
        protected $tools = array(
            'GlobalOptions',
            'GlobalOptionsCustomHtml',
        );

        /**
         * The constructor.
         *
         * @param AddThisGlobalOptionsFeature $globalOptionsObject the object
         * for the Global Options feature. Optional.
         *
         * @return null
         */
        public function __construct($globalOptionsObject = null)
        {
            $this->globalOptionsObject = $this;
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload with settings for a profile ID. Requires a
         * profile id.
         *
         * @return null
         */
        public function getBoostConfig()
        {
            $profileId = $this->getProfileId();

            if (!empty($profileId) && $this->inRegisteredMode()) {
                $url = $this->getDarkseidBaseUrl() . 'wordpress/site/'.$profileId;
                $response = wp_remote_get($url);

                if (!is_wp_error($response)
                    && isset($response['response']['code'])
                    && $response['response']['code'] == 200
                ) {
                    $config = json_decode($response['body'], true);
                    return $config;
                }
            }

            return false;
        }

        /**
         * Determines whether to use layers API code
         *
         * @return boolean true for layers API, false for lojson/boost
         */
        public function inAnonymousMode()
        {
            if (isset($this->configs['addthis_plugin_controls'])
                && $this->configs['addthis_plugin_controls'] != 'AddThis'
            ) {
                return true;
            }

            return false;
        }

        /**
         * Determines whether to use lojson/boost code
         *
         * @return boolean true for lojson/boost, false for layers API
         */
        public function inRegisteredMode()
        {
            return !$this->inAnonymousMode();
        }

        /**
         * The profile ID that should be used when loading addthis_widget.js
         *
         * @return string a profile ID
         */
        public function getUsableProfileId()
        {
            if ($this->getProfileId()) {
                $profileId = $this->getProfileId();
            } else {
                $profileId = $this->getAnonymousProfileId();
            }

            return $profileId;
        }

        /**
         * Returns the users Profile ID, if they set one. A blank string
         * otherwise.
         *
         * @return string
         */
        public function getProfileId()
        {
            if (isset($this->configs['addthis_profile'])
               && !empty($this->configs['addthis_profile'])
            ) {
                return $this->configs['addthis_profile'];
            }

            return '';
        }

        /**
         * Returns the anonymous profile ID to use for this site. If the user
         * upgrades in the future to not be anonymous, we can use this to grab
         * their historical share data and associate it with their AddThis
         * account
         *
         * @return string
         */
        public function getAnonymousProfileId()
        {
            if (!isset($this->configs['addthis_anonymous_profile'])
               || !$this->configs['addthis_anonymous_profile']
            ) {
                $prefix = $this->getAnonymousProfileIdPrefix();
                $postfix = $this->getAnonymousProfileHash();
                $profileId = $prefix . '-' . $postfix;
                $this->configs['addthis_anonymous_profile'] = $profileId;
                $this->saveConfigs();
            }

            return $this->configs['addthis_anonymous_profile'];
        }

        /**
         * Returns the anonymous profile has for this site based on the homepage
         * URL
         *
         * @return string
         */
        public function getAnonymousProfileHash()
        {
            $url = $this->getHomepageUrl();
            $hash = hash_hmac('md5', $url, 'addthis');
            return $hash;
        }

        /**
         * Returns the prefix to use on anonmyous profile IDs.
         *
         * @return string
         */
        private static function getAnonymousProfileIdPrefix()
        {
            return self::$anonymousProfileIdPrefix;
        }

        /**
         * Returns the URL for this sites homepage
         *
         * @return string
         */
        public function getHomepageUrl()
        {
            $url = get_option('home');
            return $url;
        }

        /**
         * This must be public as it's used in a callback for register_setting,
         * which is essentially a filter
         *
         * This takes form input for a settings variable, manipulates it, and
         * returns the variables that should be saved to the database.
         *
         * Sadly, this must match (at least functionally) the global options
         * sanitizer for the sharing buttons plugin, at least until we bring
         * that plugin into the fold.
         *
         * @param array $input An associative array of values input for this
         * feature's settings
         *
         * @return array A cleaned up associative array of settings specific to
         *               this feature.
         */
        public function sanitizeSettings($input)
        {
            if (!is_array($input) || empty($input)) {
                return $this->configs;
            }

            $output = $this->configs;

            if (empty($input['wpfooter'])) {
                $output['wpfooter'] = false;
                $output['script_location'] = 'header';
            } else {
                $output['wpfooter'] = true;
                $output['script_location'] = 'footer';
            }

            if (isset($input['script_location'])
                && $input['script_location'] == 'footer'
            ) {
                $output['wpfooter'] = true;
                $output['script_location'] = 'footer';
            } elseif (isset($input['script_location'])) {
                $output['wpfooter'] = false;
                $output['script_location'] = 'header';
            }

            if (!empty($input['html']) && is_array($input['html'])) {
                if (!is_array($output['html'])) {
                    $output['html'] = array();
                }

                foreach ($input['html'] as $widgetId => $toolSettings) {
                    $toolObject = $this->getToolObject('GlobalOptionsCustomHtml');

                    // if user can't do unfiltered HTML
                    if (!current_user_can('unfiltered_html')) {
                        if (isset($output['html'][$widgetId]['html'])) {
                            // use old HTML (don't let them change it) if available
                            $toolSettings['html'] = $output['html'][$widgetId]['html'];
                        } else {
                            // or leave blank
                            $toolSettings['html'] = '';
                        }
                    }

                    $sanitizedToolSettings = $toolObject->sanitizeSettings($toolSettings);
                    $output['html'][$sanitizedToolSettings['widgetId']] = $sanitizedToolSettings;
                }
            }

            if (isset($output['html']) && empty($output['html'])) {
                unset($output['html']);
            }

            // Remove empty-key setting that sometimes gets saved
            if (isset($output[''])) {
                unset($output['']);
            }

            $checkboxFields = array(
                'addthis_508',
                'addthis_addressbar',
                'addthis_append_data',
                'addthis_asynchronous_loading',
                'addthis_bitly',
                'addthis_per_post_enabled',
                'enqueue_client',
                'enqueue_local_settings',
                'ajax_support',
                'debug_enable',
                'filter_get_the_excerpt',
                'filter_the_excerpt',
                'filter_wp_trim_excerpt',
                'xmlns_attrs',
                'amp_disable',
            );

            foreach ($checkboxFields as $field) {
                if (isset($input[$field]) && $input[$field]) {
                    $output[$field] = true;
                } else {
                    $output[$field] = false;
                }
            }

            $checkAndSanitize = array(
                'addthis_config_json',
                'addthis_environment',
                'addthis_language',
                'addthis_layers_json',
                'addthis_plugin_controls',
                'addthis_profile',
                'addthis_rate_us',
                'addthis_rate_us_timestamp',
                'addthis_share_json',
                'addthis_twitter_template',
                'api_key',
                'atversion',
                'credential_validation_status',
                'darkseid_environment',
                'data_ga_property',
                'profile_edition',
                'script_location',
                'settings_ui_base_url',
            );

            foreach ($checkAndSanitize as $field) {
                if (isset($input[$field])) {
                    $output[$field] = sanitize_text_field($input[$field]);
                }
            }

            $numberFieds = array(
                'amp_inline_share_width',
                'amp_inline_share_height',
            );

            foreach ($numberFieds as $field) {
                if (isset($input[$field])) {
                    $output[$field] = intval($input[$field]);
                }
            }

            $output = $this->addDefaultConfigs($output);
            return $output;
        }

        /**
         * Creates feature specific settings for the JavaScript variable
         * addthis_share
         *
         * @return array an associative array
         */
        public function getAddThisShare()
        {
            $featureShare = array();

            if (!empty($this->configs['addthis_twitter_template'])) {
                $featureShare['passthrough']['twitter']['via'] = esc_js($this->configs['addthis_twitter_template']);
            }

            if (!empty($this->configs['addthis_bitly'])) {
                $featureShare['url_transforms']['shorten']['twitter'] = 'bitly';
                $featureShare['shorteners']['bitly'] = new stdClass();
            }

            return $featureShare;
        }

        /**
         * Creates feature specific settings for the JavaScript variable
         * addthis_config
         *
         * @return array an associative array
         */
        public function getAddThisConfig()
        {
            $featureConfig = array(
                'data_track_clickback' => false,
            );

            if ($this->inAnonymousMode()) {
                $featureConfig['ignore_server_config'] = true;
            }


            if (!empty($this->configs['data_ga_property'])) {
                $featureConfig['data_ga_property'] = $this->configs['data_ga_property'];
                $featureConfig['data_ga_social'] = true;
            }

            if (isset($this->configs['addthis_language'])
                && strlen($this->configs['addthis_language']) == 2
            ) {
                $featureConfig['ui_language'] = $this->configs['addthis_language'];
            }

            if (isset($this->configs['atversion'])) {
                $featureConfig['ui_atversion'] = $this->configs['atversion'];
            }

            $simpleCheckboxOptions = array(
                array(
                    'wpSetting'      => 'addthis_append_data',
                    'clientSetting' => 'data_track_clickback',
                ),
                array(
                    'wpSetting'      => 'addthis_addressbar',
                    'clientSetting' => 'data_track_addressbar',
                ),
                array(
                    'wpSetting'      => 'addthis_508',
                    'clientSetting' => 'ui_508_compliant',
                ),
            );

            foreach ($simpleCheckboxOptions as $option) {
                if (!empty($this->configs[$option['wpSetting']])) {
                    $featureConfig[$option['clientSetting']] = true;
                }
            }

            return $featureConfig;
        }

        /**
         * Creates feature specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @return array an associative array
         */
        public function getAddThisLayers()
        {
            $featureLayers = array();
            return $featureLayers;
        }

        /**
         * Determines if this feature is enabled by any plugin, not necessarily
         * the plugin that boostrapped this object. Global options is always
         * enabled.
         *
         * @return boolean
         */
        public function isEnabled()
        {
            return true;
        }

        /**
         * Returns the URL to use for addthis_widget.js. Based on the
         * enviornment variable
         *
         * @param string $profileId The profile Id, optional, defaults to the
         *   one in the settings
         *
         * @return string
         */
        public function getAddThisWidgetJavaScriptUrl($profileId = false)
        {
            $urlRoot = 'https://s7.addthis.com/js/';

            if (!empty($this->configs['debug_enable'])
                && !empty($this->configs['addthis_environment'])
            ) {
                $env = $this->configs['addthis_environment'];
                $urlRoot = '//cache-' . $env . '.addthis.com/js/';
            }

            if (!$profileId) {
                $profileId = $this->getUsableProfileId();
            }

            $url = $urlRoot .
                '300/addthis_widget.js#pubid=' .
                urlencode($profileId);

            return $url;
        }

        /**
         * Gets the base URL for the settings UI angular app
         *
         * @return string
         */
        public function getSettingsUiBaseUrl()
        {
            if (!empty($this->configs['debug_enable'])
                && !empty($this->configs['settings_ui_base_url'])
            ) {
                $dirtyUrl = trim($this->configs['settings_ui_base_url']);
                if (substr($dirtyUrl, -1) !== '/') {
                    $dirtyUrl .= '/';
                }
                $settingsUiRoot = $dirtyUrl;
            } else {
                $settingsUiRoot = $this->getPluginUrl() . '/frontend/';
            }

            return $settingsUiRoot;
        }

        /**
         * Gives you the base URL for AddThis API calls (to darkseid)
         *
         * @return string
         */
        public function getDarkseidBaseUrl()
        {
            if (!empty($this->configs['debug_enable'])
                && !empty($this->configs['darkseid_environment'])
            ) {
                $env = $this->configs['darkseid_environment'];
                if ($env == 'local') {
                    $url = 'http://docker.for.mac.host.internal:8019/darkseid/';
                } else {
                    $url = 'http://www-'. $env .'.addthis.com/darkseid/';
                }
            } else {
                $url = 'https://www.addthis.com/darkseid/';
            }

            return $url;
        }

        /**
         * Determines if this is a minimal plugin with no tool editing in
         * WordPress, or any other plugin with at least some tool editing in
         * WordPress.
         *
         * @return boolean
         */
        public function isMinimalPlugin()
        {
            if ($this->configs['follow_buttons_feature_enabled'] === true
                || $this->configs['recommended_content_feature_enabled'] === true
                || $this->configs['sharing_buttons_feature_enabled'] === true
            ) {
                return false;
            }

            return true;
        }

        /**
         * Upgrade from old addthis_settings format to
         * new addthis_shared_settings
         *
         * @return null
         */
        protected function upgradeIterative1()
        {
            $oldSettings = get_option('addthis_settings');
            $newSettings = array();

            if (!is_array($oldSettings)) {
                return null;
            }

            if (is_array($this->configs)) {
                $newSettings = $this->configs;
            }

            if (!isset($newSettings['wpfooter'])
                && isset($oldSettings['wpfooter'])
            ) {
                $newSettings['wpfooter'] = (boolean)$oldSettings['wpfooter'];
                if ($newSettings['wpfooter']) {
                    $newSettings['script_location'] = 'footer';
                } else {
                    $newSettings['script_location'] = 'header';
                }
            }

            $anonProfileHash = $this->getAnonymousProfileHash();
            $anonProfileId = $this->getAnonymousProfileId();

            if (!isset($newSettings['addthis_profile'])) {
                if (isset($oldSettings['profile'])) {
                    $profileId = $oldSettings['profile'];
                } elseif (isset($oldSettings['pubid'])) {
                    $profileId = $oldSettings['pubid'];
                }

                if (isset($profileId)) {
                    if ($profileId == $anonProfileId
                        || $profileId == $anonProfileHash
                    ) {
                        $newSettings['addthis_anonymous_profile'] = $profileId;
                    } else {
                        $newSettings['addthis_profile'] = $profileId;
                    }
                }
            } elseif ($newSettings['addthis_profile'] == $anonProfileId
                || $newSettings['addthis_profile'] == $anonProfileHash
            ) {
                $newSettings['addthis_anonymous_profile'] = $newSettings['addthis_profile'];
                unset($newSettings['addthis_profile']);
            }

            if (!isset($newSettings['addthis_anonymous_profile'])) {
                $newSettings['addthis_anonymous_profile'] = $anonProfileId;
            }

            if (isset($this->configs['addthis_for_wordpress'])
                && $this->configs['addthis_for_wordpress']
                && !isset($this->configs['addthis_plugin_controls'])
            ) {
                $newSettings['addthis_plugin_controls'] = 'AddThis';
            }

            foreach ($oldSettings as $field => $value) {
                if (!isset($newField[$field])
                    && in_array($field, $this->settingsFields)
                ) {
                    $newSettings[$field] = $value;
                }
            }

            foreach ($newSettings as $field => $value) {
                if (!in_array($field, $this->settingsFields)) {
                    unset($newSettings[$field]);
                }
            }

            $this->configs = $newSettings;
        }

        /**
         * Upgrade from Smart Layers by AddThis 1.*.* to
         * Smart Layers by AddThis 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative2()
        {
            // grab profile id no matter what
            $profileId = get_option('smart_layer_profile');
            if (!empty($profileId)) {
                $anonHash = $this->getAnonymousProfileHash();
                $anonPubId = $this->getAnonymousProfileId();
                // use existing profile id but don't overwrite existing
                if (empty($this->configs['addthis_anonymous_profile']) &&
                    ($profileId === $anonHash || $profileId === $anonPubId)
                ) {
                    $this->configs['addthis_anonymous_profile'] = $profileId;
                } elseif (empty($this->configs['addthis_profile'])) {
                    $this->configs['addthis_profile'] = $profileId;
                }
            }

            // don't grab layers json unless the plugin is activated
            $activated = get_option('smart_layer_activated');
            if (empty($activated)) {
                return null;
            }

            $advancedMode = get_option('smart_layer_settings_advanced');
            if (!empty($advancedMode)) {
                // get custom json
                $jsonStringOrig = get_option('smart_layer_settings');

                // replace single quotes with double quotes
                $jsonString = preg_replace('/\'/', '"', $jsonStringOrig);

                // remove comments
                $jsonString = preg_replace('/\s*\/\/.*/', '', $jsonString);

                // clean up whitespace
                $jsonString = preg_replace('/\s+/', ' ', $jsonString);

                // put quotes around properties without them
                $jsonString = preg_replace('/([,{]\s*)([a-z0-9_]+)\s*:/i', '$1"$2" :', $jsonString);

                $jsonDecoded = json_decode($jsonString, true);
                if ($jsonDecoded == null) {
                    $this->configs['smart_layers_bad_json'] = $jsonStringOrig;
                } else {
                    if (isset($jsonDecoded) && is_array($jsonDecoded)) {
                        foreach ($jsonDecoded as $name => $settings) {
                            if (is_array($settings) && empty($settings)) {
                                $layers[$name] = new stdClass();
                            }
                        }
                    }

                    // for advanced mode, just save the JSON
                    if (!empty($this->configs['addthis_layers_json'])) {
                        $layersFromSettings = json_decode(
                            $this->configs['addthis_layers_json'],
                            true
                        );
                        $jsonDecoded = array_replace_recursive(
                            $layersFromSettings,
                            $jsonDecoded
                        );
                    }

                    $jsonString = json_encode($jsonDecoded, JSON_UNESCAPED_SLASHES);
                    $this->configs['addthis_layers_json'] = $jsonString;
                }
            }
        }

        /**
         * Upgrade from Smart Layers by AddThis 2.*.* to
         * Smart Layers by AddThis 3.0.0
         *
         * @return null
         */
        protected function upgradeIterative3()
        {
            if (!empty($this->configs['addthis_asynchronous_loading'])) {
                $this->configs['enqueue_client'] = false;
                $this->configs['enqueue_local_settings'] = false;
            } else {
                $this->configs['enqueue_client'] = true;
                $this->configs['enqueue_local_settings'] = true;
            }
        }

        /**
         * Registering AJAX endpoints with WordPress
         *
         * @return null
         */
        protected function registerAjaxEndpoints()
        {
            parent::registerAjaxEndpoints();

            // nonce stuff for the UI
            add_action('wp_ajax_addthis_nonce', array($this, 'printGetJsonNonce'));
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints a JSON payload with a nonce value for the current user for
         * use with addthis_settings_update actions
         *
         * @return null
         */
        public function printGetJsonNonce()
        {
            $this->jsonSetup();
            $nonce = wp_create_nonce('addthis_settings_update');
            $results = array('nonce' => $nonce);
            $this->printJsonResults($results);
        }

        /**
         * Does special stuff for the minimal plugin then calls the parent
         * version of this function
         *
         * @param array $configs The current configs.
         *
         * @return array
         */
        protected function addDefaultConfigs($configs)
        {
            // if the mode isn't set yet, and this is the minimal plugin
            // the default mode should be AddThis
            if (!isset($configs['addthis_plugin_controls'])
                && isset($configs['follow_buttons_feature_enabled'])
                && $configs['follow_buttons_feature_enabled'] === false
                && isset($configs['recommended_content_feature_enabled'])
                && $configs['recommended_content_feature_enabled'] === false
                && isset($configs['sharing_buttons_feature_enabled'])
                && $configs['sharing_buttons_feature_enabled'] === false
            ) {
                $configs['addthis_plugin_controls'] === 'AddThis';
            }

            $output = parent::addDefaultConfigs($configs);
            return $output;
        }

        /**
         * Generates HTML for the bottom of widgets with links to settings
         * edit pages within WordPress and at AddThis.com, as appropriate.
         *
         * @return string HTML
         */
        public function getSettingsLinkHtmlForWidgets()
        {
            $links = array();
            $editLink = '';

            // minimal plugin doesn't include UI for editing tools
            if (!$this->isMinimalPlugin()) {
                $settingsText = esc_html__('the plugin\'s settings', AddThisFeature::$l10n_domain);
                $settingsUrl = $this->getSettingsPageUrl().'#';
                $links[] = '<a href="'.$settingsUrl.'" target="_blank">'.$settingsText.'</a>';
            }

            // unregistered users don't go to addthis to edit tools, ever
            if ($this->inRegisteredMode()) {
                $profileId = $this->getProfileId();
                $dashboardUrl = 'https://www.addthis.com/dashboard#gallery/pub/'.$profileId;
                $links[] = '<a href="'.$dashboardUrl.'" target="_blank">addthis.com</a>';
            }

            if (count($links) == 1) {
                $editLinkTemplate = 'To edit the options for your AddThis tools, please go to %1$s';
                $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                $editLink = sprintf($editLinkTemplate, $links[0]);
            } elseif (count($links) > 1) {
                $editLinkTemplate = 'To edit the options for your AddThis tools, please go to %1$s or %2$s';
                $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                $editLink = sprintf($editLinkTemplate, $links[0], $links[1]);
            }

            return $editLink;
        }

        /**
         * This must be public as it's used in widgets
         *
         * @param string $buttonName The text of the button that the user presses
         * to indicate that they agree without EULA
         *
         * @return string End User License Agreement text
         */
        public function eulaText($buttonName = 'Save')
        {
            $buttonName = esc_html__($buttonName, AddThisFeature::$l10n_domain);

            $eulaTemplate = 'By clicking "%1$s" you certify that you are at least 13 years old, and agree to the AddThis %2$s and %3$s.';
            $eulaTemplate = esc_html__($eulaTemplate, AddThisFeature::$l10n_domain);

            $privacyPolicyText = esc_html__(
                'Privacy Policy',
                AddThisFeature::$l10n_domain
            );

            $termsOfServiceText = esc_html__(
                'Terms of Service',
                AddThisFeature::$l10n_domain
            );

            $privacyPolicyLink = '<a href="http://www.addthis.com/privacy/privacy-policy">'.$privacyPolicyText.'</a>';
            $termsOfServiceLink = '<a href="http://www.addthis.com/tos">'.$termsOfServiceText.'</a>';

            $eula = sprintf($eulaTemplate, $buttonName, $privacyPolicyLink, $termsOfServiceLink);
            return $eula;
        }

        /**
         * Builds the element used for custom HTML above and below content on
         * pages, posts, categories, archives and the homepage
         *
         * @param string $location Is this for a sharing button above or below
         * content/excerpts?
         * @param array  $track    Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a class
         */
        public function getClassForTypeAndLocation(
            $location = 'above',
            &$track = false
        ) {
            $toolClass = $this->getDefaultClassForTypeAndLocation($location);
            return $toolClass;
        }

        /**
         * Returns HTML specified by the user. Intended for old Client API use.
         *
         * @param array $class the class that will identify the tool
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string this should be valid html
         */
        public function getHtmlForFilter($class, &$track = false)
        {
            $element = '.' . $class;
            $html = '';

            $configs = $this->getConfigs();
            if (!empty($configs['html']) && is_array($configs['html'])) {
                foreach ($configs['html'] as $toolSettings) {
                    if (!empty($toolSettings['enabled']) &&
                        is_array($toolSettings['elements']) &&
                        in_array($element, $toolSettings['elements'])
                    ) {
                        $html = $html . $toolSettings['html'];
                    }
                }
            }

            $url = $this->getShareUrl($track);
            $title = $this->getShareTitle($track);

            $html = sprintf($html, $url, $title);

            if (!empty($configs['ajax_support'])) {
                $html .= '<script>if (typeof window.atnt !== \'undefined\') { window.atnt(); }</script>';
            }

            return $html;
        }
    }
}