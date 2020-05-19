<?php

namespace ILJ\Helper;

use  ILJ\Backend\Editor ;
use  ILJ\Database\Linkindex ;
use  ILJ\Database\Postmeta ;
use  ILJ\Database\Termmeta ;
/**
 * Statistics toolset
 *
 * Methods for providing statistics
 *
 * @package ILJ\Helper
 * @since   1.0.0
 */
class Statistic
{
    /**
     * A configureable wrapper for the aggregation of columns of the linkindex
     *
     * @since  1.0.0
     * @param  array $args Configuration of the selection
     * @return array
     */
    public static function getAggregatedCount( $args = array() )
    {
        $defaults = [
            "type"  => "link_from",
            "limit" => 10,
        ];
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        if ( !is_numeric( $limit ) ) {
            $limit = $defaults['limit'];
        }
        $inlinks = Linkindex::getGroupedCount( $type );
        return array_slice( $inlinks, 0, $limit );
    }
    
    /**
     * Returns the amount of configured keywords
     *
     * @since  1.1.3
     * @return int
     */
    public static function getConfiguredKeywordsCount()
    {
        global  $ilj_fs ;
        $configuredKeywords = [];
        $postmeta = Postmeta::getAllLinkDefinitions();
        foreach ( $postmeta as $meta ) {
            $keywords = get_post_meta( $meta->post_id, Postmeta::ILJ_META_KEY_LINKDEFINITION );
            $configuredKeywords = array_merge( $configuredKeywords, $keywords[0] );
        }
        return count( $configuredKeywords );
    }

}