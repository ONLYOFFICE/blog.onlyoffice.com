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

require_once 'AddThisFeature.php';
require_once 'AddThisRecommendedContentFooterTool.php';
require_once 'AddThisRecommendedContentWhatsNextTool.php';

if (!class_exists('AddThisRecommendedContentFeature')) {
    /**
     * Class for adding AddThis recommended content tools to WordPress
     *
     * @category   RecommendedContent
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisRecommendedContentFeature extends AddThisFeature
    {
        //addthis_follow_settings, widget_addthis-follow-widget, addthis_settings
        protected $oldConfigVariableName = 'addthis_settings';
        protected $settingsVariableName = 'addthis_recommended_content_settings';
        protected $settingsPageId = 'addthis_recommended_content';
        protected $name = 'Related Posts';
        protected $RecommendedContentFooterToolObject = null;
        protected $RecommendedContentWhatsNextToolObject = null;

        protected $filterPriority = 2;
        protected $filterNamePrefix = 'addthis_recommended_content_';
        protected $enableBelowContent = true;

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array(
            'startUpgradeAt',
        );

        // used for temporary compatability with the Sharing Buttons plugin
        public $globalLayersJsonField = 'addthis_layers_recommended_json';
        public $globalEnabledField = 'recommended_content_feature_enabled';

        // require the files with the tool classes at the top of this
        // file for each tool
        protected $tools = array(
            'RecommendedContentFooter',
            'RecommendedContentWhatsNext',
        );

        /**
         * Builds the class used for recommended content below content on posts.
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
            $pageTypeClean = AddThisTool::currentTemplateType();
            if ($pageTypeClean == 'posts') {
                $toolClass = 'at-below-post-recommended';
            } else {
                $toolClass = false;
            }

            $toolClass = $this->applyToolClassFilters($toolClass, $location, $track);
            return $toolClass;
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

            // parse out values from old plugin for new plugin
            if (isset($jsonDecoded['whatsnext'])) {
                // enable the what's next tool
                if (!isset($this->configs['smlwn'])) {
                    $this->configs['smlwn'] = array();
                }

                $this->configs['smlwn']['enabled'] = true;

                if (isset($jsonDecoded['theme'])) {
                    $this->configs['smlwn']['theme'] = $jsonDecoded['theme'];
                }
            }

            if (isset($jsonDecoded['recommended'])) {
                // enable the recommended footer
                if (!isset($this->configs['smlre'])) {
                    $this->configs['smlre'] = array();
                }

                $this->configs['smlre']['enabled'] = true;

                if (isset($jsonDecoded['recommended']['title'])) {
                    $this->configs['smlre']['title'] = $jsonDecoded['recommended']['title'];
                }

                if (isset($jsonDecoded['theme'])) {
                    $this->configs['smlre']['theme'] = $jsonDecoded['theme'];
                }
            }
        }

        /**
         * Upgrade from Smart Layers by AddThis 2.0.0 to 2.1.0
         * Upgrade from Related Posts by AddThis 1.0.0 to 1.1.0
         * Upgrade from Website Tools by AddThis 1.1.2 to 1.2.0
         *
         * @return null
         */
        protected function upgradeIterative2()
        {
            $horizontalRecWidgets = AddThisSharingButtonsFeature::upgradeIterative2ReformatWidgets(
                'addthis_recommended_horizontal_widget',
                'addthis_recommended_horizontal'
            );

            $verticalRecWidgets = AddThisSharingButtonsFeature::upgradeIterative2ReformatWidgets(
                'addthis_recommended_vertical_widget',
                'addthis_recommended_vertical'
            );

            $newWidgets = array_merge(
                $horizontalRecWidgets,
                $verticalRecWidgets
            );

            $widgetIdMapping = AddThisSharingButtonsFeature::upgradeIterative2SaveWidgets($newWidgets);
            AddThisFollowButtonsFeature::upgradeIterative1MigrateSidebarWidgetIds($widgetIdMapping);
        }
    }
}