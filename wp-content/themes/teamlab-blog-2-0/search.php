<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

<main id="main" class="site-main">
<div class="SearchContainer">
	<div id="content" role="main">
			<div class="content">
				<div class="breadcrumbs-search">
					<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">
				    	<?php
				    if(function_exists('bcn_display'))
				   		 {
				    bcn_display();
				    		}?>
				  </div>
			 	</div>
				<div class="search-page">
		        	<?php get_search_form(); ?>
		    </div>

				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<article class="post searchlist">

				<div class="meta searchresult">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
						<span class="autor"><?php tmblog_posted_by(); ?></span>
				</div>
				<?php $title = get_the_title(); $keys= explode(" ",$s); $title = preg_replace('/('.implode('|', $keys) .')/iu', '<span class="search-excerpt">\0</span>', $title); ?>
				 <h2 class="entry-title results"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ),the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php echo $title; ?></a></h2>
				
				<?php $excerpt = get_the_excerpt(); $keys= explode(" ",$s); $excerpt = preg_replace('/('.implode('|', $keys) .')/iu', '<span class="search-excerpt">\0</span>', $excerpt); ?>
				 <p><?php echo $excerpt; ?></p>

				 </article>

				 <?php endwhile;
				if ($wp_query->max_num_pages > 1) : ?>
   				<script>
    			var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
    			var true_posts = '<?php echo addslashes(wp_json_encode($wp_query->query_vars)); ?>';
    			var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
    			var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
   				</script>
  				
  				<div class="load_more_results" id="true_loadmore_search"><?php _e('Load more', 'teamlab-blog-2-0'); ?></div>
				<?php wp_reset_postdata(); ?>
				<?php endif; ?>

				<?php else : ?>
  				<div class="no-results">
				 <h3><?php _e('No results matching your query could be found', 'teamlab-blog-2-0'); ?></h3>
				 <div class="bg"></div>
				</div>
				<?php endif;?>

				 


			</div><!-- #content -->
			<div class="sidebar">
			<div class="recent-post">
            <h3><?php _e( 'Recent posts', 'teamlab-blog-2-0' ); ?></h3>
            <?php 
             $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'cat'=>-1125,
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
		<?php dynamic_sidebar('sidebar-2'); ?>
		<?php include get_template_directory() . '/' . 'social-icons.php' ?>
		</div><!-- .sidebar -->
	</div><!-- .content -->
</main><!-- #main -->

<?php
get_footer();
