<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Teamlab_Blog_2.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );

		endif;

		if ( 'post' === get_post_type() ) :
			?>
			<div class="meta info-page">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
						<span class="autor"><?php tmblog_posted_by(); ?></span>
			      <span class="comments"><?php comments_number('0', '1', '%'); ?></span>
						<span class="views"><?php if(function_exists('the_views')) { the_views(); } ?></span>
						<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { ADDTOANY_SHARE_SAVE_KIT(); } ?>
			</div>
			

		<?php endif; ?>
	</div><!-- .entry-header -->
	<div class="entry-content">
		<?php the_content();?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
<div class="tagsList">
				<div class="tagLine">
					<?php

					$terms = wp_get_post_terms( $post->ID, 'post_tag');

					$term_id = $post->ID;
					$term_link = get_term_link( $term_id, $terms_list);
					?>


					<?php foreach ($terms as $term): ?>
							<div class="tagItem"><?php echo '<a href="' . get_term_link($term->slug, $term->taxonomy) . '">' . '#' . $term->name . '</a>' ;?></div>
					<?php endforeach;?>
				</div>
				<div class="tagShare">
					<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { ADDTOANY_SHARE_SAVE_KIT(); } ?>
				</div>
			</div>
			

			<?php	include get_template_directory() . '/' . 'cloud-block.php'; 
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;?>



