<?php
/*
Template Name: Onlyoffice in the press
*/

get_header();
?>

<?php
// запрос

 $args = [
  'post_type' => 'post',
  'post_status' => 'publish',
  'posts_per_page' => 5,
  'cat'=>1012,
  'category__not_in' => $news_cat_id
 ];

 $wp_query = new WP_Query($args); ?>

<main>
<div class="SingleContainer">
	<?php if ($wp_query->have_posts()) : ?>
	<div id="content" role="main">
			<div class="content">

				<h2 class="entry-title"><?php the_title(); ?></h2>

				<?php $countOfPosts = 0; 
     			$countOfCountSub = 0?>
  				<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
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

  				<?php
    			if ($countOfPosts > 0) :
     			$countOfPosts = $countOfPosts + 1;
   				?>

   				<?php if (($countOfPosts == 3 || $countOfPosts == $wp_query->post_count) && $countOfCountSub == 0) : ?>
    			<?php include get_template_directory() . '/' . 'subscribe-block.php' ?>
    			<?php $countOfCountSub = $countOfCountSub + 1 ?>
   				<?php endif; ?>

   				<?php else : ?>
    			<?php $countOfPosts = $countOfPosts + 1; ?>
   				<?php endif; ?>
  				<?php endwhile;?>

  				<?php if ($wp_query->max_num_pages > 1) : ?>
   				<script>
    			var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
    			var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
    			var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('page') : 1; ?>';
    			var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
   				</script>
  				<?php endif; ?>

  				<div class="load_more_results" id="true_loadmore_press">Load more</div>
				<?php wp_reset_postdata(); ?>

				<?php else : ?>
  				<p> <?php _e('Sorry, no posts'); ?></p>
				<?php endif;?>


			</div><!-- #content -->
		<div class="sidebar-press">
      <?php dynamic_sidebar('sidebar-2'); ?>
  </div>
	</div><!-- .content -->
	</div><!-- .SingleContainer -->
</main><!-- #main -->

<?php
get_footer();

