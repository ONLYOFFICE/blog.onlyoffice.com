<?php 
/**
 * Basic WordPress settings.  
 *
* The script to create wp-config.php uses this file in the process
* settings. You don't have to use the web interface, you can
* copy the file to "wp-config.php" and fill in the values manually.
 *
 * This file contains the following options:
 * 
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'name-database');

/** MySQL database username */
define('DB_USER', 'user-database');

/** MySQL database password */
define('DB_PASSWORD', 'password-database');

/** MySQL hostname */
define('DB_HOST', 'host1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('WEB_ROOT_URL', 'root1');

define( 'WP_AUTO_UPDATE_CORE', false );

define('WP_CONTENT_URL', 'https://static-blog.teamlab.info/wp-content');

$GLOBALS['WEB_BLOG_FOLDER_URL'] = '/blog';

/**#@+
 * Unique keys and salts for authentication.
 *
 * Change the value of each constant to a unique phrase.
 * You can generate them using {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org key service}
 * You can change them to invalidate existing cookies. Users will need to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'some_key1' );
define( 'SECURE_AUTH_KEY',  'some_key2' );
define( 'LOGGED_IN_KEY',    'some_key3' );
define( 'NONCE_KEY',        'some_key4' );
define( 'AUTH_SALT',        'some_key5' );
define( 'SECURE_AUTH_SALT', 'some_key6' );
define( 'LOGGED_IN_SALT',   'some_key7' );
define( 'NONCE_SALT',       'some_key8' );

/**#@-*/

/**
 * Table prefix in the WordPress database.
 *
 * You can install multiple sites in one database if you use
 * different prefixes. Please enter only numbers, letters and underscores.
 */
$table_prefix = 'tm_';

/**
 * For Developers: WordPress Debug Mode.
 *
 * Change this value to true to enable display of notifications during development.
 * Plugin and theme developers are strongly encouraged to use WP_DEBUG
 * in their workspace.
 *
 * Information on other debugging constants can be found in the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );
define('WP_TEMP_DIR', dirname(__FILE__) . '/wp-content/temp/');
define ('WPLANG', '');

/* That's it, no further editing. Good luck! */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Initializes WordPress variables and includes files. */
require_once( ABSPATH . 'wp-settings.php' );
add_filter('xmlrpc_enabled', '__return_false'); 
