<div class="subscribe-block">
		<h3>Newsletter</h3>
		<p>Get the latest ONLYOFFICE news delivered to your inbox</p>
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
		
		
		<span>By clicking "Subscribe", you agree to the rules for using the service and processing personal data.</span>
</div>