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

if (!class_exists('AddThisFollowButtonsPlugin')) {
    /**
     * AddThis' plugin for follow buttons
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Plugin
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsPlugin extends AddThisPlugin
    {
        protected $version = '4.2.6';
        protected $name = 'Follow Buttons by AddThis';
        protected $productPrefix = 'wpf';
        protected $pluginSlug = 'addthis-follow';

        protected $followButtonsStatus = true;
        protected $settingsLinkObject = 'followButtonsObject';
    }
}
