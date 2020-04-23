<?php
    /**
     * The Template for displaying all single posts.
     *
     * @package WordPress
     * @subpackage Twenty_Ten
     * @since Twenty Ten 1.0
     */
?>
<?php get_header(); ?>
<div class="MainContainer">
    <div class="sideBar">
        <?php //get_search_form(); ?>
        <div class="border_center">
            <?php get_sidebar(); ?>
        </div>
    </div>
    <div id="container" class="pageContent">
        <div id="content" role="main">
            <?php if ( have_posts() ) : ?>
            <?php //if ( function_exists('yoast_breadcrumb') ) { yoast_breadcrumb('<div class="breadcrumbs">', '</div>'); } ?>
            <?php while ( have_posts() ) : the_post(); ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="post-header">
                    <h2 class="entry-title"><?php the_title(); ?></h2>
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
                <div class="entry-content">
                    <?php the_content(); ?>
                    <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'tmblog' ), 'after' => '</div>' ) ); ?>
                </div><!-- .entry-content -->
                <?php if ( get_the_author_meta( 'description' ) ) : // If a user has filled out their description, show a bio on their entries  ?>
                <div id="entry-author-info">
                    <div id="author-avatar">
                        <?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'tmblog_author_bio_avatar_size', 60 ) ); ?>
                    </div><!-- #author-avatar -->
                    <div id="author-description">
                        <h2><?php printf( esc_attr__( 'About %s', 'tmblog' ), get_the_author() ); ?></h2>
                        <?php the_author_meta( 'description' ); ?>
                        <div id="author-link">
                            <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
                                <?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'tmblog' ), get_the_author() ); ?>
                            </a>
                        </div><!-- #author-link	-->
                    </div><!-- #author-description -->
                </div><!-- #entry-author-info -->
                <?php endif; ?>

                <div class="entry-utility">
                    <div class="utility-container">
                        <?php edit_post_link( __( 'Edit', 'tmblog' ), '<span class="edit-link">', '</span>' ); ?>
                    </div>
                    <div class="sc-container">
                        <?php do_action( 'addthis_widget' ); ?>
                        <span class="tweet-button">&nbsp;<script src="https://platform.twitter.com/widgets.js" type="text/javascript"></script><a href="http://twitter.com/intent/tweet?text=<?php echo urlencode(get_the_title()); ?>&amp;count=horizontal&amp;lang=en&url=<?php echo urlencode(get_permalink()); ?>" class="twitter-share-button">Tweet</a></span>
                        <span class="googleplus-button"><g:plusone href="<?php echo get_permalink(); ?>" size="medium" count="true"></g:plusone></span>
                        <span class="like-this">&nbsp;<iframe src="https://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink()); ?>&amp;locale=en_US&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;width=160&amp;height=22&amp;appId=348115305273336" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:160px; height:22px;" allowtransparency="true"></iframe></span>
                        <span class="linkedin-button">
                            <script type="IN/Share" data-url="<?php echo get_permalink(); ?>" data-counter="right"></script>
                        </span>
                        <span class="clear"></span>
                    </div>
                </div><!-- .entry-utility -->
            </div><!-- #post-## -->
            <?php comments_template( '', true ); ?>
            <?php endwhile; // end of the loop. ?>
            <?php endif; ?>
        </div><!-- #content -->
    </div><!-- #container -->
</div>
<script type="text/javascript">
    jQuery("#comments .comment-wrap:first").css("border-top", "none");
    jQuery("#comments .comment.depth-1:last").css("border-bottom", "1px solid #E0E0E0");
    if (jQuery("#comments").length) {
        jQuery("#comments").after(jQuery("<ul style='list-style: none;padding:0;'></ul>").append(jQuery("#recent-posts")));
        jQuery("#recent-posts").show();
    }
</script>
<?php get_footer(); ?>
