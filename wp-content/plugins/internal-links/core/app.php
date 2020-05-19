<?php

namespace ILJ\Core;

use  ILJ\Backend\AdminMenu ;
use  ILJ\Helper\Replacement ;
use  ILJ\Backend\Environment ;
use  ILJ\Helper\Capabilities ;
use  ILJ\Enumeration\TagExclusion ;
use  ILJ\Backend\Menupage\Settings ;
/**
 * The main app
 *
 * Coordinates all steps for the plugin usage
 *
 * @package ILJ\Core
 *
 * @since 1.0.1
 */
class App
{
    private static  $instance = null ;
    /**
     * Initializes the construction of the app
     *
     * @static
     * @since  1.0.1
     *
     * @return void
     */
    public static function init()
    {
        if ( null !== self::$instance ) {
            return;
        }
        global  $ilj_fs ;
        self::$instance = new self();
        $last_version = Environment::get( 'last_version' );
        
        if ( $last_version != ILJ_VERSION ) {
            ilj_install_db();
            Options::setOptionsDefault();
        }
        
        if ( $ilj_fs->is__premium_only() && $ilj_fs->can_use_premium_code() && !Environment::get( 'pro_initiated' ) ) {
            Options::setOptionsDefault();
        }
    }
    
    protected function __construct()
    {
        global  $ilj_fs ;
        $this->initSettings();
        $this->loadIncludes();
        add_action( 'admin_init', [ '\\ILJ\\Core\\Options', 'init' ] );
        add_action( 'admin_init', [ '\\ILJ\\Backend\\Editor', 'addAssets' ] );
        add_action( 'plugins_loaded', [ $this, 'afterPluginsLoad' ] );
        add_action( 'publish_future_post', [ $this, 'publishFuturePost' ], 99 );
        
        if ( $ilj_fs->is__premium_only() && $ilj_fs->can_use_premium_code() ) {
            add_action( 'init', function () {
                \ILJ\Posttypes\CustomLinks::init();
            } );
            add_action( 'wp_loaded', function () {
                $taxonomies = get_taxonomies( [
                    'public'   => true,
                    '_builtin' => false,
                ] );
                $taxonomies = array_merge( $taxonomies, [ 'category', 'post_tag' ] );
                foreach ( $taxonomies as $taxonomy ) {
                    add_action( $taxonomy . '_edit_form_fields', [ '\\ILJ\\Backend\\Editor', 'renderKeywordMetaBoxTaxonomy__premium_only' ] );
                    add_action( 'edit_' . $taxonomy, [ '\\ILJ\\Backend\\Editor', 'saveKeywordMetaTaxonomy__premium_only' ] );
                }
            } );
        }
    
    }
    
    /**
     * Initialising all menu and settings related stuff
     *
     * @since 1.0.1
     *
     * @return type
     */
    protected function initSettings()
    {
        add_action( 'admin_menu', [ '\\ILJ\\Backend\\AdminMenu', 'init' ] );
        add_filter( 'plugin_action_links_' . ILJ_NAME, [ $this, 'addSettingsLink' ] );
    }
    
    /**
     * Loads all include files
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function loadIncludes()
    {
        $include_files = [ 'install' ];
        global  $ilj_fs ;
        foreach ( $include_files as $file ) {
            include_once ILJ_PATH . 'includes/' . $file . '.php';
        }
    }
    
    /**
     * Handles post transitions for scheduled posts
     *
     * @since 1.1.5
     * @param $post Post ID.
     *
     * @return void
     */
    public function publishFuturePost( $post )
    {
        global  $ilj_fs ;
        $app = self::$instance;
        $app->triggerRebuildIndex( $post, 'post' );
    }
    
    /**
     * Gets called after all plugins are loaded for registering actions and filter
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function afterPluginsLoad()
    {
        $this->registerActions();
        $this->registerFilter();
        load_plugin_textdomain( 'ILJ', false, dirname( ILJ_NAME ) . '/languages/' );
        global  $ilj_fs ;
        if ( $ilj_fs->is__premium_only() && $ilj_fs->can_use_premium_code() ) {
            \ILJ\Core\Shortcodes::register();
        }
    }
    
    /**
     * Registers all actions for the plugin
     *
     * @since 1.1.5
     *
     * @return void
     */
    protected function registerActions()
    {
        global  $ilj_fs ;
        $capability = current_user_can( 'administrator' );
        add_action( 'admin_post_' . Options::KEY, array( '\\ILJ\\Helper\\Post', 'resetOptionsAction' ) );
        
        if ( $capability ) {
            add_action( 'load-post.php', [ '\\ILJ\\Backend\\Editor', 'addKeywordMetaBox' ] );
            add_action( 'load-post-new.php', [ '\\ILJ\\Backend\\Editor', 'addKeywordMetaBox' ] );
            add_action(
                'save_post',
                [ '\\ILJ\\Backend\\Editor', 'saveKeywordMeta' ],
                10,
                2
            );
            add_action( 'wp_ajax_ilj_search_posts', [ '\\ILJ\\Helper\\Ajax', 'searchPostsAction' ] );
            add_action( 'wp_ajax_ilj_hide_promo', [ '\\ILJ\\Helper\\Ajax', 'hidePromo' ] );
            add_action( 'wp_loaded', [ '\\ILJ\\Backend\\Column', 'addConfiguredLinksColumn' ] );
            $this->automaticIndexBuildingMode();
        }
    
    }
    
    /**
     * Triggers all actions for automatic index building mode.
     *
     * @since  1.1.0
     * @return void
     */
    protected function automaticIndexBuildingMode()
    {
        $app = self::$instance;
        add_action( 'save_post', function ( $post_id ) use( $app ) {
            $app->triggerRebuildIndex( $post_id, 'post' );
        }, 99 );
        add_action( 'publish_future_post', function ( $post ) use( $app ) {
            $app = self::$instance;
            $app->triggerRebuildIndex( $post, 'post' );
        }, 99 );
        //rebuild index after keyword meta got updated on gutenberg editor:
        add_action(
            'updated_post_meta',
            function (
            $meta_id,
            $post_id,
            $meta_key,
            $meta_value
        ) use( $app ) {
            if ( !is_admin() || function_exists( 'get_current_screen' ) ) {
                return;
            }
            $current_screen = get_current_screen();
            if ( $meta_key != \ILJ\Database\Postmeta::ILJ_META_KEY_LINKDEFINITION || !method_exists( $current_screen, 'is_block_editor' ) || !$current_screen->is_block_editor() ) {
                return;
            }
            $app->triggerRebuildIndex( $post_id, 'meta' );
        },
            10,
            4
        );
    }
    
    /**
     * Triggers the recreation of the linkindex
     *
     * @since  1.1.0
     * @param  int    $post_id The post id, that triggers
     * @param  string $caller  The caller, that triggers
     * @return void
     */
    public function triggerRebuildIndex( $post_id )
    {
        $post = get_post( $post_id );
        if ( !in_array( $post->post_status, [ 'publish', 'trash' ] ) ) {
            return;
        }
        add_action( 'shutdown', function () {
            $index_builder = new IndexBuilder();
            $index_builder->buildIndex();
        } );
    }
    
    /**
     * Registers plugin relevant filters
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function registerFilter()
    {
        add_filter( 'the_content', function ( $content ) {
            $link_builder = new LinkBuilder( get_the_ID(), 'post' );
            return $link_builder->linkContent( $content );
        }, 99 );
        $tag_exclusions = Options::getOption( \ILJ\Core\Options\NoLinkTags::getKey() );
        if ( count( $tag_exclusions ) ) {
            add_filter( Replacement::ILJ_FILTER_EXCLUDE_TEXT_PARTS, function ( $search_parts ) use( $tag_exclusions ) {
                foreach ( $tag_exclusions as $tag_exclusion ) {
                    $regex = TagExclusion::getRegex( $tag_exclusion );
                    if ( $regex ) {
                        $search_parts[] = $regex;
                    }
                }
                return $search_parts;
            } );
        }
        global  $ilj_fs ;
    }
    
    /**
     * Adds a link to the plugins settings page on plugins overview
     *
     * @since 1.0.0
     *
     * @param  array $links All links that get displayed
     * @return array
     */
    public function addSettingsLink( $links )
    {
        $settings_link = '<a href="admin.php?page=' . AdminMenu::ILJ_MENUPAGE_SLUG . '-' . Settings::ILJ_MENUPAGE_SETTINGS_SLUG . '">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

}