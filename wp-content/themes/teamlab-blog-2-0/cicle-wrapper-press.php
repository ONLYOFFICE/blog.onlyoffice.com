<article class="post in-the-press">		
    <div class="meta press-page">
            <?php if(get_field('URL')){
					            echo '<span><a class="press-url" href="'.get_field('URL').'" target="_blank">'.get_field('ShortURL').'</a></span>';
					        }
			?>
            <span class="date"><?php echo get_field('dateNews'); ?></span>
    </div>
    <h2 class="entry-title press-page-title"><a href="<?php echo get_field('URL')?>" target="_blank" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

    <p><?php the_excerpt() ?></p>
</article>