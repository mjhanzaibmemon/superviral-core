<?php

 // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path


function getbetween($content,$start,$end){
$r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

if($code!=='31c223b5500453655b63bf1521eb268487da3')die('404: Incorrect authorisation code');


$details = 'Payment received!';


$input = json_decode($input, true);

	$uniquepaymentid = $paymentId;
	$ordersession = $info['order_session'];
	$pricepaid = $priceamount;

	if (isset($response['bin']['issuing_country_iso2'])) {
		$billingCountry = $response['bin']['issuing_country_iso2'];
	} else {
		$billingCountry = '';
	}
	
	/////////////////////////////////////


			$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `socialmedia` = '{$info['socialmedia']}' LIMIT 1"));

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

                  if($info['account_id']!=='0'){


                        $userinsertq33 = "

                        `account_id` = '{$info['account_id']}',
                        `noaccount` = '2',

                        ";

                  }else{


                  	$checkifexistinguser = mysql_query("SELECT * FROM `accounts` WHERE `email` LIKE '%{$info['emailaddress']}%' LIMIT 1");
                  	if(mysql_num_rows($checkifexistinguser)!=='0')$redirectq12 = '&existinguser=true';//THIS IS AN EXISTING USER

                  }


			$orderinserted = mysql_query("INSERT INTO `orders` SET 
					`brand` = 'sv',
					`country`= '{$info['country']}', 
                              $userinsertq33
					`packagetype`= '{$packageinfo['type']}', 
					`packageid`= '{$packageinfo['id']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`next_fulfill_attempt` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '{$finalamount}', 
					`emailaddress`= '{$info['emailaddress']}', 
					`igusername`= '{$info['igusername']}', 
					`ipaddress`= '{$info['ipaddress']}',
					`imgsrc` = '{$info['imgsrc']}', 
					`price`= '{$finalprice}', 
					`lastfour`= '{$lastfour}',
					`socialmedia`= '{$info['socialmedia']}', 
					`payment_id` = '$uniquepaymentid',`billing_country` = '$billingCountry',
                              `payment_billingname` = '{$info['payment_billingname_crdi']}'
                               $payment_id_desc");


				sendCloudwatchData('Superviral', $info['socialmedia'].'-'.$packageinfo['type'], 'Orders', 'orders-'. $info['socialmedia'].'-'.$packageinfo['type'] .'-function', 1);

				// upsell add follower

				if (!empty($info['upsell_all'])) {
					$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);

					$upsell_packageinfo = mysql_fetch_array(mysql_query("SELECT id FROM packages WHERE `TYPE` = 'followers' and amount <= '{$packageinfo['amount']}' AND `socialmedia` = '{$packageinfo['socialmedia']}' ORDER BY amount DESC LIMIT 1"));
				

				
					$upsellprice1 = explode('###', $info['upsell_all']);
				
				
				
					$upsellamount1 = $upsellprice1[0];
				
					$upsellprice1 = $upsellprice1[1];
					$upsellprice1 = str_replace('.','',$upsellprice1);
					$finalprice = $upsellprice1;
				
					$orderinserted = mysql_query("INSERT INTO `orders` SET 
						`brand` = 'sv',
						`country`= '{$info['country']}', 
								  $userinsertq33
						`packagetype`= 'followers', 
						`packageid`= '{$upsell_packageinfo['id']}', 
						`order_session`= '{$order_session}',
						`added` = '$added', 
						`lastrefilled` = '$added', 
						`amount`= '{$upsellamount1}', 
						`from_upsell`= '1', 
						`emailaddress`= '{$info['emailaddress']}', 
						`igusername`= '{$info['igusername']}', 
						`ipaddress`= '{$info['ipaddress']}',
						`next_fulfill_attempt` = '$added', 
						`imgsrc` = '{$info['imgsrc']}', 
						`price`= '{$finalprice}', 
						`lastfour`= '{$lastfour}', 
						`socialmedia`= '{$info['socialmedia']}',
						`payment_id` = '$uniquepaymentid',`billing_country` = '$billingCountry',
								  `payment_billingname` = '{$info['payment_billingname_crdi']}'
								   $payment_id_desc");

					sendCloudwatchData('Superviral', 'upsell-followers', 'Orders', 'orders-upsell-followers-function', 1);
				} 

			
				// upsell follower end

 				mysql_query("UPDATE `order_session` SET `done` = '1',`payment_attempts` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");



 				///////////////////////////

				$choosepostsql = '';
				$multiamountposts = 0;




				if(!empty($info['chooseposts'])){

					if($info['socialmedia'] == "ig"){
						if (strpos($info['chooseposts'], '###') !== false) {

							$chooseposts = explode('~~~', $info['chooseposts']);

							foreach($chooseposts as $posts1){

							if(empty($posts1))continue;

							$posts2 = explode('###', $posts1);

							$multiamountposts++;

							$choosepostsql .= $posts2[0].' ';
							}

						} else{

							$chooseposts = explode('~~~', $info['chooseposts']);

							foreach($chooseposts as $posts1){

							if(empty($posts1))continue;

							$foundIgshortcode= getbetween($posts1,'instagram.com/p/','/');

							if(empty($foundIgshortcode))$foundIgshortcode= getbetween($posts1,'instagram.com/reel/','/');


							$choosepostsql .= $foundIgshortcode.' ';
							

							$multiamountposts++;

							}

						}

					}else{

						
						if(!empty($info['chooseposts'])){
							$chooseposts = explode('~~~', $info['chooseposts']);
	
							foreach($chooseposts as $posts1){
								if(empty($posts1))continue;
		
								$posts2 = explode('###', $posts1);
		
								$multiamountposts++;
		
								$choosepostsql .= $posts2[0].' ';
							}

						}

					}

					
				}

			


				if(!empty($info['freeviewsposts'])){
					$freeviewsposts = explode('~~~', $info['freeviewsposts']);
	
					foreach($freeviewsposts as $posts1){
	
					if(empty($posts1))continue;
	
					$posts2 = explode('###', $posts1);
	
					$multiamountposts++;
	
					$freeviewspostsql .= $posts2[0].' ';}
	
				}
				

				$chooseCommentsql = '';

				if(!empty($info['choose_comments'])){
				$choosecomments2 = explode('~~~', $info['choose_comments']);

				foreach($choosecomments2 as $comments1){

				if(empty($comments1))continue;

				$chooseCommentsql .= $comments1.' ';}

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





				//if($needsapproval==0)include('orderfulfill.php');


				
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

				$searchuserpastq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
				$searchuserpast = mysql_fetch_array($searchuserpastq);

				//if (strpos($searchuserpast['usernames'], strtolower($info['igusername'])) !== false) {
				if (stripos($searchuserpast['usernames'], $info['igusername']) === false) {
  
    				$newupdateusernamevalue = strtolower($searchuserpast['usernames'].$info['igusername'].'###');
    				$updateusersusername1 = " `usernames` = '$newupdateusernamevalue', ";

    			}





				if(($searchpastuserpast['source']=='freetrial')||($searchpastuserpast['source']=='cart'))mysql_query("UPDATE `users` SET `funnelstate` = '0' WHERE `id` = '{$searchuserpast['id']}' LIMIT 1");


				$added = time();


/*if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){

			if($searchuserpast['guarantee']=='0'){
				
				include('../emailfulfillguarantee.php');

				$updateguarantee = "`guarantee` = '1', ";}

}*/




				if(($searchuserpast['source']=='cart')||($searchuserpast['source']=='freetrial')){$updatesource = " `funnelstate` = '0', `delivered` = '0', ";}



                        if(!empty($searchuserpast['contactnumber'])){$addcontactnumber = ", `askednumber` = '2', `contactnumber` = '{$searchuserpast['contactnumber']}' ";}else{$addcontactnumber = "";}





				$updateorder = mysql_query("UPDATE `orders` SET `freeviewsposts` = '$freeviewspostsql',`choose_comments` = '$chooseCommentsql',`chooseposts` = '$choosepostsql',`added` = '$added' $addcontactnumber WHERE `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");
				$updateuser = mysql_query("UPDATE `users` SET $updateusersusername1 $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
				//$updateuser = mysql_query("UPDATE `users` SET $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");





				$updatefunnel = mysql_query("UPDATE `email_funnels` SET `clvincrease` = `clvincrease` + '{$finalprice}', `clvorders` = `clvorders` + 1 WHERE `hotsequence` = '{$searchuserpast['funnelstate']}' LIMIT 1");



/*				$duplicateordersession = mysql_query("INSERT IGNORE INTO `order_session_paid` SELECT * FROM `order_session` WHERE `order_session`= '{$info['order_session']}'");*/

				$locredirect = $loc.'.';
				if($locredirect=='ww.')$locredirect = '';



				if($orderinserted){



					if($dontredirectwebhook!==1){

						sendCloudwatchData('Superviral', 'payment-redirect' , 'PaymentProcessingRedirect', 'payment-processing-redirect-function', 1);

						header('Location: https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/?paymentmethod='.$paymentmethod.$redirectq12);

					}

				}

				else{


/*					if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo '103666<br>';

					if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo "<hr>INSERT INTO `orders` SET 
					`brand` = 'sv',
					`country`= '{$info['country']}', 
                              $userinsertq33
					`packagetype`= '{$packageinfo['type']}', 
					`packageid`= '{$packageinfo['id']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`next_fulfill_attempt` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '{$finalamount}', 
					`emailaddress`= '{$info['emailaddress']}', 
					`igusername`= '{$info['igusername']}', 
					`ipaddress`= '{$info['ipaddress']}',
					`imgsrc` = '{$info['imgsrc']}', 
					`price`= '{$finalprice}', 
					`lastfour`= '{$lastfour}',
					`socialmedia`= '{$info['socialmedia']}', 
					`payment_id` = '$uniquepaymentid',`billing_country` = '$billingCountry',
                              `payment_billingname` = '{$info['payment_billingname_crdi']}'
                               $payment_id_desc";
*/

				}

	
//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo '110<br>';

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

