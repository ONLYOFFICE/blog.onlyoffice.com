<div class="subscribe-blue">
	<h2><?php _e('Newsletter', 'teamlab-blog-2-0'); ?></h2>

	<div class="subscribe-wrapper">
		<?php if ( is_home() ) { ?>
		<div id="InputBox" class="inputBox">
			<input id="subscribe-email-input" class="main-input" placeholder="<?php _e('Your email*', 'teamlab-blog-2-0') ?>" />
			<div id="email-sub-button" class="inputButton">
				<?php _e('Subscribe', 'teamlab-blog-2-0') ?>
				<div class="loader"></div>
			</div>

			<?php } else { ?>

			<div id="InputBox" class="inputBox forPressPage">
				<input id="subscribe-email-input" class="main-input" placeholder="<?php _e('Your email*', 'teamlab-blog-2-0') ?>" />

				<div id="email-sub-button" class="inputButton pressPage"><?php _e('Subscribe', 'teamlab-blog-2-0') ?>
					<div class="loader"></div>
				</div>

				<?php  } ?>
				<p class="errorMessage empty"><?php _e('Email is empty', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage incorrect"><?php _e('Email is incorrect', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage used"><?php _e('Email is used', 'teamlab-blog-2-0') ?></p>
				<p class="errorMessage recaptcha"><?php _e('Incorrect recaptcha', 'teamlab-blog-2-0') ?></p>
			</div>
			<div class="recaptchaContainer">
				<div class="gRecaptcha" id="popupCaptcha"></div>
			</div>

			<span><a
					href="https://help.onlyoffice.com/products/files/doceditor.aspx?fileid=5048502&doc=SXhWMEVzSEYxNlVVaXJJeUVtS0kyYk14YWdXTEFUQmRWL250NllHNUFGbz0_IjUwNDg1MDIi0"
					target="_blank"><?php _e('By clicking "Subscribe", you agree to the <u>rules for using the service and processing personal data.</u>', 'teamlab-blog-2-0'); ?></a></span>
		</div>
	</div>

	<div class="subscribe-white">
		<h4><?php _e('Confirm your subscription', 'teamlab-blog-2-0'); ?></h4>
		<p><?php _e('We sent an email message with confirmation to your email address', 'teamlab-blog-2-0'); ?></p>
	</div>