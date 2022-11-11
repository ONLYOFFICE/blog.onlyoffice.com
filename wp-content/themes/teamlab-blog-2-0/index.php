<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Teamlab_Blog_2.0
 */

get_header(); ?>

<?php
// запрос

$news_cat_id = get_cat_ID('news');
$news_post_cat_id = get_cat_ID('news-post');

$argsSticky = [
  'posts_per_page' => 1,
  'post__in' => get_option('sticky_posts'),
  'ignore_sticky_posts' => 1,
  'category__not_in' => $news_cat_id
];

$argsNews =
  [
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 9,
    'category__in' => [$news_cat_id, $news_post_cat_id]
  ];

$querySticky = new WP_Query($argsSticky);
$queryNews = new WP_Query($argsNews); ?>


<main>
  <div class="WrapperBg">
    <div class="MainContainer">


    <div id="container" class="pageContent">
      <div class="search-main">
        <?php get_search_form(); ?>
      </div>
      <?php if ($querySticky->have_posts()) : ?>
        <div id="content" role="main main-post">
          <div class="content">
            <?php while ($querySticky->have_posts()) : $querySticky->the_post(); ?>

              <article class="post">
                <?php if (has_post_thumbnail()) { // условие, если есть миниатюра
                ?>
                  <a href="<?php the_permalink() ?>" alt="<?php the_title(); ?>"><img src="<?php echo the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?>" /></a>
                <?php } else { ?>
                  <a href="<?php the_permalink() ?>" alt="<?php the_title(); ?>"><img src="<?php echo bloggood_ru_image(); ?>" alt="<?php the_title(); ?>" /></a>
                <?php } ?>
                <div class="post-content">
                  <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'teamlab-blog-2-0'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                  <div class="meta head">
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
                  <?php the_excerpt() ?>
                </div>
              </article>


            <?php endwhile;
          else : ?>
            <div id="content" role="main">
              <div class="content">
                <div class="no-results">
                  <h3><?php _e('No results matching your query could be found', 'teamlab-blog-2-0'); ?></h3>
                  <div class="bg"></div>
                </div>
              </div>
              <?php if ($current_language == WEB_ROOT_URL . '/' . 'zh') {?>
              <div class="sidebar">
                <?php dynamic_sidebar('zh-sidebar'); ?>
              </div>
               <?php 
              }else{
                get_sidebar();
              }
              ?>
            </div>
            <?php get_footer(); ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="category-topics">
            <h4>Category Topics</h4>
            <ul>
              <li>
                <a class="product-releases-topic" href="<?php echo icl_get_home_url() ?>category/product-releases">Product releases</a>
              </li>
              <li>
                <a class="for-developers-topic" href="<?php echo icl_get_home_url() ?>category/for-developers">For developers</a>
              </li>
              <li>
                <a class="for-business-topic" href="<?php echo icl_get_home_url() ?>category/for-business">For business</a>
              </li>
              <li>
                <a class="for-education-topic" href="<?php echo icl_get_home_url() ?>category/for-education">For education</a>
              </li>
            </ul>
          </div>
          <?php if ($current_language == WEB_ROOT_URL . '/' . 'zh') { ?>
            <div class="sidebar">
              <?php dynamic_sidebar('zh-sidebar'); ?>
            </div>
          <?php 
            }else{
              get_sidebar();
            }
          ?>
        </div>
        </div>

        <?php 
          $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'category__not_in' => $news_cat_id,
            // 'cat'=> 1061,
            'category_name' => 'teamlab'
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('Product releases', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo icl_get_home_url() ?>category/teamlab"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo icl_get_home_url() ?>category/teamlab"><?php _e('View all posts Product releases', 'teamlab-blog-2-0'); ?></a>
            </div>
          </div>
        <?php else : ?>
        <?php endif; ?>

        <?php 
          $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'category__not_in' => $news_cat_id,
            'cat'=> 1062,
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For developers', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo icl_get_home_url() ?>category/for-developers"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo icl_get_home_url() ?>category/for-developers"><?php _e('View all posts For developers', 'teamlab-blog-2-0'); ?></a>
            </div>
          </div>
        <?php else : ?>
        <?php endif; ?>

        <?php include get_template_directory() . '/' . 'download-block.php' ?>

        <?php 
          $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'category__not_in' => $news_cat_id,
            'cat'=> 1063,
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For business', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo icl_get_home_url() ?>category/for-business"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo icl_get_home_url() ?>category/for-business"><?php _e('View all posts For business', 'teamlab-blog-2-0'); ?></a>
            </div>
          </div>
        <?php else : ?>
        <?php endif; ?>

        <?php 
          $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'category__not_in' => $news_cat_id,
            'cat'=> 1064,
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For education', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo icl_get_home_url() ?>category/for-education"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo icl_get_home_url() ?>category/for-education"><?php _e('View all posts For education', 'teamlab-blog-2-0'); ?></a>
            </div>
          </div>
        <?php else : ?>
        <?php endif; ?>

        <?php include get_template_directory() . '/' . 'subscribe-blue.php' ?>

        <?php 
          $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'category__not_in' => $news_cat_id,
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock wrapperBlockBottom">
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
          </div>

        <?php if ($wp_query->max_num_pages > 1) : ?>
          <script>
            var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
            var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
            var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('paged') : 1; ?>';
            var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
          </script>

          <div class="wrapperMain">
            <div class="main_button" id="true_loadmore"><?php _e('Load more', 'teamlab-blog-2-0'); ?></div>
            <div class="main_button" id="true_loadmore_mobile"><?php _e('View all posts', 'teamlab-blog-2-0'); ?></div>
          </div>
          <?php endif; ?>

          <?php wp_reset_postdata(); ?>

        <?php else : ?>
        <?php endif; ?>
    </div>
</main>
<div class="delimetr"></div>
<?php get_footer(); ?>