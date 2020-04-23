<?php
    /**
     * The template for displaying Tag Archive pages.
     *
     * @package WordPress
     * @subpackage Twenty_Ten
     * @since Twenty Ten 1.0
     */
    get_header();
?>
<div class="MainContainer">
    <div class="sideBar">
        <?php //get_search_form(); ?>
        <div class="border_center">
            <?php get_sidebar(); ?>
        </div>
    </div>
    <div id="container" class="pageContent">
        <div id="content" role="main">
            <h1 class="page-title"><?php
                printf( __( 'Tag Archives: %s', 'tmblog' ), '<span>' . single_tag_title( '', false ) . '</span>' );
                ?></h1>
            <?php
                /* Run the loop for the tag archive to output the posts
                 * If you want to overload this in a child theme then include a file
                 * called loop-tag.php and that will be used instead.
                 */
                 get_template_part( 'loop', 'tag' );
            ?>
        </div><!-- #content -->
    </div><!-- #container -->
</div>
<?php get_footer(); ?>
