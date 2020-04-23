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
require_once 'AddThisSharingButtonsToolParent.php';

if (!class_exists('AddThisSharingButtonsFloatingTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Mobile Sharing Toolbar tools
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsFloatingTool extends AddThisSharingButtonsToolParent
    {
        //pco shfs
        public $layersApiProductName = 'sharedock';

        protected $defaultConfigs = array();

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @param array $input array of settings
         *
         * @return array an associative array
         */
        public function getAddThisLayers($input = array())
        {
            $output = array();
            if (empty($input['enabled']) ||
                !$this->enabledOnTemplate($input['templates'])
            ) {
                return $output;
            }

            // setting up SmartLayers API confis for desktop version of this tool
            $desktopConfigs = array(
                'counts'               => $input['counts'],
                'numPreferredServices' => $input['numPreferredServices'],
                'mobile'               => false,
                'position'             => $input['desktopPosition'],
                'theme'                => $input['theme'],
            );

            if (isset($input['style'])) {
                if ($input['style'] === 'modern') {
                    $desktopPco = 'share';
                } elseif ($input['style'] === 'bordered') {
                    $desktopPco = 'customsidebar';
                    $desktopConfigs['offset'] = array('top' => '20%');
                    unset($desktopConfigs['theme']);
                }
            }

            if (empty($input['auto_personalization']) &&
                !empty($input['services'])
            ) {
                $desktopConfigs['services'] = implode(',', $input['services']);
            }

            if (!isset($input['desktopPosition'])
                || $input['desktopPosition'] !== 'hide'
            ) {
                $output[$desktopPco] = $desktopConfigs;
            }

            // setting up SmartLayers API confis for mobile version of this tool
            $mobileConfigs = array(
                'counts'               => $input['counts'],
                'numPreferredServices' => $input['numPreferredServices'],
                'mobileButtonSize'     => $input['mobileButtonSize'],
                'position'             => $input['mobilePosition'],
                'theme'                => $input['theme'],
            );

            if (empty($input['auto_personalization']) &&
                !empty($input['services'])
            ) {
                $mobileConfigs['services'] = implode(',', $input['services']);
            }

            if (isset($input['style'])) {
                if ($input['style'] === 'modern') {
                    $mobilePco = 'sharedock';
                } elseif ($input['style'] === 'bordered') {
                    $mobilePco = 'custommobilebar';
                    unset($mobileConfigs['theme']);
                }
            }

            if (!isset($input['mobilePosition'])
                || $input['mobilePosition'] !== 'hide'
            ) {
                $output[$mobilePco] = $mobileConfigs;
            }

            return $output;
        }

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
                foreach ($input as $field => $value) {
                    switch ($field) {
                        case 'enabled':
                        case 'auto_personalization':
                            $output[$field] = (boolean)$value;
                            break;
                        case 'mobilePosition':
                            if ($value === 'top' || $value === 'bottom' || $value === 'hide') {
                                $output[$field] = $value;
                            }
                            break;
                        case 'desktopPosition':
                            if ($value === 'left' || $value === 'right' || $value === 'hide') {
                                $output[$field] = $value;
                            }
                            break;
                        case 'templates':
                        case 'services':
                            if (is_array($value)) {
                                $output[$field] = array();
                                foreach ($value as $service) {
                                    $output[$field][] = sanitize_text_field($service);
                                }
                            }
                            break;
                        case 'numPreferredServices':
                            $output[$field] = (int)$value;
                            break;
                        case 'mobileButtonSize':
                            if ($value === 'small' ||
                                $value === 'medium' ||
                                $value === 'large'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'theme':
                            if ($value === 'light' ||
                                $value === 'gray' ||
                                $value === 'dark' ||
                                $value === 'transparent'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'style':
                            if ($value === 'modern' ||
                                $value === 'bordered'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'counts':
                            if ($value === 'none' ||
                                $value === 'each' ||
                                $value === 'one' ||
                                $value === 'both'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'toolName':
                        case 'widgetId':
                        case 'id':
                            $output[$field] = sanitize_text_field($value);
                            break;
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }
    }
}