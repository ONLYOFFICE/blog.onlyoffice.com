<?php
/**
 * Template part for tags posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Teamlab_Blog_2.0
 */
?>

				<article class="post tags-page">

				<div class="meta">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
						<span class="autor"><?php tmblog_posted_by(); ?></span>
				</div>
				 <h2 class="entry-title tags-page-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
				
				 <p><?php the_excerpt() ?></p>
				 </article><?php
