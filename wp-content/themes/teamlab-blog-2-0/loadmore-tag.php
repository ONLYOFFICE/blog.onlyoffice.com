<article class="post tags-page">

	<div class="meta">
		<span class="date"><?php if ($current_language == WEB_ROOT_URL . '/' . 'zh') {
								echo get_the_date('Y日m月d日');
							} else if ($current_language == WEB_ROOT_URL . '/' . 'ja'){
								echo get_the_date('Y年m月d日 ');
							} else {
								echo get_the_date('j F Y');
							} ?></span>
		<span class="autor"><?php tmblog_posted_by(); ?></span>
	</div>
	<h2 class="entry-title tags-page-title"><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'tmblog'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

	<p><?php the_excerpt() ?></p>
</article>