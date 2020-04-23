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

if (!class_exists('AddThisGlobalOptionsCustomHtmlTool')) {
    /**
     * AddThis' tool class for putting custom HTML above or below content
     *
     * @category   GlobalOptions
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisGlobalOptionsCustomHtmlTool extends AddThisTool
    {
        protected $featureClassName = 'AddThisGlobalOptionsFeature';
        public $prettyName = 'AddThis Custom HTML';

        protected $defaultConfigs = array(
            'enabled'               => false,
            'html'                  => '',
            'id'                    => 'html',
        );

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
                            $output[$field] = (boolean)$value;
                            break;
                        case 'elements':
                            if (is_array($value) && !empty($value)) {
                                $output[$field] = array();
                                foreach ($value as $service) {
                                    $output[$field][] = sanitize_text_field($service);
                                }
                            }
                            break;
                        case 'toolName':
                        case 'widgetId':
                        case 'id':
                            $output[$field] = sanitize_text_field($value);
                            break;
                        case 'html':
                            $output[$field] = $value;
                            break;
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }

        /**
         * This must be public as it's used in the tool's widget
         *
         * Returns HTML for adding the script onto a page
         *
         * @param array $args     settings for this widget (we only use
         * widget_id)
         * @param array $instance settings for this particular tool, if not
         * being used from the tool settings (a widget instance)
         *
         * @return string this should be valid html
         */
        public function getInlineCode($args = array(), $instance = array())
        {
            if (is_feed()) {
                return '';
            }

            $configs = $this->getFeatureConfigs();

            $html = '';

            return $html;
        }

        /**
         * This must be public as it's used in a callback for add_shortcode
         *
         * Returns HTML to use to replace a short tag for this tool. Includes
         * tags to identify its from a short code.
         *
         * @return string this should be valid html
         */
        public function getInlineCodeForShortCode()
        {
            $html  = '<!-- Created with a shortcode from an AddThis plugin -->';
            $html .= '<!-- tool name: ' . $this->prettyName . ' -->';
            $html .= $this->getInlineCode();
            $html .= '<!-- End of short code snippet -->';

            return $html;
        }
    }
}