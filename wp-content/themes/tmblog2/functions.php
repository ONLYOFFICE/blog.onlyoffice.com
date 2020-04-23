<?php
    /**
     * Teamlab Blog functions and definitions
     *
     * Sets up the theme and provides some helper functions. Some helper functions
     * are used in the theme as custom template tags. Others are attached to action and
     * filter hooks in WordPress to change core functionality.
     *
     * The first function, tmblog_setup(), sets up the theme by registering support
     * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
     *
     * When using a child theme (see http://codex.wordpress.org/Theme_Development and
     * http://codex.wordpress.org/Child_Themes), you can override certain functions
     * (those wrapped in a function_exists() call) by defining them first in your child theme's
     * functions.php file. The child theme's functions.php file is included before the parent
     * theme's file, so the child theme functions would be used.
     *
     * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
     * to a filter or action hook. The hook can be removed by using remove_action() or
     * remove_filter() and you can attach your own function to the hook.
     *
     * We can remove the parent theme's hook only after it is attached, which means we need to
     * wait until setting up the child theme:
     *
     * <code>
     * add_action( 'after_setup_theme', 'my_child_theme_setup' );
     * function my_child_theme_setup() {
     *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
     *     remove_filter( 'excerpt_length', 'tmblog_excerpt_length' );
     *     ...
     * }
     * </code>
     *
     * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
     *
     * @package WordPress
     * @subpackage Twenty_Ten
     * @since Twenty Ten 1.0
     */
    /**
     * Set the content width based on the theme's design and stylesheet.
     *
     * Used to set the width of images and content. Should be equal to the width the theme
     * is designed for, generally via the style.css stylesheet.
     */
    if ( ! isset( $content_width ) )
        $content_width = 640;
    /** Tell WordPress to run tmblog_setup() when the 'after_setup_theme' hook is run. */
    add_action( 'after_setup_theme', 'tmblog_setup' );
    if ( ! function_exists( 'tmblog_setup' ) ):
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which runs
     * before the init hook. The init hook is too late for some features, such as indicating
     * support post thumbnails.
     *
     * To override tmblog_setup() in a child theme, add your own tmblog_setup to your child theme's
     * functions.php file.
     *
     * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
     * @uses register_nav_menus() To add support for navigation menus.
     * @uses add_custom_background() To add support for a custom background.
     * @uses add_editor_style() To style the visual editor.
     * @uses load_theme_textdomain() For translation/localization support.
     * @uses add_custom_image_header() To add support for a custom header.
     * @uses register_default_headers() To register the default custom header images provided with the theme.
     * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
     *
     * @since Twenty Ten 1.0
     */
    function tmblog_setup() {
        // This theme styles the visual editor with editor-style.css to match the theme style.
        add_editor_style();
        // This theme uses post thumbnails
        add_theme_support( 'post-thumbnails' );
        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );
        // Make theme available for translation
        // Translations can be filed in the /languages/ directory
        load_theme_textdomain( 'tmblog', TEMPLATEPATH . '/languages' );
        $locale = get_locale();
        $locale_file = TEMPLATEPATH . "/languages/$locale.php";
        if ( is_readable( $locale_file ) )
            require_once( $locale_file );
        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => __( 'Primary Navigation', 'tmblog' ),
        ) );
        // This theme allows users to set a custom background
        add_custom_background();
        // Your changeable header business starts here
        define( 'HEADER_TEXTCOLOR', '' );
        // No CSS, just IMG call. The %s is a placeholder for the theme template directory URI.
        define( 'HEADER_IMAGE', '%s/images/headers/path.jpg' );
        // The height and width of your custom header. You can hook into the theme's own filters to change these values.
        // Add a filter to tmblog_header_image_width and tmblog_header_image_height to change these values.
        define( 'HEADER_IMAGE_WIDTH', apply_filters( 'tmblog_header_image_width', 940 ) );
        define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'tmblog_header_image_height', 198 ) );
        // We'll be using post thumbnails for custom header images on posts and pages.
        // We want them to be 940 pixels wide by 198 pixels tall.
        // Larger images will be auto-cropped to fit, smaller ones will be ignored. See header.php.
        set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );
        // Don't support text inside the header image.
        define( 'NO_HEADER_TEXT', true );
        // Add a way for the custom header to be styled in the admin panel that controls
        // custom headers. See tmblog_admin_header_style(), below.
        add_custom_image_header( '', 'tmblog_admin_header_style' );
        // ... and thus ends the changeable header business.
        // Default custom headers packaged with the theme. %s is a placeholder for the theme template directory URI.
        register_default_headers( array(
            'berries' => array(
                'url' => '%s/images/headers/berries.jpg',
                'thumbnail_url' => '%s/images/headers/berries-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Berries', 'tmblog' )
            ),
            'cherryblossom' => array(
                'url' => '%s/images/headers/cherryblossoms.jpg',
                'thumbnail_url' => '%s/images/headers/cherryblossoms-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Cherry Blossoms', 'tmblog' )
            ),
            'concave' => array(
                'url' => '%s/images/headers/concave.jpg',
                'thumbnail_url' => '%s/images/headers/concave-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Concave', 'tmblog' )
            ),
            'fern' => array(
                'url' => '%s/images/headers/fern.jpg',
                'thumbnail_url' => '%s/images/headers/fern-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Fern', 'tmblog' )
            ),
            'forestfloor' => array(
                'url' => '%s/images/headers/forestfloor.jpg',
                'thumbnail_url' => '%s/images/headers/forestfloor-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Forest Floor', 'tmblog' )
            ),
            'inkwell' => array(
                'url' => '%s/images/headers/inkwell.jpg',
                'thumbnail_url' => '%s/images/headers/inkwell-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Inkwell', 'tmblog' )
            ),
            'path' => array(
                'url' => '%s/images/headers/path.jpg',
                'thumbnail_url' => '%s/images/headers/path-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Path', 'tmblog' )
            ),
            'sunset' => array(
                'url' => '%s/images/headers/sunset.jpg',
                'thumbnail_url' => '%s/images/headers/sunset-thumbnail.jpg',
                /* translators: header image description */
                'description' => __( 'Sunset', 'tmblog' )
            )
        ) );
        add_filter('show_admin_bar', '__return_false');
    }
    endif;

    if ( ! function_exists( 'add_my_theme_stylesheet' ) ) :
    /**
     * Adds the function 'add_my_theme_stylesheet' to the
     * wp_enqueue_scripts action.
     */
    //add_action( 'wp_enqueue_scripts', 'add_my_theme_stylesheet' );
    /**
     * Function for loading your custom stylesheet
     */
    function add_my_theme_stylesheet() {
        $template_uri = get_template_directory_uri();

        // loads your stylesheet
        wp_enqueue_style( 'style_css', get_stylesheet_uri() );

        wp_enqueue_style( 'reset_css', $template_uri . '/css/reset.css' );
        wp_enqueue_style( 'correct_style_css', $template_uri . '/css/correct_style.css' );
        wp_enqueue_style( 'teamlab_css',$template_uri . '/css/teamlab.css');
        wp_enqueue_style( 'common_css', $template_uri . '/css/common.css' );
        wp_enqueue_style( 'buttons_css',$template_uri . '/css/buttons.css' );
        wp_enqueue_style( 'language_selector_css', $template_uri . '/css/language-selector.css' );
        wp_enqueue_style( 'navigation_css', $template_uri . '/css/navigation.css' );
        wp_enqueue_style( 'pushy_css', $template_uri . '/css/pushy.css' );
    }

    endif;

    if ( ! function_exists( 'add_my_theme_js' ) ) :
    function add_my_theme_js() {
        $template_uri = get_template_directory_uri();

        wp_enqueue_script( 'jquery_core_js', $template_uri . '/js/jquery/jquery.core.js', array(), '', true);
        wp_enqueue_script( 'core_main_js', $template_uri . '/js/core/main.js', array('jquery_core_js'), '', true);
        wp_enqueue_script( 'jquery_toggle_js', $template_uri . '/js/core/jquery.dropdownToggle.js', array('jquery_core_js'), '', true);
        wp_enqueue_script( 'basemaster_init_js', $template_uri . '/js/core/basemaster.init.js', array('jquery_core_js'), '', true);
    }
    endif;

    if ( ! function_exists( 'add_my_theme_page_js' ) ) :
    function add_my_theme_page_js() {
        $template_uri = get_template_directory_uri();

        wp_enqueue_script( 'jquery_core_js', $template_uri . '/js/jquery/jquery.core.js', array(), '', true);
        wp_enqueue_script( 'core_main_js', $template_uri . '/js/core/main.js', array('jquery_core_js'), '', true);
        wp_enqueue_script( 'jquery_toggle_js', $template_uri . '/js/core/jquery.dropdownToggle.js', array('jquery_core_js'), '', true);
        wp_enqueue_script( 'basemaster_init_js', $template_uri . '/js/core/basemaster.init.js', array('jquery_core_js'), '', true);
    }
    endif;
    if ( ! function_exists( 'add_pushy_js' ) ) :
        function add_pushy_js() {
            $template_uri = get_template_directory_uri();
            wp_enqueue_script( 'pushy_js', $template_uri . '/js/core/pushy.min.js', array(), '', true);
        }
    endif;

    if ( ! function_exists( 'tmblog_admin_header_style' ) ) :
    /**
     * Styles the header image displayed on the Appearance > Header admin panel.
     *
     * Referenced via add_custom_image_header() in tmblog_setup().
     *
     * @since Twenty Ten 1.0
     */
    function tmblog_admin_header_style() {
?>
<style type="text/css">
    /* Shows the same border as on front end */
    #headimg {
        border-bottom: 1px solid #000;
        border-top: 4px solid #000;
    }
    /* If NO_HEADER_TEXT is false, you would style the text with these selectors:
        #headimg #name { }
        #headimg #desc { }
    */
</style>
<?php
    }
    endif;
    /**
     * Makes some changes to the <title> tag, by filtering the output of wp_title().
     *
     * If we have a site description and we're viewing the home page or a blog posts
     * page (when using a static front page), then we will add the site description.
     *
     * If we're viewing a search result, then we're going to recreate the title entirely.
     * We're going to add page numbers to all titles as well, to the middle of a search
     * result title and the end of all other titles.
     *
     * The site title also gets added to all titles.
     *
     * @since Twenty Ten 1.0
     *
     * @param string $title Title generated by wp_title()
     * @param string $separator The separator passed to wp_title(). Twenty Ten uses a
     * 	vertical bar, "|", as a separator in header.php.
     * @return string The new title, ready for the <title> tag.
     */
    function tmblog_filter_wp_title( $title, $separator ) {
        // Don't affect wp_title() calls in feeds.
        if ( is_feed() )
            return $title;
        // The $paged global variable contains the page number of a listing of posts.
        // The $page global variable contains the page number of a single post that is paged.
        // We'll display whichever one applies, if we're not looking at the first page.
        global $paged, $page;
        if ( is_search() ) {
            // If we're a search, let's start over:
            $title = sprintf( __( 'Search results for %s', 'tmblog' ), '"' . get_search_query() . '"' );
            // Add a page number if we're on page 2 or more:
            if ( $paged >= 2 )
                $title .= " $separator " . sprintf( __( 'Page %s', 'tmblog' ), $paged );
            // Add the site name to the end:
            $title .= " $separator " . get_bloginfo( 'name', 'display' );
            // We're done. Let's send the new title back to wp_title():
            return $title;
        }
        // Otherwise, let's start by adding the site name to the end:
        $title .= get_bloginfo( 'name', 'display' );
        // If we have a site description and we're on the home/front page, add the description:
        $site_description = get_bloginfo( 'description', 'display' );
        if ( $site_description && ( is_home() || is_front_page() ) )
            $title .= " $separator " . $site_description;
        // Add a page number if necessary:
        if ( $paged >= 2 || $page >= 2 )
            $title .= " $separator " . sprintf( __( 'Page %s', 'tmblog' ), max( $paged, $page ) );
        // Return the new title to wp_title():
        return $title;
    }
    add_filter( 'wp_title', 'tmblog_filter_wp_title', 10, 2 );
    /**
     * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
     *
     * To override this in a child theme, remove the filter and optionally add
     * your own function tied to the wp_page_menu_args filter hook.
     *
     * @since Twenty Ten 1.0
     */
    function tmblog_page_menu_args( $args ) {
        $args['show_home'] = true;
        return $args;
    }
    add_filter( 'wp_page_menu_args', 'tmblog_page_menu_args' );
    /**
     * Sets the post excerpt length to 40 characters.
     *
     * To override this length in a child theme, remove the filter and add your own
     * function tied to the excerpt_length filter hook.
     *
     * @since Twenty Ten 1.0
     * @return int
     */
    function tmblog_excerpt_length( $length ) {
        return 40;
    }
    add_filter( 'excerpt_length', 'tmblog_excerpt_length' );
    /**
     * Returns a "Continue Reading" link for excerpts
     *
     * @since Twenty Ten 1.0
     * @return string "Continue Reading" link
     */
    function tmblog_continue_reading_link() {
        return ' <a href="'. get_permalink() . '">' . __( '<span class="meta-nav nav-more">Read more</span>', 'tmblog' ) . '</a>';
    }
    /**
     * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and tmblog_continue_reading_link().
     *
     * To override this in a child theme, remove the filter and add your own
     * function tied to the excerpt_more filter hook.
     *
     * @since Twenty Ten 1.0
     * @return string An ellipsis
     */
    function tmblog_auto_excerpt_more( $more ) {
        return ' &hellip;' . tmblog_continue_reading_link();
    }
    add_filter( 'excerpt_more', 'tmblog_auto_excerpt_more' );
    /**
     * Adds a pretty "Continue Reading" link to custom post excerpts.
     *
     * To override this link in a child theme, remove the filter and add your own
     * function tied to the get_the_excerpt filter hook.
     *
     * @since Twenty Ten 1.0
     * @return string Excerpt with a pretty "Continue Reading" link
     */
    function tmblog_custom_excerpt_more( $output ) {
        if ( has_excerpt() && ! is_attachment() ) {
            $output .= tmblog_continue_reading_link();
        }
        return $output;
    }
    add_filter( 'get_the_excerpt', 'tmblog_custom_excerpt_more' );
    /**
     * Remove inline styles printed when the gallery shortcode is used.
     *
     * Galleries are styled by the theme in Twenty Ten's style.css.
     *
     * @since Twenty Ten 1.0
     * @return string The gallery style filter, with the styles themselves removed.
     */
    function tmblog_remove_gallery_css( $css ) {
        return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
    }
    add_filter( 'gallery_style', 'tmblog_remove_gallery_css' );
    if ( ! function_exists( 'tmblog_comment' ) ) :
    /**
     * Template for comments and pingbacks.
     *
     * To override this walker in a child theme without modifying the comments template
     * simply create your own tmblog_comment(), and that function will be used instead.
     *
     * Used as a callback by wp_list_comments() for displaying the comments.
     *
     * @since Twenty Ten 1.0
     */
    function tmblog_comment( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;
        switch ( $comment->comment_type ) :
            case '' :
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>" class="comment-wrap">
        <div class="comment-author vcard">
            <?php echo get_avatar( $comment, 40, 'gravatar_default' ); ?>
            <div class="title">
                <?php printf( __( '%s', 'tmblog' ), sprintf( '<span class="fn">%s</span>', get_comment_author_link() ) ); ?>
                <span class="sep">-</span>
                <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
            </div>
            <span class="meta"><?php printf( __( '%1$s at %2$s', 'tmblog' ), get_comment_date(),  get_comment_time() ); ?><?php edit_comment_link( __( 'Edit', 'tmblog' ), ' ' ); ?></span>
        </div><!-- .comment-author .vcard -->
        <?php if ( $comment->comment_approved == '0' ) : ?>
        <em><?php _e( 'Your comment is awaiting moderation.', 'tmblog' ); ?></em>
        <br />
        <?php endif; ?>
        <div class="comment-body"><?php comment_text(); ?></div>
    </div><!-- #comment-##  -->
    <?php
            break;
        case 'pingback'  :
        case 'trackback' :
    ?>
<li class="post pingback">
    <p><?php _e( 'Pingback:', 'tmblog' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'tmblog'), ' ' ); ?></p>
    <?php
                    break;
            endswitch;
        }
        endif;
        /**
         * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
         *
         * To override tmblog_widgets_init() in a child theme, remove the action hook and add your own
         * function tied to the init hook.
         *
         * @since Twenty Ten 1.0
         * @uses register_sidebar
         */
        function tmblog_widgets_init() {
            // Area 1, located at the top of the sidebar.
            register_sidebar( array(
                'name' => __( 'Primary Widget Area', 'tmblog' ),
                'id' => 'primary-widget-area',
                'description' => __( 'The primary widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s"><div class="widget-top-border"></div><div class="widget-container">',
                'after_widget' => '</div><div class="widget-bottom-border"></div></li>',
                'before_title' => '<div class="widget-header"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ) );
            // Area 2, located below the Primary Widget Area in the sidebar. Empty by default.
            register_sidebar( array(
                'name' => __( 'Secondary Widget Area', 'tmblog' ),
                'id' => 'secondary-widget-area',
                'description' => __( 'The secondary widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
            // Area 3, located in the footer. Empty by default.
            register_sidebar( array(
                'name' => __( 'First Footer Widget Area', 'tmblog' ),
                'id' => 'first-footer-widget-area',
                'description' => __( 'The first footer widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
            // Area 4, located in the footer. Empty by default.
            register_sidebar( array(
                'name' => __( 'Second Footer Widget Area', 'tmblog' ),
                'id' => 'second-footer-widget-area',
                'description' => __( 'The second footer widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
            // Area 5, located in the footer. Empty by default.
            register_sidebar( array(
                'name' => __( 'Third Footer Widget Area', 'tmblog' ),
                'id' => 'third-footer-widget-area',
                'description' => __( 'The third footer widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
            // Area 6, located in the footer. Empty by default.
            register_sidebar( array(
                'name' => __( 'Fourth Footer Widget Area', 'tmblog' ),
                'id' => 'fourth-footer-widget-area',
                'description' => __( 'The fourth footer widget area', 'tmblog' ),
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
        }
        /** Register sidebars by running tmblog_widgets_init() on the widgets_init hook. */
        add_action( 'widgets_init', 'tmblog_widgets_init' );
        /**
         * Removes the default styles that are packaged with the Recent Comments widget.
         *
         * To override this in a child theme, remove the filter and optionally add your own
         * function tied to the widgets_init action hook.
         *
         * @since Twenty Ten 1.0
         */
        function tmblog_remove_recent_comments_style() {
            global $wp_widget_factory;
            remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
        }
        add_action( 'widgets_init', 'tmblog_remove_recent_comments_style' );
        if ( ! function_exists( 'tmblog_posted_by' ) ) :
        /**
         * Prints HTML with meta information for the current post—date/time and author.
         *
         * @since Twenty Ten 1.0
         */
        function tmblog_posted_by() {
            printf(
            __( ( count( get_the_category() ) ) ? '<span class="%2$s">By %3$s</span><span style="display:inline-block; margin-left: 21px;" class="%1$s">Posted in: %4$s</span>'
                    : '<span class="%1$s">Posted by %3$s</span>', 'tmblog' ),
                'meta-prep meta-prep-author',
                'entry-utility-prep entry-utility-prep-cat-links',
                sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
                    get_author_posts_url( get_the_author_meta( 'ID' ) ),
                    sprintf( esc_attr__( 'View all posts by %s', 'tmblog' ), get_the_author() ),
                    get_the_author()),
                get_the_category_list( ', ' )
            );
        }
        endif;
        if ( ! function_exists( 'tmblog_posted_on' ) ) :
        /**
         * Prints HTML with meta information for the current post—date/time and author.
         *
         * @since Twenty Ten 1.0
         */
        function tmblog_posted_on() {
            //printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'tmblog' ),
          printf( __( '<span class="%1$s">%2$s</span>', 'tmblog' ),
                'meta-prep meta-prep-date',
                sprintf( '<span class="entry-date">%1$s</span>',
                    get_the_date()
                )
            );
        }
        endif;
        if ( ! function_exists( 'tmblog_posted_in' ) ) :
        /**
         * Prints HTML with meta information for the current post (category, tags and permalink).
         *
         * @since Twenty Ten 1.0
         */
        function tmblog_posted_in() {
            // Retrieves tag list of current post, separated by commas.
            $tag_list = get_the_tag_list( '', ', ' );
            if ( $tag_list ) {
                $posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'tmblog' );
            } elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
                $posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'tmblog' );
            } else {
                $posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'tmblog' );
            }
            // Prints the string, replacing the placeholders.
            printf(
                $posted_in,
                get_the_category_list( ', ' ),
                $tag_list,
                get_permalink(),
                the_title_attribute( 'echo=0' )
            );
        }
        endif;


/**
 * Get curent language
 */
if ( ! function_exists( 'get_default_language_key' ) ) :
function get_default_language_key() {
    return "en";
}
endif;

if ( ! function_exists( 'get_language_key' ) ) :
 function get_language_key() {

    $default_lang = get_default_language_key();

    $query = $_SERVER['QUERY_STRING'];

    $regex = "/(?:lang=([a-z]{2}))?$/";
    preg_match_all($regex, $query, $matches);

    $lang = $matches[1][0];
    $regextest = "/\/blog\/([a-z]{2})/";
    $text = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    preg_match($regextest, $text, $match);
    $lang = $match[1];

    if (!$lang) {
        $lang = $default_lang;
    }
    return $lang;
}
endif;

if ( ! function_exists( 'get_language_redirect_folder' ) ) :
function get_language_redirect_folder() {
    $lang = get_language_key();
    return ($lang != get_default_language_key() ? "/".$lang : "");
}
endif;

/**
 * Display the curent language
 */
if ( ! function_exists( 'language_selector' ) ) :
function language_selector($available_langs_keys) {

    $default_lang = get_default_language_key();
    $available_langs_full = array(
        'en' =>  array('en', 'en-US', 'English'),
        'engb' =>  array('uk', 'en-GB', 'English'),
        'de' =>  array('de', 'de-DE', 'Deutsch'),
        'fr' =>  array('fr', 'fr-FR', 'Francais'),
        'es' =>  array('es', 'es-ES', 'Espanol'),
        'ru' =>  array('ru', 'ru-RU', 'Русский'),
        'it' =>  array('it', 'it-IT', 'Italiano'),
        'cs' =>  array('cs', 'cs-CZ', 'Česky')
        
    );

    $available_langs  = array();
    foreach ( (array)$available_langs_full as $k => $v ) {
       if (in_array($k, $available_langs_keys)){
           $available_langs[$k] =   $v;
       }
    }

    $lang = get_language_key();

    $queryGB = $_SERVER['QUERY_STRING'];
    
    $regexGB = "/(?:lang=([a-z]{2}))?$/";
    preg_match_all($regexGB, $queryGB, $matches);

    $langGB = $matches[1][0];

    $regextest = "/\/blog\/([a-z]{2})/";
    $text = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    preg_match($regextest, $text, $match);
    $langGB = $match[1];

    if (!$available_langs[$lang]){
        $lang = $default_lang;
    }

    $output = "<div class=\"selector " . $lang . "\" onclick=\"LanguageSelectorManager.openLngSelector();\"></div>"
                . "<div class=\"title "
                .  ($langGB == 'uk' ? $available_langs['engb'][1] : $available_langs[$lang][1])
                .  "\" onclick=\"LanguageSelectorManager.openLngSelector();\">"
                . "</div>";

    $output .= "<ul class=\"options\" style=\"display: none;\">";
    foreach ($available_langs as $lng) {
        $output .= "<li class=\"option "
                    . $lng[1]
                    . "\"><a href=\" "
                    . WEB_ROOT_URL
                    . "/blog"
                    . (($lng[0] != $default_lang || $lng[1] == "en-GB")? "/".$lng[0] : "")
                    . "\">"
                    . "</a></li>";
    }
    $output .= "</ul>";
    echo $output;
}
endif;

define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);