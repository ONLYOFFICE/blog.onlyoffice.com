<?php

namespace DemocracyPoll\Support;

use DemocracyPoll\Options;
use function DemocracyPoll\container;

class IP {

	private const IP_INFO_SERVICE_URL = 'https://ipwho.is/{IP}?fields=success,country,country_code,city';

	public static function get_user_ip(): string {

		if( container()->get( Options::class )->soft_ip_detect ){
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''; // cloudflare

			filter_var( $ip, FILTER_VALIDATE_IP ) || ( $ip = $_SERVER['HTTP_CLIENT_IP'] ?? '' );
			filter_var( $ip, FILTER_VALIDATE_IP ) || ( $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '' );
			filter_var( $ip, FILTER_VALIDATE_IP ) || ( $ip = $_SERVER['REMOTE_ADDR'] ?? '' );
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		}

		/**
		 * Allows to change determined user IP.
		 * This can be useful for testing purposes or when using a proxy
		 * (like cloudflare) that may not pass the real user IP.
		 *
		 * @param string $ip  The Determined user IP address.
		 */
		$ip = apply_filters( 'dem_get_ip', $ip );

		if( ! filter_var( $ip, FILTER_VALIDATE_IP ) ){
			/** @noinspection NonSecureUniqidUsageInspection */
			$ip = 'no_IP__' . uniqid();
		}

		return $ip;
	}

	/**
	 * Returns a string: ip_info format for the "logs" table.
	 *
	 * @param array|string $ip_info  IP or already obtained IP data in an array.
	 *
	 * @return string Format: "country_name,country_code,city" OR "current-UNIX-timestamp".
	 */
	public static function prepared_ip_info( $ip_info ): string {
		// IP was passed
		if( filter_var( $ip_info, FILTER_VALIDATE_IP ) ){
			$parts = array_map( 'intval', explode( '.', $ip_info ) );
			$is_localhost = (
				127 === $parts[0] || 10 === $parts[0] || 0 === $parts[0]
				|| ( 172 === $parts[0] && 16 <= $parts[1] && 31 >= $parts[1] )
				|| ( 192 === $parts[0] && 168 === $parts[1] )
			);

			if( $is_localhost ){
				return 'localhost,VA';
			}

			$ip_info = self::get_ip_info( (string) $ip_info );
		}

		if( isset( $ip_info['country'] ) ){
			return sanitize_text_field( $ip_info['country'] . ',' . $ip_info['country_code'] . ',' . $ip_info['city'] );
		}

		return (string) time();
	}

	/**
	 * Gets location data for the provided IP address.
	 *
	 * @param string $ip IP address to check. Current IP by default.
	 *
	 * @return array{city:string, country:string, country_code:string} Location data.
	 */
	public static function get_ip_info( string $ip = '' ): array {
		static $limit_exceeded = false;

		if( ! $ip ){
			$ip = self::get_user_ip();
		}

		if( $limit_exceeded || ! filter_var( $ip, FILTER_VALIDATE_IP ) ){
			return [];
		}

		$url = str_replace( '{IP}', rawurlencode( $ip ), self::IP_INFO_SERVICE_URL );

		$response = wp_safe_remote_get( $url, [
			'timeout'     => 3,
			'redirection' => 2,
			'headers'     => [ 'Accept' => 'application/json' ],
		] );

		$code = wp_remote_retrieve_response_code( $response );
		if( 200 !== $code ){
			if( 429 === $code ){
				$limit_exceeded = true;
			}
			return [];
		}

		$ipdat = json_decode( wp_remote_retrieve_body( $response ), true );
		if( ! is_array( $ipdat ) || empty( $ipdat['success'] ) ){
			return [];
		}

		$country_code = trim( (string) ( $ipdat['country_code'] ?? '' ) );
		$country_name = trim( (string) ( $ipdat['country'] ?? '' ) );
		$city         = trim( (string) ( $ipdat['city'] ?? '' ) );

		if( strlen( $country_code ) !== 2 ){
			return [];
		}

		return [
			'city'         => $city,
			'country'      => $country_name,
			'country_code' => $country_code,
		];
	}

}
