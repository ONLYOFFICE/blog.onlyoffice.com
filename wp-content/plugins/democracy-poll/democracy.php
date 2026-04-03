<?php
/**
 * Plugin Name: Democracy Poll
 * Description: Allows creation of democratic polls. Visitors can vote for multiple answers and add their own answers.
 *
 * Author: Kama
 * Author URI: https://wp-kama.com/
 * Plugin URI: https://wp-kama.ru/67
 *
 * Text Domain: democracy-poll
 * Domain Path: /languages/build
 *
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * Version: 6.1.1
 */

namespace DemocracyPoll;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoload.php';

register_activation_hook( __FILE__, [ \DemocracyPoll\Utils\Activator::class, 'activate' ] );

/**
 * NOTE: Init the plugin later on the 'after_setup_theme' hook to
 * run current_user_can() later to avoid possible issues.
 */
add_action( 'after_setup_theme', '\DemocracyPoll\init_plugin' );

function init_plugin(): void {
	plugin()->initor->plugin_init();
}

/**
 * Gives access to the plugin instance and all it's components.
 */
function plugin(): Plugin {
	static $inst;
	$inst || $inst = new Plugin( __FILE__ );

	return $inst;
}

/**
 * Helper function to conveniently get the plugin options.
 */
function options(): Options {
	return plugin()->opt;
}
