<?php

namespace DemocracyPoll\Helpers;

use function DemocracyPoll\options;

class IP {

	public static function get_user_ip(): string {

		if( options()->soft_ip_detect ){
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
				return (string) ( 10 * YEAR_IN_SECONDS + time() ); // на 10 лет вперед
			}

			$ip_info = self::get_ip_info( (string) $ip_info );
		}

		/**
		 * $ip_info = [
		 *     [city] =>
		 *     [state] =>
		 *     [country] => Uzbekistan
		 *     [country_code] => UZ
		 *     [continent] => Asia
		 *     [continent_code] => AS
		 * ]
		 */
		if( isset( $ip_info['country'] ) ){
			return $ip_info['country'] . ',' . $ip_info['country_code'] . ',' . $ip_info['city'];
		}

		return (string) time();
	}

	/**
	 * Gets location data for the provided IP address.
	 *
	 * @param string $ip IP address to check. Current IP by default.
	 *
	 * @return array Location data.
	 */
	public static function get_ip_info( string $ip = '' ): array {
		if( ! $ip ){
			$ip = self::get_user_ip();
		}

		if( ! filter_var( $ip, FILTER_VALIDATE_IP ) ){
			return [];
		}

		//$ipdat = json_decode( wp_remote_retrieve_body( wp_remote_get("http://www.geoplugin.net/json.gp?ip=$ip") ) ); // wp_remote_get отдает forbiden 403 !!!
		$json = @ file_get_contents( "http://www.geoplugin.net/json.gp?ip=$ip" );
		$ipdat = json_decode( $json );
		if( ! $ipdat ){
			return [];
		}

		$continent_code = trim( $ipdat->geoplugin_continentCode ?? '' );
		$country_code   = trim( $ipdat->geoplugin_countryCode ?? '' );
		$country_name   = trim( $ipdat->geoplugin_countryName ?? '' );
		$region_name    = trim( $ipdat->geoplugin_regionName ?? '' );
		$city           = trim( $ipdat->geoplugin_city ?? '' );

		if( strlen( $country_code ) !== 2 ){
			return [];
		}

		$continent = [
			'AF' => 'Africa',
			'AN' => 'Antarctica',
			'AS' => 'Asia',
			'EU' => 'Europe',
			'OC' => 'Australia (Oceania)',
			'NA' => 'North America',
			'SA' => 'South Americ',
		][ strtoupper( $continent_code ) ] ?? '';

		return [
			'city'           => $city,
			'state'          => $region_name,
			'country'        => $country_name,
			'country_code'   => $country_code,
			'continent'      => $continent,
			'continent_code' => $continent_code,
		];
	}

}
