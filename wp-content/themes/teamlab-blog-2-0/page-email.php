<?php
/**
 * The template for displaying after subscribe
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * Template Name: Page for email
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

<div class="MailContainer">

			<div class="content-mail">
				<div class="cta">
					<?php the_content(); ?>
				</div>
			</div><!-- #content -->
			<div class="letter-box"></div>
	

</div><!-- #Single Container -->
<?php get_footer();
