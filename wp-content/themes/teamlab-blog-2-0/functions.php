<?php
/**
 * Teamlab Blog 2.0 functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Teamlab_Blog_2.0
 */

if ( ! function_exists( 'teamlab_blog_2_0_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function teamlab_blog_2_0_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Teamlab Blog 2.0, use a find and replace
		 * to change 'teamlab-blog-2-0' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'teamlab-blog-2-0', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'html5', array( 'search-form' ) );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

        

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails', array('post') );
		add_image_size('full-thumbnail', 634, 320, true);
		add_image_size('mini-thumbnail', 353, 178, false);

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'teamlab-blog-2-0' ),
		) );

		
    load_theme_textdomain( 'teamlab-blog-2-0', TEMPLATEPATH . '/languages' );
        $locale = get_locale();
        $locale_file = TEMPLATEPATH . "/languages/$locale.php";
        if ( is_readable( $locale_file ) )
            require_once( $locale_file );
        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => __( 'Primary Navigation', 'teamlab-blog-2-0' ),
        ) );
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'teamlab_blog_2_0_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
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
        wp_enqueue_style( 'common_css', $template_uri . '/css/common.css' );
        wp_enqueue_style( 'navigation_css', $template_uri . '/css/navigation.css' );
        wp_enqueue_style( 'language_selector_css', $template_uri . '/css/language-selector.css' );
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

 if ( ! function_exists( 'add_pushy_js' ) ) :
     function add_pushy_js() {
         $template_uri = get_template_directory_uri();
         wp_enqueue_script( 'pushy_js', $template_uri . '/js/core/pushy.min.js', array(), '', true);
     }
 endif;

    add_filter('template_include', 'my_template');
        function my_template( $template ) {
            if( is_category( array(1012) ) ){
                return get_stylesheet_directory() . '/in-the-press.php';
            }
        return $template;

        }


     /* Action for loading your custom stylesheet and scripts*/

add_action( 'wp_enqueue_scripts', 'add_my_theme_stylesheet' );
add_action( 'wp_footer', 'add_my_theme_js' );
add_action( 'wp_footer', 'add_pushy_js' );
add_action( 'after_setup_theme', 'teamlab_blog_2_0_setup' );
add_action( 'after_setup_theme', function() {
    add_theme_support( 'pageviews' );
});


/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function teamlab_blog_2_0_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'teamlab_blog_2_0_content_width', 640 );
}
add_action( 'after_setup_theme', 'teamlab_blog_2_0_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function teamlab_blog_2_0_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'sidebar', 'teamlab-blog-2-0' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'teamlab-blog-2-0' ),
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
}


function register_my_widgets() {
    register_sidebar( array(
        'name'          => esc_html__( 'single-sidebar', 'teamlab-blog-2-0' ),
        'id'            => 'sidebar-2',
        'description'   => esc_html__( 'Add widgets single-page.', 'teamlab-blog-2-0' ),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}

add_action( 'widgets_init', 'teamlab_blog_2_0_widgets_init' );
add_action( 'widgets_init', 'register_my_widgets' );

/**
 * Enqueue scripts and styles.
 */
function teamlab_blog_2_0_scripts() {
	wp_enqueue_style( 'teamlab-blog-2-0-style', get_stylesheet_uri() );
	wp_enqueue_script('jquery'); 
 	wp_enqueue_script( 'true_loadmore', get_stylesheet_directory_uri() . '/js/loadmore.js', array('jquery') );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'teamlab_blog_2_0_scripts' );


// Get the tags



// Load more
function true_load_posts(){
  $args = unserialize(stripslashes($_POST['query']));
  $args['paged'] = $_POST['page'] + 1; // следующая страница
  $args['post_status'] = 'publish';
  $q = new WP_Query($args);
  if( $q->have_posts() ):
    while($q->have_posts()): $q->the_post();
      /*
       * Тут я пытался сделать кнопку LoadMore на странице in the press
       
      ?><?php if ($cat =1012) :?>
      <article class="post in-the-press">
                
                <div class="meta press-page">
                        <?php if(get_field('URL')){
                                echo '<span><a class="press-url" href="http://'.get_field('URL').'/" target="_blank">'.get_field('URL').'</a></span>';
                            }
                        ?>
                        <span class="date"><?php echo get_the_date('j F Y'); ?></span>
                </div>
                <h2 class="entry-title press-page-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

                 <p><?php the_excerpt() ?></p>
                 </article>
      

        <?php elseif($cat =-1012): ?>?>
        <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
        <?php else: ?>
        <?php endif; ?> 
      */?>
      <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
      <?php
    endwhile; 
  endif;
  wp_reset_postdata();
  die();
}

 
add_action('wp_ajax_loadmore', 'true_load_posts');
add_action('wp_ajax_nopriv_loadmore', 'true_load_posts');


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}


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


/* Get comments
 */

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