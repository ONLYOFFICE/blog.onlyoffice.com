<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our transients.
 *
 * @since 4.0.13
 */
class Transients {
	/**
	 * Our transients prefix.
	 *
	 * @since 4.0.13
	 *
	 * @var string
	 */
	private $prefix = '_aioseo_cache_';

	/**
	 * Our cached transients.
	 *
	 * @since 4.0.13
	 *
	 * @var array
	 */
	private static $transients = [];

	/**
	 * Returns the given transient if it exists.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $name The transient name.
	 * @return mixed        The value or false if the transient does not exist.
	 */
	public function get( $name ) {
		if ( isset( self::$transients[ $name ] ) ) {
			return self::$transients[ $name ];
		}

		$transientName           = $this->prefix . $name;
		$expirationTransientName = $this->prefix . 'expiration_' . $name;

		$value      = get_option( $transientName );
		$expiration = get_option( $expirationTransientName );
		if ( ! $expiration ) {
			// Transient doesn't appear correct.
			delete_option( $transientName );
			return false;
		}

		if ( $expiration < time() ) {
			delete_option( $transientName );
			delete_option( $expirationTransientName );
			return false;
		}

		self::$transients[ $name ] = $value;
		return $value;
	}

	/**
	 * Updates the given transient or creates it if it doesn't exist.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $name       The transient name.
	 * @param  mixed  $value      The value.
	 * @param  int    $expiration The expiration time. Defaults to 24 hours.
	 * @return void
	 */
	public function update( $name, $value, $expiration = DAY_IN_SECONDS ) {
		update_option( $this->prefix . $name, $value, false );
		update_option( $this->prefix . 'expiration_' . $name, time() + $expiration, false );
		self::$transients[ $name ] = $value;
	}

	/**
	 * Deletes the given transient.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $name The transient name.
	 * @return void
	 */
	public function delete( $name ) {
		delete_option( $this->prefix . $name );
		delete_option( $this->prefix . 'expiration_' . $name );
	}

	/**
	 * Clears all of our transients.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function clearCache() {
		$table = aioseo()->db->db->options;
		aioseo()->db->db->query( "DELETE FROM {$table} WHERE option_name LIKE '\_aioseo\_cache\_%'" );
	}
}