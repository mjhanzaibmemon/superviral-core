<?php

/*

remove $plogordersession
remove $lognow



*/

// start time
$start_time = microtime(true);

error_reporting(E_ALL); // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', false); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
$awsNotNeeded = true; // AWS is not needed in this file
include('header.php');
include('ordercontrol.php');

//ERROR LOGGING

           $plogordersession = serialize($info);
           $lognow = time();


//LOC REDIRECT
$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

// header('Location: /'. $loclinkforward . $locas[$loc]['order']. '/' .$locas[$loc]['order3'] .'/');die;

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1"));

if(!empty($info['upsell'])){

$upsellprice = explode('###',$info['upsell']);

$upsellamount = $upsellprice[0];
$upsellprice = $upsellprice[1];

$finalprice = $packageinfo['price'] + $upsellprice;
$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

}else{

$finalprice = $packageinfo['price'];

}

// upsell add follower

if (!empty($info['upsell_all'])) {



    $upsellprice1 = explode('###', $info['upsell_all']);



    $upsellamount1 = $upsellprice1[0];

    $upsellprice1 = $upsellprice1[1];



    $finalprice = $finalprice + $upsellprice1;

    // $packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

} 


//UPSELL AUTO LIKES
if(!empty($info['upsell_autolikes'])){

$upsellpriceautolikes = explode('###',$info['upsell_autolikes']);

$upsellpriceal = $upsellpriceautolikes[1];

$finalprice = $finalprice + $upsellpriceal;

}


$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);



if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}



$priceamount = $finalprice;


$cardinitypaymentamount = floatval($finalprice);

$errors = [];
$errors00 = [];
$errors1 = [];
$errors2 = [];


//////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';

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

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';


if (array_key_exists('submitForm', $_POST)){
    $submit = addslashes($_POST['submitForm']);
    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'payment_attempts_per_hour' AND `brand`='sv' LIMIT 1");

    sendCloudwatchData('Superviral', 'payment-attempt-cardinity', 'OrderPayment', 'payment-attempt-cardinity-function', 1);

}

if (array_key_exists('cardHoldername', $_POST))$cardholdername = addslashes($_POST['cardHoldername']);
if (array_key_exists('cardNumber', $_POST))$pan = addslashes($_POST['cardNumber']);
if (array_key_exists('CVC', $_POST))$cvc = addslashes($_POST['CVC']);

if (array_key_exists('expDate', $_POST)) {
    $expdate = addslashes($_POST['expDate']);
    if (array_key_exists('cardbrand', $_POST)) {
        $card_brand = addslashes($_POST['cardbrand']);
    }


    $expdate = str_replace(' ', '', $expdate);

    if (strpos($expdate, '/') !== false) {
        $expdateexplode = explode('/', $expdate);
        $expmonth = (int)$expdateexplode[0];
        $expyear = str_replace('20', '', $expdateexplode[1]);
        $expyear = '20' . $expyear;
        $expyear = (int)$expyear;
    }
}

if (array_key_exists('screen_width', $_POST))$screen_width = (INT)addslashes($_POST['screen_width']);
if (array_key_exists('screen_height', $_POST))$screen_height = (INT)addslashes($_POST['screen_height']);
if (array_key_exists('challenge_window_size', $_POST))$challenge_window_size = addslashes($_POST['challenge_window_size']);
if (array_key_exists('browser_language', $_POST))$browser_language = addslashes($_POST['browser_language']);
if (array_key_exists('color_depth', $_POST))$color_depth = (INT)addslashes($_POST['color_depth']);
if (array_key_exists('time_zone', $_POST))$time_zone = (INT)addslashes($_POST['time_zone']);
if (array_key_exists('country', $_POST))$country = addslashes($_POST['country']);
if (array_key_exists('emailaddress', $_POST))$emailaddress = addslashes($_POST['emailaddress']);

//3D Secure V1
if (array_key_exists('PaRes', $_POST))$pares = addslashes($_POST['PaRes']);

//3d Secure V2
if (array_key_exists('cres', $_POST))$cres = addslashes($_POST['cres']);
if (array_key_exists('threeDSSessionData', $_POST))$threeDSSessionData = addslashes($_POST['threeDSSessionData']);


if ((!empty($info['payment_id_crdi'])) || (!empty($info['payment_creq_crdi']))) {
    $paymentid = $info['payment_id_crdi'];
    $creq = $info['payment_creq_crdi'];
}


// code existing card

$cardId = addslashes($_POST['selectpaymentmethod']);

$existingCardCvc = addslashes($_POST[$cardId."-CVC"]);
$incorrectCvcAlert = false;
//


/////////////////////////////////////////////////////////////////// CHECK FOR FRAUDULENT PAYMENT CHECK

$paymentdetailscheck=2;//THIS IS DEFAULT

$userBlackList = 0;
$alreadyExist = 0;
$checkforfraudq = mysql_query("SELECT 1 FROM `blacklist` WHERE `emailaddress` 
                                    = '$emailaddress' OR 
                                    `igusername` = '{$info['igusername']}' OR 
                                    `ipaddress` = '{$info['ipaddress']}' LIMIT 1");

if (mysql_num_rows($checkforfraudq) > 0) {
    mysql_query("UPDATE `blacklist` SET `attempts` = `attempts` + 1,  `last_updated` = '". time() ."' WHERE ( `emailaddress` = '$emailaddress' OR `igusername` = '{$info['igusername']}' OR `ipaddress` = '{$info['ipaddress']}' ) LIMIT 1");
    sendCloudwatchData('Superviral', 'blacklist-re-attempt-cardinity', 'OrderPayment', $emailaddress .'-blacklist-re-attempt-cardinity-function', 1);
   
    $alreadyExist = 1;
    $userBlackList = 1;
}

if((!empty($submit))&&($paymentdetailscheck==2)){ //CHECK FOR FRAUDULENT PAYMENT CHECKS NOW

 // check for fraud
$cards_used = $info['cards_used'];
$exp_cards_used = explode(' ', $cards_used);
if(count($exp_cards_used) >= 4){
    $paymentdetailscheck = 1;
}

if($country=='ID')$paymentdetailscheck=1;
if($country=='MA')$paymentdetailscheck=1;
if($country=='CM')$paymentdetailscheck=1;

if($info['payment_attempts'] >= 25){$paymentdetailscheck=1;}

$checkforfraudq = mysql_query("SELECT `emailaddress` FROM `blacklist` WHERE `emailaddress` = '$emailaddress' LIMIT 1");
if(mysql_num_rows($checkforfraudq) > 0) { $paymentdetailscheck=1;  $userBlackList = 1; }

$checkforfraudq = mysql_query("SELECT `igusername` FROM `blacklist` WHERE `igusername` = '{$info['igusername']}' LIMIT 1");
if(mysql_num_rows($checkforfraudq) > 0) { $paymentdetailscheck=1;  $userBlackList = 1; }

$checkforfraudq = mysql_query("SELECT `ipaddress` FROM `blacklist` WHERE `ipaddress` = '{$info['ipaddress']}' LIMIT 1");
if(mysql_num_rows($checkforfraudq) > 0) { $paymentdetailscheck=1;  $userBlackList = 1; }


}



if ($paymentdetailscheck == 1) {

    $nowblacklist = time();

    $currentipaddress = $info['ipaddress'];
    if ($alreadyExist == 0) {
        mysql_query("INSERT INTO `blacklist` SET 
                    `emailaddress` = '$emailaddress', 
                    `igusername` = '{$info['igusername']}', 
                    `ipaddress` = '$currentipaddress',
                    `billingname` = '$cardholdername',
                    `added` = '$nowblacklist',  `last_updated` = '$nowblacklist',  brand = 'sv', `source` = 'order-payment-cardinity'");
        sendCloudwatchData('Superviral', 'blacklist-insert-cardinity', 'OrderPayment', 'blacklist-insert-cardinity-function', 1);
    }
    // // New entry table for blacklist attempts
    // $res = mysql_query("INSERT INTO `blacklist_attempts` SET 
    // `emailaddress` = '$emailaddress', 
    // `igusername` = '{$info['igusername']}', 
    // `ipaddress` = '$currentipaddress', 
    // `billingname` = '$cardholdername', 
    // `added` = '$nowblacklist'");
    if ($userBlackList == 1) {

        sendCloudwatchData('Superviral', 'blacklist-payment-attempt-cardinity', 'OrderPayment', 'blacklist-payment-attempt-cardinity-function', 1);

        // $CommonError = '<div class="emailsuccess emailfailed">We can\'t process your payment at this time, please contact support immediately</div>';
    }
    $showerror0 .= '<div class="emailsuccess emailfailed">Unfortunately, there was an error processing your payment. We cannot process your payment at this time.</div>';
    unset($submit);
}

////////////////////////////////////////////////////////////////////

//// 3D Secure V2
if((!empty($cres))&&(!empty($threeDSSessionData))){$method = new Payment\Finalize($paymentid, $cres,true);}

//// 3D Secure V1
if(!empty($pares)){$method = new Payment\Finalize($paymentid, $pares);}


// get stats	
$statsQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `brand`='sv' AND `type` = 'payment_attempts_per_hour' LIMIT 1");   	
$statsData = mysql_fetch_array($statsQuery);	
$metricCount = $statsData['metric'];	
$recaptchaUrl = "";	
$submitBtn = "";

$allowRecaptcha = false;   	
$ValidateForm = true;

if($metricCount > 17){	
    $recaptchaUrl = '<script src="https://www.google.com/recaptcha/api.js?render='.$googleV3ClientKey.'"></script>';	
    $submitBtn = ' <input id="submitbtnthis" type="submit" class="color4 btn" name="submitbtn" onclick="onSubmitData(event);" value="{maincta}" style="padding: 9px;font-size: 16px;border-radius: 21px !important;">';	
    $allowRecaptcha = true;	

}else{	
    $submitBtn = ' <input id="submitbtnthis" type="submit" class="color4 btn" name="submitbtn" value="{maincta}" style="padding: 9px;font-size: 16px;border-radius: 21px !important;">';	
    $allowRecaptcha = false;
    
}

$submitBtn = '
    <div style="position: relative;">
        '.$submitBtn.'
        <div id="custom-loader"></div>
    </div>
';


//// SUBMIT INFORMATION
if((!empty($submit))&&($paymentdetailscheck==2)){


if($existingCardCvc == "" || $existingCardCvc == null){

        if ($allowRecaptcha) {

            // print_r($_POST);

            // Validate reCAPTCHA v3 response  
            if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {

                // Google reCAPTCHA verification API Request  
                $api_url = 'https://www.google.com/recaptcha/api/siteverify';
                $resq_data = array(
                    'secret' => $googleV3ServerKey,
                    'response' => $_POST['g-recaptcha-response'],
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                );

                $curlConfig = array(
                    CURLOPT_URL => $api_url,
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => $resq_data
                );

                $ch = curl_init();
                curl_setopt_array($ch, $curlConfig);
                $response = curl_exec($ch);
                curl_close($ch);

                // Decode JSON data of API response in array  
                $responseData = json_decode($response);

                // If the reCAPTCHA API response is valid  
                if ($responseData->success) {
                    // Success
                    $nowblacklist = time();
                    if ($responseData->score < 0.5) {

                        // increment admin_stats
                        if ($alreadyExist == 0) {
                            mysql_query("INSERT INTO `blacklist` SET

                        `igusername` = '{$info['igusername']}',
                        `emailaddress` = '$emailaddress',                     
                        `added` = '$nowblacklist', brand = 'sv', `source` = 'order-payment-cardinity', `last_updated` = '$nowblacklist'");
                        }

                        sendCloudwatchData('Superviral', 'blacklist-insert-cardinity', 'OrderPayment', 'blacklist-insert-cardinity-function', 1);


                        $ValidateForm = false;
                        array_push($errors,'Error 1092: If you\'re using private browsing or incognito, please try making a payment without private/incognito mode on your browser.');
                    }
                } else {
                        $ValidateForm = false;
                        array_push($errors,'Error 2292: Something went wrong, please try again.');
                }
            } else {
                $ValidateForm = false;
                array_push($errors,'Error 3292: Something went wrong, please try again.');
            }
        }
        // echo $ValidateForm . 'assd';die;
        if ($ValidateForm) {
            mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'blacklisted_payments_attempts_per_hour'  LIMIT 1");
            //$pan ='3393339333933393';

            $pan = str_replace(' ', '', $pan);

            if ((!empty($pan)) && (is_numeric($pan))) {

                $lastfour = substr(str_replace(' ', '', $pan), -4);
                mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
            }

            if (!empty($cardholdername)) {

                mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
            }

            $cards_used = $info['cards_used'];
            $exp_cards_used = explode(' ', $cards_used);
            if (in_array($lastfour, $exp_cards_used)) {
            } else {
                if (empty($cards_used)) {
                    mysql_query("UPDATE `order_session` SET `cards_used` = '$lastfour' WHERE `id` = '{$info['id']}' LIMIT 1");
                } else {
                    mysql_query("UPDATE `order_session` SET `cards_used` = CONCAT(`cards_used`, ' ', '$lastfour')  WHERE `id` = '{$info['id']}' LIMIT 1");
                }
            }

            if (empty($country)) {
                $country = $locas[$loc]['countrycode'];
            }

            $descriptioncardinity = 'Superviral Order (' . strtoupper(rtrim($locredirect, '.')) . ')';

            $method = new Payment\Create([
                'amount' => $cardinitypaymentamount,
                'currency' => $locas[$loc]['currencypp'],
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
                    "notification_url" => 'https://superviral.io/' . $loclinkforward . $locas[$loc]['order'] . '/payment-secure/?redirectid=' . $info['order_session'] . '&new=true',
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
}    
else{

    $fetchcardinfoq  = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` = '{$userinfo['id']}' AND `id` = '{$cardId}' LIMIT 1");

    if(mysql_num_rows($fetchcardinfoq)==0)
    {
        array_push($errors,'Card not found');
    }
    
    else{

    

    $dbcardinfo = mysql_fetch_array($fetchcardinfoq);

    $existingPaymentId = $dbcardinfo['payment_id'];

    $paymentAttemptsCount = $dbcardinfo['payment_attempts'];
    $borderCssPaymentAttemptsFailed = '';   
    if($paymentAttemptsCount >= 5){
        $borderCssPaymentAttemptsFailed = 'style="border: 1px solid red;"';
    }

    if(!password_verify($existingCardCvc, $dbcardinfo['cvv_hash'])){
        // payment attempt

        if($paymentAttemptsCount >= 5){
            $showerror0 = '<div class="emailsuccess emailfailed">Incorrect CVC, Sorry you exceeded your limit for card '. $dbcardinfo['last_four'] .' !!</div>';
            $incorrectCvcAlert = true;
        }else{
            $updatePaymentAttempt  = mysql_query("UPDATE `save_payment_details` SET payment_attempts = payment_attempts + 1 WHERE `account_id` = '{$userinfo['id']}' AND `id` = '{$cardId}' LIMIT 1");
            $showerror0 = '<div class="emailsuccess emailfailed">Incorrect CVC</div>';
            $incorrectCvcAlert = true;
        }


    }else{
        
        $descriptioncardinity = 'Superviral Order ('.strtoupper(rtrim($locredirect,'.')).')';
        if(empty($country)){$country = $locas[$loc]['countrycode'];}
    
        $method = new Payment\Create([
            'amount' => $cardinitypaymentamount,
            'currency' => $locas[$loc]['currencypp'],
            'settle' => true,
            'description' => $descriptioncardinity,
            'order_id' => $info['order_session'],
            'country' => $country,
            'payment_method' => Payment\Create::RECURRING,
            'payment_instrument' => [
                'payment_id' => $existingPaymentId
            ],
        ]);

    }

   }
}
}






/**
* In case payment could not be processed exception will be thrown.
* In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.
* See Error Codes section for detailed list.
*/

if(((!empty($submit))||(!empty($pares))||((!empty($cres))&&(!empty($threeDSSessionData)))) && $ValidateForm){

try {
    /** @type Cardinity\Method\Payment\Payment */
    
    if(!$incorrectCvcAlert){
        $payment = $client->call($method);


        ///////////    
        
          if(!empty($submit)) {
        
            $status = $payment->getStatus();
            if($status == 'pending')$creq = $payment->getThreeds2Data()->getCreq();
            $paymentId = $payment->getId();
            
        
        
            mysql_query("UPDATE `order_session` SET `payment_id_crdi` = '{$paymentId}', `payment_creq_crdi` = '$creq' WHERE `id` = '{$info['id']}' LIMIT 1");
        
            } else{
        
            $paymentId = $info['payment_id_crdi'];
            $creq = $info['payment_creq_crdi'];
            
            }
        
        //////////

        if((!empty($cres))&&(!empty($threeDSSessionData))){


            $method = new Payment\Finalize(
                $paymentId, // payment object received from API call
                $creq, // payment object received from API call
                true // BOOL `true` to enable 3D secure V2 parameters
            );
            
        }
        $plogordersession = serialize($info);

    
        $status = $payment->getStatus();
    }
   









/////////////


      

    if ($status == 'approved') {

        sendCloudwatchData('Superviral', 'payment-made-cardinity', 'OrderPayment', 'order-payment-form-cardinity-function', 1);
      // Payment is approved

      //   reset payment attempts
    //    $updatePaymentAttempt  = mysql_query("UPDATE `save_payment_details` SET payment_attempts = 0 WHERE `account_id` = '{$userinfo['id']}' AND `id` = '{$cardId}' LIMIT 1");


                // save payment details code 

                // if(addslashes($_POST['savePaymentDetails']) == "Yes"){

                //    $accountId =  $userinfo['id'];
                //    $paymentProcessor = "Cardinity";
                //    $cvcHash = password_hash($cvc, PASSWORD_DEFAULT);

                //    $expirydate4444 = explode('/', $expdate);
                //    $expmonthhash = trim(str_replace(' ','',$expirydate4444[0]));
                //    $expyearhash = trim(str_replace(' ','',$expirydate4444[1]));
                //    $expyearhash = str_replace('20','',$expyearhash);
                //    $expyearhash = str_replace('20','',$expyearhash);
                //    $expyearhash = '20'.$expyearhash;
         
         
                //    if(iconv_strlen($expmonthhash)==1)$expmonthhash = '0'.$expmonthhash;
                //    $expirydays = cal_days_in_month(CAL_GREGORIAN, $expmonthhash, $expyearhash );
                //    $expiryunix = mktime(23, 59, 59, $expmonthhash, $expirydays, $expyearhash);

                //    $queryInsert = mysql_query("INSERT INTO save_payment_details 
                //                                             SET 
                //                                             account_id = '$accountId',
                //                                             payment_id = '$paymentId',
                //                                             cvv_hash = '$cvcHash',
                //                                             payment_processor = '$paymentProcessor',
                //                                             last_four = '$lastfour',
                //                                             expiry_unix = '$expiryunix',
                //                                             card_brand = '$card_brand'
                //                             ");
                // }


                $method = new Payment\Get($paymentId);
                /** @type Cardinity\Method\Payment\Payment */
                $payment = $client->call($method);
                $payment_amount = $payment->getAmount();

    


                //FULFILL ORDER IF AMOUNT IS THE SAME

                $payment_amount1 = $payment_amount * 100;
                $priceamount1 = $priceamount * 100;

                if( abs($payment_amount1 - $priceamount1) < 5) {



                    $code='31c223b5500453655b63bf1521eb268487da3';

                    // echo 'sdf ';die;

                    
                    include('pi/cardinitywebhook.php');


                }




           die;



                        //ONCE FULFILLED REDIRECT


    } elseif ($status == 'pending') {//FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS
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

            $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
            while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

            echo $tpl;

           $plogordersession = serialize($info);
           $lognow = time();

           mysql_query("INSERT INTO `payment_logs` SET 
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
            ");

            die;

        } elseif ($payment->isThreedsV1()) {//FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS
            // Retrieve information for 3D-Secure V1 authorization
            $url = $payment->getAuthorizationInformation()->getUrl();
            $data = $payment->getAuthorizationInformation()->getData();
            $callback_url = 'https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/payment-secure/?redirectid='.$info['order_session'].'&new=true';
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

            $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
            while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

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
            `error` = ''    
            ");


            die;


        }
        


    }


        } catch (Cardinity\Exception\InvalidAttributeValue $exception) {
            foreach ($exception->getViolations() as $key => $violation) {

                $ii =0;

                $propertypath = $violation->getPropertyPath();

                if((strpos($propertypath, 'pan') !== false)){array_push($errors1, $violation->getMessage());$ii=1;$inpnum = 1;}
                if((strpos($propertypath, 'exp_year') !== false)){array_push($errors2, 'Expiry Date: '.$violation->getMessage());$ii=1;$inpdate = 1; }
                if((strpos($propertypath, 'exp_month') !== false)){array_push($errors2, 'Expiry Date: '.$violation->getMessage());$ii=1;$inpdate = 1; }
                if((strpos($propertypath, 'cvc') !== false)){array_push($errors2, 'CVC/CVV: '.$violation->getMessage());$ii=1;$inpcvc = 1; }

                if($ii==0){array_push($errors, $violation->getPropertyPath() . ' ' . $violation->getMessage());}

                $ii = 0;

            }


        } catch (Cardinity\Exception\ValidationFailed $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, ''. $error['message']);
            }

            mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");

        } catch (Cardinity\Exception\Declined $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'You failed to authorize your payment through your bank: '.$error['message']);
            }

            mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");

        } catch (Cardinity\Exception\NotFound $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'The card information could not be found. '.$error['message']);
            }

            mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");
            
        } catch (Cardinity\Exception\Unauthorized $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'Your card information was missing or wrong: '.$error['message']);
            }

            mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");

        } catch (Cardinity\Exception\Forbidden $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'You do not have access to this resource: '.$error['message']);
            }
        } catch (Cardinity\Exception\MethodNotAllowed $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'You tried to access a resource using an invalid HTTP method: '.$error['message']);
            }
        } catch (Cardinity\Exception\InternalServerError $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'We had a problem on our end. Try again later: '.$error['message']);
            }
        } catch (Cardinity\Exception\NotAcceptable $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'Wrong Accept headers sent in the request: '.$error['message']);
            }
        } catch (Cardinity\Exception\ServiceUnavailable $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, 'We\'re temporarily off-line for maintenance. Please try again later: '.$error['message']);
            }
        } catch (Exception $exception) {
            $errors = [
                $exception->getMessage(),
                //$exception->getPrevious()->getMessage()
            ];
        }


  
}

 if (!empty($errors)) {
            foreach ($errors as $pererror){$error0content .= $pererror.'<br>';$errorlog .= $pererror.'\n';}
            if(!empty($error0content))$showerror0 .= '<div class="emailsuccess emailfailed">'.$error0content.'</div>';
    
        }
    if (!empty($errors00)) {
            foreach ($errors00 as $pererror){$error0content .= $pererror.'<br>';$errorlog .= $pererror.'\n';}
            if(!empty($error0content))$showerror0 .= '<div class="emailsuccess emailfailed">'.$error0content.'</div>';
        }
     if (!empty($errors1)) {
            foreach (array_unique($errors1) as $pererror){$error1content .= $pererror.'<br>';$errorlog .= $pererror.'\n';}
            if(!empty($error1content))$showerror1 = '<div class="emailsuccess emailfailed">'.$error1content.'</div>';
        }
     if (!empty($errors2)) {
            foreach (array_unique($errors2) as $pererror){$error2content .= $pererror.'<br>';$errorlog .= $pererror.'\n';}
            if(!empty($error2content))$showerror2 = '<div class="emailsuccess emailfailed">'.$error2content.'</div>';
        }


        if((!empty($errors))||(!empty($errors1))||(!empty($errors2))||(!empty($errors00))){

           $combineerrors = addslashes(serialize($errors).'###'.serialize($errors1).'###'.serialize($errors2).'###'.serialize($errors00));
            

           if(!empty($info['payment_id_crdi']))$paymentIds = $info['payment_id_crdi'];

           $plogordersession = serialize($info);

           mysql_query("INSERT INTO `payment_logs` SET 
            `url` = '$actual_link',
            `ipaddress` = '{$info['ipaddress']}',
            `lastfour` = '$lastfour', 
            `error` = '$combineerrors', 
            `payment_id` = '$paymentIds', 
            `added` = '$lognow',
            `expdate` = '$expdate',
            `cvc` = '', 
            `message` = 'Failed - general error', 
            `order_session` = '$plogordersession'  
            ");

           sendCloudwatchData('Superviral', 'cardinity-payment-form-error', 'OrderPayment', 'cardinity-payment-form-error-function', 1);

        }



if(!empty($inpnum == 1))$inpnumre = 'inputredoutline';
if(!empty($inpdate == 1))$inpdatere = 'inputredoutline';
if(!empty($inpcvc == 1))$inpcvcre = 'inputredoutline';




// if login: display checkbox else not
$checkForSavePaymentChecbox = "";

// if($loggedin==true) {

//     $checkForSavePaymentChecbox = '<span class="label" data-toggle="tooltip" title="Securely save card payment for faster purchase">
//                                     Securely save card payment for faster purchase:</span>
//                                     <input type="checkbox" style="width: 12px;" id ="savePaymentDetails" name="savePaymentDetails" value="No">';
// }

//////////////////////// check saved cards

$checkforsavedcards = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` != '0' AND `account_id` = '{$userinfo['id']}' ORDER BY `id` DESC");
//$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '' AND `approved` = '1' ORDER BY `primarycard` DESC");
$countCard = mysql_num_rows($checkforsavedcards);
$countCard = 0; // disabled for now
if($countCard !==0)
{
        $l = 0;
        while($cardinfo = mysql_fetch_array($checkforsavedcards)){

          


          if(!empty($cardinfo['card_brand'])){

            if($cardinfo['card_brand']=='Visa')$imgcardbrand = 'visa';
            if($cardinfo['card_brand']=='Mastercard')$imgcardbrand = 'mastercard';
            if($cardinfo['card_brand']=='American Express')$imgcardbrand = 'amex';
            if($cardinfo['card_brand']=='Maestro')$imgcardbrand = 'maestro';

            if(($cardinfo['primarycard']=='1')){//IF PRIMARY CARD IS SET AND SELECTED CARD PAYMENT ISNT THIS ONE
              

              $primaryclass = 'savedcardactive';
              $showerrorshere = '{error0}{error1}{error2}';
            }


            // if((!empty($cardinfo['id']))){

            //   //SET IT IF ITS NOT BEEN SUBMITTED
            //   $info['card_id'] = $cardinfo['id'];
            //   $primaryclass = 'savedcardactive';
            //   $showerrorshere = '{error0}{error1}{error2}';
            // }



            // if($info['card_id']==$cardinfo['id']){$primaryclass = 'savedcardactive';}
          

            $cardbrandset = '<img class="cardbrand" src="/imgs/payment-icons/'.$imgcardbrand.'.svg"> <b>'.$cardinfo['card_brand'].'</b> ';
          }

            
            $nowplus = time() + 2592000;



            //CHECK IF ITS EXPIRING WITHIN THE NEXT 30-DAYS
            if((time() <= $cardinfo['expiry_unix']) && ($cardinfo['expiry_unix'] <= $nowplus)){


              $datediff = time() - $cardinfo['expiry_unix'];
              $calctime = round($datediff / (60 * 60 * 24));

              $expiredmsg = '<div class="expired expiring">Expiring in '.str_replace('-','',$calctime).' days</div>';}

            if(time() > $cardinfo['expiry_unix']){$expiredmsg = '<div class="expired">Expired</div>';$makeprimary = '';}

            if($l == 0){

                $showFirstCard = 'savedcardactive';

             }else{
                $showFirstCard = '';
             }

        $cardresults .= '


        <div onclick="document.getElementById(\'selectpaymentmethod\').value = \''.$cardinfo['ID'].'\';" class="savedcardholder '.$primaryclass.' dshadow '. $showFirstCard .'">

              <div class="savedcards ">'.$cardbrandset.'**** '.$cardinfo['last_four'].$expiredmsg.$makeprimary.'<div class="paywiththis"><div class="paywiththisselected"></div></div></div>

              <div class="savedcardform">

                <div class="payholder" style="float:left;">
                  <svg xmlns="http://www.w3.org/2000/svg" class="pay-icon pay-icon-2" viewBox="0 0 512 512">
                    <title>Lock Closed</title>
                    <path d="M336 208v-95a80 80 0 00-160 0v95" fill="none" stroke="currentColor" stroke-linecap="round"
                      stroke-linejoin="round" stroke-width="32" />
                    <rect x="96" y="208" width="320" height="272" rx="48" ry="48" fill="none" stroke="currentColor"
                      stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                  </svg>
                  <span class="label securityspan" data-toggle="tooltip" title="For security reasons, please re-enter your CVV:">For security reasons, please re-enter your CVC Code:</span>
                  <input id="input4" name="'.$cardinfo['ID'].'-CVC" class="field is-empty input code" placeholder="CVC" value="" autocomplete="cc-csc">
                  </div>

              </div>

        </div>';

          unset($cardbrandset);
          unset($makeprimary);
          unset($primaryclass);
          unset($expiredmsg);
          unset($showerrorshere);
            $l++;
          }//LOOP ENDS HERE

          $secondh2 = 'Choose how you want to pay?';
          $styleCheckCardAvaialble = "display:block;";
          $styledefaultCardAvaialble = "display:none";
          
} else {//NO CARDS FOUND NOW


          $newcardprimaryclass = 'savedcardactive';
          $info['card_id'] = 'new';
          $onlymethodavailable = 'onlymethodavailable';
          $secondh2 = 'Pay securely with card';
          $styleCheckCardAvaialble = "display:none;";
          $styledefaultCardAvaialble = "display:block";
         
}

if($cardId=='new'){//MAKE NEW CARD ACTIVE / ERROR HANDLING:THIS COULD BE SET EITHER FROM NO CARDS FOUND FOR THE ACCOUNT OR THE USER HAS SELECTED A NEW CARD
    $newcardprimaryclass = 'savedcardactive';

}else{
    $tpl = str_replace('{error0}', '', $tpl);
    $tpl = str_replace('{error2}', '', $tpl);
    $tpl = str_replace('{error3}', '', $tpl);
    $tpl = str_replace('{inpnum}', '', $tpl);
    $tpl = str_replace('{inpdate}', '', $tpl);
    $tpl = str_replace('{inpcvc}', '', $tpl);
    $tpl = str_replace('{pan}', '', $tpl);
    $tpl = str_replace('{cardholdername}', '', $tpl);
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';

$applepayuserid = '&userid='.$userinfo['email_hash'];
}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order3-cardinity-2.html');

$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{discounturl}', $discounturl, $tpl);
$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
$tpl = str_replace('{price}', $priceamount, $tpl);
$tpl = str_replace('{packagetitle}', $packagetitle, $tpl);
$tpl = str_replace('{error0}', $showerror0, $tpl);
$tpl = str_replace('{error1}', $showerror1, $tpl);
$tpl = str_replace('{error2}', $showerror2, $tpl);
$tpl = str_replace('{inpnum}', $inpnumre, $tpl);
$tpl = str_replace('{inpdate}', $inpdatere, $tpl);
$tpl = str_replace('{inpcvc}', $inpcvcre, $tpl);
$tpl = str_replace('{pan}', $pan, $tpl);
$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);

$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);
$tpl = str_replace('{loc}', $loc, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{back}','/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/', $tpl);
$tpl = str_replace('{redirect}', 'https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/', $tpl);
$tpl = str_replace('{price}', $priceamount, $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$tpl = str_replace('{applepayredirectsuccess}', 'https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/', $tpl);

$tpl = str_replace('{currencycode}', $locas[$loc]['currencypp'], $tpl);
$tpl = str_replace('{countrycode}', $locas[$loc]['countrycode'], $tpl);
$tpl = str_replace('{applepayuserid}', $applepayuserid, $tpl);
$tpl = str_replace('{userBlackList}', $userBlackList, $tpl);
$tpl = str_replace('{bindSavePaymentCheckbox}', $checkForSavePaymentChecbox, $tpl);
$tpl = str_replace('{cardbrand}', $card_brand, $tpl);
$tpl = str_replace('{secondh2}', $secondh2, $tpl);
$tpl = str_replace('{cardresults}', $cardresults, $tpl);
// $tpl = str_replace('{styleCheckCardAvaialble}', $styleCheckCardAvaialble, $tpl);
// $tpl = str_replace('{styledefaultCardAvaialble}', $styledefaultCardAvaialble, $tpl);

$tpl = str_replace('{newcardprimaryclass}', $newcardprimaryclass, $tpl);
$tpl = str_replace('{onlymethodavailable}', $onlymethodavailable, $tpl);
$tpl = str_replace('{submitBtn}', $submitBtn, $tpl);
$tpl = str_replace('{googlev3recaptchakey}', $googleV3ClientKey, $tpl);
$tpl = str_replace('{recaptchaUrl}', $recaptchaUrl, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){

if($cinfo['name']=='maincta'){$cinfo['content'] = str_replace('$price',$priceamount,$cinfo['content']);}

    $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

}

sendCloudwatchData('Superviral', 'order-payment-cardinity', 'UserFunnel', 'user-funnel-order-finish-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Superviral', 'page-load-order-payment-cardinity', 'PageLoadTiming', 'page-load-order-payment-cardinity-function', number_format($execution_time_sec, 2));


// use Google\Cloud\Translate\V2\TranslateClient;


// if($notenglish==true){

//             // require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

//             // $translate = new TranslateClient(['key' => $googletranslatekey]);

//             // $result = $translate->translate($tpl, [
//             //     'source' => 'en', 
//             //     'target' => $locas[$loc]['sdb'],
//             //     'format' => 'html'
//             // ]);

//             // $tpl = $result['text'];

// }

echo $tpl;
