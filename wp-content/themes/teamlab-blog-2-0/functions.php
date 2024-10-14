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

function enqueue_comment_reply() {
	if( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script('comment-reply');
}
add_action( 'wp_enqueue_scripts', 'enqueue_comment_reply' );

     /* Action for loading your custom stylesheet and scripts*/

add_action( 'wp_enqueue_scripts', 'add_my_theme_stylesheet' );
add_action( 'wp_footer', 'add_my_theme_js' );
add_action( 'wp_footer', 'add_pushy_js' );
add_action( 'after_setup_theme', 'teamlab_blog_2_0_setup' );
add_action( 'after_setup_theme', function() {
    add_theme_support( 'pageviews' );
});

add_filter( 'excerpt_length', function(){
    return 35;
} );

add_filter('excerpt_more', function($more) {
    return '...';
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

// The output of the first image from the post
function bloggood_ru_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches); // выдираем первый имагес
  $first_img = $matches [1] [0];
 
// If there is no image in the post, then display the default image (specify the path and name for the image)
  if(empty($first_img)){
    $template_uri = get_template_directory_uri();
   $first_img = $template_uri . "/images/blog_online_editors.jpg";
  }
  return $first_img;
}

 if ( ! function_exists( 'tmblog_posted_by' ) ) :
        /**
         * Prints HTML with meta information for the current post—date/time and author.
         *
         * @since Twenty Ten 1.0
         */
        function tmblog_posted_by() {
            printf(
            __( ( count( get_the_category() ) ) ? '<span class="%2$s">By %3$s</span>'
                    : '<span class="%2$s">By %3$s</span>', 'teamlab-blog-2-0' ),
                'meta-prep meta-prep-author',
                'entry-utility-prep entry-utility-prep-cat-links',
                sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
                    get_author_posts_url( get_the_author_meta( 'ID' ) ),
                    sprintf( esc_attr__( 'View all posts by %s', 'teamlab-blog-2-0' ), get_the_author() ),
                    get_the_author()),
                get_the_category_list( ', ' )
            );
        }
        endif;

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

function register_zh_widgets() {
    register_sidebar( array(
        'name'          => esc_html__( 'zh-sidebar', 'teamlab-blog-2-0' ),
        'id'            => 'zh-sidebar',
        'description'   => esc_html__( 'Add widgets on zh-page.', 'teamlab-blog-2-0' ),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}

add_action( 'widgets_init', 'teamlab_blog_2_0_widgets_init' );
add_action( 'widgets_init', 'register_my_widgets' );
add_action( 'widgets_init', 'register_zh_widgets' );

/**
 * Enqueue scripts and styles.
 */
function teamlab_blog_2_0_scripts() {
    wp_enqueue_script('jquery'); 
    wp_enqueue_script( 'true_loadmore', get_stylesheet_directory_uri() . '/js/loadmore.js', array('jquery') );
}
add_action( 'wp_enqueue_scripts', 'teamlab_blog_2_0_scripts' );



// Functions for write "alt" onload pictures
function change_empty_alt_to_title( $response ) {
    if ( ! $response['alt'] ) {
        $response['alt'] = sanitize_text_field( $response['uploadedToTitle'] );
    }

    return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'change_empty_alt_to_title' );

// Search tags on page search (not working)
function search_tags_query($query) {
        $s = $query->get('s');
        if(strpos($s, '#') !== false){
            $terms = explode('#', $s);
            $query->set('tax_query', [
                'relation' => 'OR',
                [
                    'term_taxonomy' => 'post_tag', // or some custom taxonomy if needed
                    'field' => 'name',
                    'terms' => $terms
                ]
            ]);
        }
}
add_action('pre_get_posts', 'search_tags_query');

// Empty search query
add_filter('posts_search', function( $search, \WP_Query $q ) {
    if (empty($search) && $q->is_search() && $q->is_main_query())
        $search .=" AND 0=1 ";

    return $search;
}, 10, 2);

//Excluding pages from search results
function wph_exclude_pages($query) {
    if ($query->is_search) {
        $query->set('post_type', 'post');
    }
    return $query;
}
add_filter('pre_get_posts','wph_exclude_pages');


// Load more
function true_load_posts(){
    $args = unserialize(stripslashes($_POST['query']));
    $args['paged'] = $_POST['page'] + 1; // следующая страница
    $args['post_status'] = 'publish';
    $q = new WP_Query($args);
    if( $q->have_posts() ):
      while($q->have_posts()): $q->the_post(); 
         include get_template_directory() . '/' . $_POST['template'] . '.php' ;
      endwhile; 
    endif;
    wp_reset_postdata();
    die();
  };
  
  add_action('wp_ajax_loadmore', 'true_load_posts');
  add_action('wp_ajax_nopriv_loadmore', 'true_load_posts');



// Load more on page "In the press"
function true_load_posts_in_press(){
    $args = json_decode( stripslashes( $_POST['query'] ), true );
    $args['paged'] = $_POST['page'] + 1; // следующая страница
    $args['post_type'] = 'news';
    $args['post_status'] = 'publish';
    $args['meta_key'] = 'dateNews';
    $args['orderby'] = 'meta_value';
    $args['order'] = 'DESC';
    $q = new WP_Query($args);
    if( $q->have_posts() ):
      while($q->have_posts()): $q->the_post(); 
         include get_template_directory() . '/' . $_POST['template'] . '.php' ;
      endwhile; 
    endif;
    wp_reset_postdata();
    die();
  };
  
  add_action('wp_ajax_press', 'true_load_posts_in_press');
  add_action('wp_ajax_nopriv_press', 'true_load_posts_in_press');


// Load more on page search

function true_load_posts_in_search(){
  $args = json_decode( stripslashes( $_POST['query'] ), true );
  $args['paged'] = $_POST['page'] + 1; // следующая страница
  $args['post_status'] = 'publish';
  $q = new WP_Query($args);
  if( $q->have_posts() ):
    while($q->have_posts()): $q->the_post(); 
       include get_template_directory() . '/' . $_POST['template'] . '.php' ;
    endwhile; 
  endif;
  wp_reset_postdata();
  die();
};

 
add_action('wp_ajax_search', 'true_load_posts_in_search');
add_action('wp_ajax_nopriv_search', 'true_load_posts_in_search');

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

/****** Js variables for ajax *******/

add_action('wp_head', 'js_variables');

function js_variables()
{
    $variables = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'is_mobile' => wp_is_mobile()
    );
    echo ('<script type="text/javascript">window.wp_data = ' .
        json_encode($variables) .
        ';</script>');
}

/**********************Send subscribe email*************************/

add_action('wp_ajax_send_confirmation_email', 'send_confirmation_email');
add_action('wp_ajax_nopriv_send_confirmation_email', 'send_confirmation_email');


function send_confirmation_email()
{
    global $wpdb;
    $responce = (object) ['errorMsg' => ''];

    /*if (!verify_recaptcha($_POST['recaptchaResp'])) {
        $responce->errorMsg = "Incorrect recaptcha";

        echo json_encode($responce);
        wp_die();
    }*/

    if (!empty($_POST['email']) && isset($_POST['email'])) {

        $email = $_POST['email'];

        $regex = '/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i';

        if (preg_match($regex, $email) && strlen($email) < 50) {

            $count = $wpdb->get_var("SELECT email FROM users WHERE email='$email'");

            if (empty($count) && !isset($count)) {
                $salt = wp_salt();
                $date = current_time('mysql', 1);

                $secureKey = sha1($salt . $date . $email);
                $intDate = strtotime($date);

                $headers = array('Content-Type: text/html; charset=UTF-8');
                include get_template_directory() . '/' . 'activation-mail.php';

                $activateUrl = site_url() . "/activation?email=" . $email . "&date=" . $intDate . "&code=" . $secureKey;

                $HTMPMessage = getFirstTemplateEmail($activateUrl);

                wp_mail($email, 'Activation email', $HTMPMessage, $headers);
            } else {
                $responce->errorMsg = "Email is used";
                echo json_encode($responce);
                wp_die();
            }
        } else {
            $responce->errorMsg = "Email incorrect";
            echo json_encode($responce);
            wp_die();
        }
    } else {
        $responce->errorMsg = "Empty email";
        echo json_encode($responce);
        wp_die();
    }

    echo json_encode($responce);
    wp_die();
}

/**********************Added class for image popup*************************/

function add_image_class($class){
    $class .= ' img-popup';
    return $class;
}
add_filter('get_image_tag_class','add_image_class');


/************ Recaptcha ************* */

            
function verify_recaptcha($recaptchaResp){
    
    if (isset($_POST['g-recaptcha-response'])) {

        $captcha_response = $_POST['g-recaptcha-response'];
    } else if ($recaptchaResp != "") {
        $captcha_response = $recaptchaResp;
    } else {
        return false;
    }

    $response = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
            'body' => array(
                'secret' => "recaptcha_private_key",
                'response' => $captcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        )
    );

    $success = false;

    if ($response && is_array($response)) {
        $decoded_response = json_decode($response['body']);
        $success = $decoded_response->success;
    }

    return $success;
}

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
        switch ( $comment->comment ) :
            case '' :
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>" class="comment-wrap">
        <div class="comment-author vcard">
            <?php echo get_avatar( $comment, 40, 'gravatar_default' ); ?>
            <div class="title">
                <?php printf( __( '%s', 'teamlab-blog-2-0' ), sprintf( '<span class="fn">%s</span>', get_comment_author_link() ) ); ?>
                <span class="sep">-</span>
                <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth ) ) ); ?>
            </div>
            <span
                class="meta"><?php printf( __( '%1$s at %2$s', 'teamlab-blog-2-0' ), get_comment_date(),  get_comment_time() ); ?><?php edit_comment_link( __( 'Edit', 'teamlab-blog-2-0' ), ' ' ); ?></span>
        </div><!-- .comment-author .vcard -->
        <?php if ( $comment->comment_approved == '0' ) : ?>
        <em><?php _e( 'Your comment is awaiting moderation.', 'teamlab-blog-2-0' ); ?></em>
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
    <p><?php _e( 'Pingback:', 'teamlab-blog-2-0' ); ?>
        <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'teamlab-blog-2-0'), ' ' ); ?></p>
    <?php
                    break;
            endswitch;
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
    $regextest = "/\/([a-z]{2})/";
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
        'fr' =>  array('fr', 'fr-FR', 'Francais'),
        'de' =>  array('de', 'de-DE', 'Deutsch'),
        'es' =>  array('es', 'es-ES', 'Espanol'),
        'pt' =>  array('pt-br', 'pt-BR', 'Brazil'),
        'it' =>  array('it', 'it-IT', 'Italian'),
        'cs' =>  array('cs', 'cs-CZ', 'Česky'),
        'ja' =>  array('ja', 'ja-JP', '中文'),
        'zh' =>  array('zh-hans', 'zh-CN', '中文'),
        'el' =>  array('el', 'el-GR', 'Greek'),
        'hi' =>  array('hi', 'hi-IN', 'Hindi'),
        'ar' =>  array('ar', 'ar-AR', 'Arabic'),
        'sr' =>  array('sr', 'sr-RS', 'Serbian'),
        'hy' =>  array('hy', 'hy-AM', 'Armenia')
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

    $regextest = "/\/([a-z]{2})/";
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
                    . (($lng[0] != $default_lang || $lng[1] == "en-GB")? "/".$lng[0] : "")
                    . "\">"
                    . "</a></li>";
    }
    $output .= "</ul>";
    echo $output;
}
endif;


 /**
 * Сurrent language for urls
 */

global $sitepress;
$current_language = $sitepress->get_current_language();
    if($current_language !== 'null'){
        $current_language = WEB_ROOT_URL.'/'.$current_language;
    if($current_language == WEB_ROOT_URL.'/'.'pt-br'){
         $current_language = WEB_ROOT_URL.'/'.'pt';
    }if($current_language == WEB_ROOT_URL.'/'.'zh-hans'){
        $current_language = WEB_ROOT_URL.'/'.'zh';
   }else if($current_language == WEB_ROOT_URL.'/'.'uk'){
         $current_language = WEB_ROOT_URL.'/'.'en';
    }else if($current_language == WEB_ROOT_URL.'/'.'en'){
       $current_language = WEB_ROOT_URL;
    }
}
 /**
 * AMP Analytic for AMP Pages
 */
add_action('amp_post_template_head','ampforwp_add_amp_analytics', 11);
function ampforwp_add_amp_analytics() { ?>
    <!-- AMP Analytics -->
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <?php 
}
add_action('ampforwp_body_beginning','ampforwp_add_tag_manager', 11);
function ampforwp_add_tag_manager() { ?>
    <!-- Google Tag Manager -->
    <amp-analytics config="https://www.googletagmanager.com/amp.json?id=GTM-55Q83SS&gtm.url=SOURCE_URL"
        data-credentials="include"></amp-analytics>
    <?php 
}
 /**
 * Custom CSS for AMP Pages
 */

add_action('amp_post_template_css','ampforwp_add_custom_css_example', 11);
function ampforwp_add_custom_css_example() { ?>
    .m-menu .toggle {
    float: right;
    position: absolute;
    left: 0;
    top: 20px;
    padding-left: 300px;
    border: 1px solid transparent;
    }
    .f-w-f2{
    display: none;
    }
    .bold{
    font-weight: bold;
    }
    .margin-left{
    margin-left:10px;
    }
    .uppercase{
    text-transform: uppercase;
    }
    .amp-custom-banner-after-post {
    text-align: center
    }
    #footer-accordion-1, #footer-accordion-2, #footer-accordion-3, #footer-accordion-4, #footer-accordion-5,
    #footer-accordion-6 {
    margin-bottom: 0;
    padding: 14px 0;
    line-height: 1;
    background-color: #fff;
    }
    .amp-custom-banner-after-post section ul li::marker,
    .amp-custom-banner-after-post .SocialLinks ul li::marker{
    content: none;
    }
    .amp-custom-banner-after-post section ul{
    padding: 14px 0;
    }
    .amp-custom-banner-after-post section ul li,
    .amp-custom-banner-after-post section ul li a,
    .amp-custom-banner-after-post section ul li p,
    .amp-custom-banner-after-post section ul li p a{
    line-height: 18px;
    margin: 0 0 7px;
    }

    .amp-custom-banner-after-post .SocialLinks h6{
    padding: 14px 0;
    line-height: 1;
    margin-bottom: 0;
    }
    .amp-custom-banner-after-post .copyReserved{
    padding: 50px 0;
    }
    .ListSocLink {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    justify-content: space-between;
    -webkit-flex-wrap: wrap;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin: 12px auto 10px;
    max-width: 300px;
    }
    .ListSocLink li {
    list-style-type: none;
    display: inline-block;
    width: 26px;
    height: 42px;
    margin: 0;
    padding-right: 24px;
    vertical-align: middle
    }
    .ListSocLink li label {
    background-repeat: no-repeat;
    background-image:
    url("<?php echo WEB_ROOT_URL ?>/blog/wp-content/themes/teamlab-blog-2-0/images/color_social_icons.svg");
    -webkit-filter: grayscale(1);
    filter: grayscale(1);
    display: block;
    height: 24px;
    width: 32px;
    margin: 0;
    vertical-align: middle;
    background-position-y: 0;
    }
    .ListSocLink li label:hover {
    -webkit-filter: grayscale(0);
    filter: grayscale(0)
    }
    .ListSocLink li label:active {
    background-position-y: -41px
    }

    .ListSocLink li label.social_grey_subscribe{
    background-position-x: -430px;
    }

    .ListSocLink li label.social_grey_fb {
    background-position-x: 4px;
    }


    .ListSocLink li label.social_grey_twi {
    background-position-x: -36px;
    }


    .ListSocLink li label.social_grey_in {
    background-position-x: -76px;
    }


    .ListSocLink li label.social_grey_g {
    background-position-x: -75px;
    }


    .ListSocLink li label.social_grey_tube {
    background-position-x: -116px;
    }


    .ListSocLink li label.social_grey_blog {
    background-position-x: -196px;
    }

    .ListSocLink li label.social_grey_medium {
    background-position-x: -236px;
    }

    .ListSocLink li label.social_grey_instagram {
    background-position-x: -276px;
    }

    .ListSocLink li label.social_grey_vk {
    background-position-x: -156px;
    }

    .ListSocLink li label.social_grey_github {
    background-position-x: -316px
    }

    .ListSocLink li label.social_grey_fosstodon {
    background-position-x: -393px
    }
    <?php 
}

/**
 * Redirect pages
 */
function redirect_page() {
    if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }

    $currenturl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $currenturl_relative = wp_make_link_relative($currenturl);

    switch ($currenturl_relative) {
        case '/blog/2021/09/7-best-sharepoint-alternatives-to-consider-in-2021/':
            $urlto = home_url('/2021/09/best-sharepoint-alternatives/');
            break;
        case '/blog/2021/11/top-10-basecamp-alternatives-2021/':
            $urlto = home_url('/2021/11/basecamp-alternatives/' );
            break;
        case '/blog/fr/2021/09/7-meilleures-alternatives-a-sharepoint-a-envisager-en-2021/':
            $urlto = home_url('/fr/2021/09/meilleures-alternatives-a-sharepoint-a-envisager/' );
            break;
        case '/blog/es/2021/09/7-mejores-alternativas-a-sharepoint-para-tener-en-cuenta-en-2021/':
            $urlto = home_url('/es/2021/09/mejores-alternativas-a-sharepoint/' );
            break;
        case '/blog/it/2021/09/7-migliori-alternative-a-sharepoint-nel-2021/':
            $urlto = home_url('/it/2021/09/migliori-alternative-a-sharepoint/' );
            break;
        case '/blog/de/2022/12/beste-software-zur-automatisierung-von-dokumenten-2022/':
            $urlto = home_url('/de/2022/12/beste-software-zur-automatisierung-von-dokumenten/' );
            break;
        case '/blog/de/2021/09/7-beste-alternativen-zu-sharepoint-2021/':
            $urlto = home_url('/de/2021/09/beste-alternativen-zu-sharepoint/' );
            break;
        case '/blog/de/2021/11/die-10-besten-basecamp-alternativen-2021/':
            $urlto = home_url('/de/2021/11/die-besten-basecamp-alternativen/' );
            break;
        case '/blog/2023/02/chatgpt-plugin-in-onlyoffice-docs/':
            $urlto = home_url('/2023/02/what-is-chatgpt/' );
            break;
        case '/blog/de/2023/02/chatgpt-plugin-in-onlyoffice-docs/':
            $urlto = home_url('/de/2023/02/was-ist-chatgpt/' );
            break;
        case '/blog/fr/2023/02/plugin-chatgpt-dans-onlyoffice-docs/':
            $urlto = home_url('/fr/2023/02/c-est-quoi-chatgpt/' );
            break;
        case '/blog/es/2023/02/plugin-de-chatgpt-para-onlyoffice-docs/':
            $urlto = home_url('/es/2023/02/que-es-chatgpt/' );
            break;
        case '/blog/pt-br/2023/02/plugin-chatgpt-no-onlyoffice-docs/':
            $urlto = home_url('/pt-br/2023/02/o-que-e-chatgpt/' );
            break;
        case '/blog/it/2023/02/plugin-chatgpt-in-onlyoffice-docs/':
            $urlto = home_url('/it/2023/02/cos-e-chatgpt/' );
            break;
        case '/blog/zh-hans/2023/02/chatgpt-plugin-in-onlyoffice-docs/':
            $urlto = home_url('/zh-hans/2023/02/chatgpt/' );
            break;
        case '/blog/ja/2023/02/onlyoffice-docs-chatgpt/':
            $urlto = home_url('/ja/2023/02/chatgpt/' );
            break;

        default:
        return;
    }

    if ($currenturl != $urlto)
    exit( wp_redirect( $urlto ) );
}

add_action( 'template_redirect', 'redirect_page' );

/**
 * WPGraphQL Custom Post Types
*/
add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'discoursePermalink', [
        'type' => 'String',
        'resolve' => function( $post ) {
            $discoursePermalink = get_post_meta( $post->ID, 'discourse_permalink', true );
            return ! empty( $discoursePermalink ) ? $discoursePermalink : '';
        }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'viewCount', [ 
      'type' => 'Int',
      'resolve' => function( $post ) {
        $view_count = get_post_meta( $post->ID, 'views', true );
        return ! empty( $view_count ) ? $view_count : 0;
      }
    ]);
});

add_filter( 'register_post_type_args', function( $args, $post_type ) {
    if ( 'news' === $post_type ) {
      $args['show_in_graphql'] = true;
      $args['graphql_single_name'] = 'newsItem';
      $args['graphql_plural_name'] = 'news';
    }
    return $args;
}, 10, 2 );

add_filter( 'graphql_connection_max_query_amount', function ( int $max_amount, $source, array $args, $context, $info ) {
	if ( empty( $info->fieldName ) ) {
		return $max_amount;
	}
	return 10000;
}, 10, 5 );

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'NewsItem', 'url', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        $url = get_post_meta( $post->ID, 'URL', true );
        return ! empty( $url ) ? $url : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'NewsItem', 'shortUrl', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        $shortUrl = get_post_meta( $post->ID, 'ShortURL', true );
        return ! empty( $shortUrl ) ? $shortUrl : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'NewsItem', 'excerpt', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        $excerpt = wp_trim_words( get_the_content( $post->ID ), '35', '...' );
        return ! empty( $excerpt ) ? $excerpt : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'NewsItem', 'dateNews', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        $dateNews = get_post_meta( $post->ID, 'dateNews', true );
        return ! empty( $dateNews ) ? $dateNews : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'firstImgPost', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        $firstImgPost = bloggood_ru_image();
        return ! empty( $firstImgPost ) ? $firstImgPost : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field('Post', 'aioseoTitle', [
        'type' => 'String',
        'resolve' => function($post) {
            if (function_exists('aioseo')) {
                $aioseoData = aioseo()->meta->title->getTitle($post->ID);
                return $aioseoData ?: '';
            }
            return '';
        }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'aioseoDescription', [ 
        'type' => 'String',
        'resolve' => function($post) {
            if (function_exists('aioseo')) {
                $aioseoData = aioseo()->meta->description->getDescription($post->ID);
                return $aioseoData ?: '';
            }
            return '';
        }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'moreTextExcerpt', [ 
      'type' => 'String',
      'resolve' => function( $post ) {
        function disable_more_link( $link ) {
            $link = preg_replace('|#more-[0-9]+|', '', '');
            return $link;
        }
        add_filter( 'the_content_more_link', 'disable_more_link', 10, 2 );

        $moreTextExcerpt = !empty( $attributes['moreText'] ) ? get_the_excerpt() : wp_trim_words(get_the_content($post->ID), '35', '...');
        return ! empty( $moreTextExcerpt ) ? $moreTextExcerpt : '';
      }
    ]);
});

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'Post', 'outdated', [ 
      'type' => 'Boolean',
      'resolve' => function( $post ) {
        $outdated = get_post_meta( $post->ID, 'outdated', true );
        return ! empty( $outdated ) ? $outdated : '';
      }
    ]);
});

function discourse_publish_format_html( $output, $post_id ) {
	$post = get_post( $post_id );
    $permalink = str_replace('https://wpblog.onlyoffice.com/', 'https://www.onlyoffice.com/blog/', get_permalink($post_id));
    $post_title = get_the_title( $post );
	ob_start();
	?>

    <small>Originally published at: <a href="<?php echo  $permalink ?>" target="_blank" rel="noreferrer noopener"><?php echo $post_title ?> | ONLYOFFICE Blog</a></small>
    <br>
    {excerpt}

	<?php
	$output = ob_get_clean();
	return $output;
}

add_filter( 'discourse_publish_format_html', 'discourse_publish_format_html', 10, 2 );
