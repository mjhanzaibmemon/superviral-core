<?php

 // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path



if($code!=='31c223b5500453655b63bf1521eb268487da3')die('404: Incorrect authorisation code');




$details = 'Payment received!';



$input = json_decode($input, true);

	$uniquepaymentid = $paymentId;
	$ordersession = $info['order_session'];
	$pricepaid = $priceamount;

/////////////////////////////////////



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


			if(empty($info['lastfour'])){$lastfour = $lastfour;}else{$lastfour = $info['lastfour'];}
                  if(empty($info['payment_billingname_crdi']))$info['payment_billingname_crdi'] = $cardholdername;

			$added = time();



                  if($loggedin==true){


                        $userinsertq33 = "

                        `account_id` = '{$userinfo['id']}',
                        `noaccount` = '2',

                        ";

                  }

                 

			$orderinserted = mysql_query("INSERT INTO `orders` SET 
					`brand` = 'sv',
					`country`= '{$info['country']}', 
                              $userinsertq33
					`packagetype`= '{$packageinfo['type']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '{$finalamount}', 
					`emailaddress`= '{$info['emailaddress']}', 
					`igusername`= '{$info['igusername']}', 
					`ipaddress`= '{$info['ipaddress']}',
					`imgsrc` = '{$info['imgsrc']}', 
					`price`= '{$finalprice}', 
					`lastfour`= '{$lastfour}', 
					`payment_id` = '$uniquepaymentid',
                              `payment_billingname` = '{$info['payment_billingname_crdi']}'
                               $payment_id_desc");



 				mysql_query("UPDATE `order_session` SET `done` = '1' WHERE `order_session` = '$ordersession' LIMIT 1");



 				///////////////////////////

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

				////////////////////////////





				include('orderfulfill.php');



				
				$webhook=0;
				include('emailfulfill-new.php');

				//IF AUTO LIKES ENABLED 
				if(!empty($info['upsell_autolikes'])){

				include('order3-autolikes.php');

				
				}





				/////////////////////////////

				$searchuserpastq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
				$searchuserpast = mysql_fetch_array($searchuserpastq);

				if(($searchpastuserpast['source']=='freetrial')||($searchpastuserpast['source']=='cart'))mysql_query("UPDATE `users` SET `funnelstate` = '0' WHERE `id` = '{$searchuserpast['id']}' LIMIT 1");

				$added = time();

				if($searchuserpast['guarantee']=='0'){include('emailfulfillguarantee.php');
				$updateguarantee = "`guarantee` = '1', ";}

				if(($searchuserpast['source']=='cart')||($searchuserpast['source']=='freetrial')){$updatesource = " `funnelstate` = '0', `delivered` = '0', ";}

                        if(!empty($searchuserpast['contactnumber'])){$addcontactnumber = ", `askednumber` = '2', `contactnumber` = '{$searchuserpast['contactnumber']}' ";}else{$addcontactnumber = "";}


				$updateorder = mysql_query("UPDATE `orders` SET `chooseposts` = '$choosepostsql',`added` = '$added' $addcontactnumber WHERE `order_session` = '{$info['order_session']}' LIMIT 1");
				$updateuser = mysql_query("UPDATE `users` SET $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");

				$updatefunnel = mysql_query("UPDATE `email_funnels` SET `clvincrease` = `clvincrease` + '{$finalprice}', `clvorders` = `clvorders` + 1 WHERE `hotsequence` = '{$searchuserpast['funnelstate']}' LIMIT 1");



/*				$duplicateordersession = mysql_query("INSERT IGNORE INTO `order_session_paid` SELECT * FROM `order_session` WHERE `order_session`= '{$info['order_session']}'");*/

				$locredirect = $loc.'.';
				if($locredirect=='ww.')$locredirect = '';

				if($orderinserted){header('Location: https://'.$locredirect.'superviral.io/'.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/');}

				echo 'Redirect Now - Payment Is Complete';


// //////////////////////////////////////

// $output = [
// 	'id' => $uniquepaymentid,
// 	'orderid' => $ordersession,
// 	'status' => 'success',
// 	'Session found' => $nosqlfound,
// 	'details' => $details,
// 	'email sent' => $didemailsend,
// 	'order made' => $orderid,
// 	'last4' => $lastfour,
// 	'final price' => $finalprice3
// ];


// }
// else if ($event->type == 'payment_intent.payment_failed') {
// 	$details = 'Payment failed.';

// $output = [
// 	'status' => 'success',
// 	'details' => $details
// ];

// }

?>

