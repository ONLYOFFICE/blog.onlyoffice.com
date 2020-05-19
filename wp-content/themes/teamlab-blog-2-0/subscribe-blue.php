<div class="subscribe-blue">
		<h3><?php _e('Newsletter', 'teamlab-blog-2-0'); ?></h3>
		<p><?php _e('Get the latest ONLYOFFICE news delivered to your inbox', 'teamlab-blog-2-0'); ?></p>

			<div id="InputBox" class="inputBox">
				<input id="subscribe-email-input" class="main-input" />
				<label><?php _e('Your email*', 'teamlab-blog-2-0'); ?></label>
				<div id="email-sub-button" class="inputButton">
					<div class="loader"></div>
				</div>
				<p class="errorMessage empty"><?php _e('Email is empty', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage incorrect"><?php _e('Email is incorrect', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage used"><?php _e('Email is used', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage recaptcha"><?php _e('Incorrect recaptcha', 'teamlab-blog-2-0') ?></p>
			</div>
			<div class="recaptchaContainer">
				<div class="gRecaptcha" id="popupCaptcha"></div> 
			</div>
		
		<span><?php _e('By clicking "Subscribe", you agree to the rules for using the service and processing personal data.', 'teamlab-blog-2-0'); ?></span>
</div> 

<div class="subscribe-white">
		<h4><?php _e('Confirm your subscription', 'teamlab-blog-2-0'); ?></h4>
		<p><?php _e('We sent an email message with confirmation to your email address', 'teamlab-blog-2-0'); ?></p>
</div>