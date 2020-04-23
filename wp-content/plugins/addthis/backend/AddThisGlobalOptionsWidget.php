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
if (!class_exists('AddThisGlobalOptionsWidget')) {
    /**
     * WordPress widget class for AddThis Horizontal Follow Buttons
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools\Widgets
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisGlobalOptionsWidget extends WP_Widget
    {
        private $widgetBaseId = 'addthis_global_options_widget';
        private $widgetName = 'AddThis Script';
        private $widgetDescription = 'If your theme is not adding the AddThis script (addthis_widget.js) onto your pages, try adding this widget.';
        private $toolClassName = 'AddThisGlobalOptionsTool';

        /**
         * Bootstraps the widget for WordPress. It determines its tool settings
         * class name, and passes that string to its parent constructor.
         *
         * @return null
         */
        public function __construct()
        {
            if (!class_exists($this->toolClassName)) {
                error_log(__METHOD__ . ' class ' . $this->toolClassName . ' does not exists.');
                return null;
            }

            $toolClass = new $this->toolClassName();
            $this->toolClass = $toolClass;

            $name = __($this->widgetName, AddThisFeature::$l10n_domain);
            $description = __($this->widgetDescription, AddThisFeature::$l10n_domain);

            $widgetOptions = array(
                'description' => $description,
            );

            $controlOptions = array();

            parent::__construct(
                $this->widgetBaseId,
                $name,
                $widgetOptions,
                $controlOptions
            );
        }

        /**
         * Prints out HTML for the options form in the WordPress admin Dashboard
         *
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return null
         */
        public function form($instance)
        {
            $description = __($this->widgetDescription, AddThisFeature::$l10n_domain);
            $goo = $this->toolClass->getGlobalOptionsObject();

            $html = '<p>'.$description.'</p>';
            $html .= '<p>'.$goo->getSettingsLinkHtmlForWidgets().'</p>';
            $html .= '<p>'.$goo->eulaText('Save').'</p>';

            echo $html;
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance options values just sent to be saved
         * @param array $old_instance previously options values (from database)
         *
         * @return array
         */
        public function update($new_instance, $old_instance)
        {
            $instance = array();
            return $instance;
        }

        /**
         * Prints out HTML for the content of the widget
         *
         * @param array $args     Widget arguments
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return null
         */
        public function widget($args, $instance)
        {

            $addThisToolCode = $this->toolClass->getInlineCode($args, $instance);
            if (!isset($args['widget_name'])) {
                $args['widget_name'] = 'no name';
            }

            $html  = '<!-- Widget added by an AddThis plugin -->';
            $html .= '<!-- widget name: ' . $args['widget_name'] . ' -->';
            $html .= '<!-- tool class: ' . $this->toolClassName . ' -->';
            $html .= $addThisToolCode;
            $html .= '<!-- End of widget -->';

            echo $html;
        }
    }
}