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
require_once 'AddThisFollowButtonsToolParent.php';

if (!class_exists('AddThisFollowButtonsHeaderTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Horizontal Follow Button tools
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsHeaderTool extends AddThisFollowButtonsToolParent
    {
        public $settingsSubVariableName = 'smlfw';
        public $layersApiProductName = 'follow';
        protected $defaultConfigs = array(
            'enabled'       => false,
            'theme'         => 'transparent',
            'responsive'    => 979,
            'offset'        => array(
                'location'      => 'top',
                'amount'        => 0,
                'unit'          => 'px',
            ),
            'thankyou'      => true,
            'title'         => '',
        );

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @param array $configs optional array of settings (used with widgets)
         *
         * @return array an associative array
         */
        public function getAddThisLayers($configs = array())
        {
            if (empty($configs)) {
                $configs = $this->getToolConfigs();
            }

            if (!$this->isEnabled()) {
                return array();
            }

            $layers = array(
                'services'   => array(),
                'title'      => $configs['title'],
                'theme'      => $configs['theme'],
                'thankyou'   => $configs['thankyou'],
                'responsive' => (int)$configs['responsive'].'px',
            );

            $layers['services'] = AddThisFollowButtonsToolParent::formatServicesForAddThisLayers($configs['services']);

            if (!empty($configs['offset']['location'])) {
                $location = $configs['offset']['location'];
                $amount = $configs['offset']['amount'];
                $unit = $configs['offset']['unit'];
                $layers['offset'][$location] = (int)$amount.$unit;
            }

            $result = array($this->layersApiProductName => $layers);
            return $result;
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
            $output = parent::sanitizeSettings($input, false);

            if (is_array($input)) {
                foreach ($input as $field => $value) {
                    switch ($field) {
                        case 'responsive':
                            $output[$field] = (int)$value['responsive'];
                            break;
                        case 'offset':
                            if (isset($value['location'])) {
                                $output[$field]['location'] = sanitize_text_field($value['location']);
                            }

                            if (!empty($value['amount'])) {
                                $output[$field]['amount'] = (int)$value['amount'];
                            }

                            if (!empty($value['unit'])) {
                                $output[$field]['unit'] = sanitize_text_field($value['unit']);
                            }
                            break;
                        case 'theme':
                            $output[$field] = sanitize_text_field($value);
                            break;
                        case '__hideOnHomepage':
                            $output[$field] = (boolean)$value;
                            break;
                        case '__hideOnUrls':
                            if (is_array($value)) {
                                foreach ($value as $urlPattern) {
                                    $output[$field][] = sanitize_text_field($urlPattern);
                                }
                            }
                            break;
                    }
                }
            }

            if (isset($output['size'])) {
                unset($output['size']);
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }
    }
}