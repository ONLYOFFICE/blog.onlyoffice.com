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

require_once 'AddThisGlobalOptionsFeature.php';

if (!class_exists('AddThisTool')) {
    /**
     * AddThis' root parent class for all its tools. These objects know how to
     * render specific tools onto pages and how to store their configurations.
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisTool
    {
        public $layersClass = 'addthis_you_forgot_to_set_the_addthis_layers_class';
        public $prettyName = 'You forgot to set a pretty name';
        public $addedDefaultValue = false;
        protected $defaultConfigs = array(
            'enabled' => false,
        );
        public $contentFiltersEnabled = false;

        protected $globalOptionsClassName = 'AddThisGlobalOptionsFeature';
        protected $globalOptionsObject = null;
        protected $featureClassName = 'YouDidNotSetAFeatureClass';
        protected $featureObject = null;
        protected $featureConfigs = null;
        protected $globalOptionsConfigs = null;
        protected $toolConfigs = null;

        public $defaultTheme = 'transparent';

        /**
         * The constructor.
         *
         * @param object                      $featureObject       the object
         * for this tool's feature family. Optional.
         * @param AddThisGlobalOptionsFeature $globalOptionsObject the object
         * for the Global Options feature. Optional.
         *
         * @return null
         */
        public function __construct(
            $featureObject = null,
            $globalOptionsObject = null
        ) {
            if (is_object($globalOptionsObject)) {
                $this->globalOptionsObject = $globalOptionsObject;
            }

            if (is_object($featureObject)) {
                $this->featureObject = $featureObject;
            }
        }

        /**
         * Returns this tool's feature object (mostly used to get database
         * settings). If the object isn't already populated in a variable in
         * this class, it will attempt to create it.
         *
         * @return object|null
         */
        public function getFeatureObject()
        {
            if (!is_object($this->featureObject)) {
                if (class_exists($this->featureClassName)) {
                    $goo = $this->getGlobalOptionsObject();
                    $this->featureObject = new $this->featureClassName($goo);
                    $this->featureObject->getConfigs();
                } else {
                    error_log(__METHOD__ . ' class ' . $this->featureClassName . ' does not exists.');
                }
            }

            return $this->featureObject;
        }

        /**
         * Returns the Global Options feature object. If the object isn't
         * already populated in a variable in this class, it will attempt to
         * create it.
         *
         * @return AddThisGlobalOptionsFeature|null an object of class
         * AddThisGlobalOptionsFeature or null on failure
         */
        public function getGlobalOptionsObject()
        {
            if (!is_object($this->globalOptionsObject)) {
                if (class_exists($this->globalOptionsClassName)) {
                    $goo = new $this->globalOptionsClassName();
                    $goo->getConfigs();
                    $this->globalOptionsObject = $goo;
                } else {
                    error_log(__METHOD__ . ' class ' . $this->globalOptionsClassName . ' does not exists.');
                }
            }

            return $this->globalOptionsObject;
        }

        /**
         * Retrieves the settings for Global Options
         *
         * @return array an associative array
         */
        public function getGlobalOptionsConfigs()
        {
            if (is_null($this->globalOptionsConfigs)) {
                $this->getGlobalOptionsObject();

                if (is_object($this->globalOptionsObject)) {
                    $configs = $this->globalOptionsObject->getConfigs();
                    $this->globalOptionsConfigs = $configs;
                }
            }

            return $this->globalOptionsConfigs;
        }

        /**
         * Retrieves the settings for this feature family
         *
         * @return array an associative array
         */
        public function getFeatureConfigs()
        {
            if (is_null($this->featureConfigs)) {
                $this->getFeatureObject();

                if (is_object($this->featureObject)) {
                    $this->featureConfigs = $this->featureObject->getConfigs();
                }
            }

            return $this->featureConfigs;
        }

        /**
         * Retrieves the settings for this particular tool
         *
         * @return array an associative array
         */
        public function getToolConfigs()
        {
            $this->getFeatureConfigs();

            if (isset($this->settingsSubVariableName)
                && isset($this->featureConfigs[$this->settingsSubVariableName])
            ) {
                $toolKey = $this->settingsSubVariableName;
                $this->toolConfigs = $this->featureConfigs[$toolKey];
            }

            return $this->toolConfigs;
        }

        /**
         * Checks if this tool has been enabled by the user.
         *
         * @return boolean true for enabled, false for disabled
         */
        public function isEnabled()
        {
            $this->getToolConfigs();

            if (!empty($this->toolConfigs['enabled'])) {
                return true;
            }

            return false;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_share
         *
         * @return array an associative array
         */
        public function getAddThisShare()
        {
            $toolShare = array();
            return $toolShare;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_config
         *
         * @return array an associative array
         */
        public function getAddThisConfig()
        {
            $toolConfig = array();
            return $toolConfig;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @return array an associative array
         */
        public function getAddThisLayers()
        {
            $toolLayers = array();
            return $toolLayers;
        }

        /**
         * Returns a string describing the type of template we're currently on
         *
         * @return string|null home, archives, categories, pages, post or false
         * on unknown
         */
        public static function currentTemplateType()
        {
            global $post;

            // determine page type
            if (is_home() || is_front_page()) {
                $type = 'home';
            } elseif (is_archive()) {
                $type = 'archives';
                if (is_category()) {
                    $type = 'categories';
                }
            } elseif (is_object($post)
                && ($post instanceof WP_Post)
                && !empty($post->ID)
                && is_page($post->ID)
            ) {
                $type = 'pages';
            } elseif (is_single()) {
                $type = 'posts';
            } else {
                $type = false;
            }

            return $type;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes form input for a  tool sub settings variable, manipulates
         * it, and returns the variables that should be saved to the database.
         * All tools should override thie function, as all it does here is
         * sanitize anything given to it.
         *
         * @param array   $input             An associative array of values
         * input for this tools' settings
         * @param boolean $addDefaultConfigs Whether to populate in default
         * values for missing fields
         *
         * @return array A cleaned up associative array of settings specific to
         * this tool.
         */
        public function sanitizeSettings($input, $addDefaultConfigs = true)
        {
            $output = array();

            if (is_array($input)) {
                foreach ($input as $field => $value) {
                    if (!empty($value)) {
                        $output[$field] = sanitize_text_field($value);
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes configs and adds default values where not present
         *
         * @param array $configs An associative array of values input for this
         * tools' settings
         *
         * @return array An associative array of settings specific to this tool
         * with added defaults where not already present.
         */
        public function addDefaultConfigs($configs)
        {
            if (is_array($configs)) {
                foreach ($this->defaultConfigs as $field => $defaultValue) {
                    if (!isset($configs[$field])) {
                        $configs[$field] = $defaultValue;
                        $this->addedDefaultValue = true;
                    }
                }
            } else {
                $configs = $this->defaultConfigs;
                $this->addedDefaultValue = true;
            }

            return $configs;
        }

        /**
         * This takes configs a list of templates and returns whether the
         * current pages matches any of them.
         *
         * @param array $enabledTemplates An array of templates types
         *
         * @return boolean true for a match, false for a miss
         */
        public function enabledOnTemplate($enabledTemplates)
        {
            $templateType = $this->currentTemplateType();

            if (!is_array($enabledTemplates) ||
                !in_array($templateType, $enabledTemplates)
            ) {
                $configs = $this->getGlobalOptionsConfigs();
                if (empty($configs['enqueue_local_settings'])) {
                    return false;
                }
            }

            return true;
        }
    }
}