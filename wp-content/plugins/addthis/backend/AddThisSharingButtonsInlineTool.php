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

if (!class_exists('AddThisSharingButtonsInlineTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Sharing Button tools
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsInlineTool extends AddThisSharingButtonsToolParent
    {
        public $layersClass = 'addthis_sharing_toolbox';
        public $prettyName = 'Sharing Buttons';
        //pco shin
        public $layersApiProductName = 'sharetoolbox';

        protected $defaultConfigs = array();

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @param array $input array of settings (used with widgets)
         *
         * @return array an associative array
         */
        public function getAddThisLayers($input = array())
        {
            $output = array();
            if (empty($input['enabled'])) {
                return $output;
            }

            if (isset($input['style']) && $input['style'] === 'original') {
                $smartLayersConfigs = array(
                    'numPreferredServices' => 5,
                    'thirdPartyButtons'    => true,
                );

                if (!empty($input['originalServices']) &&
                    is_array($input['originalServices'])
                ) {
                    $smartLayersConfigs['services'] = implode(',', $input['originalServices']);
                }
            } else {
                $smartLayersConfigs = array(
                    'numPreferredServices' => $input['numPreferredServices'],
                    'counts'               => $input['counts'],
                    'size'                 => $input['size'],
                    'style'                => $input['style'],
                    'shareCountThreshold'  => 0,
                );


                if (empty($input['auto_personalization']) &&
                    !empty($input['services']) &&
                    is_array($input['services'])
                ) {
                    $smartLayersConfigs['services'] = implode(',', $input['services']);
                }
            }

            if (!empty($input['elements']) && is_array($input['elements'])) {
                $smartLayersConfigs['elements'] = implode(',', $input['elements']);
            }

            if (isset($input['counts']) &&
                $input['counts'] === 'jumbo' &&
                (isset($input['style']) || $input['style'] !== 'original')
            ) {
                $smartLayersPco = 'jumboshare';
                $smartLayersConfigs['id'] = 'jumboshare';
            } elseif (isset($input['style']) && $input['style'] === 'responsive') {
                $smartLayersPco = 'responsiveshare';
                $smartLayersConfigs['id'] = 'responsiveshare';
            } else {
                $smartLayersPco = 'sharetoolbox';
            }

            $output = array($smartLayersPco => $smartLayersConfigs);
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
                        case 'services':
                        case 'originalServices':
                        case 'elements':
                            if (is_array($value) && !empty($value)) {
                                $output[$field] = array();
                                foreach ($value as $service) {
                                    $output[$field][] = sanitize_text_field($service);
                                }
                            }
                            break;
                        case 'numPreferredServices':
                            $output[$field] = (int)$value;
                            break;
                        case 'size':
                            if ($value === '16px' ||
                                $value === '20px' ||
                                $value === '32px'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'style':
                            if ($value === 'responsive' ||
                                $value === 'fixed' ||
                                $value === 'original'
                            ) {
                                $output[$field] = $value;
                            }
                            break;
                        case 'counts':
                            if ($value === 'none' ||
                                $value === 'each' ||
                                $value === 'one' ||
                                $value === 'jumbo'
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