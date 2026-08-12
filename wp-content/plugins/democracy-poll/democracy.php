<?php
/**
 * Plugin Name: Democracy Poll
 * Description: Allows creation of democratic polls. Visitors can vote for multiple answers and add their own answers.
 *
 * Author: Kama
 * Author URI: https://wp-kama.com
 * Plugin URI: https://wp-kama.ru/67
 *
 * Text Domain: democracy-poll
 * Domain Path: /languages/build
 *
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * Version: 6.5.0
 */

namespace DemocracyPoll;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoload.php';


/// SETUP

$container = container( new Libs\Container() ); // set the container globally
$container->set( Plugin::class, [ 'main_file' => __FILE__ ] );


/// UNIT

register_activation_hook( __FILE__, [ System\Activator::class, 'activate' ] );

/**
 * NOTE: Init the plugin later on the 'after_setup_theme' hook to
 * run current_user_can() later to avoid possible issues.
 */
add_action( 'after_setup_theme', '\DemocracyPoll\init_plugin' );


/// FUNCTIONS

function init_plugin(): void {
	$initor = container()->get( System\Plugin_Initor::class ); /** @see System\Plugin_Initor::__construct() */
    $initor->init_plugin();
}

/**
 * Returns the container instance.
 * Or sets the container instance globally if a container is provided.
 */
function container( Libs\Container $container = null ): Libs\Container {
	static $c;
	return $container ? ( $c = $container ) : $c;
}

/**
 * Gives access to the plugin instance stored in the container.
 *
 * @deprecated Use {@see container()} instead.
 */
function plugin(): Plugin {
	return container()->get( Plugin::class );
}

/**
 * Helper function to conveniently get the plugin options.
 *
 * @deprecated Use {@see container()} instead.
 */
function options(): Options {
	return container()->get( Options::class );
}
