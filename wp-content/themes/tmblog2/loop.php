<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop.php or
 * loop-template.php, where 'template' is the loop context
 * requested by a template. For example, loop-index.php would
 * be used if it exists and we ask for the loop with:
 * <code>get_template_part( 'loop', 'index' );</code>
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<!--
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
  <div id="nav-above" class="navigation">
    <div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'tmblog' ) ); ?></div>
    <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'tmblog' ) ); ?></div>
  </div>
<?php endif; ?>
-->
<?php if ( ! is_home() && have_posts() ) : ?>
  <?php if ( function_exists('yoast_breadcrumb') ) { yoast_breadcrumb('<div class="breadcrumbs">', '</div>'); } ?>
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Foun d', 'tmblog' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'tmblog' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php
	/* Start the Loop.
	 *
	 * In Twenty Ten we use the same loop in multiple contexts.
	 * It is broken into three main parts: when we're displaying
	 * posts that are in the gallery category, when we're displaying
	 * posts in the asides category, and finally all other posts.
	 *
	 * Additionally, we sometimes check for whether we are on an
	 * archive page, a search page, etc., allowing for small differences
	 * in the loop on each template without actually duplicating
	 * the rest of the loop that is shared.
	 *
	 * Without further ado, the loop:
	 */
?>

<?php while ( have_posts() ) : the_post(); ?>

<?php /* How to display posts in the Gallery category. */ ?>

	<?php if ( in_category( _x('gallery', 'gallery category slug', 'tmblog') ) ) : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="post-header">
        <div class="left-side"></div>
        <div class="right-side"></div>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
        <div class="entry-meta"><?php tmblog_posted_on(); ?></div>
      </div>

			<div class="entry-content">
<?php if ( post_password_required() ) : ?>
				<?php the_content(); ?>
<?php else : ?>
				<div class="gallery-thumb">
<?php
	$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
	$total_images = count( $images );
	$image = array_shift( $images );
	$image_img_tag = wp_get_attachment_image( $image->ID, 'thumbnail' );
?>
					<a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
				</div><!-- .gallery-thumb -->
				<p><em><?php printf( __( 'This gallery contains <a %1$s>%2$s photos</a>.', 'tmblog' ),
						'href="' . get_permalink() . '" title="' . sprintf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark"',
						$total_images
					); ?></em></p>

				<?php the_excerpt(); ?>
<?php endif; ?>
			</div><!-- .entry-content -->

        <div class="entry-meta"><?php tmblog_posted_by(); ?></div>
		<div class="entry-utility">
			<a href="<?php echo get_term_link( _x('gallery', 'gallery category slug', 'tmblog'), 'category' ); ?>" title="<?php esc_attr_e( 'View posts in the Gallery category', 'tmblog' ); ?>"><?php _e( 'More Galleries', 'tmblog' ); ?></a>
			<span class="meta-sep">|</span>
			<span class="comments-link"><?php comments_popup_link( __( 'Add a comment', 'tmblog' ), __( '1 Comment', 'tmblog' ), __( '% Comments', 'tmblog' ) ); ?></span>
			<?php edit_post_link( __( 'Edit', 'tmblog' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
		</div><!-- .entry-utility -->
        </div><!-- #post-## -->

<?php /* How to display posts in the asides category */ ?>

	<?php elseif ( in_category( _x('asides', 'asides category slug', 'tmblog') ) ) : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ( is_archive() || is_search() ) : // Display excerpts for archives and search. ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
		<?php else : ?>
			<div class="entry-content">
				<?php the_content( __( '<span class="meta-nav">&rarr;</span>', 'tmblog' ) ); ?>
			</div><!-- .entry-content -->
		<?php endif; ?>

		    <div class="entry-utility">
			    <?php tmblog_posted_on(); ?>
			    <span class="meta-sep">|</span>
			    <span class="comments-link"><?php comments_popup_link( __( 'Add a comment', 'tmblog' ), __( '1 Comment', 'tmblog' ), __( '% Comments', 'tmblog' ) ); ?></span>
			    <?php edit_post_link( __( 'Edit', 'tmblog' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
		    </div><!-- .entry-utility -->
		</div><!-- #post-## -->

<?php /* How to display all other posts. */ ?>

	<?php else : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
          <div class="post-header">
            <h2 class="entry-title">
                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'tmblog' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">
                    <?php the_title(); ?>
                </a>
            </h2>
           <!-- <div class="entry-meta"><?php tmblog_posted_on(); ?></div> -->
          </div>
        
      <div class="entry-meta">
        <?php tmblog_posted_by(); ?>
      </div>
      <div class="entry-info">
          <span class="post-date post-info"><?php tmblog_posted_on(); ?></span>
          <?php $tags_list = get_the_tag_list( '', ', ' ); ?>
          <?php if ( $tags_list ) : ?>
		  <span class="post-tags post-info" style="margin-right: 3px;">
              <?php printf( __( '%1$s', 'tmblog' ), $tags_list ); ?>
          </span>
		  <?php endif; ?>
          <span class="post-comments post-info"><?php comments_popup_link( __( 'Add a comment', 'tmblog' ), __( '1 comment', 'tmblog' ), __( '% comments', 'tmblog' ) ); ?></span>
      </div>

      <?php if ( is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
        <div class="entry-summary"><?php the_excerpt(); ?></div>
      <?php else : ?>
        <div class="entry-content">
          <?php the_content( __( '<span class="meta-nav">&rarr;</span>', 'tmblog' ) ); ?>
          <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'tmblog' ), 'after' => '</div>' ) ); ?>
        </div><!-- .entry-content -->
      <?php endif; ?>

      <!--<div class="entry-utility">
        <div class="utility-container">
          <?php edit_post_link( __( 'Edit', 'tmblog' ), '<span class="edit-link">', '</span>' ); ?>
          <!--<span class="comments-link"><?php comments_popup_link( __( 'Add a comment', 'tmblog' ), __( '1 comment', 'tmblog' ), __( '% comments', 'tmblog' ) ); ?></span>-->
       <!-- </div>
        <div class="sc-container">

          <span class="tweet-button">&nbsp;<a href="http://twitter.com/intent/tweet?text=<?php echo urlencode(get_the_title()); ?>&amp;count=horizontal&amp;lang=en&url=<?php echo urlencode(get_permalink()); ?>" class="twitter-share-button">Tweet</a></span>

          <span class="googleplus-button"><g:plusone href="<?php echo get_permalink(); ?>" size="medium" count="true"></g:plusone></span>
          <span class="like-this">&nbsp;
              <iframe src="https://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink()); ?>&amp;locale=en_US&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;width=160&amp;height=22&amp;appId=348115305273336" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:160px; height:22px;" allowTransparency="true">
              </iframe>
					</span>

					<span class="linkedin-button">
							<script type="IN/Share" data-url="<?php echo get_permalink(); ?>" data-counter="right"></script>
					</span>
          <span class="clear"></span>
        </div>
      </div><!-- .entry-utility -->
	</div><!-- #post-## -->

	<?php comments_template( '', true ); ?>

	<?php endif; // This was the if statement that broke the loop into three parts based on categories. ?>

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<span class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">Older Entries &gt;</span>', 'tmblog' ) ); ?></span>
					<span class="nav-next"><?php previous_posts_link( __( '<span class="meta-nav">&lt; Newer Entries</span>', 'tmblog' ) ); ?></span>
				</div><!-- #nav-below -->
<?php endif; ?>
