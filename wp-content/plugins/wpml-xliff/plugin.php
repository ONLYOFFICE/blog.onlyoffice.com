<?php 
/*
Plugin Name: WPML XLIFF
Plugin URI: https://wpml.org/
Description: Import and Export XLIFF files for translation
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
Version: 0.9.7
*/

if(defined('WPML_XLIFF_VERSION')) return;

define('WPML_XLIFF_VERSION', '0.9.7');
define('WPML_XLIFF_PATH', dirname(__FILE__));

define('WPML_XLIFF_NEWLINES_REPLACE', 1); //
define('WPML_XLIFF_NEWLINES_ORIGINAL', 2); //

require WPML_XLIFF_PATH . '/inc/constants.inc';
require WPML_XLIFF_PATH . '/inc/wpml_xliff.class.php';