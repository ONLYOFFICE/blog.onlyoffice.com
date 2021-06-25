<?php
namespace AIOSEO\Plugin\Common\Integrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to integrate with the Semrush API.
 *
 * @since 4.0.16
 */
class Semrush {
	/**
	 * The Oauth2 URL.
	 *
	 * @since 4.0.16
	 *
	 * @var string
	 */
	public static $url = 'https://oauth.semrush.com/oauth2/access_token';

	/**
	 * The client ID for the Oauth2 integration.
	 *
	 * @since 4.0.16
	 *
	 * @var string
	 */
	public static $clientId = 'aioseo';

	/**
	 * The client secret for the Oauth2 integration.
	 *
	 * @since 4.0.16
	 *
	 * @var string
	 */
	public static $clientSecret = 'sdDUjYt6umO7sKM7mp4OrN8yeePTOQBy';

	/**
	 * Static method to authenticate the user.
	 *
	 * @since 4.0.16
	 *
	 * @param  string $authorizationCode The authorization code for the Oauth2 authentication.
	 * @return void
	 */
	public static function authenticate( $authorizationCode ) {
		$time     = time();
		$response = wp_remote_post( self::$url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'client_id'     => self::$clientId,
				'client_secret' => self::$clientSecret,
				'grant_type'    => 'authorization_code',
				'code'          => $authorizationCode,
				'redirect_uri'  => 'https://oauth.semrush.com/oauth2/aioseo/success'
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 === $responseCode ) {
			$tokens = json_decode( wp_remote_retrieve_body( $response ) );
			self::saveTokens( $tokens, $time );
		}
	}

	/**
	 * Static method to refresh the tokens once expired.
	 *
	 * @since 4.0.16
	 *
	 * @return void
	 */
	public static function refreshTokens() {
		$time     = time();
		$response = wp_remote_post( self::$url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'client_id'     => self::$clientId,
				'client_secret' => self::$clientSecret,
				'grant_type'    => 'refresh_token',
				'refresh_token' => aioseo()->internalOptions->integrations->semrush->refreshToken
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 === $responseCode ) {
			$tokens = json_decode( wp_remote_retrieve_body( $response ) );
			self::saveTokens( $tokens, $time );
		}
	}

	/**
	 * Checks if the token has expired
	 *
	 * @since 4.0.16
	 *
	 * @return boolean Whether or not the token has expired.
	 */
	public static function hasExpired() {
		$tokens = self::getTokens();
		return time() >= $tokens['expires'];
	}

	/**
	 * Returns the tokens.
	 *
	 * @since 4.0.16
	 *
	 * @return array An array of token data.
	 */
	public static function getTokens() {
		return aioseo()->internalOptions->integrations->semrush->all();
	}

	/**
	 * Saves the token options.
	 *
	 * @since 4.0.16
	 *
	 * @param  Object $tokens The tokens object.
	 * @param  string $time   The time set before the request was made.
	 * @return void
	 */
	public static function saveTokens( $tokens, $time ) {
		// Save the options.
		aioseo()->internalOptions->integrations->semrush->accessToken  = $tokens->access_token;
		aioseo()->internalOptions->integrations->semrush->tokenType    = $tokens->token_type;
		aioseo()->internalOptions->integrations->semrush->expires      = $time + $tokens->expires_in;
		aioseo()->internalOptions->integrations->semrush->refreshToken = $tokens->refresh_token;
	}

	/**
	 * API call to get keyphrases from semrush.
	 *
	 * @since 4.0.16
	 *
	 * @param  string $keyphrase A primary keyphrase.
	 * @param  string $database  A country database.
	 * @return object            A response object.
	 */
	public static function getKeyphrases( $keyphrase, $database ) {
		if ( self::hasExpired() ) {
			self::refreshTokens();
		}

		$transientKey = 'semrush_keyphrases_' . $keyphrase . '_' . $database;
		$results      = aioseo()->transients->get( $transientKey );

		if ( false !== $results ) {
			return $results;
		}

		$params = [
			'phrase'         => $keyphrase,
			'export_columns' => 'Ph,Nq,Td',
			'database'       => strtolower( $database ),
			'display_limit'  => 10,
			'display_offset' => 0,
			'display_sort'   => 'nq_desc',
			'display_filter' => '%2B|Nq|Lt|1000',
			'access_token'   => aioseo()->internalOptions->integrations->semrush->accessToken
		];

		$url = 'https://oauth.semrush.com/api/v1/keywords/phrase_fullsearch?' . http_build_query( $params );

		$response = wp_remote_get( $url );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );

		aioseo()->transients->update( $transientKey, $body );

		return $body;
	}
}