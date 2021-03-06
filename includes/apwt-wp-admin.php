<?php

//all wp-admin related functions
function APWT_admin_menu() {
	if ( (!get_option("APWTAPIKEY")) || (!get_option("APWTAPIAUTH")) ) {
		add_menu_page("AllProWebTools","AllProWebTools",'activate_plugins',"AllProWebTools3","APWTSettings",plugins_url( 'wp-icon.png', __FILE__ ));
	} else {
		add_menu_page("AllProWebTools","AllProWebTools",'activate_plugins',"AllProWebTools3","APWTConsole",plugins_url( 'wp-icon.png', __FILE__ ));
		add_submenu_page('AllProWebTools3','Settings','Settings','activate_plugins','APWTSettings','APWTSettings');
	}
}

function APWTConsole() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$thisurl = "http://auth1.allprowebtools.com/logmein.php?".APIAuth();
	$thereturn = wp_remote_get($thisurl);
	$consolelocation = $thereturn['body'];

	if (get_option("APWTAPIKEY") == '00myallprowebtoolsdemo255') {
		print '<a href="'.$consolelocation.'" target="console"><img src="'.plugins_url( 'demo-screen.png', __FILE__ ).'" alt="Demo now"></a>';
	} else {
		print '<a href="'.$consolelocation.'" target="console"><img src="'.plugins_url( 'login-screen.png', __FILE__ ).'" alt="Login now"></a>';
	}
}

function APWTSettings() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if ($_POST['B1'] != '') {
	  if((strlen($_POST['apikey']) != 25) && ($_POST['apikey'] != '')) {
	    $message = '<div id="message" class="error">Invalid API Key</div>';
	    $count = 0;
		} else {
			//they have entered their api key - now save it
			$_SESSION['serverurl'] = '';
			if (!add_option( 'APWTAPIKEY', $_POST['apikey'])) {
				update_option( 'APWTAPIKEY', $_POST['apikey'] );
				if($_POST['apikey'] == '') {
			  	$count = 0;
			  } else {
			 		$count = 1;
			 	}
			}
		}
	  if((strlen($_POST['apiauth']) != 16) && ($_POST['apiauth'] != '')) {
	    $message = '<div id="message" class="error">Invalid API Auth</div>';
	    $count2 = 0;
	  } else {
			//they have entered their api key - now save it
			if (!add_option( 'APWTAPIAUTH', $_POST['apiauth'])) {
				update_option( 'APWTAPIAUTH', $_POST['apiauth'] );
				if($_POST['apiauth'] == '') {
					$count2 = 0;
				} else {
					$count2 = 1;
				}
			}
		}
	  $count3 = $count + $count2;
	  if($count3 == 2) {
			$message = '<div id="message" class="updated fade">Congratulations You Are Ready To Go!</div>';
		}
	}
	print $message;
?>
<script type="text/javascript">
	jQuery(function() {
		if(jQuery("#apikey").val()=='') {
			jQuery("#settings").removeClass('button button-primary');
			jQuery("#settings").addClass('button');
			jQuery("#register").removeClass('button');
			jQuery("#register").addClass('button button-primary');
		} else {
		  jQuery("#settings").removeClass('button');
			jQuery("#settings").addClass('button  button-primary');
			jQuery("#register").removeClass('button  button-primary');
			jQuery("#register").addClass('button');
		}

		if(jQuery("#apiauth").val()=='') {
		  jQuery("#settings").removeClass('button button-primary');
			jQuery("#settings").addClass('button');
			jQuery("#register").removeClass('button');
			jQuery("#register").addClass('button button-primary');
		} else {
		  jQuery("#settings").removeClass('button');
			jQuery("#settings").addClass('button  button-primary');
			jQuery("#register").removeClass('button  button-primary');
			jQuery("#register").addClass('button');
		}

		jQuery("#apikey").change(function() {
			if(jQuery("#apikey").val()=='') {
				jQuery("#settings").removeClass('button button-primary');
				jQuery("#settings").addClass('button');
				jQuery("#register").removeClass('button');
				jQuery("#register").addClass('button button-primary');
			}else {
				jQuery("#settings").removeClass('button');
				jQuery("#settings").addClass('button button-primary');
				jQuery("#register").removeClass('button button-primary');
				jQuery("#register").addClass('button');
			}
		});
	});
</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>AllProWebTools API Settings</h2>
	<form method="POST" action="">
		<table class="form-table">
		<tbody>
			<tr>
		  	<th><label for="apikey">API Key</label></th>
		  	<td><input type="text" name="apikey" id="apikey" class="regular-text" value="<?php print get_option("APWTAPIKEY"); ?>"></td>
		  </tr>
			<tr>
		  	<th><label for="apiauth">API Auth</label></th>
		  	<td><input type="text" name="apiauth" id="apiauth" class="regular-text" value="<?php print get_option("APWTAPIAUTH"); ?>"></td>
		  </tr>
		 </tbody>
		 </table>
		<p><input type="submit" value="Update Settings" id="settings" name="B1" class="button"></p>
	</form>
	<p>If you don't already have an AllProWebTools account, you can <a target="register" href="http://lib1.allprowebtools.com/registration/welcome.php">sign up for one here.</a></p>
</div>
<?php
  if($count3 == 2) {
		print '<br><br><img src="'.plugins_url( 'getstartedarrow.jpg', __FILE__ ).'" alt="Click AllProWebTools to get started!">';
	}
}
?>