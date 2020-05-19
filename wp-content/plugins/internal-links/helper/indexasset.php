<?php

namespace ILJ\Helper;

/**
 * Toolset for linkindex assets
 *
 * Methods for handling linkindex data
 *
 * @package ILJ\Helper
 * @since   1.1.0
 */
class IndexAsset
{
    /**
     * Returns all meta data to a specific asset from index
     *
     * @since  1.1.0
     * @param  int    $id   The id of the asset
     * @param  string $type The type of the asset (post, term or custom)
     * @return object
     */
    public static function getMeta( $id, $type )
    {
        global  $ilj_fs ;
        
        if ( 'post' == $type ) {
            $post = get_post( $id );
            if ( !$post ) {
                return null;
            }
            $asset_title = $post->post_title;
            $asset_url = get_the_permalink( $post->ID );
            $asset_url_edit = get_edit_post_link( $post->ID );
        }
        
        return (object) [
            'title'    => $asset_title,
            'url'      => $asset_url,
            'url_edit' => $asset_url_edit,
        ];
    }

}