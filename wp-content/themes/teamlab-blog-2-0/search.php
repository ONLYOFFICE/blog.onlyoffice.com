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
		      	<?php
				var_dump($wp_query->query_vars);
				?>

				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<article class="post searchlist">

				<div class="meta searchresult">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
						<span class="autor"><?php _e('By') ?> <?php echo get_the_author(); ?></span>
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
    			var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
    			var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
    			var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
   				</script>
  				
  				<div class="load_more_results" id="true_loadmore_search">Load more</div>
				<?php wp_reset_postdata(); ?>
				<?php endif; ?>

				<?php else : ?>
  				<div class="no-results">
				 <h3>No results matching your query could be found</h3>
				 <div class="bg"></div>
				<?php endif;?>

				 


			</div><!-- #content -->
			<div class="sidebar">
		<?php dynamic_sidebar('sidebar-2'); ?>
		</div><!-- .sidebar -->
	</div><!-- .content -->
</main><!-- #main -->

<?php
get_footer();
