<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

<?php
    $recaptcha_url = "https://www.google.com/recaptcha/api.js?hl=en";

    if ($current_language == WEB_ROOT_URL.'/'.'fr') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=fr';
    } else if ($current_language == WEB_ROOT_URL.'/'.'de') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=de';
    } else if ($current_language == WEB_ROOT_URL.'/'.'it') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=it';
    } else if ($current_language == WEB_ROOT_URL.'/'.'es') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=es';
    } else if ($current_language == WEB_ROOT_URL.'/'.'cs') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=cs';
    } else if ($current_language == WEB_ROOT_URL.'/'.'pt') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=pt-BR';
    } else if ($current_language == WEB_ROOT_URL.'/'.'ja') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=ja';
    } else if ($current_language == WEB_ROOT_URL.'/'.'zh') {
        $recaptcha_url = 'https://www.google.com/recaptcha/api.js?hl=zh-CN';
    }
?>

<script src="<?php echo $recaptcha_url ?>" async defer></script>

<main>
    <div class="PostContainer">

        <div class="breadcrumbs-single">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">
                <?php
                if(function_exists('bcn_display')) 
                {
                    bcn_display();
                }?>
            </div>
        </div>
        <div id="post-content" role="main">
            <div class="content">
                <?php		
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content', get_post_type() );
                endwhile; // End of the loop.
                ?>
            </div><!-- #content -->
        </div><!-- .content -->
    </div><!-- #Single Container -->
    <div class="sidebar recent-posts-block">
        <div class="recent-posts-block-container">
            <h3><?php _e( 'Recent posts', 'teamlab-blog-2-0' ); ?></h3>
            <?php 
            $args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 3,
                'category__not_in' => $news_cat_id
            ];
            $wp_query = new WP_Query($args); 
            if ($wp_query->have_posts()) : ?>
            <div class="wrapperMain">
                <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php get_footer();