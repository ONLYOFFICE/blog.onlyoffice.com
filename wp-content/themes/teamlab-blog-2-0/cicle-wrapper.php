<div class="postwrapper">
		<a href="<?php the_permalink() ?>" alt="<?php the_title(); ?>"><img src="<?php echo bloggood_ru_image(); ?>" alt="<?php the_title(); ?>"/></a>
	<div class="postThemeGridBox">

		<h4 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'teamlab-blog-2-0' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" alt="<?php the_title(); ?>"><?php the_title(); ?></a></h4>

		<div class="meta grid">
			<span class="date"><?php echo get_the_date('j F Y'); ?></span>
			<span class="autor"><?php _e('By') ?> <?php echo get_the_author(); ?></span>
			<span class="comments"><?php comments_number('0', '1', '%'); ?></span>
			<span class="views"><?php if(function_exists('the_views')) { the_views(); } ?></span>
		</div>
	</div>
</div>
