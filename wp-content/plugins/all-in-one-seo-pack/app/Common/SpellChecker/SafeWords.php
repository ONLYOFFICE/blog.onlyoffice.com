<?php
namespace AIOSEO\Plugin\Common\SpellChecker;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the per-site safe-words custom dictionary.
 *
 * The file is a Hunspell .dic file stored at
 * uploads/aioseo/dictionaries/safe-words.dic. It is loaded into Hunspell
 * at runtime via add_dic() so its contents become indistinguishable from
 * the main dictionary during spell-check lookups.
 *
 * Format: line 1 is the word count, subsequent lines are one word each.
 *
 * @since 5.0.0
 */
class SafeWords {
	/**
	 * Lock transient key used to serialize concurrent writes.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	private $lockKey = 'aioseo_safe_words_lock';

	/**
	 * Lock TTL in seconds.
	 *
	 * @since 5.0.0
	 *
	 * @var int
	 */
	private $lockTtl = 10;

	/**
	 * Returns the absolute filesystem path to the safe-words file.
	 *
	 * @since 5.0.0
	 *
	 * @return string The absolute path.
	 */
	public function getSafeWordsPath() {
		$dictionary = new Dictionary();

		return trailingslashit( $dictionary->getDictionaryDir() ) . 'safe-words.dic';
	}

	/**
	 * Returns the public URL to the safe-words file.
	 *
	 * @since 5.0.0
	 *
	 * @return string The public URL.
	 */
	public function getSafeWordsUrl() {
		$dictionary = new Dictionary();

		return trailingslashit( $dictionary->getDictionaryBaseUrl() ) . 'safe-words.dic';
	}

	/**
	 * Returns the absolute filesystem path to the match-case sidecar file.
	 *
	 * The sidecar is a JSON object keyed by the exact-cased word for every
	 * match-case ("strict") safe word. Words absent from it are treated as
	 * case-insensitive, which keeps pre-existing dictionaries backward compatible.
	 *
	 * @since 5.0.0
	 *
	 * @return string The absolute path.
	 */
	public function getMatchCasePath() {
		$dictionary = new Dictionary();

		return trailingslashit( $dictionary->getDictionaryDir() ) . 'safe-words-meta.json';
	}

	/**
	 * Returns the public URL to the match-case sidecar file.
	 *
	 * @since 5.0.0
	 *
	 * @return string The public URL.
	 */
	public function getMatchCaseUrl() {
		$dictionary = new Dictionary();

		return trailingslashit( $dictionary->getDictionaryBaseUrl() ) . 'safe-words-meta.json';
	}

	/**
	 * Whether the safe-words file exists on disk.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if the file exists.
	 */
	public function exists() {
		return aioseo()->core->fs->exists( $this->getSafeWordsPath() );
	}

	/**
	 * Whether the match-case sidecar file exists on disk.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if the file exists.
	 */
	public function matchCaseExists() {
		return aioseo()->core->fs->exists( $this->getMatchCasePath() );
	}

	/**
	 * Reads all words from the safe-words file, skipping the count header.
	 *
	 * @since 5.0.0
	 *
	 * @return array<string>|\WP_Error The list of words, or a WP_Error on read failure.
	 */
	public function readAll() {
		if ( ! $this->exists() ) {
			return [];
		}

		$contents = aioseo()->core->fs->getContents( $this->getSafeWordsPath() );

		if ( false === $contents ) {
			return new \WP_Error(
				'safe_words_read_failed',
				__( "Couldn't read your dictionary file.", 'all-in-one-seo-pack' )
			);
		}

		// Only CR/LF, never \R: in byte mode \R also matches a lone 0x85, which is
		// the trailing byte of common letters (Arabic م U+0645, Polish ą U+0105),
		// so those words got torn in half mid-sequence. CR/LF bytes can never
		// appear inside a UTF-8 sequence, so this stays safe on invalid input —
		// unlike a /u pattern, which makes preg_split() fail outright.
		$lines = preg_split( '/\r\n|\r|\n/', $contents );

		// Drop the count header.
		array_shift( $lines );

		// Invalid UTF-8 only ever comes from a file the old split already tore
		// apart. Those fragments can't be repaired and can't be deleted either —
		// their bad bytes reach the browser as "?", so a delete request never
		// matches what's on disk. Dropping them lets the next write heal the file.
		$lines = array_filter( array_map( 'trim', $lines ), function( $word ) {
			return '' !== $word && '' !== wp_check_invalid_utf8( $word );
		} );

		return array_values( $lines );
	}

	/**
	 * Reads the match-case sidecar as an exact-cased word => true map.
	 *
	 * @since 5.0.0
	 *
	 * @return array<string,bool> The match-case words keyed by their exact form.
	 */
	public function readMatchCase() {
		if ( ! aioseo()->core->fs->exists( $this->getMatchCasePath() ) ) {
			return [];
		}

		$contents = aioseo()->core->fs->getContents( $this->getMatchCasePath() );

		if ( false === $contents || '' === trim( (string) $contents ) ) {
			return [];
		}

		$decoded = json_decode( $contents, true );

		if ( ! is_array( $decoded ) ) {
			return [];
		}

		$map = [];
		foreach ( $decoded as $word => $enabled ) {
			$word = trim( (string) $word );
			if ( '' !== $word && $enabled ) {
				$map[ $word ] = true;
			}
		}

		return $map;
	}

	/**
	 * Returns a filtered, sorted, paginated slice of the safe-words list.
	 *
	 * Sort is case-insensitive alphabetical. Search is case-insensitive
	 * substring match. Pagination is 1-based.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $search  Optional search string (case-insensitive contains).
	 * @param  int    $page    The 1-based page index.
	 * @param  int    $perPage The number of words per page.
	 * @return array|\WP_Error {
	 *     @type array[] $words   The current page of words, each { word, matchCase }.
	 *     @type int     $total   The total matching count (post-search).
	 *     @type int     $page    The 1-based page index.
	 *     @type int     $perPage The page size used.
	 * }
	 */
	public function filterAndPaginate( $search = '', $page = 1, $perPage = 50 ) {
		$plain = $this->readAll();

		if ( is_wp_error( $plain ) ) {
			return $plain;
		}

		$matchCase = $this->readMatchCase();

		$words = [];
		foreach ( $plain as $word ) {
			$words[] = [
				'word'      => $word,
				'matchCase' => false
			];
		}
		foreach ( array_keys( $matchCase ) as $word ) {
			$words[] = [
				'word'      => $word,
				'matchCase' => true
			];
		}

		$search = is_string( $search ) ? trim( $search ) : '';

		if ( '' !== $search ) {
			$needle = $this->lowercase( $search );
			$words  = array_values( array_filter( $words, function( $entry ) use ( $needle ) {
				return false !== strpos( $this->lowercase( $entry['word'] ), $needle );
			} ) );
		}

		usort( $words, function( $a, $b ) {
			return strcmp( $this->lowercase( $a['word'] ), $this->lowercase( $b['word'] ) );
		} );

		$total   = count( $words );
		$perPage = max( 1, (int) $perPage );
		$page    = max( 1, (int) $page );
		$offset  = ( $page - 1 ) * $perPage;
		$slice   = array_slice( $words, $offset, $perPage );

		return [
			'words'   => array_values( $slice ),
			'total'   => $total,
			'page'    => $page,
			'perPage' => $perPage
		];
	}

	/**
	 * Adds a word to the safe-words dictionary.
	 *
	 * @since 5.0.0
	 *
	 * @param  string           $word      The raw word submitted by the user.
	 * @param  bool             $matchCase Whether the word must match its exact casing.
	 * @return string|\WP_Error            The sanitized word on success, WP_Error on failure.
	 */
	public function addWord( $word, $matchCase = false ) {
		$sanitized = $this->sanitizeWord( $word );

		if ( '' === $sanitized ) {
			return new \WP_Error(
				'safe_words_invalid',
				__( 'Word cannot be empty.', 'all-in-one-seo-pack' )
			);
		}

		if ( ! $this->acquireLock() ) {
			return new \WP_Error(
				'safe_words_busy',
				__( 'Another change is already in progress. Please try again in a moment.', 'all-in-one-seo-pack' )
			);
		}

		$words = $this->readAll();

		if ( is_wp_error( $words ) ) {
			$this->releaseLock();

			return $words;
		}

		$matchCaseMap = $this->readMatchCase();

		if ( $matchCase ) {
			// Exact-case store keyed on the sanitized word; already present is a no-op.
			if ( isset( $matchCaseMap[ $sanitized ] ) ) {
				$this->releaseLock();

				return $sanitized;
			}

			// Write the destination (sidecar) before pruning the source (plain
			// store) so a mid-sequence failure leaves the word in both stores
			// (dedup'd on read) rather than lost from both.
			$matchCaseMap[ $sanitized ] = true;

			$writeResult = $this->writeMatchCase( $matchCaseMap );

			if ( is_wp_error( $writeResult ) ) {
				$this->releaseLock();

				return $writeResult;
			}

			// Keep each exact string in exactly one store: drop only the same-cased
			// plain form. Words differing only in casing are distinct and untouched.
			$filtered = array_values( array_filter( $words, function( $existing ) use ( $sanitized ) {
				return $existing !== $sanitized;
			} ) );

			if ( count( $filtered ) !== count( $words ) ) {
				$plainResult = $this->writeWords( $filtered );

				if ( is_wp_error( $plainResult ) ) {
					$this->releaseLock();

					return $plainResult;
				}
			}

			$this->releaseLock();

			return $sanitized;
		}

		// Idempotent: block only on an exact-case duplicate in either store. Words
		// differing only in casing (e.g. "AIOSEO" vs "aioseo") are distinct.
		if (
			in_array( $sanitized, $words, true ) ||
			isset( $matchCaseMap[ $sanitized ] )
		) {
			$this->releaseLock();

			return $sanitized;
		}

		$words[] = $sanitized;

		$writeResult = $this->writeWords( $words );

		$this->releaseLock();

		if ( is_wp_error( $writeResult ) ) {
			return $writeResult;
		}

		return $sanitized;
	}

	/**
	 * Sets or clears the match-case flag for an existing safe word.
	 *
	 * Toggling moves the word between the plain .dic store (case-insensitive,
	 * loaded into Hunspell) and the sidecar (exact-case, enforced in JS). The
	 * exact casing supplied by the caller is preserved when enabling match case.
	 *
	 * @since 5.0.0
	 *
	 * @param  string           $word      The word to update (exact casing preserved).
	 * @param  bool             $matchCase The desired match-case state.
	 * @return string|\WP_Error            The sanitized word on success, WP_Error on failure.
	 */
	public function setMatchCase( $word, $matchCase ) {
		$sanitized = $this->sanitizeWord( $word );

		if ( '' === $sanitized ) {
			return new \WP_Error(
				'safe_words_invalid',
				__( 'Word cannot be empty.', 'all-in-one-seo-pack' )
			);
		}

		if ( ! $this->acquireLock() ) {
			return new \WP_Error(
				'safe_words_busy',
				__( 'Another change is already in progress. Please try again in a moment.', 'all-in-one-seo-pack' )
			);
		}

		$words = $this->readAll();

		if ( is_wp_error( $words ) ) {
			$this->releaseLock();

			return $words;
		}

		$matchCaseMap = $this->readMatchCase();

		// Compute both target states, then write the DESTINATION store before
		// pruning the SOURCE store so a mid-sequence write failure leaves the
		// word in both stores (dedup'd on read) rather than lost from both.
		// Identity is exact-case: a move touches only the same-cased string, so
		// "AIOSEO" and "aioseo" are independent and never affect each other.
		if ( $matchCase ) {
			$prunedPlain = array_values( array_filter( $words, function( $existing ) use ( $sanitized ) {
				return $existing !== $sanitized;
			} ) );
			$targetMatchCase               = $matchCaseMap;
			$targetMatchCase[ $sanitized ] = true;

			// Destination: sidecar.
			$metaResult = $this->writeMatchCase( $targetMatchCase );

			if ( is_wp_error( $metaResult ) ) {
				$this->releaseLock();

				return $metaResult;
			}

			// Source: plain store.
			$plainResult = $this->writeWords( $prunedPlain );

			if ( is_wp_error( $plainResult ) ) {
				$this->releaseLock();

				return $plainResult;
			}
		} else {
			$targetMatchCase = $matchCaseMap;
			unset( $targetMatchCase[ $sanitized ] );

			$targetPlain = $words;

			if ( ! in_array( $sanitized, $targetPlain, true ) ) {
				$targetPlain[] = $sanitized;
			}

			// Destination: plain store.
			$plainResult = $this->writeWords( $targetPlain );

			if ( is_wp_error( $plainResult ) ) {
				$this->releaseLock();

				return $plainResult;
			}

			// Source: sidecar.
			$metaResult = $this->writeMatchCase( $targetMatchCase );

			if ( is_wp_error( $metaResult ) ) {
				$this->releaseLock();

				return $metaResult;
			}
		}

		$this->releaseLock();

		return $sanitized;
	}

	/**
	 * Removes a word from the safe-words dictionary.
	 *
	 * @since 5.0.0
	 *
	 * @param  string           $word The word to remove.
	 * @return string|\WP_Error       The sanitized word on success, WP_Error on failure.
	 */
	public function removeWord( $word ) {
		$sanitized = $this->sanitizeWord( $word );

		if ( '' === $sanitized ) {
			return new \WP_Error(
				'safe_words_invalid',
				__( 'Word cannot be empty.', 'all-in-one-seo-pack' )
			);
		}

		if ( ! $this->acquireLock() ) {
			return new \WP_Error(
				'safe_words_busy',
				__( 'Another change is already in progress. Please try again in a moment.', 'all-in-one-seo-pack' )
			);
		}

		$words = $this->readAll();

		if ( is_wp_error( $words ) ) {
			$this->releaseLock();

			return $words;
		}

		// Exact-case removal: only the same-cased string is dropped, so removing
		// "AIOSEO" leaves a separate "aioseo" entry intact.
		$filtered = array_values( array_filter( $words, function( $existing ) use ( $sanitized ) {
			return $existing !== $sanitized;
		} ) );

		$matchCaseMap    = $this->readMatchCase();
		$prunedMatchCase = $matchCaseMap;
		unset( $prunedMatchCase[ $sanitized ] );

		// Idempotent: if it wasn't in either store, succeed silently.
		if ( count( $filtered ) === count( $words ) && count( $prunedMatchCase ) === count( $matchCaseMap ) ) {
			$this->releaseLock();

			return $sanitized;
		}

		// Removal clears both stores, so there is no destination to preserve: a
		// mid-sequence failure leaves the word in one store, still listed and
		// re-removable (recoverable), never silently lost.
		if ( count( $filtered ) !== count( $words ) ) {
			$writeResult = $this->writeWords( $filtered );

			if ( is_wp_error( $writeResult ) ) {
				$this->releaseLock();

				return $writeResult;
			}
		}

		if ( count( $prunedMatchCase ) !== count( $matchCaseMap ) ) {
			$metaResult = $this->writeMatchCase( $prunedMatchCase );

			if ( is_wp_error( $metaResult ) ) {
				$this->releaseLock();

				return $metaResult;
			}
		}

		$this->releaseLock();

		return $sanitized;
	}

	/**
	 * Sanitizes a raw user-submitted word for safe storage in the .dic file.
	 *
	 * Strips HTML, newlines, control characters, and the Hunspell affix-flag
	 * separator, and folds typographic single quotes to a straight apostrophe.
	 * Preserves UTF-8 letters so non-English locales work.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $word The raw input.
	 * @return string       The sanitized word, possibly empty.
	 */
	private function sanitizeWord( $word ) {
		if ( ! is_string( $word ) ) {
			return '';
		}

		$word = sanitize_text_field( $word );
		$word = preg_replace( '/[\x00-\x1F\x7F]/u', '', $word );
		$word = str_replace( '/', '', $word );

		// The spell checker normalizes single quotes to a straight apostrophe
		// before every lookup ({@see normalizeSingle} in quotes.js), so a word
		// stored with a curly apostrophe (e.g. "WPBeginner’s") would never match
		// the normalized token and stay flagged. Fold the same set here so stored
		// words live in the space the checker queries.
		$word = str_replace( [ '‘', '’', '‛', '`', '‹', '›' ], "'", $word );

		return trim( $word );
	}

	/**
	 * UTF-8-aware lowercase helper.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $value The value to lowercase.
	 * @return string        The lowercased value.
	 */
	private function lowercase( $value ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $value, 'UTF-8' );
		}

		return strtolower( $value );
	}

	/**
	 * Atomically writes the .dic file. Caller must hold the lock.
	 *
	 * @since 5.0.0
	 *
	 * @param  array<string>  $words The full list to persist.
	 * @return true|\WP_Error
	 */
	private function writeWords( $words ) {
		$finalPath = $this->getSafeWordsPath();
		$dir       = dirname( $finalPath );

		if ( ! wp_mkdir_p( $dir ) ) {
			return new \WP_Error(
				'safe_words_mkdir_failed',
				__( "Couldn't create the dictionary folder. Please check write permissions on /wp-content/uploads/aioseo/dictionaries/.", 'all-in-one-seo-pack' )
			);
		}

		$count    = count( $words );
		$contents = $count . "\n" . ( $count ? implode( "\n", $words ) . "\n" : '' );

		$tmpPath = $finalPath . '.tmp';
		$fs      = aioseo()->core->fs;

		$written = $fs->putContents( $tmpPath, $contents );

		if ( ! $written || ! $fs->isWpfsValid() || ! $fs->fs->move( $tmpPath, $finalPath, true ) ) {
			wp_delete_file( $tmpPath );

			return new \WP_Error(
				'safe_words_write_failed',
				__( "Couldn't save your dictionary file. Please check write permissions on /wp-content/uploads/aioseo/dictionaries/safe-words.dic.", 'all-in-one-seo-pack' )
			);
		}

		return true;
	}

	/**
	 * Atomically writes the match-case sidecar. Caller must hold the lock.
	 *
	 * @since 5.0.0
	 *
	 * @param  array<string,bool> $map The exact-cased word => true map to persist.
	 * @return true|\WP_Error
	 */
	private function writeMatchCase( $map ) {
		$finalPath = $this->getMatchCasePath();
		$dir       = dirname( $finalPath );

		if ( ! wp_mkdir_p( $dir ) ) {
			return new \WP_Error(
				'safe_words_mkdir_failed',
				__( "Couldn't create the dictionary folder. Please check write permissions on /wp-content/uploads/aioseo/dictionaries/.", 'all-in-one-seo-pack' )
			);
		}

		$contents = empty( $map )
			? '{}'
			: wp_json_encode( (object) $map );

		$tmpPath = $finalPath . '.tmp';
		$fs      = aioseo()->core->fs;

		$written = $fs->putContents( $tmpPath, $contents );

		if ( ! $written || ! $fs->isWpfsValid() || ! $fs->fs->move( $tmpPath, $finalPath, true ) ) {
			wp_delete_file( $tmpPath );

			return new \WP_Error(
				'safe_words_write_failed',
				__( "Couldn't save your dictionary file. Please check write permissions on /wp-content/uploads/aioseo/dictionaries/safe-words-meta.json.", 'all-in-one-seo-pack' )
			);
		}

		return true;
	}

	/**
	 * Attempts to acquire the write lock.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if acquired, false if another writer holds it.
	 */
	private function acquireLock() {
		if ( get_transient( $this->lockKey ) ) {
			return false;
		}

		set_transient( $this->lockKey, 1, $this->lockTtl );

		return true;
	}

	/**
	 * Releases the write lock.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	private function releaseLock() {
		delete_transient( $this->lockKey );
	}
}