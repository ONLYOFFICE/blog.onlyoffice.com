<div class="subscribe-blue">
		<h3><?php _e('Newsletter', 'teamlab-blog-2-0'); ?></h3>
		<p><?php _e('Get the latest ONLYOFFICE news delivered to your inbox', 'teamlab-blog-2-0'); ?></p>
			<form id="InputBox" class="inputBox" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
				<input id="subscribe-email" type="email" class="main-input" required>
				<label>Your email*</label>
				<input type="hidden" name="action" value="contact_form">
				<input type="submit" name="inputButton" class="inputButton" value="&#62;">
					<div class="loader"></div>
				<p class="errorMessage empty">Email is empty</p>
				<p class="errorMessage incorrect">Email is incorrect</p>
				<p class="errorMessage used">Email is used</p>
			</form>
		
		
		<span><?php _e('By clicking "Subscribe", you agree to the rules for using the service and processing personal data.', 'teamlab-blog-2-0'); ?></span>
</div>