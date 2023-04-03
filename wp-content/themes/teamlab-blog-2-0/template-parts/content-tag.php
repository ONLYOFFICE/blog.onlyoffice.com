<?php

/**
 * Template part for tags posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Teamlab_Blog_2.0
 */
global $current_language;
?>

<article class="postwrapper">

	<?php if (has_post_thumbnail()) { // condition if there is a thumbnail
	?>
		<a href="<?php the_permalink() ?>" alt="<?php the_title(); ?>"><img src="<?php echo the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?>" /></a>
	<?php } else { ?>
		<a href="<?php the_permalink() ?>" alt="<?php the_title(); ?>"><img src="<?php echo bloggood_ru_image(); ?>" alt="<?php the_title(); ?>" /></a>
	<?php } ?>

	<div class="postThemeGridBox">

		<h4 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'tmblog'), the_title_attribute('echo=0')); ?>" rel="bookmark" alt="<?php the_title(); ?>"><?php the_title(); ?></a></h4>

		<div class="meta grid">
			<span class="date">
				<?php if ($current_language == WEB_ROOT_URL . '/' . 'zh') {
					echo get_the_date('Y日m月d日');
				} else if ($current_language == WEB_ROOT_URL . '/' . 'ja'){
					echo get_the_date('Y年m月d日 ');
				  } else {
					echo get_the_date('j F Y');
				} ?></span>
			<span class="autor"><?php tmblog_posted_by(); ?></span>
		</div>
	</div>
</article>
<?php
