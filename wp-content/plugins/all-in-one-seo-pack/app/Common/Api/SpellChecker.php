<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\SpellChecker\Dictionary;
use AIOSEO\Plugin\Common\SpellChecker\SafeWords;

/**
 * REST API callbacks for the spell checker.
 *
 * @since 5.0.0
 */
class SpellChecker {
	/**
	 * Downloads the Hunspell dictionary for a given locale and stores it locally.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function downloadDictionary( $request ) {
		$body   = $request->get_json_params();
		$locale = ! empty( $body['locale'] ) ? sanitize_text_field( $body['locale'] ) : '';

		if ( empty( $locale ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No locale provided.'
			], 400 );
		}

		$dictionary            = new Dictionary();
		$spellCheckableLocales = $dictionary->getSpellCheckableLocales();

		if ( ! in_array( $locale, $spellCheckableLocales, true ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unsupported locale.'
			], 400 );
		}

		$result = $dictionary->downloadForLocale( $locale );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message()
			], 'download_in_progress' === $result->get_error_code() ? 409 : 500 );
		}

		return new \WP_REST_Response( [ 'success' => true ] );
	}

	/**
	 * Returns a filtered, sorted, paginated slice of the safe-words list.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function getSafeWords( $request ) {
		$search  = (string) $request->get_param( 'search' );
		$page    = (int) $request->get_param( 'page' );
		$perPage = (int) $request->get_param( 'perPage' );

		$safeWords = new SafeWords();
		$result    = $safeWords->filterAndPaginate( $search, $page ?: 1, $perPage ?: 50 );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message()
			], 500 );
		}

		return new \WP_REST_Response( array_merge( [ 'success' => true ], $result ) );
	}

	/**
	 * Adds a word to the per-site safe-words custom dictionary.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function addSafeWord( $request ) {
		$body      = $request->get_json_params();
		$word      = isset( $body['word'] ) ? (string) $body['word'] : '';
		$matchCase = ! empty( $body['matchCase'] );

		$safeWords = new SafeWords();
		$result    = $safeWords->addWord( $word, $matchCase );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message()
			], self::statusForErrorCode( $result->get_error_code() ) );
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'word'      => $result,
			'matchCase' => $matchCase
		] );
	}

	/**
	 * Sets or clears the match-case flag for an existing safe word.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function setSafeWordMatchCase( $request ) {
		$body      = $request->get_json_params();
		$word      = isset( $body['word'] ) ? (string) $body['word'] : '';
		$matchCase = ! empty( $body['matchCase'] );

		$safeWords = new SafeWords();
		$result    = $safeWords->setMatchCase( $word, $matchCase );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message()
			], self::statusForErrorCode( $result->get_error_code() ) );
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'word'      => $result,
			'matchCase' => $matchCase
		] );
	}

	/**
	 * Removes a word from the per-site safe-words custom dictionary.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function removeSafeWord( $request ) {
		$body = $request->get_json_params();
		$word = isset( $body['word'] ) ? (string) $body['word'] : '';

		$safeWords = new SafeWords();
		$result    = $safeWords->removeWord( $word );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message()
			], self::statusForErrorCode( $result->get_error_code() ) );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'word'    => $result
		] );
	}

	/**
	 * Maps a SafeWords error code to an HTTP status.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $code The WP_Error code.
	 * @return int          The HTTP status code.
	 */
	private static function statusForErrorCode( $code ) {
		switch ( $code ) {
			case 'safe_words_invalid':
				return 400;
			case 'safe_words_busy':
				return 409;
			default:
				return 500;
		}
	}
}