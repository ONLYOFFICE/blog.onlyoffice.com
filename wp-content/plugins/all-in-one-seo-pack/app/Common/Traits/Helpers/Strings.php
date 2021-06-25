<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains string specific helper methods.
 *
 * @since 4.0.13
 */
trait Strings {
	/**
	 * Convert to snake case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to convert.
	 * @return string         The converted string.
	 */
	public function toSnakeCase( $string ) {
		$string[0] = strtolower( $string[0] );
		return preg_replace_callback( '/([A-Z])/', function ( $value ) {
			return '_' . strtolower( $value[1] );
		}, $string );
	}

	/**
	 * Convert to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string     The string to convert.
	 * @param  bool   $capitalize Whether or not to capitalize the first letter.
	 * @return string             The converted string.
	 */
	public function toCamelCase( $string, $capitalize = false ) {
		if ( $capitalize ) {
			$string[0] = strtoupper( $string[0] );
		}
		return preg_replace_callback( '/_([a-z0-9])/', function ( $value ) {
			return strtoupper( $value[1] );
		}, $string );
	}

	/**
	 * Converts kebab case to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string     The string to convert.
	 * @param  bool   $capitalize Whether or not to capitalize the first letter.
	 * @return string             The converted string.
	 */
	public function dashesToCamelCase( $string, $capitalizeFirstCharacter = false ) {
		$string = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $string ) ) );
		if ( ! $capitalizeFirstCharacter ) {
			$string[0] = strtolower( $string[0] );
		}
		return $string;
	}

	/**
	 * Returns string after converting it to lowercase.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $string The original string.
	 * @return string         The string converted to lowercase.
	 */
	public function toLowercase( $string ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string, get_option( 'blog_charset' ) ) : strtolower( $string );
	}

	/**
	 * Checks if the given string contains the given substring.
	 *
	 * @since 4.1.0.2
	 *
	 * @param  string   $stack  The stack.
	 * @param  string   $needle The needle.
	 * @param  int      $offset The offset.
	 * @return int|bool         The index of the first occurence or false.
	 */
	public function stringContains( $stack, $needle, $offset = 0 ) {
		return function_exists( 'mb_strpos' ) ? mb_strpos( $stack, $needle, $offset, get_option( 'blog_charset' ) ) : strpos( $stack, $needle, $offset );
	}
}