<?php
/*
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2019 Add This, LLC                                    |
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

/**
 * Author URI:  http://www.addthis.com
 * Author:      The AddThis Team
 * Domain Path: /frontend/build/l10n
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * License:     GNU General Public License v2
 * Plugin Name: Share Buttons by AddThis
 * Plugin URI:  https://wordpress.org/plugins/addthis/
 * Text Domain: addthis-backend
 * Version:     6.2.6
 * Description: Set up sharing tools from AddThis right in WordPress. With AddThis you can see how your users are engaging with your content, provide a personalized experience for each user and encourage them to share your content in their social networks. Follow tools, recommended content, conversion tools and social analytics available to registered accounts at addthis.com.
 */

require_once 'backend/functions.php';
require_once 'backend/AddThisSharingButtonsPlugin.php';

$baseName = plugin_basename(__FILE__);
$addThisSharingButtonsPluginsObject = new AddThisSharingButtonsPlugin($baseName);
$addThisSharingButtonsPluginsObject->bootstrap();
