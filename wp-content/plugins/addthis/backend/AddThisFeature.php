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
 * | along with this program; if nåot, write to the Free Software             |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

if (!class_exists('AddThisFeature')) {
    /**
     * AddThis' root parent class for all its features
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFeature
    {
        protected $settingsVariableName = null;
        protected $parentSettingsId = 'addthis_registration';
        protected $hookSuffix = null;
        protected $folderName = null;
        public $globalOptionsObject = null;
        protected $configs = null;
        protected $optionGroup = 'addthis_tools';
        protected $ajaxSavePrefix = 'save_settings_';
        protected $ajaxGetPrefix = 'get_settings_';
        public $globalEnabledField = 'unnamed_feature_enabled';
        protected $settingLinkText = 'Settings';
        protected $filterPriority = 0;
        protected $filterNamePrefix = 'not_yet_set_';
        protected $enableAboveContent = false;
        protected $enableBelowContent = false;
        // adminJavaScriptAction needs to match here and in AddThisPlugin
        protected $adminJavaScriptAction = 'addthis_admin_variables';
        public static $l10n_domain = 'addthis-backend';

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array();
        protected $defaultConfigs = array();
        public $addedDefaultValue = false;

        // only users with the following WordPress capability will be able to
        // see AddThis settings pages in the WordPress dashboard.
        // A full list for capabilities can be found here:
        // https://codex.wordpress.org/Roles_and_Capabilities
        protected $editOptionsCapability = 'manage_options';

        protected $tools = array();

        /**
         * The constructor.
         *
         * @param object $globalOptionsObject An object for the Global Options
         * feature. Optional.
         *
         * @retun null
         */
        public function __construct($globalOptionsObject = null)
        {
            if (is_object($globalOptionsObject)) {
                $this->globalOptionsObject = $globalOptionsObject;
            } else {
                $this->globalOptionsObject = new AddThisGlobalOptionsFeature();
            }

            AddThisAdminUtilities::setGlobalOptions($this->globalOptionsObject);
        }

        /**
         * Checks if this feature's settings variable has already been
         * registered
         *
         * @return boolean true when the setting already exists, false when not
         */
        private function existsSettingsVariable()
        {
            if (!$this->settingsVariableName) {
                return false;
            }

            $filterName = 'sanitize_option_' . $this->settingsVariableName;
            $exists = has_filter($filterName);

            return $exists;
        }

        /**
         * This must be public as it's used in a callback for register_setting,
         * which is essentially a filter
         *
         * This takes form input for a settings variable, manipulates it, and
         * returns the variables that should be saved to the database.
         *
         * @param array $input An associative array of values input for this
         * feature's settings
         *
         * @return array A cleaned up associative array of settings specific to
         *               this feature.
         */
        public function sanitizeSettings($input)
        {
            $output = array();

            foreach ($this->settingsFields as $field) {
                if (!empty($input[$field])) {
                    $output[$field] = sanitize_text_field($input[$field]);
                }
            }

            foreach ($this->tools as $toolName) {
                $toolObject = $this->getToolObject($toolName);
                $subVariable = $toolObject->settingsSubVariableName;

                if (isset($input[$subVariable])) {
                    $toolInput = $input[$subVariable];
                } else {
                    $toolInput = false;
                }

                // tools w/o anonymous support don't get saved
                // tools w/o settingsSubVariableName don't get saved
                if (isset($toolObject->settingsSubVariableName)
                ) {
                    $toolOutput = $toolObject->sanitizeSettings($toolInput);
                    $output[$subVariable] = $toolOutput;
                }
            }

            $output = $this->addDefaultConfigs($output);
            return $output;
        }

        /**
         * This must be public as it's used in a callback for the admin_init
         * action
         *
         * Registers the settings variable for this feature with WordPress
         *
         * @return null
         */
        public function registerSettingsVariable()
        {
            if ($this->existsSettingsVariable()
                || !$this->settingsVariableName
            ) {
                return null;
            }

            $optionName = $this->settingsVariableName;
            $callback = array($this, 'sanitizeSettings');

            register_setting(
                $this->optionGroup,
                $optionName,
                $callback
            );
        }

        /**
         * This must be public as it's used in a callback for add_menu_page and
         * add_submenu_page
         *
         * Prints out the HTML to bootstrap this feature's settings page
         *
         * @return null
         */
        public function printSettingsPage()
        {
            $html = '
                <div
                    class="addthis-settings-page"
                    ng-app="appAddThisWordPress"
                >
                    <div ui-view></div>
                </div>
            ';
            echo $html;
        }

        /**
         * Checks if a settings pages slug has already been used
         *
         * @return boolean true when the setting page exists, false when not
         */
        private function existsSettingPage()
        {
            global $submenu;

            if (empty($submenu[$this->parentSettingsId])) {
                return false;
            }

            $pluginMenu = $submenu[$this->parentSettingsId];
            foreach ($pluginMenu as $submenuPageInfo) {
                if (empty($submenuPageInfo[2])) {
                    continue;
                }

                $subMenuSlug = $submenuPageInfo[2];
                if ($subMenuSlug == $this->settingsPageId) {
                    return true;
                }
            }

            return false;
        }

        /**
         * This must be public as it's used in a callback for the admin_menu
         * filter
         *
         * This function creates the admin pages for this feature and also
         * enqueues CSS and JavaScript to them.
         *
         * @return null
         */
        public function registerSettingsPage()
        {
            if ($this->existsSettingPage()) {
                return null;
            }

            $parent_slug = $this->parentSettingsId;
            $page_title = 'AddThis ' . $this->name;
            $menu_title = $this->name;
            $menu_slug = $this->settingsPageId;
            $callback = array($this, 'printSettingsPage');

            if ($parent_slug == $menu_slug) {
                $this->addMenuPage(
                    $page_title,
                    $menu_title,
                    $menu_slug,
                    $callback
                );
            }

            $this->addSubmenuPage(
                $parent_slug,
                $page_title,
                $menu_title,
                $menu_slug,
                $callback
            );

            $cssCallback = array($this, 'addSettingsPageStyles');
            $this->addAdminCss($cssCallback);

            $javaScriptCallback = array($this, 'addSettingsPageScripts');
            $this->addAdminJavaScript($javaScriptCallback);
        }

        /**
         * This must be public as it's called from
         * AddThisWordPressPlugin::bootstrap
         *
         * This bootstraps this feature into wordpress, including creating the
         * settings page, settings variable, and adding short codes
         *
         * @return null
         */
        public function bootstrap()
        {
            $this->getConfigs();

            $this->upgrade();

            if (is_admin()) {
                add_filter('admin_menu', array($this, 'registerSettingsPage'));
                add_action('admin_init', array($this, 'registerSettingsVariable'));
                $this->registerAjaxEndpoints();
            }

            $this->registerContentFilters();
            $this->registerExcerptFilters();

            $this->registerAmpHooks();
        }

        /**
         * Registering AJAX endpoints with WordPress
         *
         * @return null
         */
        protected function registerAjaxEndpoints()
        {
            $getAction = $this->ajaxGetPrefix . $this->settingsPageId;
            add_action('wp_ajax_'.$getAction, array($this, 'printJsonConfigs'));
            add_action('wp_ajax_nopriv_'.$getAction, array($this, 'printJsonConfigs'));

            // make Json Ajax endpoints for saving settings
            $saveAction = $this->ajaxSavePrefix . $this->settingsPageId;
            add_action('wp_ajax_'.$saveAction, array($this, 'saveJsonConfigs'));
        }

        /**
         * Checks to see if this upgrade is older than our upgrade tracking
         * method
         *
         * @return boolean true for upgrade from a really old version, false
         * otherwise.
         */
        public function isReallyOldUpgrade()
        {
            $upgrade = false;

            // check for the sharing buttons settings
            $settings = get_option('addthis_settings');
            if (empty($settings)) {
                // check for follow button artifacts
                $settings = get_option('widget_addthis-follow-widget');
            }
            if (empty($settings)) {
                // check if smart layers is activated
                $settings = get_option('smart_layer_activated');
            }
            if (empty($settings)) {
                // check if smart layers is activated
                $settings = get_option('addthis_sharing_buttons_settings');
                if (is_array($settings)) {
                    $settings = !isset($settings['startUpgradeAt']);
                }
            }

            if (!empty($settings)) {
                $upgrade = true;
            }

            return $upgrade;
        }

        /**
         * Looks for upgrades that haven't yet been executed. Sets
         * startUpgradeAt to the next upgrade that would be run.
         *
         * @return null
         */
        public function upgrade()
        {
            $freshInstall = false;
            $oldStart = 1;

            if (!empty($this->configs['startUpgradeAt'])) {
                $oldStart = (int)$this->configs['startUpgradeAt'];
            } elseif (!$this->isReallyOldUpgrade()) {
                $freshInstall = true;
            }

            $newStart = $this->recurseUpgrades($oldStart, $freshInstall);

            if ($newStart != $oldStart || empty($this->configs['startUpgradeAt'])) {
                $this->configs['startUpgradeAt'] = $newStart;
                $this->saveConfigs();
            }
        }

        /**
         * Runs upgrades from $oldStart to newest upgrade in codebase, if on an
         * upgrade. If a fresh install, just determines what the next upgrade
         * would be.
         *
         * @param int     $oldStart     the last upgrade function # to have run
         * @param boolean $freshInstall true for a fresh install (no upgrades
         * executed), false on upgrade
         *
         * @return int the next upgrade that would be run in a future plugin
         * upgrade
         */
        protected function recurseUpgrades($oldStart, $freshInstall)
        {
            $method = 'upgradeIterative' . (int)$oldStart;

            if (method_exists($this, $method)) {
                if (!$freshInstall) {
                    $this->$method();
                }
                $oldStart++;
                $newStart = $this->recurseUpgrades($oldStart, $freshInstall);
            } else {
                $newStart = $oldStart;
            }

            return $newStart;
        }

        /**
         * Returns HTML to link to the settings page for this feature
         *
         * @return string
         */
        public function addSettingsLinkToPlugin()
        {
            $url = $this->getSettingsPageUrl();
            $text = $this->settingLinkText;
            $text = esc_html__($text, self::$l10n_domain);
            $link = '<a href="'.$url.'">'.$text.'</a>';
            return $link;
        }

        /**
         * Returns the user capability required for editing options
         *
         * @return string
         */
        public function getEditOptionsCapability() {
            return $this->editOptionsCapability;
        }

        /**
         * Gets the URL for the settings page for this feature.
         *
         * @return string URL
         */
        public function getSettingsPageUrl()
        {
            $url = menu_page_url($this->settingsPageId, false);
            return $url;
        }

        /**
         * Takes the tool name for a tool and returns the object for that tool
         *
         * @param string $toolName the name of the desired tool
         *
         * @return null|object
         */
        public function getToolObject($toolName)
        {
            $toolObjectVariable = $toolName . 'ToolObject';
            $toolClassName = 'AddThis' . $toolName . 'Tool';

            if (!class_exists($toolClassName)) {
                error_log(__METHOD__ . ' class ' . $toolClassName . ' does not exists.');
                return null;
            }

            if (!is_object($this->$toolObjectVariable)) {
                $toolClass = new $toolClassName($this, $this->globalOptionsObject);
                $this->$toolObjectVariable = $toolClass;
            }

            return $this->$toolObjectVariable;
        }

        /**
         * Returns the settings for this feature. Attempts to get them from the
         * database if necessary.
         *
         * @param boolean $cache Defaults to true. If set to false, will grab a
         * fresh copy of the settings from the database rather than relying on
         * those cached in the object.
         *
         * @return boolean true if in preview, false otherwise
         */
        public function getConfigs($cache = true)
        {
            if (!$this->settingsVariableName) {
                return null;
            }

            if (!is_null($this->configs) && $cache) {
                return $this->configs;
            }

            $this->configs = get_option($this->settingsVariableName);
            $this->configs = $this->addDefaultConfigs($this->configs);
            if ($this->addedDefaultValue) {
                $this->saveConfigs();
            }

            return $this->configs;
        }

        /**
         * Takes an array and returns the array with additional default values
         * added if not already there
         *
         * @param array $configs The current configs.
         *
         * @return array
         */
        protected function addDefaultConfigs($configs)
        {
            if (is_array($configs)) {
                foreach ($this->defaultConfigs as $field => $defaultValue) {
                    if (!isset($configs[$field])) {
                        $configs[$field] = $defaultValue;
                        $addedDefaultValue = true;
                    }
                }
            } else {
                $configs = $this->defaultConfigs;
                $addedDefaultValue = true;
            }

            foreach ($this->tools as $toolName) {
                $toolObject = $this->getToolObject($toolName);

                // we only save settings for tools in WordPress if that tool is
                // available anonymously and the user is anonymous
                // (isn't registered)
                if (!is_object($toolObject) ||
                    $this->globalOptionsObject->inRegisteredMode()

                ) {
                    continue;
                }

                if (isset($toolObject->settingsSubVariableName)) {
                    $toolConfig = false;
                    $subVariable = $toolObject->settingsSubVariableName;
                    if (isset($configs[$subVariable])) {
                        $toolConfig = $configs[$subVariable];
                    }

                    $newToolConfig = $toolObject->addDefaultConfigs($toolConfig);
                    $configs[$subVariable] = $newToolConfig;
                    if ($toolObject->addedDefaultValue) {
                        $this->addedDefaultValue = true;
                        $toolObject->addedDefaultValue = false;
                    }
                }
            }

            return $configs;
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload of the current settings for the feature. If
         * the user does not have sufficient privilates, then the sensative
         * fields are removed from the payload.
         *
         * @return null
         */
        public function printJsonConfigs()
        {
            header('Content-Type: application/json');

            $configs = $this->getConfigs();

            // sensative values, including sensative depricated fields just to be safe
            if (!$this->checkForEditPermissions(false)) {
                $sensativeFields = array(
                    'addthis_bitly_key', //deprecated
                    'addthis_bitly_login', //deprecated
                    'addthis_password', //deprecated
                    'addthis_username', //deprecated
                    'addthis_fallback_username', //deprecated
                    'password', //deprecated
                    'username', //deprecated
                    'api_key',
                );

                foreach ($sensativeFields as $field) {
                    if (isset($configs[$field])) {
                        unset($configs[$field]);
                    }
                }
            }

            self::printJsonResults($configs);
        }

        /**
         * Function wp_send_json is introduced in WordPress 3.5, so we might not
         * be able to use it.
         *
         * @param mixed $results the stuff to output in JSON format
         *
         * @return null
         */
        public static function printJsonResults($results)
        {
            if (function_exists('wp_send_json')) {
                wp_send_json($results);
            } else {
                echo json_encode($results);
                die();
            }
        }

        /**
         * Checks if the current user has permissions to install plugins
         *
         * @param boolean $die whether to die on insufficient permissions
         *
         * @return boolean|null true on sufficient permissions, false if
         * insufficient and told not to die, null if insufficient and told to
         * die
         */
        public function checkForEditPermissions($die = false)
        {
            if (!current_user_can($this->editOptionsCapability)) {
                if ($die) {
                    header('X-PHP-Response-Code: 401', true, 401);
                    die();
                } else {
                    return false;
                }
            }

            return true;
        }

        /**
         * Setup for most JSON payloads. It will check that the user has proper
         * permissions, and that the required fields are present, as well as
         * checking a nonce if inlcuded in the required fields
         *
         * @param array $required an array of strings of fields that should be
         * present in $_REQUEST['data']. 'nonce' is special. With that one it
         * will actually check that the nonce is valid, and not just present.
         *
         * @return array grabbes the string in $_REQUEST['data'] and JSON
         * decodes it
         */
        public function jsonSetup($required = array())
        {
            header('Content-Type: application/json');

            $this->checkForEditPermissions(true);

            $input = array();
            if (!empty($_REQUEST['data'])) {
                $input = json_decode(stripslashes($_REQUEST['data']), true);
            }

            if (!empty($required)) {
                if (empty($_REQUEST['data'])) {
                    header('X-PHP-Response-Code: 400', true, 400);
                    die();
                }

                foreach ($required as $field) {
                    if ($field === 'nonce'
                        && !empty($input[$field])
                        && !wp_verify_nonce($input[$field], 'addthis_settings_update')
                    ) {
                        header('X-PHP-Response-Code: 401', true, 401);
                        die();
                    } elseif (!isset($input[$field])) {
                        header('X-PHP-Response-Code: 400', true, 400);
                        die();
                    }
                }
            }

            return $input;
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Saves the  inputs from $_REQUEST['data']. See the relative
         * sanitizeSettings functions for the feature and the tools to see what
         * values are used.
         *
         * @return null
         */
        public function saveJsonConfigs()
        {
            $required = array('config', 'nonce');
            $input = $this->jsonSetup($required);
            $configs = $input['config'];

            // re json encode json fields
            foreach ($configs as $key => $value) {
                $jsonIndicator = substr($key, -5);
                if (is_array($value) && $jsonIndicator == '_json') {
                    try {
                        $phpVersion = explode('.', phpversion());
                        // use JSON_UNESCAPED_SLASHES in php 5.4.0+
                        if ($phpVersion[0] > 5 ||
                            ($phpVersion[0] == 5 && $phpVersion[1] > 3)
                        ) {
                            $json = json_encode($value, JSON_UNESCAPED_SLASHES);
                        } else {
                            $json = json_encode($value);
                        }
                        $configs[$key] = $json;
                    } catch (Exception $e) {
                        $configs[$key] = '';
                    }
                }
            }

            $this->configs = $this->sanitizeSettings($configs);
            $this->saveConfigs();
            $this->printJsonConfigs();
        }

        /**
         * Saves configs for this feature
         *
         * @param array $configs the new configs you want to save
         *
         * @return array
         */
        public function saveConfigs($configs = null)
        {
            if (!$this->settingsVariableName) {
                return null;
            } elseif (is_array($configs) && is_array($this->configs)) {
                $this->configs = array_merge($this->configs, $configs);
            } elseif (is_array($configs)) {
                $this->configs = $configs;
            }

            if (!is_null($this->configs)) {
                update_option($this->settingsVariableName, $this->configs);
            }

            $this->addedDefaultValue = false;
            return $this->configs;
        }

        /**
         * Gives you the base URL for this plugin
         *
         * @return string
         */
        public function getPluginUrl()
        {
            $url = plugins_url() . '/' . $this->getPluginFolder();
            return $url;
        }

        /**
         * Gives you the name of the folder this plugin lives in.
         *
         * @return string
         */
        public function getPluginFolder()
        {
            if (is_null($this->folderName)) {
                $file = plugin_basename(__FILE__);
                $matches = array();
                preg_match('/([^\/]*)\/(backend\/)?([^\/]*)$/', $file, $matches);
                if (isset($matches[1])) {
                    $this->folderName = $matches[1];
                }
            }

            return $this->folderName;
        }

        /**
         * Gives you the base URL for our plugin's CSS
         *
         * @return string
         */
        public function getPluginCssFolderUrl()
        {
            $url = $this->getPluginUrl() . '/css/';
            return $url;
        }

        /**
         * Gives you the base URL for our plugin's images
         *
         * @return string
         */
        public function getPluginImageFolderUrl()
        {
            $url = $this->getPluginUrl() . '/img/';
            return $url;
        }

        /**
         * Evaluates a handle and its source to determine if we should keep it.
         * We want to keep stuff from out plugin, from themes and from core
         * WordPress, but not stuff from other plugins as it can conflict with
         * our code.
         *
         * @param string   $handle    The name given to an enqueued script or
         * @param mixed    $src       style.  This is usually a string with the
         *                            the location of the enqueued script or
         *                            style, relative or absolute. Sometimes
         *                            this is not a string, and it adds CSS code
         *                            to a WordPress generated CSS file.
         * @param string[] $whitelist We will inevitably run into code from
         *                            other plugins that should be included on
         *                            our settings page. For those, their
         *                            handles can be added to this array of
         *                            strings. We've decided to whitelist
         *                            instead of blacklist, as we are likely to
         *                            encounter fewer plugins that add
         *                            functionality to our settings page than
         *                            plugins that behave badly and add unwanted
         *                            code to our page. This also keeps our code
         *                            working (though perhaps without the added
         *                            functionality from another plugin that may
         *                            be desired by the user) instead of
         *                            breaking the page outright.
         *                            Troubleshooting should also be easier, as
         *                            a user is more likely to be aware of which
         *                            of their plugins add functionality on
         *                            their settings pages, rather than which
         *                            ones doesn't play nicely with how they
         *                            enqueue their scripts and styles.
         *
         * @return boolean true when a particular script or style should be
         *                 killed from our settings page, false when it should
         *                 not be killed
         */
        public function evalKillEnqueue($handle, $src, $whitelist = array())
        {
            $regex = '/\/[^\/]+\/plugins$/';
            preg_match($regex, plugins_url(), $matches);
            if (isset($matches[0])) {
                $pluginsFolder = $matches[0] . '/';
            } else {
                $pluginsFolder = '/wp-content/plugins/';
            }

            $partialPathToOurPlugin = $pluginsFolder . $this->getPluginFolder();
            $fullUrlToOurPlugin = $this->getPluginUrl();

            if (!is_string($src)) {
                return false;
            }

            if (!is_string($src) // is the source location a string? keep css if not, cause, for some reason it breaks
                || in_array($handle, $whitelist) // keep stuff that's in the whitelist
                || strpos($handle, 'addthis') !== false  // handle has our name
                || strpos($partialPathToOurPlugin, $src) !== false // keep relative path stuff from this plugin
                || strpos($fullUrlToOurPlugin, $src) !== false // full urls for this plugin
                || strpos($src, $pluginsFolder) == false // keep enqueued stuff for non-plugins
            ) {
                return false;
            }

            return true;
        }

        /**
         * Dequeues unwanted scripts from the admin settings HTML page generated
         * by WordPress for this feature. This should only be used for our
         * settings page, not any other settings pages. See the documentation
         * for the evalKillEnqueue function for more details, secifically for
         * more information on the $whitespace variable.
         *
         * @return null
         */
        public function killUnwantedScripts()
        {
            global $wp_scripts;
            $whitelist = array();

            foreach ($wp_scripts->queue as $handle) {
                $obj = $wp_scripts->registered[$handle];
                $src = $obj->src;
                $kill = $this->evalKillEnqueue($handle, $src, $whitelist);
                if ($kill) {
                    wp_dequeue_script($handle);
                }
            }
        }

        /**
         * Dequeues unwanted styles from the admin settings HTML page generated
         * by WordPress for this feature. This should only be used for our
         * settings page, not any other settings pages. See the documentation
         * for the evalKillEnqueue function for more details, secifically for
         * more information on the $whitespace variable.
         *
         * @return null
         */
        public function killUnwantedStyles()
        {
            global $wp_styles;
            $whitelist = array();

            foreach ($wp_styles->queue as $handle) {
                $obj = $wp_styles->registered[$handle];
                $src = $obj->src;
                $kill = $this->evalKillEnqueue($handle, $src, $whitelist);
                if ($kill) {
                    wp_dequeue_style($handle);
                }
            }
        }

        /**
         * This must be public as it's used in a callback for an action on the
         * admin_print_scripts- + hook_suffix hook
         *
         * Adds an actions onto a hook to add our CSS to the settings page for
         * this feature.
         *
         * @return null
         */
        public function addSettingsPageScripts()
        {
            $this->killUnwantedScripts();
            $settingPageProfileId = 'ra-584ec0ebef0525db';

            $bootstrapSettingsUrl = admin_url('admin-ajax.php') . '?action='.$this->adminJavaScriptAction;
            wp_enqueue_script('addthis_admin', $bootstrapSettingsUrl);

            $deps = array('addthis_admin');
            $addThisWidgetUrl = $this->globalOptionsObject->getAddThisWidgetJavaScriptUrl($settingPageProfileId);
            wp_enqueue_script('addthis_widget', $addThisWidgetUrl, $deps);

            $settingsUiRoot = $this->globalOptionsObject->getSettingsUiBaseUrl();
            wp_enqueue_script('addthis_ui_vendor', $settingsUiRoot . 'build/vendor.min.js');
            $deps = array('addthis_widget', 'addthis_ui_vendor');
            wp_enqueue_script('addthis_ui_app', $settingsUiRoot . 'build/addthis_wordpress.min.js', $deps);
        }

        /**
         * This must be public as it's used in a callback for an action on the
         * admin_print_styles- + hook_suffix hook
         *
         * Adds an actions onto a hook to add our CSS to the settings page for
         * this feature.
         *
         * @return null
         */
        public function addSettingsPageStyles()
        {
            $this->killUnwantedStyles();
            $cssRoot = $this->globalOptionsObject->getSettingsUiBaseUrl() . 'build/';
            wp_enqueue_style('addthis_all_pages', $cssRoot . 'addthis_wordpress_public.min.css');
            //wp_enqueue_style('addthis_ui_vendor', $cssRoot . 'vendor.min.css');
            wp_enqueue_style('addthis_admin_css', $cssRoot . 'addthis_wordpress_admin.min.css');
            wp_enqueue_style('roboto_font', 'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,100italic,400italic|Roboto+Condensed&amp;lang=en');

            $editorcss = includes_url('css/editor.min.css');
            wp_enqueue_style('editor-buttons', $editorcss);
        }

        /**
         * Adds a menu page for this feature
         *
         * @param string       $page_title The title for this page to be used
         * in the HTML HEAD TITLE tags
         * @param string       $menu_title The display name for this page to be
         * used in the menu
         * @param string       $menu_slug  The unique identifier for this page
         * @param string|array $callback   The name of a global function as a
         * string, or an array with the first item being an object, and the
         * second being a string naming a function in that object.
         *
         * @return string the hook_suffix that can be used to name hook into
         * actions for this page (like adding CSS or JavaScript)
         */
        public function addMenuPage(
            $page_title,
            $menu_title,
            $menu_slug,
            $callback
        ) {
            /**
             * Was using $icon = 'dashicons-plus' but removed for compatibility
             * with older WordPress versions
             *
             * In WordPress 3.8+, the Icon list available is:
             * https://developer.wordpress.org/resource/dashicons/#plus
             * alternately you can provide a URL to an image of max 20x20px
             */
            $iconUrl = $this->globalOptionsObject->getSettingsUiBaseUrl() . 'src/images/menu-icon.png';

            $hookSuffix = add_menu_page(
                $page_title,
                'AddThis',
                $this->editOptionsCapability,
                $menu_slug,
                $callback,
                $iconUrl
            );

            return $hookSuffix;
        }

        /**
         * Adds a sub menu page for this feature
         *
         * @param string       $parent_slug The unique identifier for the parent
         * page (the menu item to which this page will be a submenu)
         * @param string       $page_title  The title for this page to be used
         * in the HTML HEAD TITLE tags
         * @param string       $menu_title  The display name for this page to be
         * used in the menu
         * @param string       $menu_slug   The unique identifier for this page
         * @param string|array $callback    The name of a global function as a
         * string, or an array with the first item being an object, and the
         * second being a string naming a function in that object.
         *
         * @return string the hook_suffix that can be used to name hook into
         * actions for this page (like adding CSS or JavaScript)
         */
        public function addSubmenuPage(
            $parent_slug,
            $page_title,
            $menu_title,
            $menu_slug,
            $callback
        ) {
            $page_title = esc_html__($page_title, self::$l10n_domain);
            $menu_title = esc_html__($menu_title, self::$l10n_domain);

            $this->hookSuffix = add_submenu_page(
                $parent_slug,
                $page_title,
                $menu_title,
                $this->editOptionsCapability,
                $menu_slug,
                $callback
            );

            return $this->hookSuffix;
        }

        /**
         * Adds any CSS code onto the settings page for this feature.
         *
         * @param string|array $callback The name of a global function as a
         * string, or an array with the first item being an object, and the
         * second being a string naming a function in that object.
         *
         * @return null
         */
        public function addAdminCss($callback)
        {
            $hook = 'admin_print_styles-' . $this->hookSuffix;
            add_action($hook, $callback);
        }

        /**
         * Adds any JavaScript code onto the settings page for this feature.
         *
         * @param string|array $callback The name of a global function as a
         * string, or an array with the first item being an object, and the
         * second being a string naming a function in that object.
         *
         * @return null
         */
        public function addAdminJavaScript($callback)
        {
            $hook = 'admin_print_scripts-' . $this->hookSuffix;
            add_action($hook, $callback);
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

            foreach ($this->tools as $toolName) {
                $toolObject = $this->getToolObject($toolName);

                if (!is_object($toolObject) || !$toolObject->isEnabled()) {
                    continue;
                }

                $toolShare = $toolObject->getAddThisShare();
                $featureShare = array_replace_recursive(
                    $featureShare,
                    $toolShare
                );
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
            $featureConfig = array();

            foreach ($this->tools as $toolName) {
                $toolObject = $this->getToolObject($toolName);

                if (!is_object($toolObject) || !$toolObject->isEnabled()) {
                    continue;
                }

                $toolConfig = $toolObject->getAddThisConfig();
                $featureConfig = array_replace_recursive(
                    $featureConfig,
                    $toolConfig
                );
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
         * Returns tool specific settings for the JavaScript variable for each
         * tool in this feature set
         *
         * @return array an array of associative arrays
         */
        public function getAddThisLayersTools()
        {
            $allToolLayers = array();

            foreach ($this->tools as $toolName) {
                $toolObject = $this->getToolObject($toolName);

                if (!is_object($toolObject) || !$toolObject->isEnabled()) {
                    continue;
                }

                $toolLayers = $toolObject->getAddThisLayers();
                if (!empty($toolLayers)) {
                    $allToolLayers[] = $toolLayers;
                }
            }

            return $allToolLayers;
        }

        /**
         * Determines if this feature is enabled by any plugin, not necessarily
         * the plugin that boostrapped this object
         *
         * @return boolean
         */
        public function isEnabled()
        {
            $enabledField = $this->globalEnabledField;
            $configs = $this->globalOptionsObject->getConfigs();

            if (!empty($configs[$enabledField])) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Takes the home url for the site and parses out the domain (including
         * sub domain).
         *
         * @return string|false string of the subdomain if found, false
         * otherwise
         */
        public function getSiteDomain()
        {
            $matches = array();
            preg_match('/\/\/([^\/]+)/', home_url(), $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
            return false;
        }

        /**
         * A way of applying filters that helps us track which ones were used
         *
         * @param string $filterName the name of the filter to apply
         * @param mixed  $value      the value to apply the filter on
         * @param array  $track      Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         * onto this array
         *
         * @return mixed the altered version of $value
         */
        public function applyFilter($filterName, $value, &$track = false)
        {
            $filteredValue = apply_filters($filterName, $value);
            if ($value !== $filteredValue && is_array($track)) {
                $track[] = $filterName;
            }
            return $filteredValue;
        }

        /**
         * Gets the priority that should be used when adding filters for
         * non-excerpt content
         *
         * @return interger
         */
        public function getContentFilterPriority()
        {
            $priority = 16 + $this->filterPriority;

            $excerptFilterName = 'addthis_content_filter_priority';
            $priority = (int)apply_filters($excerptFilterName, $priority);

            $featureExcerptFilterName = $this->filterNamePrefix . '_content_filter_priority';
            $priority = (int)apply_filters($featureExcerptFilterName, $priority);

            return $priority;
        }

        /**
         * Gets the priority that should be used when adding filters for excerpt
         * content
         *
         * @return interger
         */
        public function getExcerptFilterPriority()
        {
            $priority = 16 + $this->filterPriority;

            $excerptFilterName = 'addthis_excerpt_filter_priority';
            $priority = (int)apply_filters($excerptFilterName, $priority);

            $featureExcerptFilterName = $this->filterNamePrefix . '_excerpt_filter_priority';
            $priority = (int)apply_filters($featureExcerptFilterName, $priority);

            return $priority;
        }

        /**
         * Registers hooks for the insertion of AMP tools. Default for base, override on
         * individual features
         *
         * @return null
         */
        public function registerAmpHooks() { }

        /**
         * This must be public because the Minimum plugin will need to call it
         * directly
         *
         * Registers filters for adding inline AddThis tools onto the beginning
         * and end of content.
         *
         * @return null;
         */
        public function registerContentFilters()
        {
            $priority = $this->getContentFilterPriority();
            add_filter('the_content', array($this, 'addHtmlFilterTheContent'), $priority);
        }

        /**
         * This must be public because the Minimum plugin will need to call it
         * directly
         *
         * Registers filters for adding inline AddThis tools onto the beginning
         * and end of excerpts.
         *
         * @return null;
         */
        public function registerExcerptFilters()
        {
            $priority = $this->getExcerptFilterPriority();

            $gooConfigs = $this->globalOptionsObject->getConfigs();

            if (!isset($gooConfigs['filter_get_the_excerpt'])
                || $gooConfigs['filter_get_the_excerpt'] !== false
            ) {
                add_filter('get_the_excerpt', array($this, 'addHtmlFilterGetTheExcerpt'), $priority);
            }
            if (!isset($gooConfigs['filter_the_excerpt'])
                || $gooConfigs['filter_the_excerpt'] !== false
            ) {
                add_filter('the_excerpt', array($this, 'addHtmlFilterTheExcerpt'), $priority);
            }
            if (!isset($gooConfigs['filter_wp_trim_excerpt'])
                || $gooConfigs['filter_wp_trim_excerpt'] !== false
            ) {
                add_filter('wp_trim_excerpt', array($this, 'addHtmlFilterWpTrimExcerpt'), $priority);
            }
        }

        /**
         * Calls addHtmlFilter and passes the name of the filter that it
         * is associated with. This feels hacky, but it will help the AddThis
         * Support troubleshoot user issues with themes.
         *
         * @param string $inputHtml HTML, either the content of the post or an
         * excerpt
         *
         * @return string possibly manipulated HTML
         */
        public function addHtmlFilterTheContent($inputHtml)
        {
            $filterName = 'the_content';
            $outputHtml = $this->addHtmlFilter($inputHtml, $filterName);
            return $outputHtml;
        }

        /**
         * Calls addHtmlFilter and passes the name of the filter that it
         * is associated with. This feels hacky, but it will help the AddThis
         * Support troubleshoot user issues with themes.
         *
         * @param string $inputHtml HTML, either the content of the post or an
         * excerpt
         *
         * @return string possibly manipulated HTML
         */
        public function addHtmlFilterGetTheExcerpt($inputHtml)
        {
            $filterName = 'get_the_excerpt';
            $outputHtml = $this->addHtmlFilter($inputHtml, $filterName);
            return $outputHtml;
        }

        /**
         * Calls addHtmlFilter and passes the name of the filter that it
         * is associated with. This feels hacky, but it will help the AddThis
         * Support troubleshoot user issues with themes.
         *
         * @param string $inputHtml HTML, either the content of the post or an
         * excerpt
         *
         * @return string possibly manipulated HTML
         */
        public function addHtmlFilterTheExcerpt($inputHtml)
        {
            $filterName = 'the_excerpt';
            $outputHtml = $this->addHtmlFilter($inputHtml, $filterName);
            return $outputHtml;
        }

        /**
         * Calls addHtmlFilter and passes the name of the filter that it
         * is associated with. This feels hacky, but it will help the AddThis
         * Support troubleshoot user issues with themes.
         *
         * @param string $inputHtml HTML, either the content of the post or an
         * excerpt
         *
         * @return string possibly manipulated HTML
         */
        public function addHtmlFilterWpTrimExcerpt($inputHtml)
        {
            $filterName = 'wp_trim_excerpt';
            $outputHtml = $this->addHtmlFilter($inputHtml, $filterName);
            return $outputHtml;
        }

        /**
         * Determines whether to add AddThis tools above and below
         * the content
         *
         * @param string $location Is this for a tool above or below
         * content/excerpts?
         * @param array  $track    Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return boolean true for enabled, false for not enabled
         */
        public function enabledForContentAndLocation(
            $location = 'above',
            &$track = false
        ) {
            // todo check if metabox used to disabled for post (once implemented in this code base)
            $enabled = true;

            if ($location == 'above') {
                $enabled = $this->enableAboveContent;
                $filterName = $this->filterNamePrefix . 'above_enable';
            } else {
                $enabled = $this->enableBelowContent;
                $filterName = $this->filterNamePrefix . 'below_enable';
            }

            /**
             * This filter allows users to hook into the plugin and disable
             * automatically added AddThis tools on content both above and
             * below
             */
            $enabled = $this->applyFilter($this->filterNamePrefix . 'enable', $enabled, $track);

            /**
             * This filter allows users to hook into the plugin and disable
             * automatically added AddThis tools on content either above or
             * below
             */
            $enabled = $this->applyFilter($filterName, $enabled, $track);

            return $enabled;
        }

        /**
         * Builds the class used for sharing buttons above and below content on
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
            $toolClass = $this->applyToolClassFilters(false, $location, $track);
            return $toolClass;
        }

       /**
        * Builds the class used for sharing buttons above and below content on
        * pages, posts, categories, archives and the homepage
        *
        * @param string $toolClass The name of the tool class being used
        * @param string $location  Is this for a sharing button above or below
        * content/excerpts?
        * @param array  $track     Optional. Used by reference. If the
        * filter changes the value in any way the filter's name will be pushed
        *
        * @return string a class
        */
        public function applyToolClassFilters(
            $toolClass,
            $location = 'above',
            &$track = false
        ) {
            if ($location == 'above') {
                $filterName = $this->filterNamePrefix . 'above_tool';
            } else {
                $filterName = $this->filterNamePrefix . 'below_tool';
            }

            /**
             * This filter allows users to hook into the plugin and change the
             * class used to display an AddThis tool above AND below content.
             * This is where you might change which AddThis tool is being
             * displayed (as they are added based on on the class on the
             * prepended/appended div). This is not meant for styling. Custom
             * CSS is not supported. A falsey value will disable the tool in
             * both locations.
             */
            $toolClass = $this->applyFilter($this->filterNamePrefix . 'tool', $toolClass, $track);

            /**
             * This filter allows users to hook into the plugin and change the
             * class used to display an AddThis tool above OR below content.
             * This is where you might change which AddThis tool is being
             * displayed (as they are added based on on the class on the
             * prepended/appended div). This is not meant for styling. Custom
             * CSS is not supported. A falsey value will disable the tool in
             * this location.
             */
            $toolClass = $this->applyFilter($filterName, $toolClass, $track);
            $toolClass = htmlspecialchars($toolClass);

            return $toolClass;
        }

        /**
         * Builds HTML for addtional AddThis attributes for tools rendered using
         * layers
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string HTML attributes for telling AddThis what URL to share
         */
        public function getInlineLayersAttributes(&$track = false)
        {
            return '';
        }

        /**
         * Returns HTML that AddThis client code will pick up and replace, using
         * layers
         *
         * @param array $class the class that will identify the tool
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string this should be valid html
         */
        public function getHtmlForFilter($class, &$track = false)
        {
            $htmlTemplate = '<div class="%1$s addthis_tool" %2$s></div>';
            $attrString = $this->getInlineLayersAttributes($track);
            $html = sprintf($htmlTemplate, $class, $attrString);

            $gooSettings = $this->globalOptionsObject->getConfigs();
            if (!empty($gooSettings['ajax_support'])) {
                $html .= '<script>if (typeof window.atnt !== \'undefined\') { window.atnt(); }</script>';
            }

            return $html;
        }

        /**
         * Checks if AddThis tools are disabled on this post via the meta box,
         * and whether the meta box itself is even enabled
         *
         * @return boolean true for enabled, false for disabled
         */
        protected function metaBoxDisablesTools()
        {
            $configs = $this->globalOptionsObject->getConfigs();
            if ($configs['addthis_per_post_enabled']) {
                global $post;
                $disabled = AddThisPlugin::metaBoxDisablesTools($post);
            } else {
                $disabled = false;
            }

            return $disabled;
        }

        /**
         * Filter for adding HTML onto content and excerpts. Not used directly.
         *
         * @param string $inputHtml HTML, either the content of the post or an
         * excerpt
         * @param string $trigger   Optional. What caused this function to get
         * called. Used in HTML comments to help AddThis Support troubleshoot
         * user issues with themes.
         *
         * @return string HTML
         */
        public function addHtmlFilter($inputHtml, $trigger = 'unknown')
        {
            if (is_404() || is_feed()) {
                return $inputHtml;
            }

            // If the admin has disabled addthis tools on this post, do nothing
            if ($this->metaBoxDisablesTools()) {
                return $inputHtml;
            }

            $track = array();

            $aboveEnabled = $this->enabledForContentAndLocation('above', $track);
            $belowEnabled = $this->enabledForContentAndLocation('below', $track);

            $aboveClass = $this->getClassForTypeAndLocation('above', $track);
            $belowClass = $this->getClassForTypeAndLocation('below', $track);

            $aboveHtml = $this->getHtmlForFilter($aboveClass, $track);
            $belowHtml = $this->getHtmlForFilter($belowClass, $track);

            $htmlComments = array();
            $htmlCommentLocations = array('above', 'below', 'generic');

            foreach ($htmlCommentLocations as $location) {
                $htmlComments[$location] = array();
                $search = 'AddThis '.$this->name.' '.$location;
                $comment = '<!-- '.$search.' via filter on '.htmlspecialchars($trigger);
                $track = array_unique($track);
                if (!empty($track)) {
                    $comment .= ' using AddThis filters: ' . htmlspecialchars(implode(', ', $track));
                }
                $comment .= " -->";

                $htmlComments[$location]['search'] = $search;
                $htmlComments[$location]['comment'] = $comment;
            }

            $outputHtml = $inputHtml;

            // if it is enabled above and the class isn't falsey, and it wasn't
            // already added, add it
            if ($aboveEnabled
                && $aboveClass
                && strpos($inputHtml, $htmlComments['above']['search']) === false
            ) {
                $outputHtml = $aboveHtml . $outputHtml . $htmlComments['above']['comment'];
            }

            // if it is enabled below and the class isn't falsey, and it wasn't
            // already added, add it
            if ($belowEnabled
                && $belowClass
                && strpos($inputHtml, $htmlComments['below']['search']) === false
            ) {
                $outputHtml = $outputHtml . $htmlComments['below']['comment'] . $belowHtml;
            }

            // if our output still doesn't have out troubleshooting comment on
            // it, append it -- unless this feature doesn't by default include
            // itself above or below content and not filters where used
            if (strpos($inputHtml, $htmlComments['above']['search']) === false
                && strpos($inputHtml, $htmlComments['below']['search']) === false
                && strpos($inputHtml, $htmlComments['generic']['search']) === false
                && ($this->enableAboveContent
                    || $this->enableBelowContent
                    || !empty($track))
                ) {
                $outputHtml = $outputHtml . $htmlComments['generic']['comment'];
            }

            return $outputHtml;
        }

        /**
         * Builds the class used for sharing buttons above and below content on
         * pages, posts, categories, archives and the homepage
         *
         * @param string $location Is this for a sharing button above or below
         * content/excerpts?
         *
         * @return string a class
         */
        public function getDefaultClassForTypeAndLocation($location = 'above')
        {
            $pageTypeClean = AddThisTool::currentTemplateType();
            switch ($pageTypeClean) {
                case 'home':
                    $appendClass = 'post-homepage';
                    break;
                case 'archives':
                    $appendClass = 'post-arch-page';
                    break;
                case 'categories':
                    $appendClass = 'post-cat-page';
                    break;
                case 'pages':
                    $appendClass = 'post-page';
                    break;
                case 'posts':
                    $appendClass = 'post';
                    break;
                default:
                    $appendClass = false;
            }

            if ($location == 'above') {
                $toolClass = 'at-above-' . $appendClass;
            } else {
                $toolClass = 'at-below-' . $appendClass;
            }

            if (!$appendClass) {
                $toolClass = false;
            }

            return $toolClass;
        }

        /**
         * Figures out the URL to use when sharing a post or page and returns it.
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a URL
         */
        public function getShareUrl(&$track = false)
        {
            $url = get_permalink();
            /**
             * This filter allows users to hook into the plugin and change the
             * url used on an item. A flasey value will not add the data-url
             * attribute
             */
            $url = $this->applyFilter('addthis_sharing_buttons_url', $url, $track);
            return $url;
        }

        /**
         * Figures out the title to use when sharing a post or page and returns
         * it.
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a title
         */
        public function getShareTitle(&$track = false)
        {
            $title = false;
            /**
             * This filter allows users to hook into the plugin and change the
             * title used on an item. A flasey value will not add the data-title
             * attribute
             */
            $title = $this->applyFilter('addthis_sharing_buttons_title', $title, $track);
            $title = htmlspecialchars($title);
            return $title;
        }
    }
}