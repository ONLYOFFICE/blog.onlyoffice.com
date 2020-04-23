<?php

require_once 'AddThisAdminUtilities.php';
require_once 'AddThisFeature.php';

if (!class_exists('AddThisAmp')) {
    class AddThisAmp {
        const ADMIN_NOTICE_NOAMP_KEY     = 'admin_amp_notice_noamp';
        const ADMIN_NOTICE_NOAMP_MESSAGE = 'AddThis Plugin is enabled.';
        const ADMIN_NOTICE_NOAMP_LINK    = '<a href="https://wordpress.org/plugins/amp/" target="_blank">Activate on your Accelerated Mobile Pages (AMP)</a>';

        const ADMIN_NOTICE_ENABLED_KEY     = 'admin_amp_notice_enabled';
        const ADMIN_NOTICE_ENABLED_MESSAGE = 'AddThis Plugin is enabled on your site including Accelerated Mobile Pages (AMP)';

        // Key based on compatibility version, so it can be re-enabled in the future. Do not use a
        // period in the key or it will not save.
        const ADMIN_NOTICE_INCOMPAT_KEY  = 'admin_amp_notice_incompat_1-2';
        const ADMIN_NOTICE_INCOMPAT_MESSAGE = 'AddThis Plugin is incompatible with the current version of the AMP plugin (using {cur}, requires {req} or higher). AMP functionality will not be available.';
        const ADMIN_NOTICE_INCOMPAT_LINK = '<a href="https://wordpress.org/plugins/amp/" target="_blank">Update to latest Official AMP Plugin for WordPress</a>';

        const ADMIN_NOTICE_ANON_KEY     = 'admin_amp_notice_anon';
        const ADMIN_NOTICE_ANON_MESSAGE = 'AddThis Plugin is enabled. Accelerated Mobile Pages are not currently available in anonymous WordPress mode, register for free on';
        const ADMIN_NOTICE_ANON_LINK    = '<a href="http://www.addthis.com/register" target="_blank">AddThis.com</a>';

        const TOOL_DEF_WIDTH  = 480; // About mobile size, enough room for 10
        const TOOL_DEF_HEIGHT = 63;  // Minimum height for 1 column
        const TOOL_DEF_SIZE   = 32;  // Default size
        const TOOL_DEF_HPAD   = 14;  // Total horizontal margin/padding on each item
        const TOOL_DEF_VPAD   = 15;  // Total vertical margin/padding on each item
        const TOOL_DEF_CPAD   = 16;  // Total margin/padding on the container (H & V)
        const TOOL_JUMBO_HADD = 175; // Additional horizontal space to give for jumbo
        const TOOL_JUMBO_VMIN = 78;  // Minimum vertical space for jumbo

        protected static $ampCompatChecked = false;
        protected static $ampCompatible    = false;
        protected static $ampCompatVersion = '1.2';

        protected static $floatingInserted = false;

        // Tools which have configurations stored in the database
        protected static $configuredTools = array();

        /**
         * Enqueued notice callback for outputting the admin notice message when in anonymous mode
         *
         * @return null
         */
        public static function adminNoticeAnonCallback() {
            AddThisAdminUtilities::showNotice(
                AddThisAmp::ADMIN_NOTICE_ANON_MESSAGE,
                AddThisAmp::ADMIN_NOTICE_ANON_KEY,
                AddThisAmp::ADMIN_NOTICE_ANON_LINK
            );
        }

        /**
         * Enqueued notice callback for outputting the admin notice message when AMP is enabled
         *
         * @return null
         */
        public static function adminNoticeEnabledCallback() {
            AddThisAdminUtilities::showNotice(
                AddThisAmp::ADMIN_NOTICE_ENABLED_MESSAGE,
                AddThisAmp::ADMIN_NOTICE_ENABLED_KEY
            );
        }

        /**
         * Enqueued notice callback for outputting the admin notice message when AddThis plugin
         * is not compatible with AMP for WordPress plugin
         *
         * @return null
         */
        public static function adminNoticeIncompatCallback() {
            $currentVersion = 'UNKNOWN';
            if (defined('AMP__VERSION')) {
                $currentVersion = AMP__VERSION;
            }

            $find = array('{cur}', '{req}');
            $replace = array($currentVersion, self::$ampCompatVersion);
            $message = str_replace($find, $replace, AddThisAmp::ADMIN_NOTICE_INCOMPAT_MESSAGE);

            AddThisAdminUtilities::showNotice(
                $message,
                AddThisAmp::ADMIN_NOTICE_INCOMPAT_KEY,
                AddThisAmp::ADMIN_NOTICE_INCOMPAT_LINK,
                'notice-warning'
            );
        }

        /**
         * Enqueued notice callback for outputting the admin notice message when AMP is not enabled
         *
         * @return null
         */
        public static function adminNoticeNoAmpCallback() {
            AddThisAdminUtilities::showNotice(
                AddThisAmp::ADMIN_NOTICE_NOAMP_MESSAGE,
                AddThisAmp::ADMIN_NOTICE_NOAMP_KEY,
                AddThisAmp::ADMIN_NOTICE_NOAMP_LINK
            );
        }

        /**
         * Determines if the current context is an AMP page
         *
         * @return boolean
         */
        public static function inAmpMode() {
            if (self::isAmpCompatible() && function_exists('is_amp_endpoint')) {
                return is_amp_endpoint();
            }

            return false;
        }

        /**
         * Determines if the AMP plugin is compatible with this plugin
         *
         * @return boolean
         */
        public static function isAmpCompatible() {
            if (self::$ampCompatChecked === false) {
                self::$ampCompatChecked = true;

                if (defined('AMP__VERSION') &&
                    version_compare(strtok(AMP__VERSION, '-'), self::$ampCompatVersion, '>=')
                ) {
                    self::$ampCompatible = true;
                } else {
                    self::$ampCompatible = false;
                }
            }

            return self::$ampCompatible;
        }

        /**
         * Determines if the AMP plugin is enabled
         *
         * @return boolean
         */
        public static function isAmpPluginEnabled() {
            return did_action('amp_init');
        }

        /**
         * Callback for admin_init hook
         *
         * @return null
         */
        public static function initAdmin() {
            /* Disable messaging for now
            if (AddThisAdminUtilities::isAdminInterface() && AddThisAdminUtilities::userHasCapabilities()) {
                if (self::isAmpPluginEnabled()) {
                    // AMP is enabled, messaging dependent upon anonymous mode and compatibility

                    if (self::isAmpCompatible() === false) {
                        AddThisAdminUtilities::enqueueNotice(AddThisAmp::ADMIN_NOTICE_INCOMPAT_KEY, array(__CLASS__, 'adminNoticeIncompatCallback'));
                    } else if (AddThisAdminUtilities::isAnonMode()) {
                        AddThisAdminUtilities::enqueueNotice(AddThisAmp::ADMIN_NOTICE_ANON_KEY, array(__CLASS__, 'adminNoticeAnonCallback'));
                    } else {
                        AddThisAdminUtilities::enqueueNotice(AddThisAmp::ADMIN_NOTICE_ENABLED_KEY, array(__CLASS__, 'adminNoticeEnabledCallback'));
                    }
                } else {
                    // AMP is not enabled
                    AddThisAdminUtilities::enqueueNotice(AddThisAmp::ADMIN_NOTICE_NOAMP_KEY, array(__CLASS__, 'adminNoticeNoAmpCallback'));
                }
            }
            */
        }

        /**
         * Generates the AMP-specific tag using the AMP plugin's helper if available.
         *
         * @param string $profileId Pub ID
         * @param string $widgetId ID of the widget
         * @param string $widgetType the type of widget
         * @param string $class CSS class for the tag
         * @param integer $width width of element
         * @param integer $height height of element
         * @param array $attrs additional attributes
         *
         * @return string
         */
        public static function getAmpHtml($profileId, $widgetId, $widgetType = 'shin', $class = null, $width = null, $height = null, $attrs = null) {
            if (empty($width)) { $width = AddThisAmp::TOOL_DEF_WIDTH; }
            if (empty($height)) { $height = AddThisAmp::TOOL_DEF_HEIGHT; }

            $params = array(
                'width'          => $width,
                'height'         => $height,
            );

            if (!empty($profileId)) {
                $params['data-pub-id'] = $profileId;
            }

            if (!empty($widgetId)) {
                $params['data-widget-id'] = $widgetId;
            } else if (!empty($widgetType)) {
                $params['data-product-code'] = $widgetType;
            }

            // Floating type should have a responsive layout
            if ($widgetType == 'shfs') {
                $params['layout'] = 'responsive';
            }

            if (!empty($class)) {
                $params['data-class-name'] = $class;
            }

            // Add data-attr- parameters for anonymous construction
            if (is_array($attrs)) {
                foreach ($attrs as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        $pKey = 'data-attr-' . preg_replace('[^\w-]', '-', $key);
                        $params[$pKey] = $value;
                    }
                }
            }

            if (class_exists('AMP_HTML_Utils')) {
                return AMP_HTML_Utils::build_tag('amp-addthis', $params);
            } else {
                $html = '<amp-addthis';
                foreach ($params as $key => $value) {
                    $html .= ' ' . $key . '="' . $value . '"';
                }
                $html .= '></amp-addthis>';

                return $html;
            }
        }

        /**
         * Generates the AMP-specific tag from a configured tool, by classname.
         *
         * @param string $class CSS class for the tag
         * @param string $widgetType the type of widget
         * @param integer $defaultWidth default width of element
         * @param integer $defaultHeight default height of element
         *
         * @return string
         */
        public static function getAmpHtmlByClass($class, $widgetType = 'shin', $defaultWidth = null, $defaultHeight = null) {
            if (isset(self::$configuredTools[$widgetType]) &&
                isset(self::$configuredTools[$widgetType][$class])
            ) {
                return self::getAmpHtmlForConfig(
                    self::$configuredTools[$widgetType][$class],
                    $widgetType,
                    $defaultWidth,
                    $defaultHeight
                );
            }

            return '';
        }

        /**
         * Generates the AMP-specific tag from a configured tool, by type.
         *
         * @param string $widgetType the type of widget
         * @param integer $defaultWidth default width of element
         * @param integer $defaultHeight default height of element
         *
         * @return string
         */
        public static function getAmpHtmlByType($widgetType, $defaultWidth = null, $defaultHeight = null) {
            if (isset(self::$configuredTools[$widgetType])) {
                return self::getAmpHtmlForConfig(
                    self::$configuredTools[$widgetType],
                    $widgetType,
                    $defaultWidth,
                    $defaultHeight
                );
            }

            return '';
        }

        /**
         * Generates the AMP-specific tag from a configured tool from a specific config.
         *
         * @param array $toolConfig configuration for the tool
         * @param string $widgetType the type of widget
         * @param integer $defaultWidth default width of element
         * @param integer $defaultHeight default height of element
         *
         * @return string
         */
        public static function getAmpHtmlForConfig($toolConfig, $widgetType, $defaultWidth = null, $defaultHeight = null) {
            if (is_array($toolConfig)) {
                $attrs = self::getToolAttrs($toolConfig);
                $dims = self::getToolDimensions(
                    $toolConfig,
                    $defaultWidth,
                    $defaultHeight
                );

                return self::getAmpHtml(null, null, $widgetType, null, $dims['w'], $dims['h'], $attrs);
            }

            return '';
        }

        /**
         * Generates the AMP-specific tag for a floating share tool, only once
         *
         * @param string $profileId Pub ID
         *
         * @return string
         */
        public static function getFloatingHtml($profileId) {
            if (!self::$floatingInserted) {
                self::$floatingInserted = true;

                if (!empty($profileId)) {
                    return self::getAmpHtml($profileId, null, 'shfs', null, 48, 48);
                }

                return self::getAmpHtmlByType('shfs');
            }

            return '';
        }

        /**
         * Initializes pre-configured tools stored in the database
         *
         * @param array $toolConfigs array of all configured tools
         * @param array $globalOptionsObject the global options object
         *
         * @return null
         */
        public static function initConfiguredTools($toolConfigs, $globalOptionsObject) {
            if (!is_array($toolConfigs)) {
                return;
            }

            // Instance of AddThisTool, used to determine if a tool is allowed on a WP template
            $toolObject = new AddThisTool(null, $globalOptionsObject);

            foreach ($toolConfigs as $key => $config) {
                if (is_array($config)) {
                    if (isset($config['enabled']) && $config['enabled'] && !empty($config['id'])) {
                        $toolType = $config['id'];

                        if (is_array($config['elements'])) {
                            if (!is_array(self::$configuredTools[$toolType])) {
                                self::$configuredTools[$toolType] = array();
                            }

                            for ($i = 0; $i < count($config['elements']); $i++) {
                                // Remove the '.' from the element class
                                $class = substr($config['elements'][$i], 1);

                                if (!isset(self::$configuredTools[$toolType][$class])) {
                                    self::$configuredTools[$toolType][$class] = $config;
                                }
                            }
                        } else if (!isset(self::$configuredTools[$toolType])) {
                            if (!isset($config['templates']) ||
                                $toolObject->enabledOnTemplate($config['templates'])
                            ) {
                                self::$configuredTools[$toolType] = $config;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Sanitizes and prepares AMP attributes from individual tool config
         *
         * @param array $toolConfig tool configuration
         *
         * @return array
         */
        protected static function getToolAttrs($toolConfig) {
            $attrs = array();
            if (is_array($toolConfig)) {
                $auto = (isset($toolConfig['auto_personalization']) && $toolConfig['auto_personalization']);

                $orig = false;
                $modern = false;
                if (isset($toolConfig['style'])) {
                    if ($toolConfig['style'] == 'original') {
                        $orig = true;
                        $auto = false;
                    } else if ($toolConfig['style'] == 'modern') {
                        $modern = true;
                    }
                }

                foreach ($toolConfig as $key => $prop) {
                    // Omit unnecessary key/values
                    if ($key == 'elements' ||
                        $key == 'enabled' ||
                        $key == 'id' ||
                        $key == 'templates' ||
                        $key == 'widgetId'
                    ) {
                        continue;
                    }

                    // Omit key/values based on auto_personalization and original style settings
                    if ((($auto || $orig) && $key == 'services') ||
                        (!$auto && $key == 'numPreferredServices') ||
                        ($orig && $key == 'auto_personalization') ||
                        (!$orig && $key == 'originalServices') ||
                        (!$modern && $key == 'theme')
                    ) {
                        continue;
                    }

                    // Add remaining props, convert accordingly based on type for attributes
                    if (is_array($prop)) {
                        $attrs[$key] = self::propArrayToString($prop);
                    } else if (is_bool($prop)) {
                        $attrs[$key] = ($prop ? 'true' : 'false');
                    } else {
                        $attrs[$key] = $prop;
                    }
                }
            }

            return $attrs;
        }

        /**
         * Calculates dimensions from a default width and height, based on the type
         * of tool and configured services. This will only increase the height from the
         * default, will never make it shorter.
         *
         * @param array $toolConfig tool configuration
         * @param integer $defaultWidth default width of element
         * @param integer $defaultHeight default height of element
         *
         * @return array
         */
        protected static function getToolDimensions($toolConfig, $defaultWidth = null, $defaultHeight = null) {
            // Floating share tools do not require additional calculation
            if (isset($toolConfig['id']) && $toolConfig['id'] == 'shfs') {
                return array(
                    'w' => 48,
                    'h' => 48,
                    's' => 32
                );
            }

            if (empty($defaultWidth)) { $defaultWidth = AddThisAmp::TOOL_DEF_WIDTH; }
            if (empty($defaultHeight)) { $defaultHeight = AddThisAmp::TOOL_DEF_HEIGHT; }

            $count = 0;
            $jumbo = false;
            $dims = array(
                'w' => $defaultWidth,
                'h' => $defaultHeight,
                's' => AddThisAmp::TOOL_DEF_SIZE
            );

            if (isset($toolConfig['size'])) {
                $s = intval($toolConfig['size']);
                if ($s > 0) {
                    $dims['s'] = $s;
                }
            }

            if (isset($toolConfig['counts']) && $toolConfig['counts'] == 'jumbo') {
                $jumbo = true;
            }

            // Original style uses an array of originalServices. Auto personalization defines a
            // number of services. When manually selected (not auto), there is an array of services.
            if (isset($toolConfig['style']) &&
                $toolConfig['style'] == 'original' &&
                is_array($toolConfig['originalServices'])
            ) {
                $count = count($toolConfig['originalServices']);
            } else if (isset($toolConfig['auto_personalization']) &&
                isset($toolConfig['numPreferredServices']) &&
                $toolConfig['auto_personalization']
            ) {
                $count = intval($toolConfig['numPreferredServices']);
            } else if (is_array($toolConfig['services'])) {
                $count = count($toolConfig['services']);
            }

            // If there is a count available, adjust the height, but only if greater
            // than defined
            if ($count > 0) {
                $containerWidth = $dims['w'] - AddThisAmp::TOOL_DEF_CPAD;
                $itemWidth = $dims['s'] + AddThisAmp::TOOL_DEF_HPAD;
                $itemHeight = $dims['s'] + AddThisAmp::TOOL_DEF_VPAD;

                if ($containerWidth > 0 && $itemWidth > 0) {
                    if ($jumbo) {
                        $containerWidth -= AddThisAmp::TOOL_JUMBO_HADD;
                        $dims['h'] = AddThisAmp::TOOL_JUMBO_VMIN + AddThisAmp::TOOL_DEF_CPAD;
                    }

                    $rowCount = floor($containerWidth / $itemWidth);
                    $colCount = ceil($count / $rowCount);

                    $height = $colCount * $itemHeight + AddThisAmp::TOOL_DEF_CPAD;
                    if ($height > $dims['h']) {
                        $dims['h'] = $height;
                    }
                }
            }

            return $dims;
        }

        /**
         * Converts a property array into a string to be used in an AMP tag attribute.
         *
         * @param array $propArray a tool property which is an array
         *
         * @return string
         */
        protected static function propArrayToString($propArray) {
            $out = '';
            if (is_array($propArray)) {
                $out = implode(',', $propArray);
            }

            return $out;
        }
    }

    if (AddThisAdminUtilities::isAdminInterface()) {
        add_action('admin_init', array('AddThisAmp', 'initAdmin'));
    }
}