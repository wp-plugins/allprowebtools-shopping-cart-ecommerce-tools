<script>
	function toggle() {
		theval = document.getElementById('gift').checked;
			if(theval == true) {
				document.getElementById('giftmessage').style.visibility = 'visible';
				document.getElementById('giftmsg').value='Gift Message';
		  } else {
				document.getElementById('giftmessage').style.visibility = 'hidden';
			}
	}
</script>
<!-- begin main content -->
<section id="main-left-content">
		<!-- begin shopping cart area -->
		<br class="clearfix" >
		<form method="post" action="./?page_id=<?php print get_site_option( 'APWTCart'); ?>" name="myform" id="myform">
			<a href="./?page_id=<?php print get_site_option( 'APWTListProducts'); ?>" class="fakebutton left">Continue Shopping</a>
			<input class="emptycartbutton left" type="submit" value="Empty My Cart" name="B1" />
			<input class="checkoutbutton right" type="submit" name="B1" value="Checkout" />
			<br class="clearfix" >
			<? print APWTShowCartNow(); ?>
			<br class="clearfix" >
			<a href="./?page_id=<?php print get_site_option( 'APWTListProducts'); ?>" class="fakebutton left">Continue Shopping</a>
			<div class="right">
				<input type="checkbox" name="gift" id="gift" value="ON" onclick="toggle();"><b> This order is a gift.</b>
				<input class="checkoutbutton" type="submit" name="B1" value="Checkout" />
			</div>
			<div id="giftmessage" style="clear:both; float:right; visibility:hidden;">
			<br>
			<textarea id="giftmsg" rows="3" name="giftmsg" cols="30" maxlength="255" onclick="document.getElementById('giftmsg').value='';">Gift Message</textarea>
			</div>
		</form>
		<!-- end shopping cart area -->
		<br class="clearfix" >

</section>
<!-- end main content -->

