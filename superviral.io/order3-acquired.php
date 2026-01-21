<?php

// start time
$start_time = microtime(true);

/*



remove $plogordersession

remove $lognow



 */






if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {

    ob_start("ob_gzhandler");

} else {

    ob_start();

}



header('Content-type: text/html; charset=utf-8');



$db = 1;

include 'header.php';

include 'ordercontrol.php';

include 'common/common.php'; // AJ: include common



function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}
function getUserIpInfo($ip)
{
   global $ipinfoToken;

    // Make sure you set your ipinfo token here
    $token = $ipinfoToken;
    if (empty($token)) {
        $token = "YOUR_IPINFO_TOKEN";  // <-- Replace this with your token
    }

    $ip_address = trim($ip);
    $api_url = "https://ipinfo.io/" . $ip_address . "?token=" . $token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Faster timeout
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data;
}
// Get user IP and location info
$ipaddress = getUserIP();
$ipData = getUserIpInfo($ipaddress);

// Check if user is from US and on the payment page
if (!empty($ipData['country']) && strtoupper($ipData['country']) === "US") {
    header("Location: /order/payment-secure/");
    exit;
}else{
    $currentPath = $_SERVER['REQUEST_URI'];

    // Check if the URL matches the old one exactly
    if ($currentPath === '/order/payment/' || $currentPath === '/order/payment') {
        header("Location: /uk/order/payment/");
        exit;
    }
}

// echo $ipaddress . ' ' . strtoupper($ipData['country']);die;

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

//ERROR LOGGING



$plogordersession = serialize($info);

$lognow = time();



///




$diecard = "<style>body{font-family:arial;}</style><b>Unable to process card payment:</b> We're currently upgrading our card payment system so that you can have the best experience on Superviral. Card payments will resume tomorrow at 1:00 PM. Please use ApplePay in the meanwhile. We apologise for any inconvenience this may have caused and look forward to seeing you tomorrow!";

$emergencydie = 0;

//LOC REDIRECT

$locredirect = $loc . '.';

if ($locredirect == 'ww.') {

    $locredirect = '';

}

// check if email and username not empty



if((empty($info['emailaddress'])) || (empty($info['igusername']))){

    header('Location: /'. $loclinkforward . $locas[$loc]['order']. '/' .$locas[$loc]['order1'] .'/');

    die();
}


$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";



$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1"));



if (!empty($info['upsell'])) {



    $upsellprice = explode('###', $info['upsell']);



    $upsellamount = $upsellprice[0];

    $upsellprice = $upsellprice[1];



    $finalprice = $packageinfo['price'] + $upsellprice;

    $packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

} else {



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

if (!empty($info['upsell_autolikes'])) {



    $upsellpriceautolikes = explode('###', $info['upsell_autolikes']);



    $upsellpriceal = $upsellpriceautolikes[1];



    $finalprice = $finalprice + $upsellpriceal;

}



$packagetitle = $packageinfo['amount'] . ' ' . ucwords($packageinfo['type']);



if (!empty($_COOKIE['discount'])) {

    include 'detectdiscount.php';

}

if(!empty($_GET['split']) && $_GET['split'] == 'b'){

	$delFee = number_format(round($packageinfo['price'] * 0.05,2),2);
	$servTax = number_format(round($packageinfo['price'] * 0.05,2),2);

	$finalprice += $delFee + $servTax;
    $splitParam = "&split=b";

}

$priceamount = number_format(floatval($finalprice), 2);

$errors = [];

$errors00 = [];

$errors1 = [];

$errors2 = [];



//////////////////////////////////////////////////////////////////////////////////////////////////////////



//ALL PARAMETERS



if (array_key_exists('submitForm', $_POST)) {

    $submit = addslashes($_POST['submitForm']);

    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'payment_attempts_per_hour' AND `brand`='sv' LIMIT 1");
    
    sendCloudwatchData('Superviral', 'payment-attempt', 'OrderPayment', 'payment-attempt-function', 1);

    sendCloudwatchData('Superviral', 'payment-attempt-per-hour', 'AdminStats', 'payment-attempt-per-hour-function', 1);


    if($emergencydie==1)die($diecard);

}





if (array_key_exists('cardHoldername', $_POST)) {

    $cardholdername = addslashes($_POST['cardHoldername']);

}





if (array_key_exists('cardNumber', $_POST)) {

    $pan = addslashes($_POST['cardNumber']);

}






if (array_key_exists('CVC', $_POST)) {

    $cvc = addslashes($_POST['CVC']);

}



if (array_key_exists('expDate', $_POST)) {

    $expdate = addslashes($_POST['expDate']);



    $expdate = str_replace(' ', '', $expdate);



    if (strpos($expdate, '/') !== false) {

        $expdateexplode = explode('/', $expdate);

        $expmonth = $expdateexplode[0];

        $expyear = str_replace('20', '', $expdateexplode[1]);

        $expyear = '20' . $expyear;

        $expyear = (int) $expyear;

    }

}



if (array_key_exists('screen_width', $_POST)) {

    $screen_width = (int) addslashes($_POST['screen_width']);

}



if (array_key_exists('screen_height', $_POST)) {

    $screen_height = (int) addslashes($_POST['screen_height']);

}



if (array_key_exists('challenge_window_size', $_POST)) {

    $challenge_window_size = addslashes($_POST['challenge_window_size']);

}



if (array_key_exists('browser_language', $_POST)) {

    $browser_language = addslashes($_POST['browser_language']);

}



if (array_key_exists('color_depth', $_POST)) {

    $color_depth = (int) addslashes($_POST['color_depth']);

}



if (array_key_exists('time_zone', $_POST)) {

    $time_zone = (int) addslashes($_POST['time_zone']);

}



if (array_key_exists('country', $_POST)) {

    $country = addslashes($_POST['country']);

}



if (array_key_exists('emailaddress', $_POST)) {

    $emailaddress = addslashes($_POST['emailaddress']);

}



if (array_key_exists('javaenabled', $_POST)) {

    $javaenabled = addslashes($_POST['javaenabled']);

}



if (array_key_exists('useragent', $_POST)) {

    $useragent = addslashes($_POST['useragent']);

}



if ((!empty($info['payment_id_crdi'])) || (!empty($info['payment_creq_crdi']))) {

    $paymentid = $info['payment_id_crdi'];

    $creq = $info['payment_creq_crdi'];

}



/////////////////////////////////////////////////////////////////// CHECK FOR FRAUDULENT PAYMENT CHECK



$paymentdetailscheck = 2; //THIS IS DEFAULT
$userBlackList = 0;
$CommonError = "";
$alreadyExist = 0;
$CommonErrorMsg = "";

$checkforfraudq = mysql_query("SELECT * FROM `blacklist` WHERE `emailaddress` 
                                    = '$emailaddress' OR 
                                    `igusername` = '{$info['igusername']}' OR 
                                    `ipaddress` = '{$info['ipaddress']}' LIMIT 1");

if (mysql_num_rows($checkforfraudq) == '1') {

    mysql_query("UPDATE `blacklist` SET `attempts` = `attempts` + 1,  `last_updated` = '". time() ."' WHERE ( `emailaddress` = '$emailaddress' OR `igusername` = '{$info['igusername']}' OR `ipaddress` = '{$info['ipaddress']}' ) LIMIT 1");
    sendCloudwatchData('Superviral', 'blacklist-re-attempt', 'OrderPayment', $emailaddress .'-blacklist-re-attempt-function', 1);
   
    $alreadyExist = 1;
    $userBlackList = 1;
}

if ((!empty($submit)) && ($paymentdetailscheck == 2)) { //CHECK FOR FRAUDULENT PAYMENT CHECKS NOW

    // check for fraud
    $cards_used = $info['cards_used'];
    $exp_cards_used = explode(' ', $cards_used);
    if(count($exp_cards_used) >= 4){
        $paymentdetailscheck = 1;
    }

   
   if($emergencydie==1)die($diecard); 

    if ($country == 'ID') {

        $paymentdetailscheck = 1;

    }



    if ($country == 'MA') {

        $paymentdetailscheck = 1;

    }



    if ($country == 'CM') {

        $paymentdetailscheck = 1;

    }



    if ($info['payment_attempts'] >= $blacklist_limit) {

        $paymentdetailscheck = 1;

    }



    $checkforfraudq = mysql_query("SELECT `emailaddress` FROM `blacklist` WHERE `emailaddress` = '$emailaddress' LIMIT 1");

    if (mysql_num_rows($checkforfraudq) == '1') {

        $paymentdetailscheck = 1;
        $userBlackList = 1;
    }
    


    $checkforfraudq = mysql_query("SELECT `igusername` FROM `blacklist` WHERE `igusername` = '{$info['igusername']}' LIMIT 1");

    if (mysql_num_rows($checkforfraudq) == '1') {

        $paymentdetailscheck = 1;
        $userBlackList = 1;
    }



    $checkforfraudq = mysql_query("SELECT `ipaddress` FROM `blacklist` WHERE `ipaddress` = '{$info['ipaddress']}' LIMIT 1");

    if (mysql_num_rows($checkforfraudq) == '1') {

        $paymentdetailscheck = 1;
        $userBlackList = 1;
    }
  


}



//

if ($paymentdetailscheck == 1) {



    $nowblacklist = time();



    $currentipaddress = $info['ipaddress'];

    if($alreadyExist == 0){
        mysql_query("INSERT INTO `blacklist` SET

        `emailaddress` = '$emailaddress',

        `igusername` = '{$info['igusername']}',

        `ipaddress` = '$currentipaddress',

        `billingname` = '$cardholdername',

        `added` = '$nowblacklist', `last_updated` = '$nowblacklist',  brand = 'sv', `source` = 'order-payment'");
        sendCloudwatchData('Superviral', 'blacklist-insert', 'OrderPayment', 'blacklist-insert-function', 1);

    }

   



    $showerror0 .= '<div class="emailsuccess emailfailed">We can\'t process your payment at this time, please contact support immediately</div>';

     // blacklist message
       
     if($userBlackList == 1){

        sendCloudwatchData('Superviral', 'blacklist-payment-attempt', 'OrderPayment', 'blacklist-payment-attempt-function', 1);

        $CommonError = '<div class="emailsuccess emailfailed">We can\'t process your payment at this time, please contact support immediately</div>';
     }

    unset($submit);

}



////////////////////////////////////////////////////////////////////

//// SUBMIT INFORMATION



if ((!empty($submit)) && ($paymentdetailscheck == 2)) {



    $pan = str_replace(' ', '', $pan);



    if ((!empty($pan)) && (is_numeric($pan))) {



        $lastfour = substr(str_replace(' ', '', $pan), -4);

        mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");

    }



    if (!empty($cardholdername)) {



        mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");

    }



    if (empty($country)) {

        $country = $locas[$loc]['countrycode'];

    }



    $descriptioncardinity = 'Superviral Order (' . strtoupper(rtrim($locredirect, '.')) . ')';

}



/**

 * In case payment could not be processed exception will be thrown.

 * In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.

 * See Error Codes section for detailed list.

 */


// get stats	
$statsQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `brand`='sv' AND `type` = 'payment_attempts_per_hour' LIMIT 1");   	
$statsData = mysql_fetch_array($statsQuery);	
$metricCount = $statsData['metric'];	
$recaptchaUrl = "";	
$submitBtn = "";

$allowRecaptcha = false;   	
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


if ((!empty($submit))) {

    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'blacklisted_payments_attempts_per_hour'  LIMIT 1"); 

    try {



        /**

         * 'timestamp' should be in format Ymdhms

         * 'request_hash' is to be calculated with company hashcode/passcode

         * 'company_id' has been set in Acquired.Config.php

         * 'company_pass' has been set in Acquired.Config.php

         * 'company_mid_id' has been set in Acquired.Config.php

         * step 0: Very card wheather its 3dv2 or 3dv1

         * step 1: Check customer post data. (merchant action required)

         * step 2: Set Post parameters array

         * step 3: Post parameters by using CURL

         * step 4: Check response

         * step 5: Perform actions based on the result



         **/



        $ValidateForm = true;

        $CardNameError = "";

        $CardNumError = "";

        $CardCvcError = "";

        $CardDateError = "";

        $billingStreetError = "";

        $postCodeError = "";

    

        if ($pan == "") {

            $CardNumError = '<div style= "color:red;">Please enter card number</div>';

            $ValidateForm = false;

            $inpnumre = 'inputredoutline';

        }

        if (strlen($pan) < 12 ||  strlen($pan) > 19) {

            $CardNumError = '<div style= "color:red;">Card number range must be in between 12-19 digit</div>';

            $ValidateForm = false;

            $inpnumre = 'inputredoutline';

        }

        if ($cardholdername == "") {

            $CardNameError = '<div style= "color:red;">Please enter card holder name</div>';

            $ValidateForm = false;

            $inpnamere = "inputredoutline";

        }

    
        if ($expdate == "") {

            $CardDateError = '<div style= "color:red;">Please enter expiry date</div>';

            $ValidateForm = false;

            $inpdatere = "inputredoutline";

        }

        if(strlen($expmonth) != 2){
            $CardDateError = '<div style= "color:red;">Please enter valid expiry date (MM/YY)</div>';
    
            $ValidateForm = false;
    
            $inpdatere = "inputredoutline";
        }

        if(strlen($expyear) != 4){
            $CardDateError = '<div style= "color:red;">Please enter valid expiry date (MM/YY)</div>';
    
            $ValidateForm = false;
    
            $inpdatere = "inputredoutline";
        }

        if ($cvc == "") {

            $CardCvcError = '<div style= "color:red;">Please enter cvc</div>';

            $ValidateForm = false;

            $inpcvcre = "inputredoutline";

        }

        if (strlen($cvc) < 3 || strlen($cvc) > 4) {

            $CardCvcError = '<div style= "color:red;">Please enter 3 or 4 digit cvc</div>';

            $ValidateForm = false;

            $inpcvcre = "inputredoutline";

        }

        if($allowRecaptcha){ 

             
            // Validate reCAPTCHA v3 response  
            if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){  
            
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
                if($responseData->success){ 
                  
                    // Success
                    $nowblacklist = time();
                    if($responseData->score < 0.5){
                        
                        // increment admin_stats
                        if($alreadyExist == 0){
                        mysql_query("INSERT INTO `blacklist` SET

                        `igusername` = '{$info['igusername']}',
                        `emailaddress` = '$emailaddress',                     
                        `added` = '$nowblacklist', brand = 'sv', `source` = 'order-payment', `last_updated` = '$nowblacklist'");
                        }

                        sendCloudwatchData('Superviral', 'blacklist-insert', 'OrderPayment', 'blacklist-insert-function', 1);


                        $ValidateForm = false;
                        $CommonErrorMsg = 'Error 1092: If you\'re using private browsing or incognito, please try making a payment without private/incognito mode on your browser.';   
                        $CommonError = '<div class="emailsuccess emailfailed">' . $CommonErrorMsg . '</div>';
                    }
                
                }else{  
                    $ValidateForm = false;
                    $CommonErrorMsg = 'Error 2292: Something went wrong, please try again.';  
                    $CommonError = '<div class="emailsuccess emailfailed">' . $CommonErrorMsg . '</div>';
                }  
            }else{  
                $ValidateForm = false;
                $CommonErrorMsg = 'Error 3292: Something went wrong, please try again.';  
                $CommonError = '<div class="emailsuccess emailfailed">' . $CommonErrorMsg . '</div>';
            }  



        }


        if ($ValidateForm) {


            $cards_used = $info['cards_used'];
            $exp_cards_used = explode(' ', $cards_used);
            if (in_array($lastfour, $exp_cards_used)) {
            } else {
                if(empty($cards_used)){
                    mysql_query("UPDATE `order_session` SET `cards_used` = '$lastfour' WHERE `id` = '{$info['id']}' LIMIT 1");
                }else{
                    mysql_query("UPDATE `order_session` SET `cards_used` = CONCAT(`cards_used`, ' ', '$lastfour')  WHERE `id` = '{$info['id']}' LIMIT 1");
                }
            }

            // payment logs 

             $plogordersession = serialize($info);
             $lognow = time();
 
  
             $plquery = mysql_query("INSERT INTO `payment_logs` SET 
              `url` = '$actual_link',
              `ipaddress` = '{$info['ipaddress']}',
              `lastfour` = '$lastfour', 
              `message` = 'payment attempt', 
              `payment_id` = '', 
              `added` = '$lognow',
              `expdate` = '$expdate',
              `cvc` = '', 
              `order_session` = '$plogordersession',
              `brand` = 'sv',
              `error` = ''    
            ");
  

            $Common = new Common(); // inatialise common class



            $now = date("Ymdhms");

            $amount = $priceamount;

            $merchant_order_id = $info['order_session'].'-'.time();

            $transaction_type = "AUTH_CAPTURE";

            $subscription_type = "INIT";

            $exp = $expmonth . $expyear;



            //////////////////////// Creating POST Param Array



            $paydata = array(



                "timestamp" => $now,

                "company_id" => $acquiredaccountid,

                "company_pass" => $acquiredcompanypass,

                "company_mid_id" => $locas[$loc]['mid'],

                "vt" => "",

                "useragent" => "",



                "transaction" => array(



                    "merchant_order_id" => $merchant_order_id,

                    "transaction_type" => $transaction_type,

                    "amount" => $amount,

                    "currency_code_iso3" => $locas[$loc]['currencypp'],

                    "subscription_type" => "INIT",

                    "merchant_customer_id" => "",

                    "merchant_custom_1" => "",

                    "merchant_custom_2" => "",

                    "merchant_custom_3" => "",

                ),

                "billing" => array(



                    "cardholder_name" => $cardholdername,

                    "cardnumber" => $pan,

                    "cardcvv" => $cvc,

                    "cardexp" => $exp,

                    "billing_country_code_iso2" => $locas[$loc]['countrycode'],

                    "billing_email" => $emailaddress,



                ),



            );



            //////// Step 1: Verify Card /////////////////



            $method_notification_url = $siteDomain . "/order3-acquired-notification.php";

            $verifyCardData = array(



                "company_id" => $acquiredaccountid,

                "company_mid_id" => $locas[$loc]['mid'], // for testing acquired payment gateway

                "currency_code_iso3" => $locas[$loc]['currencypp'],

                "cardnumber" => $pan,

                "method_notification_url" => $method_notification_url,



            );



            $VerifyCardUrl = $VerifyCardURL; // From DB.php



            $CardContent = json_encode($verifyCardData);



            // Aj Called from common function



            $card_response = $Common->curl_request($VerifyCardURL, $CardContent);



            // Step 2: Based on Card enrolled for 3Dv2 or 3Dv1 response



            if ($card_response["enrolled"] && !empty($card_response["server_trans_id"])) {



                // for 3Dv2

                $method_url = $card_response["method_url"];

                $threeDSMethodData = $card_response["threeDSMethodData"];



                echo "<!DOCTYPE html>

            <html>

            <meta charset=\"ISO-8859-1\">

            <head>

            <title>Sample Open method_url Page</title>

            <script type=\"text/javascript\">

                function OnLoadEvent() {

                    document.getElementById('acs_form').submit();

                }

            </script>

            </head>

            <body onload=\"OnLoadEvent()\">

                <iframe id=\"hidden_iframe\" name=\"hidden_iframe\" style=\"display: none;\"></iframe>

                <form method=\"POST\" id=\"acs_form\" action=\"{$method_url}\" target=\"hidden_iframe\">

                    <input type=\"hidden\" name=\"threeDSMethodData\" value=\"{$threeDSMethodData}\">

                </form>

            </body>

            </html>";



                sleep(1);



                // $_SESSION set in merthod_notification.php

                if (isset($_SESSION["method_url_completion"])) {

                    $method_url_completion = $_SESSION["method_url_completion"];

                } else {

                    $method_url_completion = 3;

                }

                // echo $_SESSION["method_url_completion"];

                $contact_url = $siteDomain . "/order3-acquired-contact.php";

                $challenge_url = $siteDomain . '/order3-acquired-3dv2.php?loc='.$loc.'&redirectid='.$info['order_session'];
                



 /*            if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')$challenge_url = $siteDomain . '/order3-acquired-3dv2-rabban.php?loc='.$loc.'&redirectid='.$info['order_session'];*/
             if($info['emailaddress']=='manjurchowdhury.1@gmail.com')$challenge_url = $siteDomain . '/order3-acquired-3dv2-manjur.php?loc='.$loc.'&redirectid='.$info['order_session'];



                $paydata["tds"]["action"] = "SCA";

                $paydata["tds"]["source"] = "1";

                $paydata["tds"]["type"] = "2";

                $paydata["tds"]["preference"] = "0";

                $paydata["tds"]["server_trans_id"] = $card_response["server_trans_id"];

                $paydata["tds"]["method_url_complete"] = "1";

                $paydata["tds"]["browser_data"]["accept_header"] = $_SERVER['HTTP_ACCEPT'];

                $paydata["tds"]["browser_data"]["color_depth"] = "TWENTY_FOUR_BITS";

                $paydata["tds"]["browser_data"]["ip"] = $_SERVER['REMOTE_ADDR']; // AJ check comment

                $paydata["tds"]["browser_data"]["java_enabled"] = "true";

                $paydata["tds"]["browser_data"]["javascript_enabled"] = "true";

                $paydata["tds"]["browser_data"]["challenge_window_size"] = "WINDOWED_600X400";

                $paydata["tds"]["browser_data"]["language"] = $browser_language;

                $paydata["tds"]["browser_data"]["screen_height"] = $screen_height;

                $paydata["tds"]["browser_data"]["screen_width"] = $screen_width;

                $paydata["tds"]["browser_data"]["user_agent"] = $useragent;

                $paydata["tds"]["browser_data"]["timezone"] = $time_zone;

                $paydata["tds"]["merchant"]["contact_url"] = $contact_url;

                $paydata["tds"]["merchant"]["challenge_url"] = $challenge_url;
            } else {

                // for 3Dv1

                $paydata["tds"]["action"] = "ENQUIRE";

            }



            $request_hash = $Common->request_hash($paydata, $acquiredsecretpasscode);



            $paydata['request_hash'] = $request_hash;



            // print_r($paydata);

            /////////////// POST Param End



            //////////////// Doing Transaction with CURL



            $url = $TransactionURL; // From DB.php



            $content = json_encode($paydata);



            // Aj Called from common function



            $response = $Common->curl_request($url, $content);



            // print_r($response);

            $tdsobj = $response['tds'];



            if (in_array($response['response_code'], array(503))) { // For 3Dv2 return 503



                //The value will post back to challenge url

                $threeDSSessionData["transaction_id"] = $response["transaction_id"];

                $threeDSSessionData["transaction_type"] = $response["transaction_type"];

                $threeDSSessionData["merchant_order_id"] = $response["merchant_order_id"];

                $threeDSSessionData = base64_encode(json_encode($threeDSSessionData));



                echo "<!DOCTYPE html>

                  <html>

                  <head>

                      <title>3D Secure Redirect Page</title>

                      <script type=\"text/javascript\">

                          function OnLoadEvent() {

                              document.getElementById('sca_acs_form').submit();

                          }

                      </script>

                  </head>

                  <body onload=\"OnLoadEvent()\">

                      <form id=\"sca_acs_form\" action=\"{$tdsobj['url']}\" method=\"post\">

                          <input type=\"hidden\" name=\"creq\" value=\"{$tdsobj['creq']}\" />

                          <input type=\"hidden\" name=\"threeDSSessionData\" value=\"{$threeDSSessionData}\" />

                      </form>

                  </body>

                  </html>";

            } else if (in_array($response['response_code'], array(501, 502))) { // For 3Dv1 return 501, 502

                /**

                 *  set MD field

                 *  This field will required for the subsequent SETTLEMENT request.

                 */

                $md['company_id'] = $response['company_id'];

                $md['original_transaction_id'] = $response['transaction_id'];

                $md['merchant_order_id'] = $response['merchant_order_id'];

                $md['amount'] = $response['amount'];

                $md['currency_code_iso3'] = $response['currency_code_iso3'];

                $md['transaction_type'] = $response['transaction_type'];

                //$md must be encrypted and Base64 encoded.

                $md = base64_encode(json_encode($md));



                $termurl = $siteDomain . '/order3-acquired-3dv1.php?loc='.$loc.'&redirectid='.$info['order_session'];

                echo "<!DOCTYPE html>

                  <html>

                  <head>

                      <title>3D Secure Redirect Page</title>

                      <script type=\"text/javascript\">

                          function OnLoadEvent() {

                              document.getElementById('acs_form').submit();

                          }

                      </script>

                  </head>

                  <body onload=\"OnLoadEvent()\">

                      <form id=\"acs_form\" action=\"{$tdsobj['url']}\" method=\"post\">

                          <input type=\"hidden\" name=\"PaReq\" value=\"{$tdsobj['pareq']}\" />

                          <input type=\"hidden\" name=\"TermUrl\" value=\"{$termurl}\" />

                          <input type=\"hidden\" name=\"MD\" value=\"{$md}\" />

                      </form>

                  </body>

                  </html>";

            } else if (in_array($response['response_code'], array(251, 401, 403))) { // fraud
                $nowblacklist = time();

                if($alreadyExist == 0){
                    mysql_query("INSERT INTO `blacklist` SET
            
                    `emailaddress` = '$emailaddress',
            
                    `igusername` = '{$info['igusername']}',
            
                    `ipaddress` = '{$info['ipaddress']}',
            
                    `billingname` = '$cardholdername',
            
                    `added` = '$nowblacklist', `last_updated` = '$nowblacklist',  brand = 'sv', `source` = 'order-payment'");
                    sendCloudwatchData('Superviral', 'blacklist-insert', 'OrderPayment', 'blacklist-insert-function', 1);
            
                }

            } else if ($response["response_message"] == "Transaction Success") { // Normal success without 3D secure

                mysql_query("UPDATE `order_session` SET `payment_attempts` = 0 WHERE `id` = '{$info['id']}' LIMIT 1");

                sendCloudwatchData('Superviral', 'payment-made', 'OrderPayment', 'order-payment-form-function', 1);
                
                // Do on Success

                $code='31c223b5500453655b63bf1521eb268487da3';    

                $paymentId = $response['transaction_id'];
               
                include('pi/cardinitywebhook.php');
                die();

            }

            $query = "INSERT INTO `acquired_payment_logs` SET

        `brand`='sv',
        
        `url` = '$actual_link',

        `ipaddress` = '{$info['ipaddress']}',

        `lastfour` = '$lastfour',

        `msg` = '{$response['response_message']}',

        `payment_id` = '{$response['transaction_id']}',

        `added` = '$lognow',

        `expdate` = '$expdate',

        `cvc` = '',

        `order_session` = '$plogordersession'

        ";

            $res = mysql_query($query);



        }



    } catch (Exception $exception) {





    }

}



if ($_COOKIE["ResponseMessage"] == "Transaction Success" || $response["response_message"] == "Transaction Success") {

    if ($_COOKIE["ResponseMessage"] != "") {

        $CommonErrorMsg = $_COOKIE["ResponseMessage"];

    } else {

        $CommonErrorMsg = $response["response_message"];

    }

    $CommonError = '<div class="emailsuccess">' . $CommonErrorMsg . '</div>';

    } else if ($response["response_message"] == "Error:Invalid cardnumber" || $response["response_message"] == "Error:Card Type/Number Mismatch") {

        $CardNumError = '<div class="emailsuccess emailfailed">' . $response["response_message"] . '</div>';

        $inpnumre = "inputredoutline";
        mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");
    }

     else if ($response["response_message"] == "Error:Invalid cardcvv") {

        $CardCvcError = '<div class="emailsuccess emailfailed">' . $response["response_message"] . '</div>';

        $inpcvcre = "inputredoutline";
        mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");
    } 

     else if ($response["response_message"] == "Error:Invalid or Expired cardexp"  || $response["response_message"] == "Declined:Card Expired") {

        $CardDateError = '<div class="emailsuccess emailfailed">' . $response["response_message"] . '</div>';

        $inpdatere = "inputredoutline";
        mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");

    } else if ($_COOKIE["ResponseMessage"] != "" || $response["response_message"] != "") {

        if($ValidateForm){
            if ($_COOKIE["ResponseMessage"] != "" ) {

                $CommonErrorMsg = $_COOKIE["ResponseMessage"];
        
            } else {
                $CommonErrorMsg = $response["response_message"];
            }
            
        }

   
    if($response["response_message"] == "Pending:Card Enrolled" || $response["response_message"] == "SCA: Challenge Required"){

        $CommonError = '';
    }else{
        if(!empty($CommonErrorMsg)){	
            $CommonError = '<div class="emailsuccess emailfailed">' . $CommonErrorMsg . '</div>';	
        }
        mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");

    }

    

}



//////////////////////////////////////////////////////////////////////////////////////////////////////////



if ($loggedin == true) {

    $displayaccountbtn = 'displayaccountbtn';



    $applepayuserid = '&userid=' . $userinfo['email_hash'];

}

if(!empty($CommonError) || !empty($CardNameError) || !empty($CardNumError) || !empty($CardDateError) || !empty($CardCvcError)){

    sendCloudwatchData('Superviral', 'payment-form-error', 'OrderPayment', 'order-payment-form-error-function', 1);
   
}


$tpl = file_get_contents('order-template.html');

$body = file_get_contents('order3-acquired.html');
//if($_GET['split'] == 'b'){$body = file_get_contents('order3-acquired-b.html');}

$priceamount = sprintf('%0.2f', $priceamount);

$tpl = str_replace('{body}', $body, $tpl);

$tpl = str_replace('{recaptchaUrl}', $recaptchaUrl, $tpl);

$tpl = str_replace('{discounturl}', $discounturl, $tpl);

$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

$tpl = str_replace('{price}', $priceamount, $tpl);

$tpl = str_replace('{packagetitle}', $packagetitle, $tpl);

$tpl = str_replace('{commonError}', $CommonError, $tpl);

$tpl = str_replace('{cardNameError}', $CardNameError, $tpl);

$tpl = str_replace('{cardNumError}', $CardNumError, $tpl);

$tpl = str_replace('{cardDateError}', $CardDateError, $tpl);

$tpl = str_replace('{cardCvcError}', $CardCvcError, $tpl);

$tpl = str_replace('{billingStreetError}', $billingStreetError, $tpl);

$tpl = str_replace('{postCodeError}', $postCodeError, $tpl);

$tpl = str_replace('{inpname}', $inpnamere, $tpl);

$tpl = str_replace('{inpnum}', $inpnumre, $tpl);

$tpl = str_replace('{inpdate}', $inpdatere, $tpl);

$tpl = str_replace('{inpcvc}', $inpcvcre, $tpl);

$tpl = str_replace('{inpstreet}', $inpstreetre, $tpl);

$tpl = str_replace('{inppostcode}', $inppostcodere, $tpl);

$tpl = str_replace('{pan}', $pan, $tpl);

$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);

$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);

$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);

$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);

$tpl = str_replace('{back}', '/' . $loclinkforward. $locas[$loc]['order'] . '/' . $locas[$loc]['order2'] . '/', $tpl);

$tpl = str_replace('{redirect}', 'https://superviral.io/' . $loclinkforward.  $locas[$loc]['order'] . '/' . $locas[$loc]['order3-processing'] . '/', $tpl);

$tpl = str_replace('{price}', $priceamount, $tpl);



$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);



$tpl = str_replace('{applepayredirectsuccess}', 'https://superviral.io/' . $loclinkforward . $locas[$loc]['order'] . '/' . $locas[$loc]['order3-processing'] . '/', $tpl);

$tpl = str_replace('{loc}', $loc, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);


$tpl = str_replace('{currencycode}', $locas[$loc]['currencypp'], $tpl);

$tpl = str_replace('{countrycode}', $locas[$loc]['countrycode'], $tpl);

$tpl = str_replace('{applepayuserid}', $applepayuserid, $tpl);
$tpl = str_replace('{userBlackList}', $userBlackList, $tpl);
$tpl = str_replace('{submitBtn}', $submitBtn, $tpl);
$tpl = str_replace('{googlev3recaptchakey}', $googleV3ClientKey, $tpl);
$tpl = str_replace('{splitParam}', $splitParam, $tpl);

$req_uri = $_SERVER['REQUEST_URI'];
$afterDomain = substr($req_uri,0,strrpos($req_uri,'/'));
$tpl = str_replace('{page_url}', $afterDomain.'/', $tpl);
$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while ($cinfo = mysql_fetch_array($contentq)) {



    if ($cinfo['name'] == 'maincta') {

        $cinfo['content'] = str_replace('$price', $priceamount, $cinfo['content']);

    }



    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);

}



// Clearing Msg Cookie

if (isset($_COOKIE['ResponseMessage'])) {

    setcookie('ResponseMessage', null, -1, '/');

    setcookie("ResponseMessage", "", time() - 3600);

}



use Google\Cloud\Translate\V2\TranslateClient;



if ($notenglish == true) {



    //require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';



    //$translate = new TranslateClient(['key' => $googletranslatekey]);



    //// AJ: commented for now



    // $result = $translate->translate($tpl, [

    //     'source' => 'en',

    //     'target' => $locas[$loc]['sdb'],

    //     'format' => 'html'

    // ]);



    // $tpl = $result['text'];



}

if($packageinfo['socialmedia'] == 'tt'){
	$tpl = str_ireplace("Instagram", "Tiktok", $tpl);
}

sendCloudwatchData('Superviral', 'order-payment', 'UserFunnel', 'user-funnel-order-finish-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Superviral', 'page-load-order-payment', 'PageLoadTiming', 'page-load-order-payment-function', number_format($execution_time_sec, 2));


echo $tpl;
