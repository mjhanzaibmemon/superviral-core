<?php





 // Error/Exception engine, always use E_ALL



ini_set('ignore_repeated_errors', TRUE); // always use TRUE



ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment



ini_set('log_errors', TRUE); // Error/Exception file logging engine.

ini_set('error_log', '/var/www/html/errors.log'); // Logging file path





if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");

else ob_start();

header('Content-type: text/html; charset=utf-8');



$db = 1;

include('header.php');

include('ordercontrol.php');



//ERROR LOGGING



$plogordersession = serialize($info);

$lognow = time();







//LOC REDIRECT

$locredirect = $loc . '.';

if ($locredirect == 'ww.') $locredirect = '';



$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";





$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));

$packagetitle = $packageinfo['amount'] . ' ' . ucwords($packageinfo['type']);



if (!empty($info['upsell'])) {



	$upsellprice = explode('###', $info['upsell']);



	$upsellamount = $upsellprice[0];

	$upsellprice = $upsellprice[1];



	$finalprice = $packageinfo['price'] + $upsellprice;

} else {



	$finalprice = $packageinfo['price'];

}





if (!empty($_COOKIE['discount'])) {

	include('detectdiscount.php');

}



$priceamount = $finalprice;

$cardinitypaymentamount = floatval($finalprice);





$commonMsg = "";

$cardError = "";

$cvcError = "";

$expiryError = "";

$cardHolderName = "";

$pan = "";

$errors = [];



include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';



/* Start to develop here. Best regards https://php-download.com/ */



use Cardinity\Client;

use Cardinity\Method\Payment;

use Cardinity\Exception;

use Cardinity\Method\ResultObject;



$client = Client::create([

	'consumerKey' => $cardinitykey,

	'consumerSecret' => $cardinitysecret,

]);





//ALL PARAMETERS





if (array_key_exists('submit', $_POST)) $submit = addslashes($_POST['submit']);



if (array_key_exists('cardHoldername', $_POST)) $cardholdername = addslashes($_POST['cardHoldername']);

if (array_key_exists('cardNumber', $_POST)) $pan = addslashes($_POST['cardNumber']);

if (array_key_exists('CVC', $_POST)) $cvc = addslashes($_POST['CVC']);



if (array_key_exists('expDate', $_POST)) {

	$expdate = addslashes($_POST['expDate']);



	$expdate = str_replace(' ', '', $expdate);



	if (strpos($expdate, '/') !== false) {

		$expdateexplode = explode('/', $expdate);

		$expmonth = (int)$expdateexplode[0];

		$expyear = str_replace('20', '', $expdateexplode[1]);

		$expyear = '20' . $expyear;

		$expyear = (int)$expyear;

	}

}



if (array_key_exists('screen_width', $_POST)) $screen_width = (int)addslashes($_POST['screen_width']);

if (array_key_exists('screen_height', $_POST)) $screen_height = (int)addslashes($_POST['screen_height']);

if (array_key_exists('challenge_window_size', $_POST)) $challenge_window_size = addslashes($_POST['challenge_window_size']);

if (array_key_exists('browser_language', $_POST)) $browser_language = addslashes($_POST['browser_language']);

if (array_key_exists('color_depth', $_POST)) $color_depth = (int)addslashes($_POST['color_depth']);

if (array_key_exists('time_zone', $_POST)) $time_zone = (int)addslashes($_POST['time_zone']);

if (array_key_exists('country', $_POST)) $country = addslashes($_POST['country']);

if (array_key_exists('emailaddress', $_POST)) $emailaddress = addslashes($_POST['emailaddress']);



//3D Secure V1

if (array_key_exists('PaRes', $_POST)) $pares = addslashes($_POST['PaRes']);



//3d Secure V2

if (array_key_exists('cres', $_POST)) $cres = addslashes($_POST['cres']);

if (array_key_exists('threeDSSessionData', $_POST)) $threeDSSessionData = addslashes($_POST['threeDSSessionData']);





if ((!empty($info['payment_id_crdi'])) || (!empty($info['payment_creq_crdi']))) {

	$paymentid = $info['payment_id_crdi'];

	$creq = $info['payment_creq_crdi'];

}







/////////////////////////////////////////////////////////////////// CHECK FOR FRAUDULENT PAYMENT CHECK



$paymentdetailscheck = 2; //THIS IS DEFAULT



if ((!empty($submit)) && ($paymentdetailscheck == 2)) { //CHECK FOR FRAUDULENT PAYMENT CHECKS NOW



	if ($country == 'ID') $paymentdetailscheck = 1;

	if ($country == 'MA') $paymentdetailscheck = 1;

	if ($country == 'CM') $paymentdetailscheck = 1;



	if ($info['payment_attempts'] >= 25) {

		$paymentdetailscheck = 1;

	}



	$checkforfraudq = mysql_query("SELECT `emailaddress` FROM `blacklist` WHERE `emailaddress` LIKE '%$emailaddress%' AND `brand` = 'to' LIMIT 1");

	if (mysql_num_rows($checkforfraudq) == '1') $paymentdetailscheck = 1;



	$checkforfraudq = mysql_query("SELECT `igusername` FROM `blacklist` WHERE `igusername` LIKE '%{$info['igusername']}%' AND `brand` = 'to' LIMIT 1");

	if (mysql_num_rows($checkforfraudq) == '1') $paymentdetailscheck = 1;



	$checkforfraudq = mysql_query("SELECT `ipaddress` FROM `blacklist` WHERE `ipaddress` LIKE '%{$info['ipaddress']}%' AND `brand` = 'to' LIMIT 1");

	if (mysql_num_rows($checkforfraudq) == '1') $paymentdetailscheck = 1;



	/*$checkforfraudq = mysql_query("SELECT `billingname` FROM `blacklist` WHERE `billingname` LIKE '%$cardholdername%' AND `brand` = 'to' LIMIT 1");

if(mysql_num_rows($checkforfraudq)=='1')$paymentdetailscheck=1;

*/

}







if ($paymentdetailscheck == 1) {



	$nowblacklist = time();



	$currentipaddress = $info['ipaddress'];



	mysql_query("INSERT INTO `blacklist` SET 

    `emailaddress` = '$emailaddress', 

    `igusername` = '{$info['igusername']}', 

    `ipaddress` = '$currentipaddress',

    `billingname` = '$cardholdername',

    `added` = '$nowblacklist', `brand` = 'to'");



	$commonMsg .= '<div class="emailsuccess emailfailed">Unfortunately, there was an error processing your payment. We cannot process your payment at this time.</div>';

	unset($submit);

}



////////////////////////////////////////////////////////////////////



//// 3D Secure V2

if ((!empty($cres)) && (!empty($threeDSSessionData))) {

	$method = new Payment\Finalize($paymentid, $cres, true);

}



//// 3D Secure V1

if (!empty($pares)) {

	$method = new Payment\Finalize($paymentid, $pares);

}





//// SUBMIT INFORMATION



if ((!empty($submit)) && ($paymentdetailscheck == 2)) {



	//$pan ='3393339333933393';



	$pan = str_replace(' ', '', $pan);



	if ((!empty($pan)) && (is_numeric($pan))) {



		$lastfour = substr(str_replace(' ', '', $pan), -4);

	$res = mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	}



	if (!empty($cardholdername)) {



		mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	}



	if (empty($country)) {

		$country = 'US';

	}



	$descriptioncardinity = 'Tikoid Order (' . strtoupper(rtrim($locredirect, '.')) . ')';



	$method = new Payment\Create([

		'amount' => $cardinitypaymentamount,

		'currency' => 'USD',

		'settle' => true,

		'description' => $descriptioncardinity,

		'order_id' => $info['order_session'],

		'country' => $country,

		'payment_method' => Payment\Create::CARD,

		'payment_instrument' => [

			'pan' => $pan,

			'exp_year' => $expyear,

			'exp_month' => $expmonth,

			'cvc' => $cvc,

			'holder' => $cardholdername

		],

		'threeds2_data' =>  [

			"notification_url" => $siteDomain . '/order/payment/?redirectid=' . $info['order_session'] . '&new=true',

			"browser_info" => [   

				"accept_header" => "text/html",

				"browser_language" => $browser_language,

				"screen_width" => $screen_width,

				"screen_height" => $screen_height,

				'challenge_window_size' => $challenge_window_size,
				"user_agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0",

				"color_depth" => $color_depth,

				"time_zone" => $time_zone

			],

			"cardholder_info" => [

				"email_address" => $emailaddress

			],

		],

	]);

}







// if (!empty($_POST['stripeToken'])) {





// 	if ($charge) { // When successful payment done



// 		$added = time();



// 		mysql_query("INSERT INTO `orders` SET 

// 		`packagetype`= '{$packageinfo['type']}', 

// 		`order_session`= '{$ordersession}',

// 		`added` = '$added', 

// 		`amount`= '{$packageinfo['amount']}', 

// 		`emailaddress`= '{$info['emailaddress']}', 

// 		`igusername`= '{$info['igusername']}', 

// 		`ipaddress`= '{$info['ipaddress']}',

// 		`imgsrc` = '{$info['imgsrc']}', 

// 		`price`= '{$finalprice}', `brand` = 'to'");





// 		mysql_query("UPDATE `order_session` SET `done` = '1' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");







// 		//include('orderfulfill.php');

// 		//include('emailfulfill.php');





// 		/////////////////////////////



// 		$choosepostsql = '';



// 		if (!empty($info['chooseposts'])) {

// 			$chooseposts = explode('~~~', $info['chooseposts']);



// 			foreach ($chooseposts as $posts1) {



// 				$choosepostsql .= $posts1 . ',';

// 				$choosepostsql = rtrim($choosepostsql, ',');

// 			}

// 		}



// 		/////////////////////////////



// 		$searchuserpastq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");

// 		$searchuserpast = mysql_fetch_array($searchuserpastq);



// 		if (($searchpastuserpast['source'] == 'freetrial') || ($searchpastuserpast['source'] == 'cart')) mysql_query("UPDATE `users` SET `funnelstate` = '0' WHERE `id` = '{$searchuserpast['id']}' AND `brand` = 'to' LIMIT 1");



// 		$added = time();



// 		if ($searchuserpast['guarantee'] == '0') {

// 			include('emailfulfillguarantee.php');

// 			$updateguarantee = "`guarantee` = '1', ";

// 		}



// 		if (($searchuserpast['source'] == 'cart') || ($searchuserpast['source'] == 'freetrial')) {

// 			$updatesource = " `funnelstate` = '0', `delivered` = '0', ";

// 		}





// 		$updateorder = mysql_query("UPDATE `orders` SET `chooseposts` = '$choosepostsql',`added` = '$added' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");

// 		$updateuser = mysql_query("UPDATE `users` SET $updateguarantee $updatesource `clv` = `clv` + '{$priceamount}',`source` = 'order',`orders` = `orders` + '1' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");



// 		$updatefunnel = mysql_query("UPDATE `email_funnels` SET `clvincrease` = `clvincrease` + '{$finalprice}', `clvorders` = `clvorders` + 1 WHERE `hotsequence` = '{$searchuserpast['funnelstate']}' LIMIT 1");



// 		header('Location: /order/finish/');

// 	}

// }





/**

 * In case payment could not be processed exception will be thrown.

 * In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.

 * See Error Codes section for detailed list.

 */



if ((!empty($submit)) || (!empty($pares)) || ((!empty($cres)) && (!empty($threeDSSessionData)))) {



	try {

		/** @type Cardinity\Method\Payment\Payment */

		$payment = $client->call($method);





		///////////    



		if (!empty($submit)) {



			$status = $payment->getStatus();

			if ($status == 'pending') $creq = $payment->getThreeds2Data()->getCreq();

			$paymentId = $payment->getId();







			$res = mysql_query("UPDATE `order_session` SET `payment_id_crdi` = '{$paymentId}', `payment_creq_crdi` = '$creq' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

		} else {



			$paymentId = $info['payment_id_crdi'];

			$creq = $info['payment_creq_crdi'];

		}



		//////////

















		if ((!empty($cres)) && (!empty($threeDSSessionData))) {





			$method = new Payment\Finalize(

				$paymentId, // payment object received from API call

				$creq, // payment object received from API call

				true // BOOL `true` to enable 3D secure V2 parameters

			);

		}



		/////////////





		$plogordersession = serialize($info);





		$status = $payment->getStatus();



		if ($status == 'approved') {

			// Payment is approved









			$method = new Payment\Get($paymentId);

			/** @type Cardinity\Method\Payment\Payment */

			$payment = $client->call($method);

			$payment_amount = $payment->getAmount();









			//FULFILL ORDER IF AMOUNT IS THE SAME



			$payment_amount1 = $payment_amount * 100;

			$priceamount1 = $priceamount * 100;



			if (abs($payment_amount1 - $priceamount1) < 5) {







				$code = '31c223b5500453655b63bf1521eb268487da3';



				echo ' ';

				// $commonMsg = "Transaction Success";

				// echo $commonMsg;

				include('pi/cardinitywebhook.php');





			}









			die;







			//ONCE FULFILLED REDIRECT





		} elseif ($status == 'pending') { //FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS

			// check if passed through 3D secure version 2

			if ($payment->isThreedsV2()) {



				// get data required to finalize payment

				$creq = $payment->getThreeds2Data()->getCreq();

				$paymentId = $payment->getId();

				$url = $payment->getThreeds2Data()->getAcsUrl();

				// finalize process should be done here.







				//echo $creq;



				/// 3DS Redirection

				$tpl = file_get_contents('order-template.html');

				$body = file_get_contents('order3-3dsv2.html');



				$tpl = str_replace('{body}', $body, $tpl);

				$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

				$tpl = str_replace('{back}', '/order/review/', $tpl);

				$tpl = str_replace('{creq}', $creq, $tpl);

				$tpl = str_replace('{acs_url}', $url, $tpl);

				$tpl = str_replace('{orderid}', $info['order_session'], $tpl);

				$tpl = str_replace('<body>', '<body onload="OnLoadEvent();">', $tpl);



				$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='to' AND ((`country` = 'ww' AND `page` = 'order3') OR (`country` = 'ww' AND `page` = 'global'))");

				while ($cinfo = mysql_fetch_array($contentq)) {

					$tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);

				}



				echo $tpl;



				$plogordersession = serialize($info);

				$lognow = time();


				$sql = "INSERT INTO `payment_logs` SET 

				`url` = '$actual_link',

				`ipaddress` = '{$info['ipaddress']}',

				`lastfour` = '$lastfour', 

				`message` = '$status - 3dS V2', 

				`payment_id` = '$paymentId', 

				`added` = '$lognow',

				`expdate` = '$expdate',

				`cvc` = '', 

				`order_session` = '$plogordersession',

				`error` = ''    

				";
			$res =	mysql_query($sql);



				die;

			} elseif ($payment->isThreedsV1()) { //FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS

				// Retrieve information for 3D-Secure V1 authorization

				$url = $payment->getAuthorizationInformation()->getUrl();

				$data = $payment->getAuthorizationInformation()->getData();

				$callback_url = $siteDomain. '/order/payment/?redirectid=' . $info['order_session'] . '&new=true';

				// finalize process should be done here.





				/// 3DS Redirection

				$tpl = file_get_contents('order-template.html');

				$body = file_get_contents('order3-3dsv1.html');



				$tpl = str_replace('{body}', $body, $tpl);

				$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

				$tpl = str_replace('{back}', '/order/review/', $tpl);

				$tpl = str_replace('{data}', $data, $tpl);

				$tpl = str_replace('{acs_url}', $url, $tpl);

				$tpl = str_replace('{callback_url}', $callback_url, $tpl);

				$tpl = str_replace('<body>', '<body onload="OnLoadEvent();">', $tpl);



				$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='to' AND ((`country` = 'ww' AND `page` = 'order3') OR (`country` = 'ww' AND `page` = 'global'))");

				while ($cinfo = mysql_fetch_array($contentq)) {

					$tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);

				}



				echo $tpl;



				$plogordersession = serialize($info);

				$lognow = time();



				mysql_query("INSERT INTO `payment_logs` SET 

				`url` = '$actual_link',

				`ipaddress` = '{$info['ipaddress']}',

				`lastfour` = '$lastfour', 

				`message` = '$status - 3dS V1', 

				`payment_id` = '$paymentId', 

				`added` = '$lognow',

				`expdate` = '$expdate',

				`cvc` = '', 

				`order_session` = '$plogordersession',

				`error` = '',    
				`brand` = 'to'
				");





				die;

			}

		}

	} catch (Cardinity\Exception\InvalidAttributeValue $exception) {

		foreach ($exception->getViolations() as $key => $violation) {



			$ii = 0;



			$propertypath = $violation->getPropertyPath();



			if ((strpos($propertypath, 'pan') !== false)) {

				array_push($errors1, $violation->getMessage());

				$ii = 1;

				$inpnum = 1;

			}

			if ((strpos($propertypath, 'exp_year') !== false)) {

				array_push($errors2, 'Expiry Date: ' . $violation->getMessage());

				$ii = 1;

				$inpdate = 1;

			}

			if ((strpos($propertypath, 'exp_month') !== false)) {

				array_push($errors2, 'Expiry Date: ' . $violation->getMessage());

				$ii = 1;

				$inpdate = 1;

			}

			if ((strpos($propertypath, 'cvc') !== false)) {

				array_push($errors2, 'CVC/CVV: ' . $violation->getMessage());

				$ii = 1;

				$inpcvc = 1;

			}



			if ($ii == 0) {

				array_push($errors, $violation->getPropertyPath() . ' ' . $violation->getMessage());

			}



			$ii = 0;

		}

	} catch (Cardinity\Exception\ValidationFailed $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, '' . $error['message']);

		}



		mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	} catch (Cardinity\Exception\Declined $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'You failed to authorize your payment through your bank: ' . $error['message']);

		}



		mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	} catch (Cardinity\Exception\NotFound $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'The card information could not be found. ' . $error['message']);

		}



		mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	} catch (Cardinity\Exception\Unauthorized $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'Your card information was missing or wrong: ' . $error['message']);

		}



		mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

	} catch (Cardinity\Exception\Forbidden $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'You do not have access to this resource: ' . $error['message']);

		}

	} catch (Cardinity\Exception\MethodNotAllowed $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'You tried to access a resource using an invalid HTTP method: ' . $error['message']);

		}

	} catch (Cardinity\Exception\InternalServerError $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'We had a problem on our end. Try again later: ' . $error['message']);

		}

	} catch (Cardinity\Exception\NotAcceptable $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'Wrong Accept headers sent in the request: ' . $error['message']);

		}

	} catch (Cardinity\Exception\ServiceUnavailable $exception) {

		foreach ($exception->getErrors() as $key => $error) {

			array_push($errors, 'We\'re temporarily off-line for maintenance. Please try again later: ' . $error['message']);

		}

	} catch (\Exception $exception) {

		$errors = [

			$exception->getMessage(),

			//$exception->getPrevious()->getMessage()

		];

	}





	if (!empty($errors)) {

		foreach ($errors as $pererror) {

			$error0content .= $pererror . '<br>';

			$errorlog .= $pererror . '\n';

		}

		if (!empty($error0content)) $commonMsg .= '<div class="emailsuccess emailfailed">' . $error0content . '</div>';

	}

	if (!empty($errors00)) {

		foreach ($errors00 as $pererror) {

			$error0content .= $pererror . '<br>';

			$errorlog .= $pererror . '\n';

		}

		if (!empty($error0content)) $commonMsg .= '<div class="emailsuccess emailfailed">' . $error0content . '</div>';

	}

	if (!empty($errors1)) {

		foreach (array_unique($errors1) as $pererror) {

			$error1content .= $pererror . '<br>';

			$errorlog .= $pererror . '\n';

		}

		if (!empty($error1content)) $cardError = '<div class="emailsuccess emailfailed">' . $error1content . '</div>';

	}

	if (!empty($errors2)) {

		foreach (array_unique($errors2) as $pererror) {

			$error2content .= $pererror . '<br>';

			$errorlog .= $pererror . '\n';

		}

		if (!empty($error2content)) $showerror2 = '<div class="emailsuccess emailfailed">' . $error2content . '</div>';

	}





	if ((!empty($errors)) || (!empty($errors1)) || (!empty($errors2)) || (!empty($errors00))) {



		$combineerrors = addslashes(serialize($errors) . '###' . serialize($errors1) . '###' . serialize($errors2) . '###' . serialize($errors00));





		if (!empty($info['payment_id_crdi'])) $paymentIds = $info['payment_id_crdi'];



		$plogordersession = serialize($info);



	$res =	mysql_query("INSERT INTO `payment_logs` SET 

				`url` = '$actual_link',

				`ipaddress` = '{$info['ipaddress']}',

				`lastfour` = '$lastfour', 

				`error` = '$combineerrors', 

				`payment_id` = '$paymentIds', 

				`added` = '$lognow',

				`expdate` = '$expdate',

				`cvc` = '', 

				`order_session` = '$plogordersession',

				`brand` = 'to'  

				");

	}







	if (!empty($inpnum == 1)) $inpnumre = 'inputredoutline';

	if (!empty($inpdate == 1)) $inpdatere = 'inputredoutline';

	if (!empty($inpcvc == 1)) $inpcvcre = 'inputredoutline';

}

// Apple pay

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';

	$applepayuserid = '&userid='.$userinfo['email_hash'];
}


$tpl = file_get_contents('order-template.html');

$body = file_get_contents('order3.html');



$tpl = str_replace('{body}', $body, $tpl);

$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

$tpl = str_replace('{back}', '/order/review/', $tpl);

$tpl = str_replace('{price}', $priceamount, $tpl);

$tpl = str_replace('{commonMsg}', $commonMsg, $tpl);

$tpl = str_replace('{cardError}', $cardError, $tpl);

$tpl = str_replace('{expiryError}', $expiryError, $tpl);

$tpl = str_replace('{cvcError}', $cvcError, $tpl);

$tpl = str_replace('{cardHolderName}', $cardHolderName, $tpl);

$tpl = str_replace('{pan}', $pan, $tpl);

$tpl = str_replace('{error2}', $showerror2, $tpl);

$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);

$tpl = str_replace('{applepayuserid}', $applepayuserid, $tpl);
$tpl = str_replace('{applepayredirectsuccess}', 'https://tikoid.com/order/payment-processing/', $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{loc}', $loc, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{currencycode}', 'USD', $tpl);
$tpl = str_replace('{countrycode}', 'US', $tpl);
$tpl = str_replace('{packagetitle}', $packagetitle, $tpl);
$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='to' AND ((`country` = 'ww' AND `page` = 'order3') OR (`country` = 'ww' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){



if($cinfo['name']=='maincta'){$cinfo['content'] = str_replace('$price',$priceamount,$cinfo['content']);}



    $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);



}



echo $tpl;

