<?php

namespace WPML\Element\API;

use WPML\Collect\Support\Traits\Macroable;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\FP\Str;
use function WPML\FP\curryN;
use function WPML\FP\invoke;
use function WPML\FP\pipe;

/**
 * @method static array getActive()
 *
 * It returns an array of the active languages.
 *
 * The returned array is indexed by language code and every element has the following structure:
 * ```
 *  'fr' => [
 *      'code'           => 'fr',
 *      'id'             => 3,
 *      'english_name'   => 'French',
 *      'native_name'    => 'Français',
 *      'major'          => 1,
 *      'default_locale' => 'fr_FR',
 *      'encode_url'     => 0,
 *      'tag'            => 'fr ,
 *      'display_name'   => 'French
 *  ]
 * ```
 * @method static array|callback getLanguageDetails( ...$code ) - Curried :: string->array
 *
 * It returns details of a language.
 *
 * An example output:
 * ```
 * [
 *      'code'           => 'fr',
 *      'id'             => 3,
 *      'english_name'   => 'French',
 *      'native_name'    => 'Français',
 *      'major'          => 1,
 *      'default_locale' => 'fr_FR',
 *      'encode_url'     => 0,
 *      'tag'            => 'fr ,
 *      'display_name'   => 'French
 *  ]
 * ```
 *
 *
 * @method static array getDefault()
 *
 * It returns a default language details.
 *
 * An example output:
 *```
 *[
 *      'code'           => 'fr',
 *      'id'             => 3,
 *      'english_name'   => 'French',
 *      'native_name'    => 'Français',
 *      'major'          => 1,
 *      'default_locale' => 'fr_FR',
 *      'encode_url'     => 0,
 *      'tag'            => 'fr ,
 *      'display_name'   => 'French
 * ]
 *```
 *
 * @method static callable|string getFlagUrl( ...$code ) - Curried :: string → string
 *
 * Gets the flag url for the given language code.
 *
 * @method static callable|array withFlags( ...$langs ) - Curried :: [code => lang] → [code => lang]
 *
 * Adds the language flag url to the array of languages.
 *
 * @method static array getAll( $lang = false ) string|false → [lang]
 *
 * It returns an array of the all the languages.
 *
 * The returned array is indexed by language code and every element has the following structure:
 * ```
 *  'fr' => [
 *      'code'           => 'fr',
 *      'id'             => 3,
 *      'english_name'   => 'French',
 *      'native_name'    => 'Français',
 *      'major'          => 1,
 *      'default_locale' => 'fr_FR',
 *      'encode_url'     => 0,
 *      'tag'            => 'fr ,
 *      'display_name'   => 'French
 *  ]
 * ```
 *
 * @method static callable|int|false setLanguageTranslation(...$langCode, ...$displayLangCode, ...$name) - Curried :: string->string->string->int|false
 *
 * It sets a language translation.
 *
 * @method static callable|int|false setFlag(...$langCode, ...$flag, ...$fromTemplate) - Curried :: string->string->bool->int|false
 *
 * It sets a language flag.
 */
class Languages {
	use Macroable;

	/**
	 * @return void
	 */
	public static function init() {
		global $sitepress;

		self::macro( 'getActive', [ $sitepress, 'get_active_languages' ] );

		self::macro( 'getLanguageDetails', curryN( 1, [ $sitepress, 'get_language_details' ] ) );

		self::macro( 'getDefault', pipe( [ $sitepress, 'get_default_language' ], self::getLanguageDetails() ) );

		self::macro( 'getAll', [ $sitepress, 'get_languages' ] );

		self::macro( 'getFlagUrl', curryN( 1, [ $sitepress, 'get_flag_url' ] ) );

		self::macro( 'withFlags', curryN( 1, function ( $langs ) {
			$addFlag = function ( $lang, $code ) {
				$lang['flag_url'] = self::getFlagUrl( $code );

				return $lang;
			};

			return Fns::map( $addFlag, $langs );
		} ) );

		self::macro( 'setLanguageTranslation', curryN( 3, function ( $langCode, $displayLangCode, $name ) {
			global $wpdb;

			$sql = "
				REPLACE INTO {$wpdb->prefix}icl_languages_translations (`language_code`, `display_language_code`, `name`) 
				VALUE (%s, %s, %s) 
			";

			return $wpdb->query( $wpdb->prepare( $sql, $langCode, $displayLangCode, $name ) ) ? $wpdb->insert_id : false;
		} ) );


		self::macro( 'setFlag', curryN( 3, function ( $langCode, $flag, $fromTemplate ) {
			global $wpdb;

			$sql = "
				REPLACE INTO {$wpdb->prefix}icl_flags (`lang_code`, `flag`, `from_template`)
				VALUES (%s, %s, %d)		
			";

			return $wpdb->query( $wpdb->prepare( $sql, $langCode, $flag, $fromTemplate) ) ? $wpdb->insert_id : false;
		} ) );

	}

	/**
	 * Curried :: string → bool
	 * Determine if the language is Right to Left
	 *
	 * @param string|null $code
	 *
	 * @return callable|bool
	 */
	public static function isRtl( $code = null ) {
		$isRtl = function ( $code ) {
			global $sitepress;

			return $sitepress->is_rtl( $code );
		};

		return call_user_func_array( curryN( 1, $isRtl ), func_get_args() );
	}

	/**
	 * Curried :: [code => lang] → [code => lang]
	 *
	 * Adds language direction, right to left, to the languages data
	 *
	 * @param string[] $langs
	 *
	 * @return callable|mixed[]
	 */
	public static function withRtl( $langs = null ) {
		$withRtl = function ( $langs ) {
			$addRtl = function ( $lang, $code ) {
				$lang['rtl'] = self::isRtl( $code );

				return $lang;
			};

			return Fns::map( $addRtl, $langs );
		};

		return call_user_func_array( curryN( 1, $withRtl ), func_get_args() );
	}

	/**
	 * Curried :: string -> string|false
	 *
	 * Returns the language code given a locale
	 *
	 * @param string|null $locale
	 *
	 * @return callable|string|false
	 */
	public static function localeToCode( $locale = null ) {
		$localeToCode = function ( $locale ) {
			$allLangs = Obj::values( self::getAll() );

			$guessedCode = Maybe::of( $locale )
			                    ->map( Str::split( '_' ) )
			                    ->map( Lst::nth( 0 ) )
			                    ->filter( Lst::includes( Fns::__, Lst::pluck( 'code', $allLangs ) ) )
			                    ->getOrElse( false );

			return Obj::pathOr(
				$guessedCode,
				[ $locale, 'code' ],
				Lst::keyBy( 'default_locale', $allLangs )
			);
		};

		return call_user_func_array( curryN( 1, $localeToCode ), func_get_args() );
	}

	/**
	 * @param string $code
	 * @param string $english_name
	 * @param string $default_locale
	 * @param int    $major
	 * @param int    $active
	 * @param int    $encode_url
	 * @param string $tag
	 *
	 * @return bool|int
	 */
	public static function add( $code, $english_name, $default_locale, $major = 0, $active = 0, $encode_url = 0, $tag = '' ) {
		global $wpdb;

		$existingCodes = Obj::keys( self::getAll() );

		$res = $wpdb->insert(
			$wpdb->prefix . 'icl_languages', [
				'code'           => $code,
				'english_name'   => $english_name,
				'default_locale' => $default_locale,
				'major'          => $major,
				'active'         => $active,
				'encode_url'     => $encode_url,
				'tag'            => $tag,
			]
		);

		if ( ! $res ) {
			return false;
		}

		$languageId = $wpdb->insert_id;
		$codes = Lst::concat( $existingCodes, [ $code ] );

		Fns::map( self::setLanguageTranslation( $code, Fns::__, $english_name ), $codes );

		return $languageId;
	}
}

Languages::init();
