<?php

namespace DemocracyPoll\Support;

use DemocracyPoll\Plugin;
use function DemocracyPoll\container;

class Kses {

	/**
	 * The tags allowed in questions and answers.
	 * Will be appended for the global {@see $allowedtags}.
	 */
	private static array $allowed_tags = [
		'a'      => [
			'href'   => true,
			'rel'    => true,
			'name'   => true,
			'target' => true,
		],
		'p'      => [],
		'br'     => [],
		'b'      => [],
		'strong' => [],
		'i'      => [],
		'em'     => [],
		'span'   => [ 'class' => true ],
		'code'   => [],
		'var'    => [],
		'del'    => [ 'datetime' => true, ],
		'img'    => [
			'class'  => true,
			'alt'    => true,
			'src'    => true,
			'width'  => true,
			'height' => true,
			'srcset' => true,
			'sizes'  => true,
			'align'  => true,
		],
		'h2' => [],
		'h3' => [],
		'h4' => [],
		'h5' => [],
		'h6' => [],
	];

	public static function setup_allowed_tags(): void {
		self::$allowed_tags = array_merge( wp_kses_allowed_html( 'data' ), self::$allowed_tags );
		self::$allowed_tags = array_map( '_wp_add_global_attributes', self::$allowed_tags );

		/**
		 * Allows modification of the collected allowed HTML tags for Democracy Poll.
		 *
		 * @param array $allowed_tags The array of computed allowed HTML tags.
		 */
		self::$allowed_tags = apply_filters( 'democracy__allowed_tags', self::$allowed_tags );
	}

	/**
	 * wp_kses() value with democracy allowed tags. For esc output strings...
	 */
	public static function kses_html( $value ): string {
		return wp_kses( $value, self::$allowed_tags );
	}

	/**
	 * @param string|array $data            What to sanitize? If a string is passed, remove disallowed HTML tags from it.
	 * @param string       $filter_context  The type of filter applied. Can be used to differentiate between different sanitization contexts.
	 *                                      Passed to the `dem_sanitize_answer_data` filter.
	 *
	 * @return string|array Clean data.
	 */
	public static function sanitize_answer_data( $data, string $filter_context = '' ) {
		$plugin = container()->get( Plugin::class );

		if( is_string( $data ) ){
			$val = trim( $data );
			$data = $plugin->admin_access ? self::kses_html( $val ) : wp_kses( $val, 'strip' );
		}
		else {
			foreach( $data as $key => & $val ){
				if( is_string( $val ) ){
					$val = trim( $val );
				}

				// allowed tags
				if( $key === 'answer' ){
					$val = $plugin->admin_access ? self::kses_html( $val ) : wp_kses( $val, 'strip' );
				}
				// numbers
				elseif( in_array( $key, [ 'qid', 'aid', 'votes' ] ) ){
					$val = (int) $val;
				}
				// other
				else{
					$val = wp_kses( $val, 'strip' );
				}
			}
		}

		/**
		 * Allows to modify the sanitized answer data.
		 *
		 * @param array|string $data  The sanitized answer itself or the array of sanitized answers data.
		 *                            If a string is passed, it means that only one answer is sanitized.
		 * @param string $filter_context  The type of filter applied.
		 *                                Can be used to differentiate between different sanitization contexts.
		 */
		return apply_filters( 'dem_sanitize_answer_data', $data, $filter_context );
	}

}
