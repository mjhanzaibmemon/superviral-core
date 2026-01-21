<?php


if($code!=='31c223b5500453655b63bf1521eb268487da3')die('404: Incorrect authorisation code');


$details = 'Payment received!';


	$uniquepaymentid = $paymentId;
	$ordersession = $info['order_session'];
	$pricepaid = $priceamount;
	
/////////////////////////////////////


			$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));

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

			//$lastfour = $info['lastfour'];

			if(empty($info['lastfour'])){$lastfour = $lastfour;}else{$lastfour = $info['lastfour'];}
                  if(empty($info['payment_billingname_crdi']))$info['payment_billingname_crdi'] = $cardholdername;

			$added = time();




                  if($loggedin==true){

					
                        $userinsertq33 = "

                        `account_id` = '{$userinfo['id']}',
                        `noaccount` = '2',

                        ";

                  }else{


                  	$checkifexistinguser = mysql_query("SELECT * FROM `accounts` WHERE `email` LIKE '%{$info['emailaddress']}%' AND `brand` = 'to' LIMIT 1");
                  	if(mysql_num_rows($checkifexistinguser)!=='0')$redirectq12 = '&existinguser=true';//THIS IS AN EXISTING USER

                  }
   
			$orderinserted = mysql_query("INSERT INTO `orders` SET 
					`country`= 'us', 
                              $userinsertq33
					`packagetype`= '{$packageinfo['type']}', 
					`socialmedia`= '{$packageinfo['socialmedia']}', 
					`packageid`= '{$packageinfo['id']}', 
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
					`brand` = 'to',
                              `payment_billingname` = '{$info['payment_billingname_crdi']}'
                               $payment_id_desc");


				sendCloudwatchData('Tikoid',  $packageinfo['type'], 'Orders', 'orders-'. $packageinfo['type'] .'-function', 1);

 				mysql_query("UPDATE `order_session` SET `done` = '1',`payment_attempts` = '0' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");


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

				/*				
				if($packageinfo['id']==5){$needsapproval = 1;}
				if($packageinfo['id']==10){$needsapproval = 1;}
				if($packageinfo['id']==11){$needsapproval = 1;}
				if($packageinfo['id']==16){$needsapproval = 1;}
				if($packageinfo['id']==17){$needsapproval = 1;}
				
				
				//check on database if this is a fraudulent user 
				$checkfraudulentuser = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND `fraud` = '1' LIMIT 1");
				if(mysql_num_rows($checkfraudulentuser)=='1')$needsapproval = 1;

				//checkblacklist table
				$checkblacklist = mysql_query("SELECT * FROM `blacklist` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' OR `igusername` LIKE '%{$info['igusername']}%' OR `ipaddress` LIKE '%{$info['ipaddress']}%' OR `lastfour` LIKE '%$lastfour%' LIMIT 1");
				if(mysql_num_rows($checkblacklist)=='1')$needsapproval = 1;
				*/

				////////////////////////////



				
				if($needsapproval==0)include('orderfulfill.php');

				
				$webhook=0;
				include('emailfulfill.php');



				///////////////////////////

				//IF AUTO LIKES ENABLED 
				if(!empty($info['upsell_autolikes'])){

				include('order3-autolikes.php');

				}

				if($applepayprocess=='12313'){

					$paymentmethod='applepay';

				}

				else{

					$paymentmethod='card';
				}
				


				/////////////////////////////

				$searchuserpastq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");
				$searchuserpast = mysql_fetch_array($searchuserpastq);

				if(($searchpastuserpast['source']=='freetrial')||($searchpastuserpast['source']=='cart'))mysql_query("UPDATE `users` SET `funnelstate` = '0' WHERE `id` = '{$searchuserpast['id']}' AND `brand` = 'to' LIMIT 1");

				$added = time();
				
				if(($searchuserpast['source']=='cart')||($searchuserpast['source']=='freetrial')){$updatesource = " `funnelstate` = '0', `delivered` = '0', ";}

                        if(!empty($searchuserpast['contactnumber'])){$addcontactnumber = ", `askednumber` = '2', `contactnumber` = '{$searchuserpast['contactnumber']}' ";}else{$addcontactnumber = "";}

					
				$updateorder = mysql_query("UPDATE `orders` SET `chooseposts` = '$choosepostsql',`added` = '$added' $addcontactnumber WHERE `order_session` = '{$info['order_session']}' LIMIT 1");
				
				$updateuser = mysql_query("UPDATE `users` SET $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");
				$updatefunnel = mysql_query("UPDATE `email_funnels` SET `clvincrease` = `clvincrease` + '{$finalprice}', `clvorders` = `clvorders` + 1 WHERE `hotsequence` = '{$searchuserpast['funnelstate']}' AND `brand` = 'to' LIMIT 1");

	

/*				$duplicateordersession = mysql_query("INSERT IGNORE INTO `order_session_paid` SELECT * FROM `order_session` WHERE `order_session`= '{$info['order_session']}'");*/

				$locredirect = $loc.'.';
				if($locredirect=='ww.')$locredirect = '';
				if($orderinserted){

					if($dontredirectwebhook!==1){

						sendCloudwatchData('Tikoid', 'payment-redirect' , 'PaymentProcessingRedirect', 'payment-processing-redirect-function', 1);

						header('Location: /order/payment-processing/');die;

					}

				}

	
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

