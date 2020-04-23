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
require_once 'AddThisTool.php';
require_once 'AddThisFollowButtonsFeature.php';

if (!class_exists('AddThisFollowButtonsToolParent')) {
    /**
     * AddThis' root parent class for all follow button tools.
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsToolParent extends AddThisTool
    {
        public $availableSizes = array();
        protected $featureClassName = 'AddThisFollowButtonsFeature';

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes form input for a  tool sub settings variable, manipulates
         * it, and returns the variables that should be saved to the database.
         *
         * @param array   $input             An associative array of values
         * input for this tools' settings
         * @param boolean $addDefaultConfigs Whether to populate in default
         * values for missing fields
         *
         * @return array A cleaned up associative array of settings specific to
         *               this feature.
         */
        public function sanitizeSettings($input, $addDefaultConfigs = true)
        {
            $output = array();

            if (is_array($input)) {
                // just don't touch settings in conflict mode
                if (!empty($input['conflict'])) {
                    return $input;
                }

                foreach ($input as $field => $value) {
                    switch ($field) {
                        case 'services':
                            if (is_array($value)) {
                                foreach ($value as $service => $username) {
                                    if (!empty($username)) {
                                        $output['services'][$service] = sanitize_text_field($username);
                                    }
                                }
                            }
                            break;
                        case 'title':
                        case 'size':
                            $output[$field] = sanitize_text_field($value);
                            break;
                        case 'enabled':
                        case 'thankyou':
                            $output[$field] = (boolean)$value;
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
         *               with added defaults where not already present.
         */
        public function addDefaultConfigs($configs)
        {
            $configs = parent::addDefaultConfigs($configs);

            if (empty($configs['services'])) {
                $configs['services'] = new stdClass();
            }

            return $configs;
        }

        /**
         * Formats the service information for follow buttons as the AddThis
         * SmartLayers API would want it.
         *
         * @param array $input the services and accounts as an associative array
         *
         * @return array[] the services as an array of associative arrays with
         * usertype and service keys
         */
        public static function formatServicesForAddThisLayers($input = array())
        {
            $output = array();
            if (is_array($input)) {
                foreach ($input as $service => $id) {
                    if (empty($id)) {
                        continue;
                    }

                    $parts = explode('_', $service);

                    $serviceInfo = array('id' => $id);
                    if ($parts[0] === 'facebook') {
                        $serviceInfo['service'] = 'facebook';
                    } elseif (count($parts) > 1) {
                        $serviceInfo['usertype'] = array_pop($parts);
                        $serviceInfo['service'] = implode($parts, '_');
                    } else {
                        $serviceInfo['usertype'] = 'id';
                        $serviceInfo['service'] = $service;
                    }

                    $output[] = $serviceInfo;
                }
            }

            return $output;
        }
    }
}