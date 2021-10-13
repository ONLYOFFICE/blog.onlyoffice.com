<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains date/time specific helper methods.
 *
 * @since 4.1.2
 */
trait DateTime {
	/**
	 * Formats a timestamp as an ISO 8601 date.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $dateTime The raw datetime.
	 * @return string           The formatted datetime.
	 */
	public function formatDateTime( $dateTime ) {
		return gmdate( 'c', mysql2date( 'U', $dateTime ) );
	}

	/**
	 * Returns the timezone offset.
	 * We use the code from wp_timezone_string() which became available in WP 5.3+
	 *
	 * @since 4.0.0
	 *
	 * @return string The timezone offset.
	 */
	public function getTimeZoneOffset() {
		$timezoneString = get_option( 'timezone_string' );
		if ( $timezoneString ) {
			return $timezoneString;
		}

		$offset   = (float) get_option( 'gmt_offset' );
		$hours    = (int) $offset;
		$minutes  = ( $offset - $hours );
		$sign     = ( $offset < 0 ) ? '-' : '+';
		$absHour  = abs( $hours );
		$absMins  = abs( $minutes * 60 );
		$tzOffset = sprintf( '%s%02d:%02d', $sign, $absHour, $absMins );

		return $tzOffset;
	}

	/**
	 * Formats a date in ISO8601 format.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $date The date.
	 * @return string       The date formatted in ISO8601 format.
	 */
	public function dateToIso8601( $date ) {
		return date( 'Y-m-d', strtotime( $date ) );
	}

	/**
	 * Formats an amount of minutes in ISO8601 format.
	 * This is used in our JSON schema to adhere to Google's standards.
	 *
	 * @since 4.1.2
	 *
	 * @param  integer|string $minutes The minutes.
	 * @return                         The minutes formatted in ISO8601 format.
	 */
	public function minutesToIso8601( $minutes ) {
		return "PT${minutes}M";
	}
}