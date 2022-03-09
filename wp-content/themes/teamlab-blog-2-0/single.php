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
            <div class="sidebar">
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
        </div><!-- .content -->
    </div><!-- #Single Container -->
</main>
<?php get_footer();