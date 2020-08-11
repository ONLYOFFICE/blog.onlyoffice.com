<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

		<main>
			<div class="SingleContainer">
				<div class="breadcrumbs-single">
					<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">
				    	<?php
				    if(function_exists('bcn_display'))
				   		 {
				    bcn_display();
				    		}?>
				  </div>
			 	</div>
		
		<div id="content" role="main">
			<div class="content">
			<?php if ( have_posts() ) : ?>
			<?php
			add_filter( 'get_the_archive_title', function( $title ){
			return preg_replace('~^[^:]+: ~', '', $title );
			});?>

			
			

			<?php if( is_tag() ) {?>
			<h2 class="entry-title"><?php _e('Tag Archives:', 'teamlab-blog-2-0'); ?>
			<?php
			the_archive_title( '<a class="nameTags">#', '</a>' );
			?></h2>
			<?php  } elseif( is_author() ) {?>
			<h2 class="entry-title"><?php _e('Author Archives:', 'teamlab-blog-2-0'); ?>
			<?php
			the_archive_title( '<a class="nameTags">', '</a>' );
			?></h2>
			<?php  } ?>
				

			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content-tag', get_post_type() );

			endwhile;
			global $wp_query;
			if ($wp_query->max_num_pages > 1) : ?>
   				<script>
    			var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
    			var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
    			var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
    			var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
   				</script>
  				
  				<div class="load_more_results" id="true_loadmore_tags"><?php _e('Load more', 'teamlab-blog-2-0'); ?></div>
				<?php wp_reset_postdata(); ?>
				<?php endif; ?>

				<?php else : ?>
  				<div class="no-results">
				 <h3><?php _e('No results matching your query could be found', 'teamlab-blog-2-0'); ?></h3>
				 <div class="bg"></div>
				<?php endif;?>


					</div><!-- #content -->
					<div class="sidebar-press">
				<?php dynamic_sidebar('sidebar-2'); ?>
				<?php include get_template_directory() . '/' . 'social-icons.php' ?>
				</div>
				</div><!-- .content -->
			</div><!-- SingleContainer -->
		</main><!-- #main -->

<?php
get_footer();
