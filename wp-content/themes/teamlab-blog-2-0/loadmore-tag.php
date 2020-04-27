<article class="post tags-page">

				<div class="meta">
						<span class="date"><?php echo get_the_date('j F Y'); ?></span>
						<span class="autor"><?php _e('By') ?> <?php echo get_the_author(); ?></span>
				</div>
				 <h2 class="entry-title tags-page-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
				
				 <p><?php the_excerpt() ?></p>
				 </article><?php