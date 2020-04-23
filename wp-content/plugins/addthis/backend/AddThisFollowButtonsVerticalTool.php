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

if (!class_exists('AddThisFollowButtonsVerticalTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Vertical Follow Button tools
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsVerticalTool extends AddThisFollowButtonsToolParent
    {
        public $layersClass = 'addthis_vertical_follow_toolbox';
        public $prettyName = 'Vertical Follow Buttons';
        public $settingsSubVariableName = 'flwv';
        public $layersApiProductName = 'followtoolbox';

        public $availableSizes = array(
            'large' => array(
                'displayName' => 'Large',
                'class'     => 'addthis_toolbox addthis_vertical_style addthis_32x32_style'
            ),
            'medium' => array(
                'displayName' => 'Medium',
                'class'     => 'addthis_toolbox addthis_vertical_style addthis_20x20_style'
            ),
            'small' => array(
                'displayName' => 'Small',
                'class'     => 'addthis_toolbox addthis_vertical_style addthis_16x16_style'
            ),
        );

        protected $defaultConfigs = array(
            'enabled'   => false,
            'size'      => 'large',
            'title'     => '',
            'thankyou'  => true,
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
                'services'    => array(),
                'orientation' => 'vertical',
                'size'        => $configs['size'],
                'thankyou'    => $configs['thankyou'],
                'title'       => $configs['title'],
                'elements'    => '.'.$this->layersClass,
            );

            $layers['services'] = AddThisFollowButtonsToolParent::formatServicesForAddThisLayers($configs['services']);

            $result = array($this->layersApiProductName => $layers);
            return $result;
        }
    }
}