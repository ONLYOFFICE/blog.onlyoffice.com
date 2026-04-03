<?php
defined('ABSPATH') or die;

/**
 * Plugin Name: Fluent Forms
 * Description: Contact Form By Fluent Forms is the advanced Contact form plugin with drag and drop, multi column supported form builder plugin
 * Version: 6.2.0
 * Author: Contact Form - WPManageNinja LLC
 * Author URI: https://fluentforms.com
 * Plugin URI: https://wpmanageninja.com/wp-fluent-form/
 * License: GPLv2 or later
 * Text Domain: fluentform
 * Domain Path: /resources/languages
 */

defined('ABSPATH') or die;
defined('FLUENTFORM') or define('FLUENTFORM', true);
define('FLUENTFORM_DIR_PATH', plugin_dir_path(__FILE__));
define('FLUENTFORM_FRAMEWORK_UPGRADE', '4.3.22');
defined('FLUENTFORM_VERSION') or define('FLUENTFORM_VERSION', '6.2.0');
defined('FLUENTFORM_MINIMUM_PRO_VERSION') or define('FLUENTFORM_MINIMUM_PRO_VERSION', '6.0.0');

if (!defined('FLUENTFORM_HAS_NIA')) {
    define('FLUENTFORM_HAS_NIA', true);
}

return (function($_) {
    return $_(__FILE__);
})(
    require __DIR__.'/boot/app.php',
    require __DIR__.'/vendor/autoload.php'
);
