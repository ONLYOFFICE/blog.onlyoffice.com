<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="ErrorContainer">
				<div class="bg"></div>
				<h1><?php _e( '404 Page not found', 'teamlab-blog-2-0' ); ?></h1>
				<p><?php _e( "The page you were looking for doesn't exist, isn't available, or was loading incorrectly.", "teamlab-blog-2-0" ); ?></p>
				<a class="go-home" href="<?php echo icl_get_home_url() ?>"><?php _e('Go back to Home', 'teamlab-blog-2-0'); ?></a>
			</div><!-- .ErrorContainer -->
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
