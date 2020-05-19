<?php

namespace ILJ\Helper;

use  ILJ\Backend\User ;
use  ILJ\Core\IndexBuilder ;
use  ILJ\Helper\IndexAsset ;
use  ILJ\Database\Linkindex ;
use  ILJ\Backend\IndexRebuildNotifier ;
/**
 * Ajax toolset
 *
 * Methods for handling AJAX requests
 *
 * @package ILJ\Helper
 *
 * @since 1.0.0
 */
class Ajax
{
    /**
     * Searches the posts for a given phrase
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function searchPostsAction()
    {
        if ( !isset( $_POST['search'] ) && !isset( $_POST['per_page'] ) && !isset( $_POST['page'] ) ) {
            wp_die();
        }
        $search = sanitize_text_field( $_POST['search'] );
        $per_page = (int) $_POST['per_page'];
        $page = (int) $_POST['page'];
        $args = [
            "s"              => $search,
            "posts_per_page" => $per_page,
            "paged"          => $page,
        ];
        $query = new \WP_Query( $args );
        $data = [];
        foreach ( $query->posts as $post ) {
            $data[] = [
                "id"   => $post->ID,
                "text" => $post->post_title,
            ];
        }
        wp_send_json( $data );
        wp_die();
    }
    
    /**
     * Hides the promo box in the sidebar
     *
     * @since  1.1.2
     * @return void
     */
    public static function hidePromo()
    {
        User::update( 'hide_promo', true );
    }

}