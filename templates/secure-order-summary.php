<!-- begin main content -->
<section id="main-one-column">
	<h2>Shipping & Payment Information</h2>
	<?
			if ($data['error'] != '') {
				print "<div class='error_message'>".$data['error']."</div><br />";
			}
	?>
	<div id="summleft">
		<form action="<? print $_SERVER['PHP_SELF']; ?>" method="POST">
<?php
			$thereturn = FetchShippingOptions();
			$shippingnum = ShowShippingOptions($thereturn['option'], $thereturn['message'], $thereturn['showshipping'], $buyer_id);
			ShowAvailablePaymentMethods($payreturn,$buyer_id,$cart_id,$shippingnum);
			DisplayCustomContent('secure-add-shipping.plg');
?>
	</div>
	<div id="summright">
			<?php ShowOrder($cart_id,$buyer_id); ?>
			<?php PaypalOptions($cart_id); ?>
	</div>
	<br class="clearfix">
	<span class="cartnum">Shopping Cart #<? print $_SESSION['cartid']; ?></span>
</section>
<!-- end main content -->