<?php

namespace ILJ\Database;

/**
 * Postmeta wrapper for the inlink postmeta
 *
 * @package ILJ\Database
 * @since   1.0.0
 */
class Postmeta
{
    const ILJ_META_KEY_LINKDEFINITION = 'ilj_linkdefinition';

    /**
     * Returns all Linkdefinitions from postmeta table
     *
     * @since  1.0.0
     * @return array
     */
    public static function getAllLinkDefinitions()
    {
        global $wpdb;
        $meta_key = self::ILJ_META_KEY_LINKDEFINITION;
        $query    = "
            SELECT postmeta.*
            FROM $wpdb->postmeta postmeta
            LEFT JOIN $wpdb->posts posts ON postmeta.post_id = posts.ID
            WHERE postmeta.meta_key = '$meta_key'
            AND posts.post_status = 'publish'
        ";
        return $wpdb->get_results($query);
    }

    /**
     * Removes all link definitions from postmeta table
     *
     * @since  1.1.3
     * @return int
     */
    public static function removeAllLinkDefinitions()
    {
        global $wpdb;
        $meta_key = self::ILJ_META_KEY_LINKDEFINITION;
        return $wpdb->delete($wpdb->postmeta, array( 'meta_key' => $meta_key ));
    }
}
