<?php global $current_language; ?>
<article class="post searchlist">

	<div class="meta searchresult">
		<span class="date"><?php if ($current_language == WEB_ROOT_URL . '/' . 'zh') {
								echo get_the_date('Y日m月d日');
							} else {
								echo get_the_date('j F Y');
							} ?></span>
		<span class="autor"><?php tmblog_posted_by(); ?></span>
	</div>
	<h2 class="entry-title results"><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'tmblog'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php echo the_title(); ?></a></h2>
	<p><?php echo the_excerpt(); ?></p>
</article>