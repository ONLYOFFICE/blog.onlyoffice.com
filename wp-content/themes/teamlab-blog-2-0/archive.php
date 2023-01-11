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
	<div class="WrapperBg <?php echo (is_tag() || is_author()) ? ' WrapperTag' : '' ?>">
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
					<?php if (is_tag() || is_author()) : ?>
						<?php if ( have_posts() ) : ?>
						<?php add_filter( 'get_the_archive_title', function( $title ){
							return preg_replace('~^[^:]+: ~', '', $title );
						});?>
						<?php if( is_tag() ) {?>
							<h2 class="entry-title category-title"><?php _e('Tag Archives:', 'teamlab-blog-2-0'); ?><?php the_archive_title( '<a class="nameTags">#', '</a>' ); ?></h2>
						<?php  } elseif( is_author() ) {?>
						<h2 class="entry-title category-title"><?php _e('Author Archives:', 'teamlab-blog-2-0'); ?>
							<?php the_archive_title( '<a class="nameTags">', '</a>' ); ?></h2>
						<?php  } ?>
						<div class="wrapperMain wrapperMainTag">
							<?php while ( have_posts() ) : the_post(); 
								get_template_part( 'template-parts/content-tag', get_post_type() );
								endwhile;
							?>
						<?php global $wp_query;
						if ($wp_query->max_num_pages > 1) : ?>
						<script>
							var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
							var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
							var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
							var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
						</script>
						
						<div class="load_more_results" id="true_loadmore_tags"><?php _e('Load more', 'teamlab-blog-2-0'); ?></div>
						<div class="load_more_results" id="true_loadmore_tags_mobile"><?php _e('View all posts', 'teamlab-blog-2-0'); ?></div>
						<?php wp_reset_postdata(); ?>
						<?php endif; ?>

						<?php else : ?>
							<div class="no-results">
						<h3><?php _e('No results matching your query could be found', 'teamlab-blog-2-0'); ?></h3>
						<div class="bg"></div>
						<?php endif;?>
					<?php else : ?>
						<?php
						$currentCategory = get_queried_object()->category_nicename;
							$pageposts = new WP_Query(
								array(
									'posts_per_page' => 15, 
									'category_name' => $currentCategory,
								)
							);
						if ($pageposts->have_posts()) : ?>
							<h1><?php single_cat_title(); ?></h1>
							<?php $countOfPosts = 0; $countOfCountSub = 0 ?>
							<div class="content wrapperMain">

								<?php while ($pageposts->have_posts()) : $pageposts->the_post(); get_template_part( 'template-parts/content-tag', get_post_type() ); ?>
								<?php if ($countOfPosts > 0) : $countOfPosts = $countOfPosts + 1; ?>

								<?php if ($countOfPosts == 6) : ?>
									<?php include get_template_directory() . '/' . 'download-block.php' ?>
									<?php $countOfCountSub = $countOfCountSub ?>
								<?php endif; ?>

								<?php if ($countOfPosts == 12) : ?>
									<?php include get_template_directory() . '/' . 'subscribe-blue.php' ?>
							
							<?php $countOfCountSub = $countOfCountSub + 1 ?>
							<?php endif; ?>

						<?php else : ?>
							<?php $countOfPosts = $countOfPosts + 1; ?>
						<?php endif; ?>
						<?php endwhile; ?>

						<?php if ($countOfPosts < 6) : ?>
							<?php include get_template_directory() . '/' . 'download-block.php' ?>
						<?php endif; ?>

						<?php if ($countOfPosts < 12) : ?>
							<?php include get_template_directory() . '/' . 'subscribe-blue.php' ?>
						<?php endif; ?>

						<?php global $wp_query;
						if ($wp_query->max_num_pages > 1) : ?>
							<script>
								var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
								var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
								var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
								var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
							</script>
								
						<div class="load_more_results" id="true_loadmore_tags"><?php _e('Load more', 'teamlab-blog-2-0'); ?></div>
						<div class="load_more_results" id="true_loadmore_tags_mobile"><?php _e('View all posts', 'teamlab-blog-2-0'); ?></div>
						<?php wp_reset_postdata(); ?>
						<?php endif; ?>

						<?php else : ?>
						<div class="no-results">
							<h3><?php _e('No results matching your query could be found', 'teamlab-blog-2-0'); ?></h3>
							<div class="bg"></div>
						<?php endif;?>
					<?php endif; ?>
				</div><!-- #content -->
			</div><!-- .content -->
		</div><!-- SingleContainer -->
	</div>
</main><!-- #main -->

<?php get_footer();
