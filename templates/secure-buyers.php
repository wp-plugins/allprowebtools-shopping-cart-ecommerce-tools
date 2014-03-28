<?php
	//christine hacking
		require_once(APWT_PLUGIN_PATH.'includes/apwt-cart.php');
?>
<!-- begin main content -->
<section id="main">
	<!-- begin main product area -->
			<form action="./?page_id=<?php print $_GET['page_id']; ?>" method="post" name="buyer" class="loginbox">
			<h2>Sign In</h2>
<?
			if ($error_message != ''){
				print '<div class="error_message">'.$error_message.'</div>';
			}
?>
			<h3>What is your e-mail address?</h3>
				<p>My e-mail address is: <input type="email" name="username" value="<? print $_POST['username']; ?>" class="loginfield loginemail" tabindex="1"></p>
			<h3>Do you have a <?php APWTCompanyName(); ?> password?</h3>
			<p>
				<label for="no"><input type="radio" name="create" id="no" value="1" onclick="enableDisableTxt(true);"> No, I am a new customer.</label><br><br>
				<label for="yes"><input type="radio" name="create" id="yes" value="0" checked="checked" onclick="enableDisableTxt(false);">
				Yes, I have a password:
				<input type="password" name="password" id="password" value="<? print $_POST['password']; ?>" class="loginfield loginpass" tabindex="2"></label>
				<br><br>
			</p>
			<input type="submit" name="B1" value="Continue" class="loginbutton" />
			<a href="<?php print get_site_option( 'APWTForgotPassword'); ?>">Forgot Your Password?</a>

		</form>
</section>
<!-- end main content -->
