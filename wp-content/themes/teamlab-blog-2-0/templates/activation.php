<?php

/**
 * Template Name: activation
 *
 
 */

get_header(); ?>
<?php 

if(!empty($_GET['code']) && isset($_GET['code']) && !empty($_GET['email']) && isset($_GET['email']) && !empty($_GET['date']) && isset($_GET['date']))
{

	$secureCode = $_GET['code'];
	$email = $_GET['email'];
	$date = date('Y-m-d H:i:s', $_GET['date']);

	if(!empty($date) && isset($date)){
		renderResult("$email", "$secureCode", "$date");
	}
} else { 
	echo("Go it");
	//wp_redirect("/");
}?>

<?php function renderResult($email, $secureCode, $date) {
        global $wpdb;
        $salt = wp_salt();     
        $checkCode = $secureCode == sha1($salt . $date . $email);
        $count = $wpdb->get_var("SELECT email FROM users WHERE email='$email'");

        if(!empty($count) && isset($count)){ ?>


		<div class="MailContainer">

			<div class="content-mail">
				<div class="cta">
					<h2><?php _e('You are already subscribed. Please check your spam folder', 'teamlab-blog-2-0') ?></h2>
					<p>
						<?php _e('Enjoy reading ONLYOFFICE blog!', 'teamlab-blog-2-0') ?>
					</p>
					<a class="go-home" href="<?php echo site_url() ?><?php _e('/') ?>"><?php _e('Explore blog') ?></a>
				</div>
			</div><!-- #content -->
			<div class="letter-box"></div>


		</div><!-- #Single Container -->


        <?php }
        elseif ($checkCode && addEmail($email, $date)) { ?>

            <?php 
            
                $headers = array('Content-Type: text/html; charset=UTF-8');
                include get_template_directory() . '/' . 'second-mail.php';

                $HTMPMessage = getSecondEmail();
                wp_mail($email, 'Welcome to ONLYOFFICE email list', $HTMPMessage, $headers);
            ?>

			<div class="MailContainer">

				<div class="content-mail">
					<div class="cta">
						<h2><em class="thank-you"><?php _e('Thank you </em>for signing up for ONLYOFFICE newsletter!', 'teamlab-blog-2-0') ?></h2>
						<p>
						<?php _e('Check your inbox for your first email and stay updated from now on!', 'teamlab-blog-2-0') ?>
						</p>
						<a class="go-home" href="<?php echo site_url() ?><?php _e('/') ?>"><?php _e('Explore blog', 'teamlab-blog-2-0') ?></a>	
					</div>
				</div><!-- #content -->
				<div class="letter-box"></div>


			</div><!-- Mail Container -->

        <?php } else {
            wp_redirect(site_url()); 
        }
	};
	
	function addEmail($email, $date){
        global $wpdb;
        $wpdb->insert("users",  array("email" => "$email", "date" => "$date"));
        return true;
	}
	
	?>


<?php get_footer();
