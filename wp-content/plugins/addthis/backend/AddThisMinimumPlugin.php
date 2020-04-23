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

require_once 'AddThisPlugin.php';

if (!class_exists('AddThisMinimumPlugin')) {
    /**
     * AddThis' plugin for users who will only be configuring tools via AddThis'
     * dashboard at AddThis.com
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Plugin
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisMinimumPlugin extends AddThisPlugin
    {
        protected $version = '3.2.6';
        protected $name = 'Website Tools by AddThis';
        protected $productPrefix = 'wpwt';
        protected $pluginSlug = 'addthis-all';

        protected $settingsLinkObject = 'globalOptionsObject';

        /**
         * This must be public as it's called from bootstrap.php
         *
         * This bootstraps this plugin into WordPress, including adding our
         * JavaScript and CSS onto relevant pages and calling the bootstrap
         * function for every enabled feature
         *
         * This calls the parent, then registers the content and excerpt filters
         * of disabled features (if they're enabled, then that parent function
         * would have taken care of this already).
         *
         * @return null
         */
        public function bootstrap()
        {
            parent::bootstrap();

            foreach ($this->features as $feature => $info) {
                $objectVariable = $feature . 'Object';
                $enabledVariable = $feature . 'Status';
                $enabledByPlugin = $this->$enabledVariable;

                if (!$enabledByPlugin) {
                    $this->$objectVariable->registerContentFilters();
                    $this->$objectVariable->registerExcerptFilters();
                    $this->$objectVariable->registerAmpHooks();
                    add_action('widgets_init', array($this, 'registerWidgets'));
                }
            }
        }
    }
}
