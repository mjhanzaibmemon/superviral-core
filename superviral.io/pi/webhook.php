<?php

require_once 'shared.php';

$event = null;

try {
	// Make sure the event is coming from Stripe by checking the signature header
	$event = \Stripe\Webhook::constructEvent($input, $_SERVER['HTTP_STRIPE_SIGNATURE'], 'whsec_QMfyXZdkFED4Fd0ecY22WBsKavQy0Vly');
}
catch (Exception $e) {
	http_response_code(403);
	echo json_encode([ 'error' => $e->getMessage() ]);
	exit;
}

$details = '';

if ($event->type == 'payment_intent.succeeded') {
	// Fulfill any orders, e-mail receipts, etc
	// To cancel the payment you will need to issue a Refund (https://stripe.com/docs/api/refunds)
	$details = 'Payment received!';

$input = json_decode($input, true);

	$uniquepaymentid = $input['data']['object']['id'];
	$ordersession = $input['data']['object']['metadata']['order_id'];
	$pricepaid = $input['data']['object']['amount'];

/////////////////////////////////////
			$db=1;
			$webhookbypass=1;
			include('../db.php');


			$checkifexistsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' LIMIT 1");
			if(mysql_num_rows($checkifexistsq)=='0'){$nosqlfound = 'No order session found';}
			else{

			$nosqlfound = 'Order session found';


			$info = mysql_fetch_array($checkifexistsq);

/////


			$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));

			if(!empty($info['upsell'])){

			$upsellprice = explode('###',$info['upsell']);

			$upsellamount = $upsellprice[0];
			$upsellprice = $upsellprice[1];


			$packageinfo['price'] = str_replace('.','',$packageinfo['price']);
			$upsellprice = str_replace('.','',$upsellprice);

			$finalprice = $packageinfo['price'] + $upsellprice;
			$finalamount = $packageinfo['amount'] + $upsellamount;

			}else{

			$finalprice = $packageinfo['price'];
			$finalamount = $packageinfo['amount'];

			}

			$finalprice = str_replace('.','',$finalprice);
			$finalprice3 = $finalprice;
			$finalprice = sprintf('%.2f', $finalprice / 100);
			$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);


			if(!empty($_COOKIE['discount'])){include('../detectdiscount.php');}

			$priceamount = $finalprice;
			$finalprice = str_replace('.', '', $finalprice);

			$lastfour = $input['data']['object']['charges']['data']['0']['payment_method_details']['card']['last4'];

			if(empty($last4))$last4 = '0';

			$added = time();

				mysql_query("INSERT INTO `orders` SET 
					`country`= '{$info['country']}', 
					`packagetype`= '{$packageinfo['type']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '{$finalamount}', 
					`emailaddress`= '{$info['emailaddress']}', 
					`igusername`= '{$info['igusername']}', 
					`ipaddress`= '{$info['ipaddress']}',
					`imgsrc` = '{$info['imgsrc']}', 
					`price`= '{$pricepaid}', 
					`lastfour`= '{$lastfour}', 
					`payment_id` = '$uniquepaymentid'");


				mysql_query("UPDATE `order_session` SET `done` = '1' WHERE `order_session` = '$ordersession' LIMIT 1");



				/////////////////////////////

				$choosepostsql = '';
				$multiamountposts = 0;

				if(!empty($info['chooseposts'])){
				$chooseposts = explode('~~~', $info['chooseposts']);

				foreach($chooseposts as $posts1){

				if(empty($posts1))continue;

				$posts2 = explode('###', $posts1);

				$multiamountposts++;

				$choosepostsql .= $posts2[0].' ';}

				}

				/////////////////////

				$needsapproval = 0;
/*				if($packageinfo['id']==5){$needsapproval = 1;}
				if($packageinfo['id']==10){$needsapproval = 1;}
				if($packageinfo['id']==11){$needsapproval = 1;}
				if($packageinfo['id']==16){$needsapproval = 1;}
				if($packageinfo['id']==17){$needsapproval = 1;}*/

				/*
				//check on database if this is a fraudulent user 
				$checkfraudulentuser = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND `fraud` = '1' LIMIT 1");
				if(mysql_num_rows($checkfraudulentuser)=='1')$needsapproval = 1;

				//checkblacklist table
				$checkblacklist = mysql_query("SELECT * FROM `blacklist` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' OR `igusername` LIKE '%{$info['igusername']}%' OR `ipaddress` LIKE '%{$info['ipaddress']}%' OR `lastfour` LIKE '%$lastfour%' LIMIT 1");
				if(mysql_num_rows($checkblacklist)=='1')$needsapproval = 1;*/


				////////////////////////////




				if($needsapproval==0)include('../orderfulfill.php');
				
				$webhook=1;
				include('../emailfulfill.php');


				///////////////////////////

				//IF AUTO LIKES ENABLED 
				if(!empty($info['upsell_autolikes'])){

				include('../order3-autolikes.php');

				}


				/////////////////////////////

				$searchuserpastq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
				$searchuserpast = mysql_fetch_array($searchuserpastq);

				if(($searchpastuserpast['source']=='freetrial')||($searchpastuserpast['source']=='cart'))mysql_query("UPDATE `users` SET `funnelstate` = '0' WHERE `id` = '{$searchuserpast['id']}' LIMIT 1");

				$added = time();

				if($searchuserpast['guarantee']=='0'){include('../emailfulfillguarantee.php');
				$updateguarantee = "`guarantee` = '1', ";}

				if(($searchuserpast['source']=='cart')||($searchuserpast['source']=='freetrial')){$updatesource = " `funnelstate` = '0', `delivered` = '0', ";}


				$updateorder = mysql_query("UPDATE `orders` SET `chooseposts` = '$choosepostsql',`added` = '$added' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");
				$updateuser = mysql_query("UPDATE `users` SET $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");

				$updatefunnel = mysql_query("UPDATE `email_funnels` SET `clvincrease` = `clvincrease` + '{$finalprice}', `clvorders` = `clvorders` + 1 WHERE `hotsequence` = '{$searchuserpast['funnelstate']}' LIMIT 1");

				$duplicateordersession = mysql_query("INSERT IGNORE INTO `order_session_paid` SELECT * FROM `order_session` WHERE `order_session`= '{$info['order_session']}'");


				}


//////////////////////////////////////

$output = [
	'id' => $uniquepaymentid,
	'orderid' => $ordersession,
	'status' => 'success',
	'Session found' => $nosqlfound,
	'details' => $details,
	'email sent' => $didemailsend,
	'order made' => $orderid,
	'last4' => $lastfour,
	'final price' => $payment
];


}
else if ($event->type == 'payment_intent.payment_failed') {
	$details = 'Payment failed.';

$output = [
	'status' => 'success',
	'details' => $details
];

}



echo json_encode($output, JSON_PRETTY_PRINT);
