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

if (!class_exists('AddThisMaximumPlugin')) {
    /**
     * AddThis' plugin for sharing and follow buttons, as well a recommended
     * content. This is a replacement for the Smart Layers by AddThis plugin
     *
     * @category   SocialTools
     * @package    AddThisWordPress
     * @subpackage Plugin
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisMaximumPlugin extends AddThisPlugin
    {
        protected $version = '3.1.2';
        protected $name = 'Smart Layers by AddThis';
        protected $productPrefix = 'wpsl';
        protected $pluginSlug = 'addthis-smart-layers';

        protected $followButtonsStatus = true;
        protected $sharingButtonsStatus = true;
        protected $recommendedContentStatus = true;
        protected $settingsLinkObject = 'globalOptionsObject';
    }
}
