<?php
    /**
     * The template for displaying Category Archive pages.
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
                printf( __( 'Category Archives: %s', 'tmblog' ), '<span>' . single_cat_title( '', false ) . '</span>' );
                ?></h1>
            <?php
                    $category_description = category_description();
                    if ( ! empty( $category_description ) )
                        echo '<div class="archive-meta">' . $category_description . '</div>';
                /* Run the loop for the category page to output the posts.
                 * If you want to overload this in a child theme then include a file
                 * called loop-category.php and that will be used instead.
                 */
                get_template_part( 'loop', 'category' );
            ?>
        </div><!-- #content -->
    </div><!-- #container -->
</div>
<?php get_footer(); ?>
