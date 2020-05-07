<?php
    /**
     * The template for displaying Comments.
     *
     * The area of the page that contains both current comments
     * and the comment form.  The actual display of comments is
     * handled by a callback to tmblog_comment which is
     * located in the functions.php file.
     *
     * @package WordPress
     * @subpackage Twenty_Ten
     * @since Twenty Ten 1.0
     */
?>
<div id="comments">
    <?php if ( post_password_required() ) : ?>
    <p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'teamlab-blog-2-0' ); ?></p>
</div><!-- #comments -->
<?php
    /* Stop the rest of comments.php from being processed,
    * but don't kill the script entirely -- we still have
    * to fully load the template.
    */
    return;
?>
<?php endif; ?>
<?php
    // You can start editing here -- including this comment!
?>
<?php if ( have_comments() ) : ?>
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
<div class="navigation">
    <div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'teamlab-blog-2-0' ) ); ?></div>
    <div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'teamlab-blog-2-0' ) ); ?></div>
</div> <!-- .navigation -->
<?php endif; // check for comment navigation ?>
<div class="comments-title">
    <span class="comments-link"><?php comments_number( '', __( '1 comment', 'teamlab-blog-2-0' ), __( 'Comments (%)', 'teamlab-blog-2-0' ) ); ?></span>
</div>
<ol class="commentlist">
    <?php
            wp_list_comments( array( 'callback' => 'tmblog_comment' ) );
    ?>
</ol>
<?php
    else : // or, if we don't have comments:
         /* If there are no comments and comments are closed,
         * let's leave a little note, shall we?
         */
         if ( ! comments_open() ) :
?>
<p class="nocomments"><?php _e( 'Comments are closed.', 'teamlab-blog-2-0' ); ?></p>
<?php endif; // end ! comments_open() ?>
<?php endif; // end have_comments() ?>
<!-- <comment form>  -->
<div id="respond">
    <div class="respond-header">
        <h3 class="respond-title"><?php comment_form_title( __('Add a comment'), __('Add a comment to %s' ) ); ?></h3>
    </div>
    <div id="cancel-comment-reply">
        <small><?php cancel_comment_reply_link() ?></small>
    </div>
    <?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
    <p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.'), wp_login_url( get_permalink() )); ?></p>
    <?php else : ?>
    <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
        <?php if ( is_user_logged_in() ) : ?>
        <p><?php printf(__('Logged in as <a class="account-name" href="%1$s">%2$s</a>'), get_option('siteurl') . '/wp-admin/profile.php', $user_identity); ?><a class="logout" href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e('Log out of this account'); ?>"><?php _e('Log out &raquo;'); ?></a></p>
        <?php else : ?>
        <p class="author">
            <label for="author"><?php _e('Name:'); ?>&nbsp;<?php if ($req) _e('<span class="important">*</span>'); ?></label>
            <div class="textinput"><input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" <?php if ($req) echo "aria-required='true'"; ?> /></div>
        </p>
        <p class="email">
            <label for="email"><?php _e('E-mail (will not be published):'); ?>&nbsp;<?php if ($req) _e('<span class="important">*</span>'); ?></label>
            <div class="textinput"><input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" <?php if ($req) echo "aria-required='true'"; ?> /></div>
        </p>
        <p class="url disabled">
            <label for="url" class="disabled"><?php _e('Website:'); ?></label>
            <div class="textinput disabled"><input type="text" name="url" id="url" value="<?php echo  esc_attr($comment_author_url); ?>" /></div>
        </p>
        <?php endif; ?>
        <p class="message">
            <label for="comment"><?php _e('Message:'); ?></label>
            <div class="textarea"><textarea name="comment" id="comment"></textarea></div>
        </p>
        <?php do_action('comment_form', $post->ID); ?>
        <p class="submit">
            <input name="submit" type="submit" id="commentformsubmit" value="<?php _e('Add comment'); ?>" class="button gray" /><?php comment_id_fields(); ?>
        </p>
    </form>
    <?php endif; // If registration required and not logged in ?>
</div>
<!-- </comment form> -->
</div><!-- #comments -->
