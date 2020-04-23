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
require_once 'AddThisSharingButtonsFloatingTool.php';
require_once 'AddThisSharingButtonsInlineTool.php';
require_once 'AddThisSharingButtonsMobileToolbarTool.php';

if (!class_exists('AddThisSharingButtonsFeature')) {
    /**
     * Class for adding AddThis sharing buttonst tools to WordPress
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsFeature extends AddThisFeature
    {
        protected $settingsVariableName = 'addthis_sharing_buttons_settings';
        protected $settingsPageId = 'addthis_sharing_buttons';
        protected $name = 'Share Buttons';
        protected $SharingButtonsFloatingToolObject = null;
        protected $SharingButtonsInlineToolObject = null;
        protected $SharingButtonsMobileToolbarToolObject = null;
        protected $filterPriority = 1;
        protected $filterNamePrefix = 'addthis_sharing_buttons_';
        protected $enableAboveContent = true;
        protected $enableBelowContent = true;

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array(
            'startUpgradeAt',
        );

        public $globalLayersJsonField = 'addthis_layers_share_json';
        public $globalEnabledField = 'sharing_buttons_feature_enabled';

        // require the files with the tool and widget classes at the top of this
        // file for each tool
        protected $tools = array(
            'SharingButtonsFloating',
            'SharingButtonsInline',
            'SharingButtonsMobileToolbar',
        );

        public $contentFiltersEnabled = true;

        /**
         * Registers hooks for the insertion of AMP tools
         *
         * @return null
         */
        public function registerAmpHooks() {
            $gooSettings = $this->globalOptionsObject->getConfigs();
            if ($gooSettings['amp_disable'] == false) {
                add_filter('amp_post_template_data', array($this, 'addFilterAmpPostTemplateData'));
                add_action('amp_post_template_footer', array($this, 'addHtmlAmpFloating'));
                add_action('wp_footer', array($this, 'addHtmlAmpFloating'));
            }
        }

        /**
         * Callback for AMP hooks
         *
         * Modifies AMP classic/reader template data to ensure that the amp-addthis
         * extension script is loaded
         *
         * @param array $data template data
         *
         * @return array
         */
        public function addFilterAmpPostTemplateData($data) {
            if (AddThisAmp::isAmpCompatible()) {
                if (!is_array($data['amp_component_scripts'])) {
                    $data['amp_component_scripts'] = array();
                }

                if (!$data['amp_component_scripts']['amp-addthis']) {
                    $data['amp_component_scripts']['amp-addthis'] = true;
                }
            }

            return $data;
        }

        /**
         * Callback for AMP hooks
         *
         * Inserts element for floating share tools when using AMP
         *
         * @return null
         */
        public function addHtmlAmpFloating() {
            if (AddThisAmp::inAmpMode()) {
                if ($this->globalOptionsObject->inRegisteredMode()) {
                    $profileId = $this->globalOptionsObject->getUsableProfileId();
                    echo AddThisAmp::getFloatingHtml($profileId);
                } else {
                    echo AddThisAmp::getFloatingHtml(null);
                }
            }
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
        public function getHtmlForFilter($class, &$track = false) {
            if (AddThisAmp::inAmpMode() && !empty($class)) {
                $gooSettings = $this->globalOptionsObject->getConfigs();

                if ($gooSettings['amp_disable']) {
                    return '';
                }

                $widgetType = 'shin';
                $width = $gooSettings['amp_inline_share_width'];
                $height = $gooSettings['amp_inline_share_height'];

                if ($this->globalOptionsObject->inRegisteredMode()) {
                    $profileId = $this->globalOptionsObject->getUsableProfileId();

                    return AddThisAmp::getAmpHtml($profileId, null, $widgetType, $class, $width, $height);
                } else {
                    return AddThisAmp::getAmpHtmlByClass($class, $widgetType, $width, $height);
                }
            }

            return parent::getHtmlForFilter($class, $track);
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
            $toolClass = $this->getDefaultClassForTypeAndLocation($location);

            if ($location == 'above') {
                $filterName = $this->filterNamePrefix . 'above_tool';
            } else {
                $filterName = $this->filterNamePrefix . 'below_tool';
            }

            $toolClass = $this->applyToolClassFilters($toolClass, $location, $track);
            return $toolClass;
        }

        /**
         * Builds HTML for teling AddThis what URL to share for inline layers
         * buttons
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string HTML attributes for telling AddThis what URL to share
         */
        public function getInlineLayersAttributes(&$track = false)
        {
            $url = $this->getShareUrl($track);
            $title = $this->getShareTitle($track);
            $attrString = self::buildDataAttrString($url, $title);
            return $attrString;
        }

        /**
         * Builds string for data attributes that can be included on AddThis
         * inline share buttons
         *
         * @param string|boolean $url         Optional. URL to put in data
         * attributes
         * @param string|boolean $title       Optional. Title to put in data
         * attributes
         * @param string|boolean $description Optional. Description to put in
         * data attributes
         * @param string|boolean $media       Optional. Media url to put in
         * data attributes
         *
         * @return string HTML attributes for telling AddThis what share
         * attributes to use on some inline share buttons
         */
        public static function buildDataAttrString(
            $url = false,
            $title = false,
            $description = false,
            $media = false
        ) {

            $attrs = array();

            if (!empty($url)) {
                $url = str_replace('"', '', $url);
                $attrs[] = 'data-url="' . $url . '"';
            }

            if (!empty($title)) {
                $title = str_replace('"', '', $title);
                $attrs[] = 'data-title="' . $title . '"';
            }

            if (!empty($description)) {
                $description = str_replace('"', '', $description);
                $attrs[] = 'data-description="' . $description . '"';
            }

            if (!empty($media)) {
                $media = str_replace('"', '', $media);
                $attrs[] = 'data-media="' . $media . '"';
            }

            $attrString = implode(' ', $attrs);
            return $attrString;
        }

        /**
         * This must be public as it's used in a callback for register_setting,
         * which is essentially a filter
         *
         * This takes form input for a settings variable, manipulates it, and
         * returns the variables that should be saved to the database.
         *
         * This version of the function overrides the one in AddThisFeature and
         * works with multiple versions of the same tool. Eventually this would
         * replace AddThisFeature::sanitizeSettings
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

            if (is_array($input)) {
                foreach ($input as $key => $toolSettings) {
                    // determine which tool it is, and run it through the appropriate tool object's sanitizeSettings

                    if (isset($toolSettings['id'])) {
                        //if shfs - do special stuff to break out sharing sidebar and mobile sharing toolbar
                        if ($toolSettings['id'] === 'shfs') {
                            $toolObject = new AddThisSharingButtonsFloatingTool();
                        } elseif ($toolSettings['id'] === 'shin') {
                            $toolObject = new AddThisSharingButtonsInlineTool();
                        } elseif ($toolSettings['id'] === 'smlmo') {
                            $toolObject = new AddThisSharingButtonsMobileToolbarTool();
                        }

                        $toolOutput = $toolObject->sanitizeSettings($toolSettings);
                        $output[$toolOutput['widgetId']] = $toolOutput;
                    } elseif ($key === 'startUpgradeAt') {
                        $output['startUpgradeAt'] = $toolSettings;
                    }
                }
            }

            return $output;
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

            $configs = $this->getConfigs();
            if (is_array($configs)) {
                foreach ($configs as $toolSettings) {
                    if (!empty($toolSettings['id'])) {
                        if ($toolSettings['id'] === 'shfs') {
                            $toolObject = new AddThisSharingButtonsFloatingTool();
                        } elseif ($toolSettings['id'] === 'shin') {
                            $toolObject = new AddThisSharingButtonsInlineTool();
                        } elseif ($toolSettings['id'] === 'smlmo') {
                            $toolObject = new AddThisSharingButtonsMobileToolbarTool();
                        }

                        if (is_object($toolObject)) {
                            $toolLayers = $toolObject->getAddThisLayers($toolSettings);
                            if (!empty($toolLayers)) {
                                $allToolLayers[] = $toolLayers;
                            }
                        }
                    }
                }
            }

            return $allToolLayers;
        }

        /**
         * Upgrade from Smart Layers by AddThis 1.*.* to
         * Smart Layers by AddThis 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative1()
        {
            $activated = get_option('smart_layer_activated');
            if (empty($activated)) {
                return null;
            }

            $advancedMode = get_option('smart_layer_settings_advanced');
            if (!empty($advancedMode)) {
                return null;
            }

            $jsonString = get_option('smart_layer_settings');
            $jsonString = preg_replace('/\'/', '"', $jsonString);
            $jsonDecoded = json_decode($jsonString, true);

            $followServices = array();
            if (!empty($jsonDecoded['follow']) &&
                !empty($jsonDecoded['follow']['services'])
            ) {
                // prep mobile toolbar folllow settings
                $oldServices = $jsonDecoded['follow']['services'];
                $followServices = AddThisFollowButtonsFeature::upgradeIterative2SmartLayersServices($oldServices);
            }

            $sharingSidebarConfigs = array();
            $mobileToolbarConfigs = array();
            if (isset($jsonDecoded['share'])) {
                // prep sharing sidebar settings & mobile toolbar settings
                $sharingSidebarConfigs['enabled'] = true;
                $mobileToolbarConfigs['enabled'] = true;

                if (isset($jsonDecoded['share']['position'])) {
                    $sharingSidebarConfigs['position'] = $jsonDecoded['share']['position'];
                }

                if (isset($jsonDecoded['share']['numPreferredServices'])) {
                    $sharingSidebarConfigs['numPreferredServices'] = (int)$jsonDecoded['share']['numPreferredServices'];
                }

                if (!empty($followServices)) {
                    // include follow services for mobile
                    $mobileToolbarConfigs['follow'] = 'on';
                    $mobileToolbarConfigs['followServices'] = $followServices;
                } else {
                    $mobileToolbarConfigs['follow'] = 'off';
                }

                if (isset($jsonDecoded['theme'])) {
                    $sharingSidebarConfigs['theme'] = $jsonDecoded['theme'];
                    $mobileToolbarConfigs['buttonBarTheme'] = $jsonDecoded['theme'];
                }
            }

            $this->configs['smlsh'] = $sharingSidebarConfigs;
            $this->configs['smlmo'] = $mobileToolbarConfigs;
        }

        /**
         * Upgrade from Smart Layers by AddThis 2.0.0 to 3.0.0
         * Upgrade from Website Tools by AddThis 1.1.2 to 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative2()
        {
            $customShareWidgets = self::upgradeIterative2ReformatWidgets(
                'addthis_custom_sharing_widget',
                'addthis_custom_sharing'
            );

            $jumboShareWidgets = self::upgradeIterative2ReformatWidgets(
                'addthis_jumbo_share_widget',
                'addthis_jumbo_share'
            );

            $nativeShareWidgets = self::upgradeIterative2ReformatWidgets(
                'addthis_native_toolbox_widget',
                'addthis_native_toolbox'
            );

            $responsiveShareWidgets = self::upgradeIterative2ReformatWidgets(
                'addthis_responsive_sharing_widget',
                'addthis_responsive_sharing'
            );

            $squareShareWidgets = self::upgradeIterative2ReformatWidgets(
                'addthis_sharing_buttons_widget',
                'addthis_sharing_toolbox'
            );

            $newWidgets = array_merge(
                $customShareWidgets,
                $jumboShareWidgets,
                $nativeShareWidgets,
                $responsiveShareWidgets,
                $squareShareWidgets
            );

            $widgetIdMapping = self::upgradeIterative2SaveWidgets($newWidgets);
            AddThisFollowButtonsFeature::upgradeIterative1MigrateSidebarWidgetIds($widgetIdMapping);
        }

        /**
         * Reformats widgets settings from where the CSS class for the AddThis
         * tool is hard coded per widget PHP class, to one widget PHP class
         * which stores the proper CSS class as an instance variable for that
         * widget
         *
         * @param array $oldWidgetName old settings for widgets
         * @param array $class         the CSS class to use for all the old
         * widgets passed
         *
         * @return array associated array of reformatted widgets, keys are used
         * for migrating widgets
         */
        public static function upgradeIterative2ReformatWidgets($oldWidgetName, $class)
        {
            $oldWidgets = get_option('widget_' . $oldWidgetName);
            $newWidgets = array();

            if (!is_array($oldWidgets) || empty($oldWidgets)) {
                return array();
            }

            foreach ($oldWidgets as $key => $widget) {
                if ($key == '_multiwidget') {
                    continue;
                }

                $oldWidgetKey = $oldWidgetName . '-' . $key;
                $newWidgets[$oldWidgetKey] = array();
                $newWidgets[$oldWidgetKey]['title'] = $widget['title'];
                $newWidgets[$oldWidgetKey]['class'] = $class;
            }

            return $newWidgets;
        }


        /**
         * Saves new widgets by appending to existing, returns an array mapping
         * old widgets IDs to new ones
         *
         * @param array $inputWidgets new settings for widgets
         *
         * @return array associated array of old widgets IDs as keys and new
         * widget IDs as values
         */
        public static function upgradeIterative2SaveWidgets($inputWidgets)
        {
            if (empty($inputWidgets)) {
                return;
            }

            if (isset($inputWidgets['_multiwidget'])) {
                unset($inputWidgets['_multiwidget']);
            }

            $widgetIdMapping = array();
            $newWidgetName = 'addthis_tool_by_class_name_widget';
            $outputWidgets = get_option('widget_' . $newWidgetName);

            if (is_array($outputWidgets) && !empty($outputWidgets)) {
                if (!isset($outputWidgets['_multiwidget'])) {
                    unset($outputWidgets['_multiwidget']);
                }

                $widgetIdNextNumber = max(array_keys($outputWidgets)) + 1;
            } else {
                $widgetIdNextNumber = 0;
                $outputWidgets = array();
            }

            foreach ($inputWidgets as $key => $widget) {
                $newWidgetId = $newWidgetName . '-' . $widgetIdNextNumber;
                $oldWidgetId = $key;
                $widgetIdMapping[$oldWidgetId] = $newWidgetId;
                $outputWidgets[$widgetIdNextNumber] = $widget;

                $widgetIdNextNumber = $widgetIdNextNumber + 1;
            }
            $outputWidgets['_multiwidget'] = 1;

            update_option('widget_addthis_tool_by_class_name_widget', $outputWidgets);
            return $widgetIdMapping;
        }

        /**
         * Upgrade from Smart Layers by AddThis 2.0.0 to 3.0.0
         *
         * @return null
         */
        protected function upgradeIterative3()
        {
            $newConfigs = array();
            $oldConfigs = $this->getConfigs();
            if (!empty($oldConfigs['msd'])) {
                $toolSettings = array(
                    'enabled'               => $oldConfigs['msd']['enabled'],
                    'counts'                => (empty($oldConfigs['msd']['counts']) ? 'none' : 'one'),
                    'numPreferredServices'  => $oldConfigs['msd']['numPreferredServices'],
                    'mobilePosition'        => $oldConfigs['msd']['position'],
                    'services'              => $oldConfigs['msd']['services'],
                    'auto_personalization'  => (empty($oldConfigs['msd']['services']) ? true : false),
                    'style'                 => 'modern',
                    'theme'                 => 'transparent',
                    'mobileButtonSize'      => 'large',
                    'id'                    => 'shfs',
                    'desktopPosition'       => 'hide',
                    'toolName'              => 'Mobile Sharing Toolbar',
                    'widgetId'              => 'msd',
                    'templates'             => array(
                        'home',
                        'posts',
                        'pages',
                        'archives',
                        'categories',
                    ),
                );
                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            if (!empty($oldConfigs['smlsh'])) {
                $toolSettings = array(
                    'enabled'               => $oldConfigs['smlsh']['enabled'],
                    'counts'                => (empty($oldConfigs['smlsh']['counts']) ? 'none' : 'one'),
                    'numPreferredServices'  => $oldConfigs['smlsh']['numPreferredServices'],
                    'desktopPosition'       => $oldConfigs['smlsh']['position'],
                    'services'              => $oldConfigs['smlsh']['services'],
                    'auto_personalization'  => (empty($oldConfigs['smlsh']['services']) ? true : false),
                    'style'                 => 'modern',
                    'theme'                 => 'transparent',
                    'id'                    => 'shfs',
                    'mobilePosition'        => 'hide',
                    'toolName'              => 'Sidebar',
                    'widgetId'              => 'smlsh',
                    'templates'             => array(
                        'home',
                        'posts',
                        'pages',
                        'archives',
                        'categories',
                    ),
                );
                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            if (!empty($oldConfigs['tbx'])) {
                $toolSettings = array(
                    'enabled'               => $oldConfigs['tbx']['enabled'],
                    'counts'                => (empty($oldConfigs['tbx']['counts']) ? 'none' : 'one'),
                    'numPreferredServices'  => $oldConfigs['tbx']['numPreferredServices'],
                    'services'              => $oldConfigs['tbx']['services'],
                    'auto_personalization'  => (empty($oldConfigs['tbx']['services']) ? true : false),
                    'elements'              => $oldConfigs['tbx']['elements'],
                    'style'                 => 'fixed',
                    'id'                    => 'shin',
                    'toolName'              => 'Share Buttons',
                    'widgetId'              => 'tbx',
                );

                switch ($oldConfigs['tbx']['size']) {
                    case 'small':
                        $toolSettings['size'] = '16px';
                        break;
                    case 'medium':
                        $toolSettings['size'] = '20px';
                        break;
                    default:
                        $toolSettings['size'] = '32px';
                }

                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            if (!empty($oldConfigs['scopl'])) {
                $toolSettings = array(
                    'enabled'               => $oldConfigs['scopl']['enabled'],
                    'auto_personalization'  => (empty($oldConfigs['scopl']['services']) ? true : false),
                    'originalServices'      => $oldConfigs['scopl']['services'],
                    'elements'              => $oldConfigs['scopl']['elements'],
                    'style'                 => 'original',
                    'id'                    => 'shin',
                    'toolName'              => 'Original Share Buttons',
                    'widgetId'              => 'scopl',
                );
                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            if (!empty($oldConfigs['smlmo'])) {
                $oldConfigs['smlmo']['widgetId'] = 'smlmo';
                $oldConfigs['smlmo']['id'] = 'smlmo';
                $oldConfigs['smlmo']['toolName'] = 'Mobile Toolbar';
                $oldConfigs['smlmo']['templates'] = array(
                    'home',
                    'posts',
                    'pages',
                    'archives',
                    'categories',
                );

                $newConfigs['smlmo'] = $oldConfigs['smlmo'];
            }

            if (!empty($oldConfigs['startUpgradeAt'])) {
                $newConfigs['startUpgradeAt'] = $oldConfigs['startUpgradeAt'];
            }

            $this->saveConfigs($newConfigs);
        }

        /**
         * Upgrade from Share Buttons by AddThis 4/5.*.* to 6.0.0
         *
         * Reformats the old 4.0/5.0 Widgets that used client API
         *
         * @return null
         */
        protected function upgradeIterative4()
        {
            if ($this->globalOptionsObject->inRegisteredMode()) {
                // This widget is only visible in Anonymous mode.
                // Do nothing if the user is registered
                return;
            }

            $gooConfigs = $this->globalOptionsObject->getConfigs();
            $gooConfigs['filter_get_the_excerpt'] = true;
            $gooConfigs['filter_the_excerpt'] = false;
            $gooConfigs['filter_wp_trim_excerpt'] = false;

            //
            // MIGRATE WIDGETS
            //
            $oldWidgetName = 'addthis-widget';
            $oldWidgets = get_option('widget_' . $oldWidgetName);
            $newWidgets = array();

            if (!is_array($oldWidgets) || empty($oldWidgets)) {
                return;
            }

            foreach ($oldWidgets as $key => $widget) {
                if ($key == '_multiwidget') {
                    continue;
                }

                $oldWidgetKey = $oldWidgetName . '-' . $key;
                $newWidgets[$oldWidgetKey] = array();
                $newWidgets[$oldWidgetKey]['title'] = $widget['title'];
                $newWidgets[$oldWidgetKey]['conflict'] = true;

                // save the old client api HTML for this tool
                switch ($widget['style']) {
                    case 'large_toolbox':
                        //32x32 px square buttons, top services, 5 buttons with counter on the + only
                        $newWidgets[$oldWidgetKey]['html'] = ''.
                            '<div class="addthis_toolbox addthis_default_style addthis_32x32_style">'.
                                '<a class="addthis_button_preferred_1"></a>'.
                                '<a class="addthis_button_preferred_2"></a>'.
                                '<a class="addthis_button_preferred_3"></a>'.
                                '<a class="addthis_button_preferred_4"></a>'.
                                '<a class="addthis_button_compact"></a>'.
                                '<a class="addthis_counter addthis_bubble_style"></a>'.
                            '</div>';
                        break;
                    case 'small_toolbox':
                        //16x16 px square buttons, top services, 5 buttons with counter on the + only
                        $newWidgets[$oldWidgetKey]['html'] = ''.
                            '<div class="addthis_toolbox addthis_default_style addthis_">'.
                                '<a class="addthis_button_preferred_1"></a>'.
                                '<a class="addthis_button_preferred_2"></a>'.
                                '<a class="addthis_button_preferred_3"></a>'.
                                '<a class="addthis_button_preferred_4"></a>'.
                                '<a class="addthis_button_compact"></a>'.
                                '<a class="addthis_counter addthis_bubble_style"></a>'.
                            '</div>';
                        break;
                    case 'button':
                        // that weird super compact thing / bn3
                        $newWidgets[$oldWidgetKey]['html'] = ''.
                            '<div>'.
                                '<a class="addthis_button" href="//addthis.com/bookmark.php?v=300">'.
                                    '<img src="//cache.addthis.com/cachefly/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/>'.
                                '</a>'.
                            '</div>';
                        break;
                    default:
                        //fb_tw_p1_sc
                        // third party/original buttons: facebook like with counter, tweet, pinteres, addthis with counter
                        $newWidgets[$oldWidgetKey]['html'] = ''.
                            '<div class="addthis_toolbox addthis_default_style ">'.
                                '<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>'.
                                '<a class="addthis_button_tweet"></a>'.
                                '<a class="addthis_button_pinterest_pinit"></a>'.
                                '<a class="addthis_counter addthis_pill_style"></a>'.
                            '</div>';
                        break;
                }
            }

            //
            // MIGRATE GENERAL SETTINGS
            //
            $widgetIdMapping = self::upgradeIterative2SaveWidgets($newWidgets);
            AddThisFollowButtonsFeature::upgradeIterative1MigrateSidebarWidgetIds($widgetIdMapping);

            $oldConfigs = $this->getConfigs();
            if (isset($oldConfigs['addthis_sidebar_count'])) {
                $newConfigs = array();
            } else {
                $newConfigs  = $oldConfigs;
            }


            $defaultSettingsPill = array(
                'id'       => 'html',
                'html'     => '<div><a class="addthis_button" href="//addthis.com/bookmark.php?v='.$oldConfigs['atversion'].'" %1$s><img src="//cache.addthis.com/cachefly/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a></div>',
                'elements' => array(),
            );

            $defaultSettingsOldButtons = array(
                'id'                   => 'shin',
                'style'                => 'original',
                'originalServices'     => array(
                    'facebook_like',
                    'tweet',
                    'pinterest_pinit',
                    'counter',
                ),
                'elements'             => array(),
            );

            $defaultSettingsNewButtons = array(
                'id'                    => 'shin',
                'style'                 => 'fixed',
                'counts'                => 'one',
                'numPreferredServices'  => 5,
                'services'              => array(),
                'elements'              => array(),
            );

            //
            // ABOVE BUTTON SETTINGS MIGRATION
            //
            if (!empty($oldConfigs['above'])) {
                $toolSettings = array();
                if ($oldConfigs['above'] === 'fb_tw_p1_sc') {
                    $toolSettings = $defaultSettingsOldButtons;
                    if (!empty($oldConfigs['above_custom_services'])) {
                        $toolSettings['originalServices'] = explode(',', $oldConfigs['above_custom_services']);
                        foreach ($toolSettings['originalServices'] as $key => $service) {
                            $toolSettings['originalServices'][$key] = trim($service);
                        }
                    }
                } elseif ($oldConfigs['above'] === 'large_toolbox'
                    || $oldConfigs['above'] === 'small_toolbox'
                ) {
                    $toolSettings = $defaultSettingsNewButtons;

                    if ($oldConfigs['above'] === 'large_toolbox') {
                        $toolSettings['size'] = '32px';
                    } else {
                        $toolSettings['size'] = '16px';
                    }

                    if (!empty($oldConfigs['above_custom_services'])) {
                        $toolSettings['services'] = explode(',', $oldConfigs['above_custom_services']);
                        foreach ($toolSettings['services'] as $key => $service) {
                            $toolSettings['services'][$key] = trim($service);
                        }
                    }
                } elseif ($oldConfigs['above'] === 'button') {
                    $toolSettings = $defaultSettingsPill;
                } elseif ($oldConfigs['above'] === 'custom_string') {
                    $toolSettings = array(
                        'id'       => 'html',
                        'html'     => $oldConfigs['above_custom_string'],
                        'elements' => array(),
                    );
                }

                if ($oldConfigs['above'] !== 'custom_string') {
                    $toolSettings['elements'][] = '.addthis_inline_share_toolbox_above';
                }

                if (!empty($oldConfigs['addthis_above_showon_excerpts'])) {
                    if (!empty($oldConfigs['addthis_above_showon_home'])) {
                        $toolSettings['elements'][] = '.at-above-post-homepage';
                    }
                    if (!empty($oldConfigs['addthis_above_showon_archives'])) {
                        $toolSettings['elements'][] = '.at-above-post-arch-page';
                    }
                    if (!empty($oldConfigs['addthis_above_showon_categories'])) {
                        $toolSettings['elements'][] = '.at-above-post-cat-page';
                    }
                }
                if (!empty($oldConfigs['addthis_above_showon_posts'])) {
                    $toolSettings['elements'][] = '.at-above-post';
                }
                if (!empty($oldConfigs['addthis_above_showon_pages'])) {
                    $toolSettings['elements'][] = '.at-above-post-page';
                }

                $toolSettings['enabled'] = (boolean)$oldConfigs['addthis_above_enabled'];
                $toolSettings['auto_personalization'] = (boolean)$oldConfigs['above_auto_services'];
                $toolSettings['toolName'] = 'Sharing Buttons Above Content';
                $toolSettings['widgetId'] = 'above';

                if ($oldConfigs['above'] === 'custom_string') {
                    $gooConfigs['html'][$toolSettings['widgetId']] = $toolSettings;
                } else {
                    $newConfigs[$toolSettings['widgetId']] = $toolSettings;
                }
            }

            //
            // BELOW BUTTON SETTINGS MIGRATION
            //
            if (!empty($oldConfigs['below'])) {
                $toolSettings = array();
                if ($oldConfigs['below'] === 'fb_tw_p1_sc') {
                    $toolSettings = $defaultSettingsOldButtons;
                    if (!empty($oldConfigs['below_custom_services'])) {
                        $toolSettings['originalServices'] = explode(',', $oldConfigs['below_custom_services']);
                        foreach ($toolSettings['originalServices'] as $key => $service) {
                            $toolSettings['originalServices'][$key] = trim($service);
                        }
                    }
                } elseif ($oldConfigs['below'] === 'large_toolbox' | $oldConfigs['below'] === 'small_toolbox') {
                    $toolSettings = $defaultSettingsNewButtons;

                    if ($oldConfigs['below'] === 'large_toolbox') {
                        $toolSettings['size'] = '32px';
                    } else {
                        $toolSettings['size'] = '16px';
                    }

                    if (!empty($oldConfigs['below_custom_services'])) {
                        $toolSettings['services'] = explode(',', $oldConfigs['below_custom_services']);
                        foreach ($toolSettings['services'] as $key => $service) {
                            $toolSettings['services'][$key] = trim($service);
                        }
                    }
                } elseif ($oldConfigs['below'] === 'button') {
                    $toolSettings = $defaultSettingsPill;
                } elseif ($oldConfigs['below'] === 'custom_string') {
                    $toolSettings = array(
                        'id'       => 'html',
                        'html'     => $oldConfigs['below_custom_string'],
                        'elements' => array(),
                    );
                }

                if ($oldConfigs['below'] !== 'custom_string') {
                    $toolSettings['elements'][] = '.addthis_inline_share_toolbox_below';
                }

                if (!empty($oldConfigs['addthis_below_showon_excerpts'])) {
                    if (!empty($oldConfigs['addthis_below_showon_home'])) {
                        $toolSettings['elements'][] = '.at-below-post-homepage';
                    }
                    if (!empty($oldConfigs['addthis_below_showon_archives'])) {
                        $toolSettings['elements'][] = '.at-below-post-arch-page';
                    }
                    if (!empty($oldConfigs['addthis_below_showon_categories'])) {
                        $toolSettings['elements'][] = '.at-below-post-cat-page';
                    }
                }
                if (!empty($oldConfigs['addthis_below_showon_posts'])) {
                    $toolSettings['elements'][] = '.at-below-post';
                }
                if (!empty($oldConfigs['addthis_below_showon_pages'])) {
                    $toolSettings['elements'][] = '.at-below-post-page';
                }

                $toolSettings['enabled'] = (boolean)$oldConfigs['addthis_below_enabled'];
                $toolSettings['auto_personalization'] = (boolean)$oldConfigs['below_auto_services'];
                $toolSettings['toolName'] = 'Sharing Buttons Below Content';
                $toolSettings['widgetId'] = 'below';
                if ($oldConfigs['below'] === 'custom_string') {
                    $gooConfigs['html'][$toolSettings['widgetId']] = $toolSettings;
                } else {
                    $newConfigs[$toolSettings['widgetId']] = $toolSettings;
                }
            }

            //
            // SIDEBAR SETTINGS MIGRATION
            //
            if (!empty($oldConfigs['addthis_sidebar_count'])) {
                $toolSettings = array(
                    'id' => 'shfs',
                    'auto_personalization' => true,
                    'toolName'             => 'Sharing Sidebar',
                    'widgetId'             => 'sidebar',
                    'mobilePosition'       => 'hide',
                    'mobileButtonSize'     => 'large',
                    'style'                => 'modern',
                    'counts'               => 'none',
                    'enabled'              => (boolean)$oldConfigs['addthis_sidebar_enabled'],
                    'theme'                => strtolower($oldConfigs['addthis_sidebar_theme']),
                    'desktopPosition'      => strtolower($oldConfigs['addthis_sidebar_position']),
                    'numPreferredServices' => (int)$oldConfigs['addthis_sidebar_count'],
                    'templates'            => array(),
                );

                if (!empty($oldConfigs['addthis_sidebar_showon_home'])) {
                    $toolSettings['templates'][] = 'home';
                }
                if (!empty($oldConfigs['addthis_sidebar_showon_posts'])) {
                    $toolSettings['templates'][] = 'posts';
                }
                if (!empty($oldConfigs['addthis_sidebar_showon_pages'])) {
                    $toolSettings['templates'][] = 'pages';
                }
                if (!empty($oldConfigs['addthis_sidebar_showon_archives'])) {
                    $toolSettings['templates'][] = 'archives';
                }
                if (!empty($oldConfigs['addthis_sidebar_showon_categories'])) {
                    $toolSettings['templates'][] = 'categories';
                }
                $sidebarTemplates = $toolSettings['templates'];

                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            //
            // MOBILE SHARING TOOLBAR SETTINGS MIGRATION
            //
            if (!empty($oldConfigs['addthis_mobile_toolbar_numPreferredServices'])) {
                $toolSettings = array(
                    'id'                   => 'shfs',
                    'auto_personalization' => true,
                    'toolName'             => 'Mobile Sharing Toolbar',
                    'widgetId'             => 'mobileshare',
                    'desktopPosition'      => 'hide',
                    'mobileButtonSize'     => 'large',
                    'style'                => 'modern',
                    'enabled'              => (boolean)$oldConfigs['addthis_mobile_toolbar_enabled'],
                    'theme'                => 'transparent',
                    'mobilePosition'       => strtolower($oldConfigs['addthis_mobile_toolbar_position']),
                    'numPreferredServices' => (int)$oldConfigs['addthis_mobile_toolbar_numPreferredServices'],
                    'templates'            => array(),
                );

                if (!empty($oldConfigs['addthis_mobile_toolbar_counts'])) {
                    $toolSettings['counts'] = 'one';
                } else {
                    $toolSettings['counts'] = 'none';
                }

                if (!empty($oldConfigs['addthis_mobile_toolbar_showon_home'])) {
                    $toolSettings['templates'][] = 'home';
                }
                if (!empty($oldConfigs['addthis_mobile_toolbar_showon_posts'])) {
                    $toolSettings['templates'][] = 'posts';
                }
                if (!empty($oldConfigs['addthis_mobile_toolbar_showon_pages'])) {
                    $toolSettings['templates'][] = 'pages';
                }
                if (!empty($oldConfigs['addthis_mobile_toolbar_showon_archives'])) {
                    $toolSettings['templates'][] = 'archives';
                }
                if (!empty($oldConfigs['addthis_mobile_toolbar_showon_categories'])) {
                    $toolSettings['templates'][] = 'categories';
                }

                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            //
            // OLD MOBILE TOOLBAR SETTINGS MIGRATION
            //
            if (!empty($oldConfigs['addthis_sidebar_enabled'])
                && empty($oldConfigs['addthis_mobile_toolbar_enabled'])
            ) {
                $toolSettings = array(
                    'id'                => 'smlmo',
                    'widgetId'          => 'mobile',
                    'enabled'           => true,
                    'follow'            => 'off',
                    'buttonBarPosition' => 'bottom',
                    'templates'         => $sidebarTemplates,
                );

                if (strtolower($oldConfigs['addthis_sidebar_theme']) === 'transparent') {
                    $toolSettings['buttonBarTheme'] = 'light';
                } else {
                    $toolSettings['buttonBarTheme'] = strtolower($oldConfigs['addthis_sidebar_theme']);
                }

                $newConfigs[$toolSettings['widgetId']] = $toolSettings;
            }

            if (!empty($newConfigs)) {
                $this->saveConfigs($newConfigs);
            }

            $this->globalOptionsObject->saveConfigs($gooConfigs);
        }

        /**
         * Upgrade from Share Buttons by AddThis to 6.1.0,
         * Follow Buttons by AddThis to 4.1.0,
         * Related Posts by AddThis to 2.1.0,
         * Smart Layers by AddThis to 3.1.0 and
         * Website Tools by AddThis to 3.1.0
         *
         * Deletes Share Buttons by WordPress 4.0/5.0 addthis_run_once flag
         *
         * @return null
         */
        protected function upgradeIterative5() {
            if (get_option('addthis_run_once')) {
                delete_option('addthis_run_once');
            }
        }
    }
}