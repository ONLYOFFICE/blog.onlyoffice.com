<article class="post in-the-press">
    <div class="meta press-page">
        <?php if (get_field('URL')) {
            echo '<span><a class="press-url" href="' . get_field('URL') . '" target="_blank">' . get_field('ShortURL') . '</a></span>';
        }
        ?>
        <span class="date">
            <?php 
            global $sitepress;
            $current_language = $sitepress->get_current_language();
            
            if ($current_language == 'zh-hans') {
                $dateNews = strval(get_field('dateNews', '', false));
                echo date("Y日m月d日", strtotime($dateNews));
            }  else if ($current_language == 'ja'){
                $dateNews = strval(get_field('dateNews','', false));
                echo date("Y年m月d日", strtotime($dateNews));
            }   else {
                echo get_field('dateNews');
            } ?></span>
    </div>
    <h2 class="entry-title press-page-title"><a href="<?php echo get_field('URL') ?>" target="_blank" title="<?php printf(esc_attr__('Permalink to %s', 'tmblog'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

    <p><?php $content = get_the_content();
        echo wp_trim_words($content, '35'); ?></p>
</article>