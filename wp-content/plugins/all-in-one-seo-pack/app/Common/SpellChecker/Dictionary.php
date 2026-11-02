<?php
namespace AIOSEO\Plugin\Common\SpellChecker;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages dictionary files for the spell checker.
 *
 * Dictionary files (.aff/.dic) are Hunspell format and stored in
 * the WordPress uploads directory under `aioseo/dictionaries/{code}/`.
 *
 * Folder structure: uploads/aioseo/dictionaries/{code}/{locale}.aff
 * Example: uploads/aioseo/dictionaries/en/en_US.aff
 *
 * @since 5.0.0
 */
class Dictionary {
	/**
	 * The dictionaries base URL.
	 *
	 * @since   5.0.0
	 *
	 * @var string
	 */
	private $dictionariesBaseUrl = 'https://aioseo-spell-checker.nyc3.cdn.digitaloceanspaces.com';

	/**
	 * Per-instance cache for getSupportedLanguages().
	 *
	 * @since 5.0.0
	 *
	 * @var array|null
	 */
	private $supportedLanguagesCache = null;

	/**
	 * Per-instance cache for getInstalledLocales().
	 *
	 * @since 5.0.0
	 *
	 * @var array|null
	 */
	private $installedLocalesCache = null;

	/**
	 * Returns the local filesystem base directory for dictionaries.
	 *
	 * @since 5.0.0
	 *
	 * @return string The absolute path to the dictionaries directory.
	 */
	public function getDictionaryDir() {
		$uploadDir = wp_upload_dir();

		return trailingslashit( $uploadDir['basedir'] ) . 'aioseo/dictionaries';
	}

	/**
	 * Returns the base URL for the dictionaries directory.
	 *
	 * @since 5.0.0
	 *
	 * @return string The base URL for dictionary files.
	 */
	public function getDictionaryBaseUrl() {
		$uploadDir = wp_upload_dir();

		return trailingslashit( $uploadDir['baseurl'] ) . 'aioseo/dictionaries';
	}

	/**
	 * Extracts the 2-letter language code from a locale string.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $locale The locale (e.g. 'en_US').
	 * @return string         The language code (e.g. 'en').
	 */
	public function getLanguageCode( $locale ) {
		return strstr( $locale, '_', true ) ?: $locale;
	}

	/**
	 * Checks whether the dictionary files for a locale need to be downloaded.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $locale The locale (e.g. 'en_US').
	 * @return bool           True if either .aff or .dic file is missing.
	 */
	public function needsDownload( $locale ) {
		$code = $this->getLanguageCode( $locale );
		$dir  = $this->getDictionaryDir() . '/' . $code;
		$fs   = aioseo()->core->fs;

		return ! $fs->exists( $dir . '/' . $locale . '.aff' )
			|| ! $fs->exists( $dir . '/' . $locale . '.dic' );
	}

	/**
	 * Downloads the Hunspell dictionary archive for a locale from the CDN and stores its files locally.
	 *
	 * The CDN serves one zip per language code (e.g. `ca.zip`) containing a
	 * `{code}/` folder with the `{locale}.aff` and `{locale}.dic` files, which
	 * maps directly onto our local layout once extracted.
	 *
	 * @since 5.0.0
	 *
	 * @param  string         $locale The locale to download (e.g. 'en_US').
	 * @return true|\WP_Error         True on success, WP_Error on failure.
	 */
	public function downloadForLocale( $locale ) {
		if ( ! $this->needsDownload( $locale ) ) {
			return true;
		}

		$lockKey = 'aioseo_dict_dl_' . $locale;

		if ( aioseo()->core->cache->get( $lockKey ) ) {
			return new \WP_Error( 'download_in_progress', 'Dictionary download already in progress.' );
		}

		aioseo()->core->cache->update( $lockKey, 1, 60 );

		$code    = $this->getLanguageCode( $locale );
		$baseDir = $this->getDictionaryDir();

		if ( ! wp_mkdir_p( $baseDir . '/' . $code ) ) {
			aioseo()->core->cache->delete( $lockKey );

			return new \WP_Error( 'mkdir_failed', 'Could not create dictionary directory.' );
		}

		$url      = $this->getDictionariesBaseUrl() . '/' . $code . '.zip';
		$response = aioseo()->helpers->wpRemoteGet( $url, [
			'timeout'          => 30,
			'aioseo_skip_lock' => true
		] );

		if ( is_wp_error( $response ) ) {
			aioseo()->core->cache->delete( $lockKey );

			return $response;
		}

		$statusCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $statusCode ) {
			aioseo()->core->cache->delete( $lockKey );

			return new \WP_Error( 'download_failed', "Failed to download {$code}.zip. Try again later." );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			aioseo()->core->cache->delete( $lockKey );

			return new \WP_Error( 'download_failed', "Failed to download {$code}.zip. Try again later." );
		}

		$fs     = aioseo()->core->fs;
		$tmpZip = $baseDir . '/' . $code . '.zip.tmp';

		if ( ! $fs->putContents( $tmpZip, $body ) || ! $fs->isWpfsValid() ) {
			wp_delete_file( $tmpZip );
			aioseo()->core->cache->delete( $lockKey );

			return new \WP_Error( 'write_failed', "Failed to write {$code}.zip to disk. Try again later." );
		}

		// unzip_file() lives in wp-admin/includes/file.php and needs WP_Filesystem,
		// both already set up by aioseo()->core->fs above.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$unzipped = unzip_file( $tmpZip, $baseDir );
		wp_delete_file( $tmpZip );

		// Drop the macOS resource-fork junk that the archive ships alongside the real files.
		if ( $fs->isDir( $baseDir . '/__MACOSX' ) ) {
			$fs->fs->delete( $baseDir . '/__MACOSX', true );
		}

		if ( is_wp_error( $unzipped ) ) {
			aioseo()->core->cache->delete( $lockKey );

			return $unzipped;
		}

		aioseo()->core->cache->delete( $lockKey );

		if ( $this->needsDownload( $locale ) ) {
			return new \WP_Error( 'download_failed', "Dictionary files for {$locale} were missing from {$code}.zip. Try again later." );
		}

		// Files on disk just changed — drop the per-instance caches so any
		// follow-up call in the same request sees the new install state.
		$this->supportedLanguagesCache = null;
		$this->installedLocalesCache   = null;

		return true;
	}

	/**
	 * Returns every TruSEO-supported analysis language (22 entries).
	 *
	 * Each entry carries a `hasSpellChecker` flag — most also have a Hunspell
	 * dictionary (those drive the spell-check assessment), but a few (e.g.
	 * Arabic, Indonesian, Japanese, Persian) are analysis-only and never download.
	 * `exists` reflects on-disk install state and is only meaningful when
	 * `hasSpellChecker` is true.
	 *
	 * Languages with regional spelling standards (en, es, pt, de) also carry a
	 * `variants` array of dialects (each with its own `locale`, `label`,
	 * `shortLabel` and `exists`); the default dialect is listed first and equals
	 * the primary `locale`. Analysis is unaffected — it keys off the 2-letter code.
	 *
	 * @since 5.0.0
	 *
	 * @return array<array{code: string, locale: string, label: string, nativeLabel: string, hasSpellChecker: bool, exists: bool, variants?: array}>
	 */
	public function getSupportedLanguages() {
		if ( null !== $this->supportedLanguagesCache ) {
			return $this->supportedLanguagesCache;
		}

		$languages = [
			'ar' => [
				'locale'          => 'ar',
				'label'           => 'Arabic',
				'nativeLabel'     => 'العربية',
				'hasSpellChecker' => false
			],
			'ca' => [
				'locale'          => 'ca',
				'label'           => 'Catalan',
				'nativeLabel'     => 'Català',
				'hasSpellChecker' => true
			],
			'cs' => [
				'locale'          => 'cs_CZ',
				'label'           => 'Czech',
				'nativeLabel'     => 'Čeština',
				'hasSpellChecker' => true
			],
			'de' => [
				'locale'          => 'de_DE',
				'label'           => 'German',
				'nativeLabel'     => 'Deutsch',
				'hasSpellChecker' => true,
				'variants'        => [
					[
						'locale'     => 'de_DE',
						'label'      => 'German (Germany)',
						'shortLabel' => 'DE'
					],
					[
						'locale'     => 'de_AT',
						'label'      => 'German (Austria)',
						'shortLabel' => 'AT'
					],
					[
						'locale'     => 'de_CH',
						'label'      => 'German (Switzerland)',
						'shortLabel' => 'CH'
					]
				]
			],
			'el' => [
				'locale'          => 'el',
				'label'           => 'Greek',
				'nativeLabel'     => 'Ελληνικά',
				'hasSpellChecker' => true
			],
			'en' => [
				'locale'          => 'en_US',
				'label'           => 'English',
				'nativeLabel'     => 'English',
				'hasSpellChecker' => true,
				'variants'        => [
					[
						'locale'     => 'en_US',
						'label'      => 'English (US)',
						'shortLabel' => 'US'
					],
					[
						'locale'     => 'en_GB',
						'label'      => 'English (UK)',
						'shortLabel' => 'UK'
					],
					[
						'locale'     => 'en_CA',
						'label'      => 'English (Canada)',
						'shortLabel' => 'CA'
					],
					[
						'locale'     => 'en_AU',
						'label'      => 'English (Australia)',
						'shortLabel' => 'AU'
					]
				]
			],
			'es' => [
				'locale'          => 'es_ES',
				'label'           => 'Spanish',
				'nativeLabel'     => 'Español',
				'hasSpellChecker' => true,
				'variants'        => [
					[
						'locale'     => 'es_ES',
						'label'      => 'Spanish (Spain)',
						'shortLabel' => 'ES'
					],
					[
						'locale'     => 'es_MX',
						'label'      => 'Spanish (Mexico)',
						'shortLabel' => 'MX'
					]
				]
			],
			'fa' => [
				'locale'          => 'fa_IR',
				'label'           => 'Persian',
				'nativeLabel'     => 'فارسی',
				// Persian ships a Hunspell dictionary, but its RTL/ZWNJ handling is
				// unreliable, so spell-check is disabled — it stays analysis-only.
				'hasSpellChecker' => false
			],
			'fr' => [
				'locale'          => 'fr_FR',
				'label'           => 'French',
				'nativeLabel'     => 'Français',
				'hasSpellChecker' => true
			],
			'he' => [
				'locale'          => 'he_IL',
				'label'           => 'Hebrew',
				'nativeLabel'     => 'עברית',
				'hasSpellChecker' => true
			],
			'hu' => [
				'locale'          => 'hu_HU',
				'label'           => 'Hungarian',
				'nativeLabel'     => 'Magyar',
				'hasSpellChecker' => true
			],
			'id' => [
				'locale'          => 'id_ID',
				'label'           => 'Indonesian',
				'nativeLabel'     => 'Bahasa Indonesia',
				'hasSpellChecker' => false
			],
			'it' => [
				'locale'          => 'it_IT',
				'label'           => 'Italian',
				'nativeLabel'     => 'Italiano',
				'hasSpellChecker' => true
			],
			'ja' => [
				'locale'          => 'ja',
				'label'           => 'Japanese',
				'nativeLabel'     => '日本語',
				'hasSpellChecker' => false
			],
			'nb' => [
				'locale'          => 'nb_NO',
				'label'           => 'Norwegian Bokmål',
				'nativeLabel'     => 'Norsk Bokmål',
				'hasSpellChecker' => true
			],
			'nl' => [
				'locale'          => 'nl_NL',
				'label'           => 'Dutch',
				'nativeLabel'     => 'Nederlands',
				'hasSpellChecker' => true
			],
			'pl' => [
				'locale'          => 'pl_PL',
				'label'           => 'Polish',
				'nativeLabel'     => 'Polski',
				'hasSpellChecker' => true
			],
			'pt' => [
				'locale'          => 'pt_BR',
				'label'           => 'Portuguese (Brazil)',
				'nativeLabel'     => 'Português',
				'hasSpellChecker' => true,
				'variants'        => [
					[
						'locale'     => 'pt_BR',
						'label'      => 'Portuguese (Brazil)',
						'shortLabel' => 'BR'
					],
					[
						'locale'     => 'pt_PT',
						'label'      => 'Portuguese (Portugal)',
						'shortLabel' => 'PT'
					]
				]
			],
			'ru' => [
				'locale'          => 'ru_RU',
				'label'           => 'Russian',
				'nativeLabel'     => 'Русский',
				'hasSpellChecker' => true
			],
			'sk' => [
				'locale'          => 'sk_SK',
				'label'           => 'Slovak',
				'nativeLabel'     => 'Slovenčina',
				'hasSpellChecker' => true
			],
			'sv' => [
				'locale'          => 'sv_SE',
				'label'           => 'Swedish',
				'nativeLabel'     => 'Svenska',
				'hasSpellChecker' => true
			],
			'tr' => [
				'locale'          => 'tr_TR',
				'label'           => 'Turkish',
				'nativeLabel'     => 'Türkçe',
				'hasSpellChecker' => true
			]
		];

		$this->supportedLanguagesCache = array_values( array_map( function( $code, $data ) {
			$entry = [
				'code'            => $code,
				'locale'          => $data['locale'],
				'label'           => $data['label'],
				'nativeLabel'     => $data['nativeLabel'],
				'hasSpellChecker' => $data['hasSpellChecker'],
				'exists'          => $data['hasSpellChecker'] && ! $this->needsDownload( $data['locale'] )
			];

			// Regional dialect variants share the language's analyzer (keyed by the
			// 2-letter code) but each ships its own Hunspell dictionary. The default
			// dialect is listed first and matches the primary `locale` above.
			if ( ! empty( $data['variants'] ) ) {
				$entry['variants'] = array_map( function( $variant ) use ( $data ) {
					return [
						'locale'     => $variant['locale'],
						'label'      => $variant['label'],
						'shortLabel' => $variant['shortLabel'],
						'exists'     => $data['hasSpellChecker'] && ! $this->needsDownload( $variant['locale'] )
					];
				}, $data['variants'] );
			}

			return $entry;
		}, array_keys( $languages ), array_values( $languages ) ) );

		return $this->supportedLanguagesCache;
	}

	/**
	 * Returns the locales whose dictionary files already exist on disk.
	 *
	 * @since 5.0.0
	 *
	 * @return string[] Locale strings (e.g. ['en_US', 'fr_FR']).
	 */
	public function getInstalledLocales() {
		if ( null !== $this->installedLocalesCache ) {
			return $this->installedLocalesCache;
		}

		$installed = [];
		foreach ( $this->getSupportedLanguages() as $language ) {
			if ( empty( $language['hasSpellChecker'] ) ) {
				continue;
			}

			if ( ! empty( $language['exists'] ) ) {
				$installed[] = $language['locale'];
			}

			if ( empty( $language['variants'] ) ) {
				continue;
			}

			foreach ( $language['variants'] as $variant ) {
				if ( ! empty( $variant['exists'] ) ) {
					$installed[] = $variant['locale'];
				}
			}
		}

		// The default dialect duplicates the primary locale, so drop repeats.
		$this->installedLocalesCache = array_values( array_unique( $installed ) );

		return $this->installedLocalesCache;
	}

	/**
	 * Returns every locale that can be spell-checked, including regional dialect variants.
	 *
	 * @since 5.0.0
	 *
	 * @return string[] Locale strings (e.g. ['en_US', 'en_GB', 'es_ES', 'es_MX']).
	 */
	public function getSpellCheckableLocales() {
		$locales = [];
		foreach ( $this->getSupportedLanguages() as $language ) {
			if ( empty( $language['hasSpellChecker'] ) ) {
				continue;
			}

			$locales[] = $language['locale'];

			if ( empty( $language['variants'] ) ) {
				continue;
			}

			foreach ( $language['variants'] as $variant ) {
				$locales[] = $variant['locale'];
			}
		}

		return array_values( array_unique( $locales ) );
	}

	/**
	 * Returns the dictionaries base URL.
	 *
	 * @since   5.0.0
	 *
	 * @return string The dictionaries base URL.
	 */
	public function getDictionariesBaseUrl() {
		return defined( 'AIOSEO_DICTIONARIES_URL' ) ? AIOSEO_DICTIONARIES_URL : $this->dictionariesBaseUrl;
	}

	/**
	 * Maps a WordPress user locale to a supported analysis language and reports its install state.
	 *
	 * Exact matches win, including a dialect variant (e.g. an en_GB site resolves
	 * to en_GB, pt_PT to pt_PT). Otherwise falls back to a same-language match on
	 * the default dialect (e.g. en_NZ maps to en_US). Returns `supported=false`
	 * only when no entry matches at all — non-Hunspell analysis languages (e.g.
	 * Japanese) still resolve, just with `hasSpellChecker=false`.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $userLocale The WordPress user locale (e.g. 'en_US', 'en_GB', 'pt_PT').
	 * @return array              Keys: locale, label, nativeLabel, supported, hasSpellChecker, needsDownload.
	 */
	public function resolveUserLocale( $userLocale ) {
		$default = [
			'locale'          => '',
			'label'           => '',
			'nativeLabel'     => '',
			'supported'       => false,
			'hasSpellChecker' => false,
			'needsDownload'   => false
		];

		if ( empty( $userLocale ) ) {
			return $default;
		}

		$languages = $this->getSupportedLanguages();
		$code      = $this->getLanguageCode( $userLocale );

		$match         = null;
		$matchedLocale = '';
		$matchedExists = false;
		$byCode        = null;
		foreach ( $languages as $language ) {
			if ( $language['locale'] === $userLocale ) {
				$match         = $language;
				$matchedLocale = $language['locale'];
				$matchedExists = ! empty( $language['exists'] );
				break;
			}

			if ( ! empty( $language['variants'] ) ) {
				foreach ( $language['variants'] as $variant ) {
					if ( $variant['locale'] === $userLocale ) {
						$match         = $language;
						$matchedLocale = $variant['locale'];
						$matchedExists = ! empty( $variant['exists'] );
						break 2;
					}
				}
			}

			if ( null === $byCode && $language['code'] === $code ) {
				$byCode = $language;
			}
		}

		if ( ! $match ) {
			if ( ! $byCode ) {
				return $default;
			}

			$match         = $byCode;
			$matchedLocale = $byCode['locale'];
			$matchedExists = ! empty( $byCode['exists'] );
		}

		return [
			'locale'          => $matchedLocale,
			'label'           => $match['label'],
			'nativeLabel'     => $match['nativeLabel'],
			'supported'       => true,
			'hasSpellChecker' => ! empty( $match['hasSpellChecker'] ),
			'needsDownload'   => ! empty( $match['hasSpellChecker'] ) && ! $matchedExists
		];
	}
}