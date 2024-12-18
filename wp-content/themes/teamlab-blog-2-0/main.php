<?php
/*
Template Name: Main page
*/


get_header(); ?>

<?php
// request

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
                <?php if (has_post_thumbnail()) { // condition if there is a thumbnail
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
        <div class="sidebar-wrapper">
          <div class="category-topics">
            <h4><?php _e('Category Topics', 'teamlab-blog-2-0'); ?></h4>
            <ul>
              <?php if ($current_language == WEB_ROOT_URL.'/'.'cs') { ?>
                <li>
                  <a class="for-business-topic" href="<?php echo get_home_url() ?>/category/<?php _e('for-business', 'teamlab-blog-2-0'); ?>"><?php _e('For business', 'teamlab-blog-2-0'); ?></a>
                </li>
              <?php } else { ?>
                <li>
                  <a class="product-releases-topic" href="<?php echo get_home_url() ?>/category/<?php _e('product-releases', 'teamlab-blog-2-0'); ?>"><?php _e('Product releases', 'teamlab-blog-2-0'); ?></a>
                </li>
                <li>
                  <a class="for-developers-topic" href="<?php echo get_home_url() ?>/category/<?php _e('for-developers', 'teamlab-blog-2-0'); ?>"><?php _e('For developers', 'teamlab-blog-2-0'); ?></a>
                </li>
                <li>
                  <a class="for-business-topic" href="<?php echo get_home_url() ?>/category/<?php _e('for-business', 'teamlab-blog-2-0'); ?>"><?php _e('For business', 'teamlab-blog-2-0'); ?></a>
                </li>
                <li>
                  <a class="for-education-topic" href="<?php echo get_home_url() ?>/category/<?php _e('for-education', 'teamlab-blog-2-0'); ?>"><?php _e('For education', 'teamlab-blog-2-0'); ?></a>
                </li>
              <?php } ?>
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
            'category_name' => 'product-releases, veroeffentlichungen, mises-a-jour-des-produits-fr, lanzamientos-de-productos, lancamentos-de-produtos, rilascio-dei-prodotti, product-releases-ja, product-releases-zh-hans'
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('Product releases', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo get_home_url() ?>/category/<?php _e('product-releases', 'teamlab-blog-2-0'); ?>"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo get_home_url() ?>/category/<?php _e('product-releases', 'teamlab-blog-2-0'); ?>"><?php _e('View all posts Product releases', 'teamlab-blog-2-0'); ?></a>
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
            'category_name' => 'for-developers, entwicklung, pour-les-developpeurs-fr, para-desarrolladores, para-desenvolvedores, per-gli-sviluppatori, for-developers-ja, for-developers-zh-hans'
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For developers', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo get_home_url() ?>/category/<?php _e('for-developers', 'teamlab-blog-2-0'); ?>"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo get_home_url() ?>/category/<?php _e('for-developers', 'teamlab-blog-2-0'); ?>"><?php _e('View all posts For developers', 'teamlab-blog-2-0'); ?></a>
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
            'category_name' => 'for-business, business, pour-les-entreprises-fr, para-empresas, para-negocios, per-affari, pro-firmy, for-business-ja, for-business-zh-hans'
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For business', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo get_home_url() ?>/category/<?php _e('for-business', 'teamlab-blog-2-0'); ?>"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo get_home_url() ?>/category/<?php _e('for-business', 'teamlab-blog-2-0'); ?>"><?php _e('View all posts For business', 'teamlab-blog-2-0'); ?></a>
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
            'category_name' => 'for-education, bildung, pour-education-fr, para-la-educacion, para-educacao, per-l-istruzione, for-education-ja, for-education-zh-hans'
          ];

          $wp_query = new WP_Query($args);

          if ($wp_query->have_posts()) : ?>
          <div class="wrapperBlock">
            <div class="wrapperTitle">
              <h2><?php _e('For education', 'teamlab-blog-2-0'); ?></h2>
              <div class="view-all"><a href="<?php echo get_home_url() ?>/category/<?php _e('for-education', 'teamlab-blog-2-0'); ?>"><?php _e( 'View all <div class="no-wrap">posts&nbsp;<div class="grey-arrow"></div></div>', 'teamlab-blog-2-0'); ?></a></div>
            </div>
            <div class="wrapperMain">
              <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <?php include get_template_directory() . '/' . 'cicle-wrapper.php' ?>
              <?php endwhile; wp_reset_query(); ?>
            </div>
            <div class="wrapperMorePosts">
              <a href="<?php echo get_home_url() ?>/category/<?php _e('for-education', 'teamlab-blog-2-0'); ?>"><?php _e('View all posts For education', 'teamlab-blog-2-0'); ?></a>
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

        <script>
          var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
          var true_posts = '<?php echo serialize($wp_query->query_vars); ?>';
          var current_page = '<?php echo (get_query_var('paged')) ? get_query_var('paged') : 1; ?>';
          var max_pages = '<?php echo $wp_query->max_num_pages; ?>';
        </script>
        <?php if ($wp_query->max_num_pages > 1) : ?>
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