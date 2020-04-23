<?php
    /**
     * The template for displaying 404 pages (Not Found).
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
            <?php if ( function_exists('yoast_breadcrumb') ) { yoast_breadcrumb('<div class="breadcrumbs">', '</div>'); } ?>
            <div id="post-0" class="post error404 not-found">
                <h1 class="entry-title"><?php _e( 'Not Found', 'tmblog' ); ?></h1>
                <div class="entry-content">
                    <p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'tmblog' ); ?></p>
                    <?php //get_search_form(); ?>
                </div><!-- .entry-content -->
            </div><!-- #post-0 -->
        </div><!-- #content -->
    </div><!-- #container -->
    <script type="text/javascript">
        // focus on search field after it has loaded
        document.getElementById('s') && document.getElementById('s').focus();
    </script>
</div>
<?php get_footer(); ?>