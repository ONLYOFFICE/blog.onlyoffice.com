<?php
/**
 * Plugin Name:       Cyr to Lat Enhanced
 * Plugin URI:        https://wordpress.org/plugins/cyr3lat/
 * Description:       Converts Cyrillic, European and Georgian characters in post and term slugs, and media file names to Latin characters. Useful for creating human-readable URLs.
 * Version:           3.7.3
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Ivijan Stefan Stipic
 * Author URI:        https://www.linkedin.com/in/ivijanstefanstipic/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cyr3lat
 * Domain Path:       /languages
 *
 * Credits:
 * - Based on cyr3lat / cyr2lat lineage (karevn, Sergey Biryukov, Atrax, webvitaly and others).
 * - Original Rus-To-Lat concept by Anton Skorobogatov.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class CTL_Enhanced {

	/**
	 * Plugin version.
	 */
	private const VERSION = '3.7.3';

    /**
     * Cron hook name for background conversion.
     */
    private const CRON_HOOK = 'ctl_enhanced_convert_existing_slugs';

    /**
     * Option name that stores conversion progress.
     */
    private const OPTION_PROGRESS = 'ctl_enhanced_conversion_progress';

    /**
     * Batch size to reduce timeouts.
     */
    private const BATCH_SIZE = 200;
	
	/**
	 * WordPress.org plugin slug.
	 */
	private const WPORG_SLUG = 'cyr3lat';

	/**
	 * Transient key used to cache WordPress.org rating data.
	 */
	private const TRANSIENT_WPORG_RATE = 'ctl_enhanced_wporg_rating_v1';

    /**
     * Cached transliteration table per-locale.
     *
     * @var array<string, array<string, string>>
     */
    private static array $table_cache = [];

    /**
     * Bootstrap plugin.
     */
    public static function init(): void {
		add_filter( 'plugin_row_meta', [ __CLASS__, 'plugin_meta' ], 10, 2 );
        add_filter( 'sanitize_title', [ __CLASS__, 'filter_sanitize_title' ], 9, 3 );
        add_filter( 'sanitize_file_name', [ __CLASS__, 'filter_sanitize_file_name' ], 10, 2 );

        add_action( self::CRON_HOOK, [ __CLASS__, 'convert_existing_slugs_batch' ] );

        register_activation_hook( __FILE__, [ __CLASS__, 'on_activation' ] );
        register_deactivation_hook( __FILE__, [ __CLASS__, 'on_deactivation' ] );
    }
	
	/**
	 * Add extra links to plugin row meta on Plugins page.
	 *
	 * @param array  $links Plugin row meta links.
	 * @param string $file  Plugin base file.
	 * @return array
	 */
	public static function plugin_meta( array $links, string $file ): array {

		if ( $file !== plugin_basename( __FILE__ ) ) {
			return $links;
		}

		$advanced_plugin = 'serbian-transliteration/serbian-transliteration.php';

		// is_plugin_active() is not always loaded on every admin screen.
		if ( is_admin() && ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Show link ONLY if the advanced plugin is NOT active.
		if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( $advanced_plugin ) ) {

			$links[] = sprintf(
				'<a href="%s" class="thickbox" title="%s">%s</a>',
				esc_url(
					admin_url(
						'plugin-install.php?tab=plugin-information&plugin=serbian-transliteration&TB_iframe=true&width=772&height=857'
					)
				),
				esc_attr__( 'Advanced Transliteration', 'cyr3lat' ),
				esc_html__( 'Advanced Transliteration', 'cyr3lat' )
			);
		}

		// Optional: neutral support link (safer than "5 stars" nudges).
		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://wordpress.org/support/plugin/cyr3lat/' ),
			esc_html__( 'Support', 'cyr3lat' )
		);
		
		$stars = self::get_wporg_rating_stars();
		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">%s</span></a>',
			esc_url( 'https://wordpress.org/support/plugin/cyr3lat/reviews/?filter=5#new-post' ),
			esc_attr__( 'Plugin reviews', 'cyr3lat' ),
			esc_attr__( 'View plugin reviews on WordPress.org', 'cyr3lat' ),
			esc_html( $stars )
		);

		return $links;
	}

    /**
     * Activation: schedule background conversion (single event).
     */
    public static function on_activation(): void {
        if ( ! get_option( self::OPTION_PROGRESS, false ) ) {
            add_option(
                self::OPTION_PROGRESS,
                [
                    'posts_offset' => 0,
                    'terms_offset' => 0,
                    'done_posts'   => 0,
                    'done_terms'   => 0,
                    'finished'     => 0,
                    'started_at'   => time(),
                ],
                '',
                false
            );
        }

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            // Schedule soon, but not immediately, to avoid activation timeouts.
            wp_schedule_single_event( time() + 15, self::CRON_HOOK );
        }
    }

    /**
     * Deactivation: unschedule cron hook.
     */
    public static function on_deactivation(): void {
        if( function_exists( 'wp_clear_scheduled_hook' ) ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
			return;
		}
		
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
        }
    }

    /**
     * Filter: sanitize_title.
     *
     * @param string $title     Sanitized title (may be empty depending on call order).
     * @param string $raw_title The title prior to sanitization.
     * @param string $context   The context for which the title is being sanitized.
     * @return string
     */
    public static function filter_sanitize_title( string $title, string $raw_title, string $context ): string {
        // Use raw title as source when available.
        $source = ( '' !== $raw_title ) ? $raw_title : $title;

        if ( '' === $source ) {
            return $title;
        }

        // Transliterate first, then let WP do its normal dash/cleanup later in the chain.
        $transliterated = self::transliterate( $source );

        // If WP calls this filter late (after dashify), keep it safe anyway.
        $transliterated = self::normalize_slug_string( $transliterated );

        /**
		 * Filter the sanitized and transliterated post or term slug.
		 *
		 * Runs after transliteration and normalization, before WordPress
		 * performs final processing.
		 *
		 * @since 3.7.0
		 *
		 * @param string $transliterated Resulting slug after transliteration.
		 * @param string $source         Original, unprocessed string.
		 * @param string $context        Sanitization context provided by WordPress.
		 * @return string Filtered slug value.
		 */
        return (string) apply_filters( 'ctl_enhanced_sanitized_title', $transliterated, $source, $context );
    }

    /**
     * Filter: sanitize_file_name.
     *
     * @param string $filename     Sanitized filename.
     * @param string $filename_raw Filename prior to sanitization.
     * @return string
     */
    public static function filter_sanitize_file_name( string $filename, string $filename_raw ): string {
        $source = ( '' !== $filename_raw ) ? $filename_raw : $filename;

        if ( '' === $source ) {
            return $filename;
        }

        $transliterated = self::transliterate( $source );

        // Keep dots for extensions, replace other invalid chars with hyphen.
        $transliterated = self::normalize_filename_string( $transliterated );

        /**
		 * Filter the sanitized and transliterated file name.
		 *
		 * Runs after transliteration and filename normalization,
		 * before WordPress finalizes the file name.
		 *
		 * @since 3.7.0
		 *
		 * @param string $transliterated Resulting filename after transliteration.
		 * @param string $source         Original, unprocessed filename.
		 * @return string Filtered filename value.
		 */
        return (string) apply_filters( 'ctl_enhanced_sanitized_file_name', $transliterated, $source );
    }

    /**
     * Transliterate using locale-specific table, with safe fallbacks.
     *
     * @param string $value
     * @return string
     */
    private static function transliterate( string $value ): string {
        $locale = (string) get_locale();

        if ( ! isset( self::$table_cache[ $locale ] ) ) {
            self::$table_cache[ $locale ] = self::build_table_for_locale( $locale );
        }

        $table = self::$table_cache[ $locale ];

        /**
		 * Filter the transliteration table.
		 *
		 * Allows themes and plugins to modify or extend the character
		 * mapping used for transliteration before it is applied.
		 *
		 * @since 3.7.0
		 *
		 * @param array<string, string> $table Associative array of character mappings.
		 * @return array<string, string> Modified transliteration table.
		 */
        $table = (array) apply_filters( 'ctl_table', $table );

        $value = strtr( $value, $table );

        // iconv is optional; do not assume it exists.
        if ( function_exists( 'iconv' ) ) {
            $converted = @iconv( 'UTF-8', 'UTF-8//TRANSLIT//IGNORE', $value );
            if ( false !== $converted && '' !== $converted ) {
                $value = $converted;
            }
        }

        return $value;
    }

    /**
     * Build transliteration table (Cyrillic + Georgian) and adjust by locale.
     *
     * @param string $locale
     * @return array<string, string>
     */
    private static function build_table_for_locale( string $locale ): array {
        $iso9_table = [
            'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Ѓ' => 'G',
            'Ґ' => 'G',  'Д' => 'D',  'Е' => 'E',  'Ё' => 'YO', 'Є' => 'YE',
            'Ж' => 'ZH', 'З' => 'Z',  'Ѕ' => 'Z',  'И' => 'I',  'Й' => 'J',
            'Ј' => 'J',  'І' => 'I',  'Ї' => 'YI', 'К' => 'K',  'Ќ' => 'K',
            'Л' => 'L',  'Љ' => 'L',  'М' => 'M',  'Н' => 'N',  'Њ' => 'N',
            'О' => 'O',  'П' => 'P',  'Р' => 'R',  'С' => 'S',  'Т' => 'T',
            'У' => 'U',  'Ў' => 'U',  'Ф' => 'F',  'Х' => 'H',  'Ц' => 'TS',
            'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH','Ъ' => '',
            'Ы' => 'Y',  'Ь' => '',   'Э' => 'E',  'Ю' => 'YU', 'Я' => 'YA',

            'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'ѓ' => 'g',
            'ґ' => 'g',  'д' => 'd',  'е' => 'e',  'ё' => 'yo', 'є' => 'ye',
            'ж' => 'zh', 'з' => 'z',  'ѕ' => 'z',  'и' => 'i',  'й' => 'j',
            'ј' => 'j',  'і' => 'i',  'ї' => 'yi', 'к' => 'k',  'ќ' => 'k',
            'л' => 'l',  'љ' => 'l',  'м' => 'm',  'н' => 'n',  'њ' => 'n',
            'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',
            'у' => 'u',  'ў' => 'u',  'ф' => 'f',  'х' => 'h',  'ц' => 'ts',
            'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh','ъ' => '',
            'ы' => 'y',  'ь' => '',   'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
        ];

        $geo2lat = [
            'ა' => 'a',  'ბ' => 'b',  'გ' => 'g',  'დ' => 'd',  'ე' => 'e',  'ვ' => 'v',
            'ზ' => 'z',  'თ' => 'th', 'ი' => 'i',  'კ' => 'k',  'ლ' => 'l',  'მ' => 'm',
            'ნ' => 'n',  'ო' => 'o',  'პ' => 'p',  'ჟ' => 'zh', 'რ' => 'r',  'ს' => 's',
            'ტ' => 't',  'უ' => 'u',  'ფ' => 'ph', 'ქ' => 'q',  'ღ' => 'gh', 'ყ' => 'qh',
            'შ' => 'sh', 'ჩ' => 'ch', 'ც' => 'ts', 'ძ' => 'dz', 'წ' => 'ts', 'ჭ' => 'tch',
            'ხ' => 'kh', 'ჯ' => 'j',  'ჰ' => 'h',
        ];

        $table = array_merge( $iso9_table, $geo2lat );

        // Locale adjustments.
        switch ( $locale ) {
            case 'bg_BG':
                $table['Щ'] = 'SHT';
                $table['щ'] = 'sht';
                $table['Ъ'] = 'A';
                $table['ъ'] = 'a';
                break;

            case 'uk':
            case 'uk_ua':
            case 'uk_UA':
                $table['И'] = 'Y';
                $table['и'] = 'y';
                break;
        }

        return $table;
    }

    /**
     * Normalize string for slugs: keep [A-Za-z0-9_-] and convert others to hyphen.
     *
     * @param string $value
     * @return string
     */
    private static function normalize_slug_string( string $value ): string {
        $value = preg_replace( "/[^A-Za-z0-9'_\-\.]+/u", '-', $value );
        $value = preg_replace( '/-+/', '-', (string) $value );
        $value = trim( (string) $value, '-' );

        return (string) $value;
    }

    /**
     * Normalize string for filenames: keep dots, underscores and dashes.
     *
     * @param string $value
     * @return string
     */
    private static function normalize_filename_string( string $value ): string {
        $value = preg_replace( "/[^A-Za-z0-9_\-\.]+/u", '-', $value );
        $value = preg_replace( '/-+/', '-', (string) $value );
        $value = preg_replace( '/\.-+/', '.', (string) $value );
        $value = trim( (string) $value, '-.' );

        return (string) $value;
    }

    /**
     * Convert existing post and term slugs in batches via cron.
     * Uses WP APIs to keep caches and hooks correct.
     */
    public static function convert_existing_slugs_batch(): void {
        $progress = get_option( self::OPTION_PROGRESS, [] );

        if ( empty( $progress ) || ! is_array( $progress ) ) {
            $progress = [
                'posts_offset' => 0,
                'terms_offset' => 0,
                'done_posts'   => 0,
                'done_terms'   => 0,
                'finished'     => 0,
                'started_at'   => time(),
            ];
        }

        if ( ! empty( $progress['finished'] ) ) {
            return;
        }

        $did_work = false;

        // 1) Convert posts first.
        $did_work = self::convert_posts_batch( $progress ) || $did_work;

        // 2) Then convert terms.
        $did_work = self::convert_terms_batch( $progress ) || $did_work;

        // Decide whether to reschedule.
        if ( empty( $progress['finished'] ) ) {
            update_option( self::OPTION_PROGRESS, $progress, false );

            // Reschedule only if we actually worked or there may still be more.
            if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
                wp_schedule_single_event( time() + 30, self::CRON_HOOK );
            }
        } else {
            update_option( self::OPTION_PROGRESS, $progress, false );
        }
    }

    /**
     * Convert a batch of posts.
     *
     * @param array $progress
     * @return bool True if any work was done.
     */
    private static function convert_posts_batch( array &$progress ): bool {
	
		$batch_size = self::get_batch_size( 'convert_posts_batch' );
        $offset = isset( $progress['posts_offset'] ) ? (int) $progress['posts_offset'] : 0;

        $args = [
            'post_type'      => 'any',
            'post_status'    => [ 'publish', 'future', 'private' ],
            'fields'         => 'ids',
            'posts_per_page' => $batch_size,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ];
		
        $query = new WP_Query( $args );

        if ( empty( $query->posts ) ) {
            return false;
        }

        $did_work = false;

        foreach ( $query->posts as $post_id ) {
            $post = get_post( (int) $post_id );
            if ( ! $post ) {
                continue;
            }

            $old_slug = (string) $post->post_name;
            $new_slug = self::normalize_slug_string( self::transliterate( urldecode( $old_slug ) ) );

            if ( '' === $new_slug || $new_slug === $old_slug ) {
                continue;
            }

            // Store old slug for redirects, WP uses this meta key as well.
            add_post_meta( (int) $post_id, '_wp_old_slug', $old_slug );

            // Update via API to ensure caches and hooks are correct.
            wp_update_post(
                [
                    'ID'        => (int) $post_id,
                    'post_name' => $new_slug,
                ]
            );

            $progress['done_posts'] = isset( $progress['done_posts'] ) ? (int) $progress['done_posts'] + 1 : 1;
            $did_work               = true;
        }

        $progress['posts_offset'] = $offset + $batch_size;

        return $did_work;
    }

    /**
     * Convert a batch of terms.
     *
     * @param array $progress
     * @return bool True if any work was done.
     */
    private static function convert_terms_batch( array &$progress ): bool {
	
		$batch_size = self::get_batch_size( 'convert_terms_batch' );
        $offset = isset( $progress['terms_offset'] ) ? (int) $progress['terms_offset'] : 0;

        $terms = get_terms(
            [
                'hide_empty' => false,
                'fields'     => 'all',
                'number'     => $batch_size,
                'offset'     => $offset,
                'orderby'    => 'term_id',
                'order'      => 'ASC',
            ]
        );
		
		// Temporary failure: do not mark as finished. Next cron run will retry.
		if ( is_wp_error( $terms ) ) {
			return false;
		}

		// If terms return empty, we are finished.
        if ( empty( $terms ) ) {
            $progress['finished'] = 1;
            return false;
        }

        $did_work = false;

        foreach ( $terms as $term ) {
            if ( empty( $term->term_id ) ) {
                continue;
            }

            $old_slug = (string) $term->slug;
            $new_slug = self::normalize_slug_string( self::transliterate( urldecode( $old_slug ) ) );

            if ( '' === $new_slug || $new_slug === $old_slug ) {
                continue;
            }

            // Update via API to ensure taxonomy caches and hooks are correct.
            $updated = wp_update_term(
                (int) $term->term_id,
                (string) $term->taxonomy,
                [
                    'slug' => $new_slug,
                ]
            );

            if ( ! is_wp_error( $updated ) ) {
                $progress['done_terms'] = isset( $progress['done_terms'] ) ? (int) $progress['done_terms'] + 1 : 1;
                $did_work               = true;
            }
        }

        $progress['terms_offset'] = $offset + $batch_size;

        return $did_work;
    }
	
	/**
	 * Get batch size for background processing.
	 *
	 * @param string $context
	 * @return int
	 */
	private static function get_batch_size( string $context = '' ): int {
		/**
		 * Filter the batch size used for background slug conversion.
		 *
		 * Allows developers to adjust how many posts or terms are processed
		 * per cron execution, which can be useful for low-memory or
		 * shared hosting environments.
		 *
		 * @since 3.7.2
		 *
		 * @param int    $size    Default batch size.
		 * @param string $context Execution context (e.g. 'convert_posts_batch', 'convert_terms_batch').
		 * @return int Adjusted batch size.
		 */
		$size = (int) apply_filters( 'ctl_enhanced_batch_size', self::BATCH_SIZE, $context );

		if ( $size < 1 ) {
			$size = self::BATCH_SIZE;
		} elseif ( $size > 2000 ) {
			$size = 2000;
		}

		return $size;
	}
	
	/**
	 * Get WordPress.org rating stars string, cached.
	 *
	 * @return string
	 */
	private static function get_wporg_rating_stars(): string {
		$cached = get_transient( self::TRANSIENT_WPORG_RATE );

		if ( is_array( $cached ) && isset( $cached['stars'] ) && is_string( $cached['stars'] ) ) {
			return $cached['stars'];
		}

		$percent = self::fetch_wporg_rating_percent();

		// If API fails, avoid saving a bad value.
		if ( $percent <= 0 ) {
			return '★★★★★';
		}

		$stars = self::format_stars_from_percent( $percent );

		set_transient(
			self::TRANSIENT_WPORG_RATE,
			[
				'percent' => $percent,
				'stars'   => $stars,
				'ts'      => time(),
			],
			12 * HOUR_IN_SECONDS
		);

		return $stars;
	}

	/**
	 * Fetch rating percent (0-100) from WordPress.org Plugin API.
	 *
	 * @return int
	 */
	private static function fetch_wporg_rating_percent(): int {
		$url = add_query_arg(
			[
				'action' => 'plugin_information',
				'slug'   => self::WPORG_SLUG,
			],
			'https://api.wordpress.org/plugins/info/1.2/'
		);

		$response = wp_remote_get(
			$url,
			[
				'timeout'     => 3,
				'redirection' => 2,
				'user-agent' => 'CTL-Enhanced/' . self::VERSION . '; ' . home_url(),
			]
		);

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return 0;
		}

		$body = (string) wp_remote_retrieve_body( $response );
		if ( '' === $body ) {
			return 0;
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) || ! isset( $data['rating'] ) ) {
			return 0;
		}

		$percent = (int) $data['rating'];
		if ( $percent < 0 ) {
			$percent = 0;
		} elseif ( $percent > 100 ) {
			$percent = 100;
		}

		return $percent;
	}

	/**
	 * Convert rating percent (0-100) to star string using ★⯪☆ symbols.
	 * Example: 90% -> 4.5 -> "★★★★⯪"
	 *
	 * @param int $percent
	 * @return string
	 */
	private static function format_stars_from_percent( int $percent ): string {
		$score = $percent / 20; // 0..5
		$score = round( $score * 2 ) / 2; // round to 0.5 steps

		$full = (int) floor( $score );
		$half = ( ( $score - $full ) >= 0.5 ) ? 1 : 0;

		$full  = max( 0, min( 5, $full ) );
		$half  = max( 0, min( 1, $half ) );
		$empty = 5 - $full - $half;

		return str_repeat( '★', $full ) . ( $half ? '⯪' : '' ) . str_repeat( '☆', $empty );
	}
}

CTL_Enhanced::init();