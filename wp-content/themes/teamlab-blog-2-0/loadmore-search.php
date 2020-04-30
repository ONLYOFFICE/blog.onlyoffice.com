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