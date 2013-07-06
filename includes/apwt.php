<?php

//master apwt functions (generally used in all other functions and includes)

function APIAuth() {
	$apikey = get_option("APWTAPIKEY");
	$apiauth = get_option("APWTAPIAUTH");

	if ( ($apiauth == '') || ($apikey == '') ) {
		print "The AllProWebTools plugin has not been configured.  Please enter API Credential in wp-admin under 'AllProWebTools -> Settings'.";
		exit;
	}

	return "apwtpluginversion=".urlencode(get_option("APWT_THIS_VERSION"))."&apikey=".get_option("APWTAPIKEY")."&apiauth=".get_option("APWTAPIAUTH");
}

function APWTGetUrl() {
	$thisurl = "http://auth1.allprowebtools.com/listener/installinfo.php?".APIAuth();
	$thereturn = wp_remote_get($thisurl);

	if ($thereturn['body'] == 'API credentials not found') {
		print $thereturn['body'];
		exit;
	}

	return $thereturn['body'];
}

function init_sessions() {
  if (!session_id()) {
      session_start();
  }
}

function APWTGetPageIDs() {
	//gets all the pageids for the APWT pages
	$pagelist = APWTPageList();

	foreach ($pagelist as $var) {
	  $the_page = get_site_option( $var['Title'] );
	  if (!$the_page) {
	  	print "lost page ".$var['Title']." cannot continue.";
	  	exit;
	  }
	  $newarr[$var['Title']] = $the_page;
	 }
	 return $newarr;
}

function apwt_remote_post($url,$postvars,$secure=0) {
	if ($_SESSION['serverurl'] == '') {
		$_SESSION['serverurl'] = APWTGetUrl();
	}

	if ($_SESSION['serverurl'] == '') {
		print "unable to contact auth server";
		exit;
	}

	$debug = 0;
	$args = cookiesessionhandler();
	$args['timeout'] = 30;
	$pagelist = APWTGetPageIDs();

	foreach ($pagelist as $key => $value) {
		$postvars['pagearray'][$key] = $value;
	}

	$url .= $urlappend;
	if ($secure > 0 ) {
		$protocol = 'https:';
	} else {
		$protocol = 'http:';
	}
	$postvars['ip'] = $_SERVER["REMOTE_ADDR"];
	$args['body'] = $postvars;
	$response = wp_remote_post($protocol.$_SESSION['serverurl'].$url, $args);

	if (empty($response->errors)) {
		//no errors found

		$lastsessionname = $response['cookies'][0]->name;
		if ($lastsessionname == 'PHPSESSID') {
			$_SESSION['PHPSESSID'] = $response['cookies'][0]->value;
		}
		if ($debug > 0) {
			print "<hr>".$url."<hr>";

			print "<pre>";
			print_r ($args);
			print "</pre>";

			print "<pre>";
			print_r ($_SESSION);
			print "</pre>";
		}
		return $response['body'];
	} else {
		print "<pre>";
		print_r($response);
		print "</pre>";
		print "error in apwt_remote_post";
		exit;
	}
}

function apwt_remote_get($url) {
	if ($_SESSION['serverurl'] == '') {
		$_SESSION['serverurl'] = APWTGetUrl();
	}

	if ($_SESSION['serverurl'] == '') {
		print "unable to contact auth server";
		exit;
	}

	$debug = 0;
	$args = cookiesessionhandler();
	$args['timeout'] = 30;
	$pagelist = APWTGetPageIDs();

	foreach ($pagelist as $key => $value) {
		$urlappend .= "&pagearray[".$key."]=".$value;
	}
	$url .= $urlappend.'&siteurl='.$_SERVER['HTTP_HOST'].'&ip='.$_SERVER["REMOTE_ADDR"];
	$response = wp_remote_get("http:".$_SESSION['serverurl'].$url, $args);

	if (empty($response->errors)) {
		//no errors found

		$lastsessionname = $response['cookies'][0]->name;
		if ($lastsessionname == 'PHPSESSID') {
			$_SESSION['PHPSESSID'] = $response['cookies'][0]->value;
		}
		if ($debug > 0) {
			print "<hr>".$url."<hr>";

			print "<pre>";
			print_r ($args);
			print "</pre>";

			print "<pre>";
			print_r ($_SESSION);
			print "</pre>";
		}
		return $response['body'];
	} else {
		print "<pre>";
		print_r($response);
		print "</pre>";
		print "error in apwt_remote_get";
		exit;
	}
}

function cookiesessionhandler() {
	if ($_SESSION['PHPSESSID'] != '') {
	  $cookie = new WP_Http_Cookie( 'PHPSESSID' );
	  $cookie->name = 'PHPSESSID';
	  $cookie->value = $_SESSION['PHPSESSID'];
	  $cookie->expires = mktime( 0, 0, 0, date('m'), date('d') + 7, date('Y') ); // expires in 7 days
	  $cookie->path = '/';
	  $cookie->domain = '';

	  $cookies[] = $cookie;
		$args = array(  'cookies' => $cookies );
	}
	return $args;
}


function APWTCartActivate() {
//http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building
	$APWTThisVersion = APWT_plugin_get_version();
	update_option("APWT_THIS_VERSION", $APWTThisVersion);

	$APWTPageArray = APWTPageList();

	foreach ($APWTPageArray as $APWTVar) {
		APWTAddPage($APWTVar['Title'],$APWTVar['URL'],$APWTVar['Content'],$APWTVar['Template']);
	}
}

function APWTCartDeactivate() {
	$APWTPageArray = APWTPageList();

	foreach ($APWTPageArray as $APWTVar) {
		APWTRemovePage($APWTVar['Title']);
	}
}

function APWTRemovePage($the_page_title) {

  global $wpdb;

  $the_page = get_page_by_title( $the_page_title );
  //  the id of our page...
  $the_page_id = $the_page->ID;
  if( $the_page_id ) {
    wp_delete_post( $the_page_id ); // this will trash, not delete
  }
}

function APWTAddPage($the_page_title,$the_page_name,$content,$template='') {

    global $wpdb;

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = $content;
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }
    update_site_option( $the_page_title, $the_page_id );
    if ($template != '') {
    	//we need to apply a specific template
    	update_post_meta($the_page_id, "_wp_page_template", $template);
    }
}

function APWTPageList() {

	$APWTPageArray[0]['Title'] = 'APWTListProducts';
	$APWTPageArray[0]['Content'] = '[APWTListProducts]';
	$APWTPageArray[0]['URL'] = 'APWTListProducts';
	$APWTPageArray[0]['Template'] = '';

	$APWTPageArray[1]['Title'] = 'APWTForgotPassword';
	$APWTPageArray[1]['Content'] = '[APWTForgotPassword]';
	$APWTPageArray[1]['URL'] = 'APWTForgotPassword';
	$APWTPageArray[1]['Template'] = '';

	$APWTPageArray[2]['Title'] = 'APWTCart';
	$APWTPageArray[2]['Content'] = '[APWTShowCart]';
	$APWTPageArray[2]['URL'] = 'APWTCart';
	$APWTPageArray[2]['Template'] = '';

	$APWTPageArray[3]['Title'] = 'APWTCheckout';
	$APWTPageArray[3]['Content'] = '[APWTCheckout]';
	$APWTPageArray[3]['URL'] = 'APWTCheckout';
	$APWTPageArray[3]['Template'] = 'page-templates/full-width.php';

	$APWTPageArray[4]['Title'] = 'APWTLogout';
	$APWTPageArray[4]['Content'] = '[APWTLogout]';
	$APWTPageArray[4]['URL'] = 'APWTLogout';
	$APWTPageArray[4]['Template'] = '';

	$APWTPageArray[5]['Title'] = 'APWTShowProduct';
	$APWTPageArray[5]['Content'] = '[APWTShowProduct]';
	$APWTPageArray[5]['URL'] = 'APWTShowProduct';
	$APWTPageArray[5]['Template'] = '';

	$APWTPageArray[6]['Title'] = 'APWTAddReview';
	$APWTPageArray[6]['Content'] = '[APWTAddReview]';
	$APWTPageArray[6]['URL'] = 'APWTAddReview';
	$APWTPageArray[6]['Template'] = '';

	$APWTPageArray[7]['Title'] = 'APWTThankYou';
	$APWTPageArray[7]['Content'] = '[APWTThankYou]';
	$APWTPageArray[7]['URL'] = 'APWTThankYou';
	$APWTPageArray[7]['Template'] = '';

	return $APWTPageArray;
}

function APWTHitTracker() {
	$apikey = get_option("APWTSITEID");	//someday we can define this in the WP settings
	if ($apikey == '') {
		$apikey = 'wp1';
	}

	@$thisurl = '/wordpress/wpapi.php?action=iptrackhit&ip='.$_SERVER["REMOTE_ADDR"].'&siteid='.$apikey.'&aff='.$_GET['aff'].'&refcode='.$_GET['ref'].'&'.APIAuth();
	$thereturn = apwt_remote_get($thisurl);
}

////////////////////////////// Begin Classes /////////////////////////////////

class APWTLeadBox extends WP_Widget {
//http://azuliadesigns.com/create-wordpress-widgets/
  function APWTLeadBox() {
    $widget_ops = array('classname' => 'APWTLeadBox', 'description' => 'Displays an APWT LeadBox Widget' );
    $this->WP_Widget('APWTLeadBox', 'APWT LeadBox Widget', $widget_ops);
  }

  function form($instance) {
  //get a title variable defined by the user
    $instance = wp_parse_args( (array) $instance, array( 'APWTLeadBoxID' => '' ) );
    $APWTLeadBoxID = $instance['APWTLeadBoxID'];
    //get the ids for the lead box
		$thisurl = "/wordpress/wpapi.php?action=leadboxids&".APIAuth();
		$thereturn = apwt_remote_get($thisurl);

		$leadboxes = explode("-|-",$thereturn);
		array_pop($leadboxes);

		foreach ($leadboxes as $val) {
			$pieces = explode("-*-",$val);
			$newarray[$pieces[0]] = $pieces[1];
		}
    $APWTLeadBoxID = empty($instance['APWTLeadBoxID']) ? ' ' : apply_filters('widget_title', $instance['APWTLeadBoxID']);

?>
  <p><label for="<?php echo $this->get_field_id('APWTLeadBoxID'); ?>">Lead Box:
<?php
		//create select box
		print '<select id="'.$this->get_field_id('APWTLeadBoxID').'" name="'.$this->get_field_name('APWTLeadBoxID').'">';
		foreach ($newarray as $lbid => $lbname) {
			if ($APWTLeadBoxID == $lbid) {
				$selected = "SELECTED";
			} else {
				$selected = "";
			}
			print '<option value="'.$lbid.'" '.$selected.'>'.$lbname.'</option>';
		}
		print '</select>';
?>
	</label></p>
<?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['APWTLeadBoxID'] = $new_instance['APWTLeadBoxID'];
    return $instance;
  }

  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
		if (isset($_POST['APWTLeadBox'])) {
    $APWTLeadBoxID = empty($instance['APWTLeadBoxID']) ? ' ' : apply_filters('widget_title', $instance['APWTLeadBoxID']);
			$thisurl = "/wordpress/wpapi.php?action=getleadbox&leadboxid=".$APWTLeadBoxID."&name=".urlencode($_POST['APWTName'])."&email=".urlencode($_POST['APWTEmail'])."&".APIAuth();
			$thereturn = apwt_remote_get($thisurl);
			echo $thereturn;

		} else {
    $APWTLeadBoxID = empty($instance['APWTLeadBoxID']) ? ' ' : apply_filters('widget_title', $instance['APWTLeadBoxID']);

    if ($APWTLeadBoxID > 0) {
			$thisurl = "/wordpress/wpapi.php?action=getleadbox&leadboxid=".$APWTLeadBoxID."&".APIAuth();
			$thereturn = apwt_remote_get($thisurl);

      echo $before_title . $thereturn . $after_title;
		} else {
	    echo "<h1>No Leadbox defined</h1>";
	  }
	}
    echo $after_widget;
  }
} //end class extend APWTLeadBox

class APWTCartWidget extends WP_Widget {
//http://azuliadesigns.com/create-wordpress-widgets/
  function APWTCartWidget()
  {
    $widget_ops = array('classname' => 'APWTCartWidget', 'description' => 'Displays the APWT Cart Widget' );
    $this->WP_Widget('APWTCartWidget', 'APWT Cart Widget', $widget_ops);
  }

  function form($instance) {
  //get a title variable defined by the user
    $instance = wp_parse_args( (array) $instance, array( 'APWTCartWidget' => '' ) );
    $APWTCartWidget = $instance['APWTCartWidget'];
?>
  <p><label for="<?php echo $this->get_field_id('APWTCartWidget'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('APWTCartWidget'); ?>" name="<?php echo $this->get_field_name('APWTCartWidget'); ?>" type="text" value="<?php echo attribute_escape($APWTCartWidget); ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['APWTCartWidget'] = $new_instance['APWTCartWidget'];
    return $instance;
  }

  function widget($args, $instance) {
    extract($args, EXTR_SKIP);

    $APWTCartWidget = empty($instance['APWTCartWidget']) ? ' ' : apply_filters('widget_title', $instance['APWTCartWidget']);
    	print '<div class="cartwidget">';
				print $APWTCartWidget."<br>";
				$thisurl = "/wordpress/wpapi.php?action=cartwidget&".APIAuth();
				$thereturn = apwt_remote_get($thisurl);
				print $thereturn;
			print '</div>';

  }
} //end class extend APWTCartWidget
?>