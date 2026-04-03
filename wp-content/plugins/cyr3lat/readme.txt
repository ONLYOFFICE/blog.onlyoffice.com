=== Cyr to Lat Enhanced ===
Contributors: ivijanstefan, creativform, Atrax, SergeyBiryukov, karevn, webvitaly
Tags: cyrillic, transliteration, russian, ukrainian, slugs
Requires at least: 2.3
Tested up to: 6.9
Stable tag: 3.7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Converts Cyrillic, European and Georgian characters in post, term slugs and media file names into Latin characters.

== Description ==

Cyr to Lat Enhanced automatically converts Cyrillic, European and Georgian characters in post slugs, term slugs and media file names into Latin characters.  
It helps maintain clean, readable and consistent URLs on WordPress sites that use non-Latin scripts.

The plugin integrates directly with WordPress core sanitization (`sanitize_title` and `sanitize_file_name`) and performs transliteration at the moment slugs and filenames are generated.  
It does **not** modify post content, titles or front-end text. Its scope is strictly limited to URLs and file names.

On activation, existing post and term slugs are converted in the background using safe, batch-based processing via WP-Cron.  
WordPress core APIs are used to preserve permalink integrity and existing redirects.

Transliteration is based on an ISO 9-style mapping table with built-in support for:
- Russian
- Belarusian
- Ukrainian
- Bulgarian
- Macedonian
- Georgian

Locale-specific adjustments are applied automatically, and the transliteration table can be customized using a public filter.

Cyr to Lat Enhanced is intentionally lightweight and focused:
- no admin interface
- no settings pages
- no bidirectional conversion
- no data collection or tracking

The plugin continues the cyr2lat / cyr3lat lineage and remains compatible with the original approach introduced by Sergey Biryukov.

== Advanced Transliteration ==

For advanced transliteration needs such as full content conversion, bidirectional processing, extended language rules and fine-grained control, consider the separate plugin:
https://wordpress.org/plugins/serbian-transliteration/

That plugin is designed for complex multilingual setups and broader language processing beyond slugs and file names.

== Installation ==

1. Upload the `cyr3lat` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. If `iconv` is available on your server, it will be used as a best-effort fallback.

If you experience unexpected characters in slugs, please open a support topic and include your server OS, PHP version and site locale.

== Frequently Asked Questions ==

= How can I define my own substitutions? =

You can modify or extend the transliteration table by using the `ctl_table` filter.

Add this code to your theme's `functions.php` file:

`
function my_cyr_to_lat_table( $ctl_table ) {
    $ctl_table['Ъ'] = 'U';
    $ctl_table['ъ'] = 'u';
    return $ctl_table;
}
add_filter( 'ctl_table', 'my_cyr_to_lat_table' );
`

= How can I adjust the background batch size? =

By default, the plugin processes slugs in batches to avoid timeouts.  
On slower or low-memory servers, you may want to reduce the batch size.

Add this code to your theme's `functions.php` file:

`
function my_ctl_batch_size( $size, $context ) {
    // Apply the same batch size for posts and terms.
    return 100;
}
add_filter( 'ctl_enhanced_batch_size', 'my_ctl_batch_size', 10, 2 );
`

= Can I modify the generated slugs after transliteration? =

Yes. You can adjust the final slug value after transliteration and normalization
by using the `ctl_enhanced_sanitized_title` filter.

Add this code to your theme's `functions.php` file:

`
function my_ctl_modify_slug( $slug, $source, $context ) {
    // Example: append a suffix to all generated slugs.
    return $slug . '-custom';
}
add_filter( 'ctl_enhanced_sanitized_title', 'my_ctl_modify_slug', 10, 3 );
`

= Can I modify media file names after transliteration? =

Yes. You can adjust media file names after transliteration and normalization
by using the `ctl_enhanced_sanitized_file_name` filter.

Add this code to your theme's `functions.php` file:

`
function my_ctl_modify_filename( $filename, $source ) {
    // Example: prepend a prefix to all media file names.
    return 'media-' . $filename;
}
add_filter( 'ctl_enhanced_sanitized_file_name', 'my_ctl_modify_filename', 10, 2 );
`

= What is the difference between Cyr to Lat Enhanced, cyr2lat and Transliterator? =

Cyr to Lat Enhanced is a continuation of the original cyr2lat / cyr3lat lineage.
Its purpose is strictly limited to transliteration of post slugs, term slugs and media file names.

The original [cyr2lat](https://wordpress.org/plugins/cyr2lat/) plugin by Sergey Biryukov introduced a simple and effective way to generate Latin slugs from Cyrillic titles.
Cyr to Lat Enhanced preserves this philosophy while modernizing the codebase, improving reliability and ensuring compatibility with current WordPress versions.

[Transliterator](https://wordpress.org/plugins/serbian-transliteration/) is a separate plugin with a different scope and goals.
It is designed for advanced and complex use cases, including:
- transliteration of post content and front-end text
- bidirectional conversion (Latin and Cyrillic)
- extended language rules and exclusions
- fine-grained control over when and where transliteration is applied

Because these plugins solve different problems, they are intentionally separated.
Cyr to Lat Enhanced focuses on clean URLs and file names with zero configuration.
Transliterator focuses on full-language processing and advanced multilingual setups.

Users should choose the plugin that best matches their needs:
- use Cyr to Lat Enhanced for simple, automatic slug and filename transliteration
- use [Transliterator](https://wordpress.org/plugins/serbian-transliteration/) when full content-level transliteration is required

== Changelog ==

= 3.7.3 =
* Minor improvements to plugin metadata presentation in the WordPress admin.
* Improved robustness and consistency of internal admin-only logic.
* No changes to front-end behavior or existing URLs.

= 3.7.2 =
* Added filter support for adjusting background batch size during slug conversion.
* Improved safety and flexibility of background processing for large sites.
* Refined cron cleanup on plugin deactivation.
* Minor internal code cleanup and consistency improvements.
* No changes to front-end behavior or existing URLs.

= 3.7.1 =
* Adopted plugin maintenance and modernized codebase for current WordPress standards
* Reworked sanitize_title and sanitize_file_name hooks to use proper arguments
* Removed debug_backtrace usage to improve performance and reliability
* Replaced direct database updates with WordPress APIs (wp_update_post, wp_update_term)
* Added background batch processing for existing slugs via WP-Cron
* Improved transliteration handling with locale-aware caching
* General code cleanup and stability improvements

= 3.7 =
* Added prepare() for every SQL query

= 3.6 =
* Added esc_sql for SQL query

= 3.5 =
* Removed quotes from table which added extra dashes

= 3.4 =
* Fixes for Ukrainian characters

= 3.3.3 =
* Bugfix: posts of status "future" were not affected

= 3.3.2 =
* Added support for European diacritics

= 3.3.1 =
* Added Georgian transliteration table
* A problem with some letters causing apostrophes in slugs was resolved

= 3.3 =
* Internal improvements

= 3.2 =
* Added transliteration when publishing via XML-RPC
* Fixed Invalid Taxonomy error when viewing the most used tags

= 3.1 =
* Fixed transliteration when saving a draft

= 3.0 =
* Added automatic conversion of existing post, page and term slugs
* Added saving of existing post and page permalinks integrity
* Added transliteration of attachment file names
* Adjusted transliteration table in accordance with ISO 9 standard
* Included Russian, Belarusian, Ukrainian, Bulgarian and Macedonian characters
* Added filter for the transliteration table

= 2.1 =
* Optimized filter call

= 2.0 =
* Added check for existing terms

= 1.0.1 =
* Updated description

= 1.0 =
* Initial release

== Adoption Notice ==

This plugin represents the continued maintenance of the original "Cyr to Lat Enhanced" project from the cyr3lat lineage.

The project has been revived to ensure long-term stability, modern WordPress compatibility and responsible maintenance, while preserving the original behavior and purpose.

== Credits ==

Original upstream concept: Rus-To-Lat by Anton Skorobogatov  
cyr2lat / cyr3lat lineage contributors: karevn, Atrax, Sergey Biryukov, webvitaly

Maintainer: Ivijan Stefan Stipic (INFINITUM FORM)

== Legal Notice ==

This plugin is licensed under the GPLv2 or later license.

All new contributions are released under the same license.
