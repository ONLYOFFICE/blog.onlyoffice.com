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
