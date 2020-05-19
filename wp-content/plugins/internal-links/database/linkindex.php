<?php

namespace ILJ\Database;

/**
 * Database wrapper for the linkindex table
 *
 * @package ILJ\Database
 * @since   1.0.0
 */
class Linkindex
{
    const  ILJ_DATABASE_TABLE_LINKINDEX = "ilj_linkindex" ;
    /**
     * Cleans the whole index table
     *
     * @since  1.0.0
     * @return void
     */
    public static function flush()
    {
        global  $wpdb ;
        $wpdb->query( "TRUNCATE TABLE " . $wpdb->prefix . self::ILJ_DATABASE_TABLE_LINKINDEX );
    }
    
    /**
     * Returns all post outlinks from linkindex table
     *
     * @since  1.0.1
     * @param  int $id The post ID where outlinks should be retrieved
     * @return array
     */
    public static function getRules( $id, $type )
    {
        if ( !is_numeric( $id ) ) {
            return [];
        }
        global  $wpdb ;
        $query = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . self::ILJ_DATABASE_TABLE_LINKINDEX . " linkindex WHERE linkindex.link_from = %d AND linkindex.type_from = %s", $id, $type );
        return $wpdb->get_results( $query );
    }
    
    /**
     * Adds a post rule to the linkindex table
     *
     * @since  1.0.1
     * @param  int    $link_from Post ID which gives the link
     * @param  int    $link_to   Post ID where the link should point to
     * @param  string $anchor    The anchor text which gets used for linking
     * @param  string $type_from The type of asset which gives the link
     * @param  string $type_to   The type of asset which receives the link
     * @return void
     */
    public static function addRule(
        $link_from,
        $link_to,
        $anchor,
        $type_from,
        $type_to
    )
    {
        if ( !is_integer( (int) $link_from ) || !is_integer( (int) $link_to ) || !is_string( (string) $anchor ) ) {
            return;
        }
        global  $wpdb ;
        $wpdb->insert( $wpdb->prefix . self::ILJ_DATABASE_TABLE_LINKINDEX, [
            'link_from' => $link_from,
            'link_to'   => $link_to,
            'anchor'    => $anchor,
            'type_from' => $type_from,
            'type_to'   => $type_to,
        ], [
            '%d',
            '%d',
            '%s',
            '%s',
            '%s'
        ] );
    }
    
    /**
     * Aggregates and counts entries for a given column
     *
     * @since  1.0.0
     * @param  string $column The column in the linkindex table
     * @return array
     */
    public static function getGroupedCount( $column )
    {
        $allowed_columns = [ 'link_from', 'link_to', 'anchor' ];
        if ( !in_array( $column, $allowed_columns ) ) {
            return;
        }
        $type_mapping = [
            'link_from' => 'type_from',
            'link_to'   => 'type_to',
        ];
        $type = ( in_array( $column, array_keys( $type_mapping ) ) ? ', ' . $type_mapping[$column] . ' AS type ' : '' );
        global  $wpdb ;
        $query = sprintf(
            'SELECT  %1$s, COUNT(*) AS elements%2$s FROM %3$s linkindex GROUP BY %1$s ORDER BY elements DESC',
            $column,
            $type,
            $wpdb->prefix . self::ILJ_DATABASE_TABLE_LINKINDEX
        );
        return $wpdb->get_results( $query );
    }

}