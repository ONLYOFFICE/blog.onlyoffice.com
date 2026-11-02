<?php
/**
 * Plugin Name: ONLYOFFICE GraphQL Extras
 * Description: Site-specific extensions to the WPGraphQL schema (e.g. META orderby for connections that allow custom-meta sorting).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'graphql_enum_values', function ( $values, $type_name ) {
    if ( 'PostObjectsConnectionOrderbyEnum' === $type_name ) {
        $values['META'] = [
            'value'       => 'meta_value_num',
            'description' => 'Order by numeric meta value (combine with metaQuery to set the meta_key).',
        ];
    }
    return $values;
}, 10, 2 );

/**
 * Suppress the `wpml_notices` option write on front-end / GraphQL requests.
 *
 * WPML persists its admin-notices object (~270KB serialized blob) via
 * `update_option('wpml_notices', ...)` on virtually every request, regardless
 * of whether the GraphQL response was served from cache. Under concurrent
 * load (SSG builds, bot storms) hundreds of parallel UPDATEs of the same row
 * serialize on the row lock and thrash the InnoDB adaptive hash index,
 * collapsing the database. Notices are only meaningful in wp-admin, so the
 * write is allowed there (plus cron/CLI for background WPML jobs) and
 * short-circuited everywhere else by returning the old value: when the
 * filtered value equals the stored one, update_option() skips the UPDATE.
 */
add_filter( 'pre_update_option_wpml_notices', function ( $value, $old_value ) {
    if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
        return $value;
    }
    return $old_value;
}, 10, 2 );

/**
 * Skip SQL_CALC_FOUND_ROWS for singular GraphQL lookups.
 *
 * WPGraphQL resolves `post(id: ..., idType: URI)` through WP_Query with
 * posts_per_page = 1. WP core (with WPML JOINs on top) still issues
 * SQL_CALC_FOUND_ROWS to count total matches, which is useless for a
 * single-object lookup and forces a full scan of the joined result set.
 * Pagination of GraphQL connections is not affected: connection queries use
 * posts_per_page > 1 and their own cursor logic.
 */
add_action( 'pre_get_posts', function ( $query ) {
    if ( ! function_exists( 'is_graphql_request' ) || ! is_graphql_request() ) {
        return;
    }
    if ( 1 === (int) $query->get( 'posts_per_page' ) && ! $query->get( 'paged' ) ) {
        $query->set( 'no_found_rows', true );
    }
} );
