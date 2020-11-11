<?php
/*
Template Name: For header on site Onlyoffice.
*/

get_header();
?>

<?php
// запрос

$news_cat_id = get_cat_ID('news'); 
$news_post_cat_id = get_cat_ID('news-post'); 

$argsSticky = [
 'posts_per_page' => 1,
 'post__in' => get_option('sticky_posts'),
 'ignore_sticky_posts' => 1,
 'cat'=>-1012,
 'category__not_in' => $news_cat_id
];

$querySticky = new WP_Query($argsSticky);?>


<main>
	<div class="ForSiteContainer">
    	
    
    	<div id="container" class="pageContent">
    		<?php if ($querySticky->have_posts()) : ?>
    		<div id="content" role="main">
			<div class="content">
				<?php while ($querySticky->have_posts()) : $querySticky->the_post(); ?>

				<article class="post"> 
					<?php if( has_post_thumbnail() ) { // условие, если есть миниатюра?>
          <a href="<?php the_permalink() ?>" target="_blank alt="<?php the_title(); ?>"><img src="<?php echo the_post_thumbnail_url( 'full' ); ?>" alt="<?php the_title(); ?>"/></a>
          <?php } else { ?>
          <a href="<?php the_permalink() ?>" target="_blank" alt="<?php the_title(); ?>"><img src="<?php echo bloggood_ru_image(); ?>" alt="<?php the_title(); ?>"/></a>
          <?php } ?>
		  			<div class="meta head">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
					</div>
					<p class="entry-title"><a href="<?php the_permalink(); ?>" target="_blank" title="<?php printf( esc_attr__( 'Permalink to %s', 'teamlab-blog-2-0' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></p>
				</article>
				<?php  endwhile;else: ?>
				 <?php  endif; ?>
			</div>

	</div>
 <?php wp_reset_postdata(); ?>
	
</main>
<?php get_footer(); ?>