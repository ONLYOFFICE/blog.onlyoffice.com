<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Teamlab_Blog_2.0
 */

get_header();
?>

<div class="SingleContainer">
	
	<div class="breadcrumbs-single">
          <div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">
              <?php
            if(function_exists('bcn_display'))
               {
            bcn_display();
                }?>
          </div>
      </div>
	<div id="content" role="main">
			<div class="content">


		

		<?php		
				
		while ( have_posts() ) :
			the_post();


			get_template_part( 'template-parts/content', get_post_type() );
			include get_template_directory() . '/' . 'cloud-block.php';
						
		
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;



		endwhile; // End of the loop.
			
		?>			

		</div><!-- #content -->
		<div class="sidebar">
			<?php dynamic_sidebar('sidebar-2'); ?>
	</div>
	</div><!-- .content -->
	

</div><!-- #Single Container -->
<script type="text/javascript">
    jQuery("#comments .comment-wrap:first").css("border-top", "none");
    jQuery("#comments .comment.depth-1:last").css("border-bottom", "1px solid #E0E0E0");
    if (jQuery("#comments").length) {
        jQuery("#comments").after(jQuery("<ul style='list-style: none;padding:0;'></ul>").append(jQuery("#recent-posts")));
        jQuery("#recent-posts").show();
    }
</script>
<?php get_footer();
