<?php

//all cart related functions

function APWTLogout( $atts ) {
	$thisurl = "/wordpress/wpapi.php?action=logmeout&".APIAuth();
	$thereturn = apwt_remote_get($thisurl);
	$_SESSION['cartid'] = '';
	$_SESSION['PHPSESSID'] = '';
	$_SESSION['specialoffer_seen'] = '';
	return $thereturn;
}

function APWTListProducts( $atts ) {
	$thisurl = "/wordpress/wpapi.php?action=listproducts&".APIAuth();
	$thereturn = apwt_remote_get($thisurl);

	return $thereturn;
}

function APWTShowProduct() {
	if ($_REQUEST['wp'] == 'redirect') {
		//need to create get string for all the variations
		foreach ($_GET as $key => $value) {
			//$keycheck = substr($key,0,-1);
			if ($key > 0) {
				//this is a variation add it to the get string
				//$getstring .= urlencode($key)."=".urlencode($value)."&";
				$getstring .= $key."=".$value."&";
			}
		}

		$thisurl = "/wordpress/wpapi.php?action=findproduct&prodid=".$_REQUEST['prodid']."&".$getstring.APIAuth();
		$thereturn = apwt_remote_get($thisurl);
		//this returns the url string to redirect to
		echo "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option('APWTShowProduct')."&".$thereturn."' />";
		exit;
		//return $thereturn."rr";
	} else {
		foreach ($_REQUEST as $key => $value) {
			$keycheck = substr($key,0,-1);
			if ($keycheck > 0) {
				//this is a variation add it to the get string
				//$getstring .= urlencode($key)."=".urlencode($value)."&";
				$getstring .= $keycheck."=".$value."&";
			}
			if ($_GET['available'] == '0') {
				$getstring .= "available=0&";
			}
			if ($_GET['coupon'] == 'getacoupon') {
				$getstring .= "coupon=getacoupon&";
			}
		}
		$thisurl = "/wordpress/wpapi.php?action=showproduct&prodid=".$_REQUEST['prodid'].'&'.$getstring.APIAuth();
		$thereturn = apwt_remote_get($thisurl);

		$thereturn .= '<div id="dialog-form" title="Get A Coupon">';
		$thereturn .= APWTAjaxGetCouponContent($_REQUEST);
		$thereturn .= '</div>';

		return $thereturn;
	}

}

function APWTGetVars() {	//not used
	$thisurl = "/wordpress/wpapi.php?action=getvars&".APIAuth();
	$thereturn = apwt_remote_get($thisurl);

	return $thereturn;
}

function APWTShowCart() {
	if ($_POST['B1'] == 'Checkout') {
		//needs to redirect to secure special offer if there is one.
		$thisurl = '/wordpress/wpapi.php?action=specialoffer&'.APIAuth();
		$thereturn = apwt_remote_get($thisurl);
		if (($thereturn == 'there is an offer') && ($_SESSION['specialoffer_seen'] != 'true'))  {
			//display the plug
			$_SESSION['specialoffer_seen'] = 'true';
			$thisurl = '/wordpress/wpapi.php?action=displayspecialoffer&'.APIAuth();
			$thereturn = apwt_remote_get($thisurl);
			print $thereturn;
			exit;
		} else {
			if (!empty($_POST['quantity'])) {
					//needs to update qty
				echo "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTCheckout')."&cmd=checkout' />";
				exit;
			} else {
				//redirect to listproducts
				echo "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTListProducts')."' />";
				exit;
			}
		}
	}

	if ($_POST['B1'] == 'Discount ME!') {
		APWTShowCartNow();
	}

	require_once(APWT_PLUGIN_PATH.'templates/shopping-cart.php');

}

function APWTCheckout() {
	if (($_POST['username'] != '') && ($_POST['create'] == '0')) {
		//they have submitted credentials
		//send credentials to api for validation
		$url = "/wordpress/wpapi.php?action=logmein&username=".$_POST['username']."&password=".$_POST['password']."&create=".$_POST['create']."&".APIAuth()."&wp=1";
		$thereturn = apwt_remote_get($url);

		if ($thereturn == 'apwtcart') {
			print "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTCart')."' />";
			exit;
		} else {
			print $thereturn;
		}

	} else if ($_POST['create'] == '1') {
		//we are creating a new buyer
		//need to get rid of all spaces in the get
		foreach ($_POST as $keyu => $itemu) {
			$_POST[$keyu] = urlencode($itemu);
		}
		$url = "/wordpress/wpapi.php?action=createbuyer&username=".$_POST['username']."&create=".$_POST['create']."&D1=".$_POST['D1']."&firstname=".urlencode($_POST['first_name'])."&lastname=".urlencode($_POST['last_name'])."&phone=".$_POST['phone']."&email=".$_POST['email']."&password=".$_POST['password']."&password2=".$_POST['password2']."&company=".urlencode($_POST['company'])."&address1=".urlencode($_POST['address1'])."&address2=".urlencode($_POST['address2'])."&city=".urlencode($_POST['city'])."&state=".$_POST['state']."&state2=".$_POST['state2']."&zip=".$_POST['zip']."&country=".urlencode($_POST['country'])."&promos=".$_POST['promos']."&".APIAuth()."&wp=1";
		$thereturn = apwt_remote_get($url);
			if ($thereturn == 'apwtlistproducts') {
				print "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTCart')."' />";
				exit;
			} else {
				print $thereturn;
			}
		exit;
	}
	//are we logged in?
	$url = "/wordpress/wpapi.php?action=loginstatus&".APIAuth()."&wp=1";
	$loggedinstatus = apwt_remote_get($url);

	if ($loggedinstatus == "true") {
		if ($_POST['chargeme'] == 'Place Order') {
		$thisurl = '/wordpress/wpapi.php';
		$postvars['action'] = 'ordersummary';
		$postvars['chargeme'] = 'yes';
		$postvars['shipmethod'] = urlencode($_POST['ship_method']);
		$postvars['cc'] = urlencode($_POST['card_num']);
		$postvars['expmonth'] = urlencode($_POST['exp_month']);
		$postvars['expyear'] = urlencode($_POST['exp_year']);
		$postvars['cvc'] = urlencode($_POST['cvc']);
		$postvars['cczip'] = urlencode($_POST['cczip']);
		$postvars['cim'] = urlencode($_POST['cim']);
		$postvars['R1'] = urlencode($_POST['R1']);
		$postvars['wp'] = 1;
		$postvars['apikey'] = get_option("APWTAPIKEY");
		$postvars['apiauth'] = get_option("APWTAPIAUTH");
		$postvars['apwtpluginversion'] = get_option("APWT_THIS_VERSION");
		$thereturn = apwt_remote_post($thisurl,$postvars,1);

		if (intval($thereturn) > 0 ) {
			//go to thanks
			print "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTThankYou')."&orderid=".$thereturn."' />";
			exit;
		} else {
			print $thereturn;
			exit;
		}

	} else {
		if ( ($_POST['B1'] == 'Checkout') || ($_GET['cmd'] == 'checkout') ) {
			//yes - send to order-summary page
			$url = "/wordpress/wpapi.php?action=ordersummary&".APIAuth()."&wp=1";
			$thereturn = apwt_remote_get($url);
			print $thereturn;
			exit;
		} else {
			print "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option( 'APWTCart')."' />";
			exit;
		}
	}
}

	//if not validated - show error msg and ask for re-entry
	require_once(APWT_PLUGIN_PATH.'templates/secure-buyers.php');
}

function APWTThankYou() {
	if ($_REQUEST['giftcertificate'] == 'Submit') {
		if ($_REQUEST['error'] == '') {
			//send the info to be attached to a gift certificate
			$thisurl = '/wordpress/wpapi.php';
			$postvars['action'] = 'thanks';
			$postvars['giftcertificate'] = 'yes';
			$postvars['orderid'] = $_POST['orderid'];
			$postvars['amount'] = $_POST['amount'];
			$postvars['prodid'] = $_POST['prodid'];
			$postvars['exp'] = $_POST['exp'];
			$postvars['giftname'] = $_POST['giftname'];
			$postvars['email'] = $_POST['email'];
			$postvars['method'] = $_POST['method'];
			$postvars['date'] = $_POST['date'];
			$postvars['from'] = $_POST['from'];
			$postvars['message'] = $_POST['message'];
			$postvars['wp'] = 1;
			$postvars['apikey'] = get_option("APWTAPIKEY");
			$postvars['apiauth'] = get_option("APWTAPIAUTH");
			$postvars['apwtpluginversion'] = get_option("APWT_THIS_VERSION");
			$thereturn = apwt_remote_post($thisurl,$postvars,1);
			print $thereturn;
		}
	}

	if (($_REQUEST['orderid'] > 0) && ($_REQUEST['giftcertificate'] != 'Submit')) {
			//have encoded key in return
			$thisurl = '/wordpress/wpapi.php?action=thanks&orderid='.$_REQUEST['orderid'].'&'.APIAuth().'&wp=1';
			$thereturn = apwt_remote_get($thisurl);
			print $thereturn;
			//return $thereturn;
			exit;
	} else {
		//print "your invoice id was lost.";
	}
}


function APWTShowCartNow() {
	$sendargs['qty'] = $_GET['qty'];
	$sendargs['itemid'] = $_GET['itemid'];
	$sendargs['coupon'] = $_GET['coupon'];
	$sendargs['customerid'] = session_id();

	if ($_POST['B1'] == 'Empty My Cart') {
		//needs to update qty

		$url = "/wordpress/wpapi.php?action=updateqty&".APIAuth()."&wp=1";
		$thereturn = apwt_remote_get($url);

//		$cartreturn = explode("#",$thereturn);

//		$_SESSION['cartid'] = $cartreturn[1];
		if ($thereturn == 'put in show cart here') {
			print "<meta http-equiv='refresh' content='0;url=?page_id=".get_site_option('APWTCart')."&order_id=".$thereturn."' />";
			exit;
		}
		return $thereturn;
		exit;
	}

	if (count($_POST['quantity']) > 0) {
//this needs to loop thru all quantities and fill the array
		$arrresult = array_keys($_POST['quantity']);

		foreach ($_POST['quantity'] as $key => $value) {
		    $arrstring .= "&quantity[".$key."]=".$value;
		}
	}

	if ( ($arrstring != '') || ($_POST['coupon'] != '') ) {
		$arrstring .= "&coupon=".$_POST['coupon'];

		$thekey = $arrresult[0];
		$thevalue = $_POST['quantity'][$thekey];

		$url = "/wordpress/wpapi.php?action=updateqty&".APIAuth()."&wp=1".$arrstring;

		$thereturn = apwt_remote_get($url);
		$cartreturn = explode("#",$thereturn);

		$_SESSION['cartid'] = $cartreturn[1];
		return $thereturn;
	}

	if ($_GET['buynow'] == 'Add to Cart') {
		//check for quantity

		$url = "/wordpress/wpapi.php?action=addtocart&".APIAuth()."&wp=1&cartid=&itemid=".$sendargs['itemid']."&qty=".$sendargs['qty']."&customerid=".$sendargs['customerid']."&coupon=".$sendargs['coupon'];
		$thereturn = apwt_remote_get($url);

		$cartreturn = explode("#",$thereturn);

		$_SESSION['cartid'] = $cartreturn[1];
		return $thereturn;
	} else {
		$url = "/wordpress/wpapi.php?action=showcart&".APIAuth()."&wp=1";
		$thereturn = apwt_remote_get($url);

		$cartreturn = explode("#",$thereturn);

		$_SESSION['cartid'] = $cartreturn[1];
		return $thereturn;
	}
}

function APWTForgotPassword() {
	$thisurl = "/wordpress/wpapi.php?action=forgotpassword&".APIAuth()."&wp=1&email=".$_REQUEST['email']."&forgot=".urlencode($_REQUEST['forgot']);
	$thereturn = apwt_remote_get($thisurl);

	return $thereturn;
}

function APWTAddReview() {
	$thisurl = "/wordpress/wpapi.php?action=addreview&".APIAuth()."&wp=1&prodid=".$_REQUEST['prodid']."&email=".$_REQUEST['email']."&review=".urlencode($_REQUEST['review'])."&review_name=".urlencode($_REQUEST['review_name'])."&B1=".$_REQUEST['B1'];
	$thereturn = apwt_remote_get($thisurl);

	print $thereturn;
}

function APWTCompanyName() {
	$thisurl = "/wordpress/wpapi.php?action=companyname&".APIAuth()."&wp=1";
	$thereturn = apwt_remote_get($thisurl);

	print $thereturn;
}

function APWTGetCoupon($_POST) {
	$thisurl = "/wordpress/wpapi.php?action=coupon&name=".urlencode($_POST['name'])."&email=".urlencode($_POST['email'])."&prodid=".urlencode($_POST['prodid'])."&".APIAuth()."&wp=1";
	$thereturn = apwt_remote_get($thisurl);

	return $thereturn;
}

function APWTUpdateBilling($_POST) {
	$thisurl = '/wordpress/wpapi.php';
	$postvars['action'] = 'updatebilling';
	$postvars['editcard'] = urlencode($_POST['editcard']);
	$postvars['card_number_m'] = urlencode($_POST['card_number_m']);
	$postvars['exp_month'] = urlencode($_POST['exp_month']);
	$postvars['exp_year'] = urlencode($_POST['exp_year']);
	$postvars['cvc'] = urlencode($_POST['cvc']);
	$postvars['apikey'] = get_option("APWTAPIKEY");
	$postvars['apiauth'] = get_option("APWTAPIAUTH");
	$postvars['apwtpluginversion'] = get_option("APWT_THIS_VERSION");
	$thereturn = apwt_remote_post($thisurl,$postvars,1);

	return $thereturn;
}


// ajax functions

function APWTAjaxGetCoupon() {
    if ( !wp_verify_nonce( $_POST['nonce'], "apwt_coupon_nonce")) {
        exit("Wrong nonce");
    }

   	$thisurl = "/wordpress/wpapi.php?action=coupon&prodid=".urlencode($_POST['prodid']).'&email='.urlencode($_POST['email']).'&name'.urlencode($_POST['name']).'&'.APIAuth();
		$thereturn = apwt_remote_get($thisurl);
		$results = $thereturn;
    die($results);
}



function APWTAjaxGetCouponContent($_REQUEST){

    $results ='';
    $nonce = wp_create_nonce("apwt_coupon_nonce");

		$result = 'Sign Up to Get GREAT Discounts on 256 Products!<br>Enter your email address and we will send you a coupon for Right Away!<br><br>';
    $result.=  '<div id="loadpostresult"></div>';
    $result .= 'Your Name: <input type="text" name="name" id="name"><br>';
    $result .= 'Email Address : <input type="text" name="email" id="email"><br>';
    $result .= '<input type="hidden" name="prodid" id="prodid" value="'.$_REQUEST['prodid'].'">';

		//form fields to send to ajax
 		$arguments =  "'name','email','prodid','".$nonce."'";

    $result .= ' <input type="submit" name="coupon" value="Get A Coupon" onclick="APWTCouponAjax('.$arguments.');">';
    $result .= '<br><br>I agree to receive additonal coupons, offers and discounts at my email address.  I can cancel anytime by clicking the cancel link at the bottom of any email from 256.';


    return $result;
}

?>