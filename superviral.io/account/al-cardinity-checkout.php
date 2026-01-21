<?php


 // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', true); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development 
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path



if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');

$alcheckout = 1; //to prevent the bypass with onetimetoken occuring on any other page

include('../db.php');
include('auth.php');
include('header.php');
include(dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/emailer.php'); //TO EMAIL ONCE ORDER IS COMPLETE
include '../common/common.php';


use Google\Cloud\Translate\V2\TranslateClient;

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/gtranslate/index.php';

$translate = new TranslateClient(['key' => $googletranslatekey]);

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



/////////////////////////////

function encrypt($data, $password)
{
  $iv = substr(sha1(mt_rand()), 0, 16);
  $password = sha1($password);

  $salt = sha1(mt_rand());
  $saltWithPassword = hash('sha256', $password . $salt);

  $encrypted = openssl_encrypt(
    "$data",
    'aes-256-cbc',
    "$saltWithPassword",
    null,
    $iv
  );
  $msg_encrypted_bundle = "$iv:$salt:$encrypted";
  return $msg_encrypted_bundle;
}


function decrypt($msg_encrypted_bundle, $password)
{
  $password = sha1($password);

  $components = explode(':', $msg_encrypted_bundle);
  $iv            = $components[0];
  $salt          = hash('sha256', $password . $components[1]);
  $encrypted_msg = $components[2];

  $decrypted_msg = openssl_decrypt(
    $encrypted_msg,
    'aes-256-cbc',
    $salt,
    null,
    $iv
  );

  if ($decrypted_msg === false)
    return false;

  $msg = substr($decrypted_msg, 41);
  return $decrypted_msg;
}



/////////////////////////////


//LOC REDIRECT
$locredirect = $loc . '.';
if ($locredirect == 'ww.') $locredirect = '';

$id = addslashes($_GET['id']);
$submit = addslashes($_POST['submit']);
$username = addslashes($_POST['username']);


$findsessionq = mysql_query("SELECT * FROM `automatic_likes_session` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' AND `order_session` = '$id' ORDER BY `id` DESC LIMIT 1");

if (mysql_num_rows($findsessionq) == 0) die('No session found');

$info = mysql_fetch_array($findsessionq);

$account_query = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$info['account_id']}' LIMIT 1");
$account_data = mysql_fetch_array($account_query);
$emailaddress = $account_data['email'];

$alreadyExist = 0;
$userBlacklist = 0;
$checkforfraudq = mysql_query("SELECT * FROM `blacklist` WHERE `emailaddress` 
                                    = '$emailaddress' OR 
                                    `igusername` = '{$username}' OR 
                                    `ipaddress` = '{$info['ipaddress']}' LIMIT 1");
if (mysql_num_rows($checkforfraudq) == '1') {

  mysql_query("UPDATE `blacklist` SET `attempts` = `attempts` + 1,  `last_updated` = '". time() ."' WHERE ( `emailaddress` = '$emailaddress' OR `igusername` = '{$info['igusername']}' OR `ipaddress` = '{$info['ipaddress']}' ) LIMIT 1");

  sendCloudwatchData('Superviral', 'al-blacklist-re-attempt', 'OrderPayment', $emailaddress .'-al-blacklist-re-attempt-function', 1);
 
  $alreadyExist = 1;
}

////////////////////////// VERIFY AUTO LIKES PACKAGE


$fetchpackageinfoq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '{$info['packageid']}' AND `brand`='sv' and `retention` = '0' LIMIT 1");

if (mysql_num_rows($fetchpackageinfoq) == 0) die('Package not found');

$dbpackageinfo = mysql_fetch_array($fetchpackageinfoq);
$al_package_price = $dbpackageinfo['price'];
$al_package_price = floatval($al_package_price);


////////////////////////// DETECT IF THE INTENTION IS TO PAY FOR FREE AUTO LIKES, PAY FOR A NEW AL PACKAGE OR RENEW CURRENT AL WITH FAULTY CARD

if (!empty($info['freeautolikes'])) {

  $checktypeq = mysql_query("SELECT * FROM `automatic_likes` WHERE `id` = '{$info['freeautolikes']}' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

  if (mysql_num_rows($checktypeq) == 1) {

    $checktypeinfo = mysql_fetch_array($checktypeq);

    $fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$info['igusername']}%' ORDER BY `id` DESC LIMIT 1");
    $fetchimg = mysql_fetch_array($fetchimgq);

    $datediff = time() - $checktypeinfo['expires'];
    $calctime = round($datediff / (60 * 60 * 24));
    $calctime = str_replace('-', '', $calctime);



    $h1 = 'Upgrade to continue your auto likes';

    if (time() < $checktypeinfo['expires']) {
      $alexpiringmsg = '<div class="freeautolikesexpiring">Free Auto Likes Expiring in ' . str_replace('-', '', $calctime) . ' days</div>';
    } else {
      $alexpiringmsg = '<div class="freeautolikesexpiring">Free Auto Likes Has Expired</div>';
    }

    $autolikesusernameselected = '<div>
      <span>Your order:</span>
        <div class="autolikesusernameselected">

             <img class="dp" src="https://cdn.superviral.io/dp/' . $fetchimg['dp'] . '.jpg">
             <b>' . $info['igusername'] . '</b> <a href="/' . $loclinkforward . 'account/edit/' . $checktypeinfo['md5'] . '">(manage)</a>
             <ul class="listctn">

                <li><span class="tick"></span><b>Real likes</b> from real users</li>
                <li><span class="tick"></span>Additional <b>' . $calctime . ' days free</b></li>
                <li><span class="tick"></span><b>24/7</b> customer support</li>
                <li><span class="tick"></span><b>Safe & Secure</b> since 2012</li>
                <li><span class="tick"></span><b>30-day</b> moneyback guarantee</li>
                <li><span class="tick"></span>Cancel <b>anytime</b></li>

            </ul>
        </div>
      </div>';


    $igusernamechangeform = 'display:none;';

    $maincta = 'Continue Auto Likes with free ' . str_replace('-', '', $calctime) . ' days';

    $checkoutmode = 'free';
  }
}




if (!empty($info['billingfailure'])) {

  function ago($time)
  {
    $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference     = $now - $time;
    $tense         = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
      $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
      $periods[$j] .= "s";
    }
    return "$difference $periods[$j] ago";
  }


  $checktypeq = mysql_query("SELECT * FROM `automatic_likes` WHERE `autolikes_session` = '{$info['order_session']}' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

  $checktypeinfo = mysql_fetch_array($checktypeq);

  $h1 = 'Payment failed, please use another card';
  $checkoutmode = 'billingfailure';
  $maincta = 'Update my card and resume service';

  $fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$info['igusername']}%' ORDER BY `id` DESC LIMIT 1");
  $fetchimg = mysql_fetch_array($fetchimgq);


  $alexpiringmsg = '<div class="freeautolikesexpiring">Payment failed ' . ago($checktypeinfo['lastbilled']) . '<br> Reason: ' . $info['billingfailure'] . '</div>';

  $autolikesusernameselected = '<div>
      <span>Your order:</span>
        <div class="autolikesusernameselected">

             <img class="dp" src="https://cdn.superviral.io/dp/' . $fetchimg['dp'] . '.jpg">
             <b>' . $info['igusername'] . '</b> <font color="red">(payment failed)</font>

        </div>
      </div>';
}





if (empty($checkoutmode)) { //STANDARD 


  $h1 = 'Get Likes Automatically';
  $maincta = 'Start my Auto Likes';

  $checkoutmode = 'normal';
}

////////////////////////// FORM IS SUBMITTED


// require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';

// use Cardinity\Client;
// use Cardinity\Method\Payment;
// use Cardinity\Exception;
// use Cardinity\Method\ResultObject;


// $client = Client::create([
//     'consumerKey' => $cardinitykey,
//     'consumerSecret' => $cardinitysecret,
// ]);


if ((!empty($info['payment_id_crdi'])) || (!empty($info['payment_creq_crdi']))) {
    $paymentid = $info['payment_id_crdi'];
    $creq = $info['payment_creq_crdi'];
}

//3D Secure V1 paremeters
if (array_key_exists('PaRes', $_POST)) {
    $pares = addslashes($_POST['PaRes']);
}

//3d Secure V2 paremeters
if (array_key_exists('cres', $_POST)) $cres = addslashes($_POST['cres']);
if (array_key_exists('threeDSSessionData', $_POST)) $threeDSSessionData = addslashes($_POST['threeDSSessionData']);

//// 3D Secure V2 finalize
if ((!empty($cres)) && (!empty($threeDSSessionData))) {
    $method = new Payment\Finalize($paymentid, $cres, true);
}

// //// 3D Secure V1 finalize
if (!empty($pares)) {

    $method = new Payment\Finalize($paymentid, $pares, true);
}



if (!empty($submit)) { //A PAYMENT METHOD HAS BEEN SELECTED and this is a user's actions

   // check for fraud
   $cards_used = $info['cards_used'];
   $exp_cards_used = explode(' ', $cards_used);
   
    if(count($exp_cards_used) >= 4){
      
      $userBlacklist = 1;
     
    }

  //SUBMIT VARIABLES
  $info['igusername'] = addslashes($_POST['username']);
  $info['card_id'] = addslashes($_POST['selectpaymentmethod']);
  $info['packageid'] = addslashes($_POST['package']);

  $info['igusername'] = str_replace('@', '', $info['igusername']);


  $fetchpackageinfoq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '{$info['packageid']}' AND `brand`='sv' AND `retention` = '0' LIMIT 1");

  if (mysql_num_rows($fetchpackageinfoq) == 0) die('Package not found');

  $dbpackageinfo = mysql_fetch_array($fetchpackageinfoq);
  $al_package_price = $dbpackageinfo['price'];
  $al_package_price = floatval($al_package_price);

  $errors = [];
  $errors00 = [];
  $errors1 = [];
  $errors2 = [];


  // detect payment here

  if ($info['card_id'] == 'new') {


    $cardholdername = addslashes($_POST['new-cardHoldername']);
    $pan = addslashes($_POST['new-cardNumber']);
    $cvc = addslashes($_POST['new-CVC']);
    $card_brand = addslashes($_POST['cardbrand']);

    $cardholdername = str_replace('}', '', $cardholdername);
    $cardholdername = str_replace('{', '', $cardholdername);
    $pan  = str_replace('}', '', $pan);
    $pan  = str_replace('{', '', $pan);
    $cvc  = str_replace('}', '', $cvc);
    $cvc  = str_replace('{', '', $cvc);
    $_POST['new-expDate'] = str_replace('{', '', $_POST['new-expDate']);
    $_POST['new-expDate'] = str_replace('}', '', $_POST['new-expDate']);
    $country = addslashes($_POST['country']);

    if (empty($card_brand)) $card_brand = addslashes($_POST['cardbrand1']);

    if (array_key_exists('new-expDate', $_POST)) {

      $expdate = addslashes($_POST['new-expDate']);
      $expdate2 = addslashes($_POST['new-expDate']);

      $expdate = str_replace(' ', '', $expdate);

      if (strpos($expdate, '/') !== false) {
        $expdateexplode = explode('/', $expdate);
        $expmonth = (int)$expdateexplode[0];
        $expyear = str_replace('20', '', $expdateexplode[1]);
        $expyear = '20' . $expyear;
        $expyear = (int)$expyear;
      }
    }



    $pan = str_replace(' ', '', $pan);



    if ((!empty($pan)) && (is_numeric($pan))) {

      $lastfour = substr(str_replace(' ', '', $pan), -4);
      mysql_query("UPDATE `automatic_likes_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
      $info['lastfour'] = $lastfour;
    }

    if (!empty($cardholdername)) {

      mysql_query("UPDATE `automatic_likes_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
      $info['payment_billingname_crdi'] = $cardholdername;
    }


    //THE EXPIRY UNIX IS SAVED AT THE BOTTOM



    if (!empty($country)) {

      mysql_query("UPDATE `automatic_likes_session` SET `billing_country` = '{$country}' WHERE `id` = '{$info['id']}' LIMIT 1");
      $info['billing_country'] = $country;
    }
  } else { //PULL OUT EXISTING CARD INFO




    //  $fetchcardinfoq  = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '{$userinfo['id']}' AND `id` = '{$info['card_id']}' LIMIT 1");
    $fetchcardinfoq  = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '###' AND `id` = '{$info['card_id']}' AND `brand`='sv' LIMIT 1");

    if (mysql_num_rows($fetchcardinfoq) == 0) {
      $errors[] = 'Card not found';
    } else {



      $dbcardinfo = mysql_fetch_array($fetchcardinfoq);


      $dbcardinfoid = $dbcardinfo['id'];
      $country = addslashes($dbcardinfo['country']);

      $cardholdername = decrypt($dbcardinfo['billingnamehash'], $billingnamesecretphrase);
      $pan = decrypt($dbcardinfo['longdigitshash'], $longdigitsecretphrase);
      $cvc = addslashes($_POST[$dbcardinfoid . '-CVC']);

      $expdate = str_replace(' ', '', decrypt($dbcardinfo['exphash'], $expsecretphrase));

      if (strpos($expdate, '/') !== false) {
        $expdateexplode = explode('/', $expdate);
        $expmonth = (int)$expdateexplode[0];
        $expyear = str_replace('20', '', $expdateexplode[1]);
        $expyear = '20' . $expyear;
        $expyear = (int)$expyear;
      }





      $pan = str_replace(' ', '', $pan);

      if ((!empty($pan)) && (is_numeric($pan))) {

        $lastfour = substr(str_replace(' ', '', $pan), -4);
        mysql_query("UPDATE `automatic_likes_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
        $info['lastfour'] = $lastfour;
      }

      if (!empty($cardholdername)) {

        mysql_query("UPDATE `automatic_likes_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
        $info['payment_billingname_crdi'] = $cardholdername;
      }

      if (!empty($dbcardinfo['expiryunix'])) {

        mysql_query("UPDATE `automatic_likes_session` SET `cardexpiringtime` = '{$dbcardinfo['expiryunix']}' WHERE `id` = '{$info['id']}' LIMIT 1");
        $info['cardexpiringtime'] = $dbcardinfo['expiryunix'];
      }


      if (!empty($dbcardinfo['country'])) {

        mysql_query("UPDATE `automatic_likes_session` SET `billing_country` = '{$dbcardinfo['billing_country']}' WHERE `id` = '{$info['id']}' LIMIT 1");
        $info['billing_country'] = $dbcardinfo['country']; //TEST THIS
      }
    }
  }

  if($userBlacklist == 1){

      $nowblacklist = time();

      $currentipaddress = $info['ipaddress'];

      if($alreadyExist == 0){
          mysql_query("INSERT INTO `blacklist` SET

          `emailaddress` = '$emailaddress',

          `igusername` = '{$username}',

          `ipaddress` = '$currentipaddress',

          `billingname` = '$cardholdername',

          `added` = '$nowblacklist', `last_updated` = '$nowblacklist',  brand = 'sv', `source` = 'al-order-payment'");
          sendCloudwatchData('Superviral', 'al-blacklist-insert', 'OrderPayment', 'al-blacklist-insert-function', 1);

      }
  }

  $cards_used = $info['cards_used'];
  $exp_cards_used = explode(' ', $cards_used);
  if (in_array($lastfour, $exp_cards_used)) {
  } else {
    if (empty($cards_used)) {
      mysql_query("UPDATE `automatic_likes_session` SET `cards_used` = '$lastfour' WHERE `id` = '{$info['id']}' LIMIT 1");
    } else {
      mysql_query("UPDATE `automatic_likes_session` SET `cards_used` = CONCAT(`cards_used`, ' ', '$lastfour')  WHERE `id` = '{$info['id']}' LIMIT 1");
    }
  }




  // in sync regardless of new or existing card, now process the payment

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////


  $screen_width = (int)addslashes($_POST['screen_width']);
  $screen_height = (int)addslashes($_POST['screen_height']);
  $challenge_window_size = addslashes($_POST['challenge_window_size']);
  $browser_language = addslashes($_POST['browser_language']);
  $color_depth = (int)addslashes($_POST['color_depth']);
  $time_zone = (int)addslashes($_POST['time_zone']);
  $useragent = addslashes($_POST['useragent']);
  $emailaddress = $userinfo['email'];



  if (empty($country)) {
    $country = $locas[$loc]['countrycode'];
  }

  $descriptioncardinity = 'Superviral Order (' . strtoupper(rtrim($locredirect, '.')) . ')';

  if($userBlacklist== 0){
    if (!empty($_POST['username'])) {

      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < 35; $i++) {
        $onetimetoken .= $characters[rand(0, $charactersLength - 1)];
      }
  
  
      mysql_query("UPDATE `automatic_likes_session` SET `payment_onetime_token` = '{$onetimetoken}', payment_onetime_token_active = 1 WHERE `id` = '{$info['id']}' LIMIT 1");
  
      $info['payment_onetime_token'] = $onetimetoken;
      try {
  
            $method = new Payment\Create([
                'amount' => $al_package_price,
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
                    "notification_url" => 'https://superviral.io/account/al-checkout/' . $info['order_session'] . '?onetimetoken=' .$onetimetoken,
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
  
         
      } catch (Exception $exception) {
      }
    } else {
  
      unset($submit);
      $error4 = '<div class="emailsuccess emailfailed">Please enter a Instagram username. This is there we\'ll deliver the automatic likes</div>';
    }
  
  }



  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////





  if (!empty($inpnum == 1)) $inpnumre = 'inputredoutline';
  if (!empty($inpdate == 1)) $inpdatere = 'inputredoutline';
  if (!empty($inpcvc == 1)) $inpcvcre = 'inputredoutline';
} //submit end


mysql_query("UPDATE `automatic_likes_session` SET 

`igusername` = '{$info['igusername']}',
`packageid` = '{$info['packageid']}',
`card_id` = '{$info['card_id']}'

WHERE `account_id` = '{$userinfo['id']}' AND `order_session` = '$id' AND `brand`='sv' LIMIT 1");

// SAVE CARD DETAILS HERE AUTOMATICALLY REGARDLESS SUCCESSFUL OR NOT
if ($info['card_id'] == 'new') {

  $info['lastfour'] = $lastfour;
  $info['payment_billingname'] = $cardholdername;

  if (empty($expdate2)) {
    $expdate2 = $_COOKIE['expdate'];
  }

  $billingnamehash = encrypt($cardholdername, $billingnamesecretphrase);
  $longdigitshash = encrypt($pan, $longdigitsecretphrase);
  $lastfourhash = encrypt($lastfour, $lastfoursecretphrase);
  $exphash = encrypt($expdate2, $expsecretphrase);



  $expirydate4444 = explode('/', $expdate2);
  $expmonthhash = trim(str_replace(' ', '', $expirydate4444[0]));
  $expyearhash = trim(str_replace(' ', '', $expirydate4444[1]));
  $expyearhash = str_replace('20', '', $expyearhash);
  $expyearhash = str_replace('20', '', $expyearhash);

  if (empty($expyearhash)) $expyearhash = '00';

  $expyearhash = '20' . $expyearhash;

  if (empty($expmonthhash)) $expmonthhash = '01';

  if (iconv_strlen($expmonthhash) == 1) $expmonthhash = '0' . $expmonthhash;
  $expirydays = cal_days_in_month(CAL_GREGORIAN, (int)$expmonthhash, $expyearhash);
  $expiryunix = mktime(23, 59, 59, $expmonthhash, $expirydays, $expyearhash);

  $cardexpiryinsertq = "`cardexpiringtime` ='$expiryunix',";

  $info['cardexpiringtime'] = $expiryunix;

  if ($userinfo['disablesavepayments'] == '0' && !empty($paymentId)) {
    //SAVE CARD DETAILS HERE
    mysql_query("INSERT INTO `card_details`

      SET 
      `brand` = 'sv',
      `account_id` = '{$userinfo['id']}', 
      `payment_id` = '$paymentId', 
      `al_order_session` = '{$info['order_session']}', 
      `card_brand` = '$card_brand', 
      `billingnamehash` = '$billingnamehash', 
      `longdigitshash` = '$longdigitshash', 
      `lastfourhash` = '$lastfourhash', 
      `exphash` = '$exphash', 
      `expiryunix` = '$expiryunix',
      `country` = '$country'
      ");
  }
} else {
}

mysql_query("UPDATE `automatic_likes_session` SET 
      `payment_id_crdi` = '{$paymentId}', 
      $cardexpiryinsertq 
     `payment_creq_crdi` = '$creq' 
      WHERE `id` = '{$info['id']}' LIMIT 1");

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


/// NOW ITS TIME TO PROCESS THE PAYMENT, OUR SYSTEM WILL AUTOMATICALLY DETECT IF WE NEED TODO ANYTHING WITH 3DS SECURE V1 + V2


if (((!empty($submit)) || (!empty($pares)) || ((!empty($cres)) && (!empty($threeDSSessionData))))) {

    try {
        /** @type Cardinity\Method\Payment\Payment */

        $payment = $client->call($method);


        ///////////    

        if (!empty($submit)) {

            $status = $payment->getStatus();
            if ($status == 'pending') $creq = $payment->getThreeds2Data()->getCreq();
            $paymentId = $payment->getId();



            mysql_query("UPDATE `order_session` SET `payment_id_crdi` = '{$paymentId}', `payment_creq_crdi` = '$creq' WHERE `id` = '{$info['id']}' LIMIT 1");
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
        $plogordersession = serialize($info);


        $status = $payment->getStatus();
    } catch (Exception $exception) {
    }
}


try {

  if (!empty($paymentId))
    $info['payment_id_crdi'] = $paymentId;


  /////////////

  if ($status == 'approved') {



        $method = new Payment\Get($paymentId);
        /** @type Cardinity\Method\Payment\Payment */
        $payment = $client->call($method);
        $payment_amount = $payment->getAmount();

        //FULFILL ORDER IF AMOUNT IS THE SAME

        $payment_amount1 = $payment_amount * 100;
        $priceamount1 = $al_package_price * 100;

    //FULFILL ORDER IF AMOUNT IS THE SAME

    if (abs($payment_amount1 - $priceamount1) < 5) { //SUCCESFULLY MATCHED WITH THE SAME PRICES, with Cardinity and Us


      ////////////////////#################### PAYMENT SCENERARIOS

      if ($info['card_id'] == 'new') { //SAVE NEW CARD DETAILS HERE

        if ($userinfo['disablesavepayments'] == '0') {
          //MYSQL QUERY: SET ALL OF THE CARDHOLDER'S ACCOUNT AS NOT PRIMARY
          mysql_query("UPDATE `card_details` SET `primarycard` = '0' WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv'");

          //MYSQL QUERY UPDATE THAT THIS CARD IS THE PRIMARY ONE

          mysql_query("UPDATE `card_details` SET `approved` = '1',`primarycard` = '1' WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");
        }
      } else { //UPDATE CARD DETAILS THAT ITS BEEN USED AGAIN


        mysql_query("UPDATE `card_details`

                      SET 
                      `used` = `used` + 1

                      WHERE `id` = '{$info[card_id]}'

                      LIMIT 1

                      ");
      }


      $max_post_per_day = $dbpackageinfo['postlimit'];
      $max_likes_per_day = $dbpackageinfo['amount'] * 1.10;
      $lastbilled = time();

      if ($info['card_id'] == 'new') {

        if ($userinfo['disablesavepayments'] == '0') {
          //    $fetchlatestcardq = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '{$userinfo['id']}' ORDER BY `id` DESC LIMIT 1");
          $fetchlatestcardq = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '###' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

          if (mysql_num_rows($fetchlatestcardq) == 1) {

            $fetchlatestcardinfo = mysql_fetch_array($fetchlatestcardq);
            $info['card_id'] = $fetchlatestcardinfo['id'];
          } else {
            $info['card_id'] = '0';
          }
        } else {
          $info['card_id'] = '0';
        }
      }

      if ($checktypeinfo['cancelbilling'] == 3) {
        $cancelbillingq = "`cancelbilling` = '0',";
        $info['cancelbilling'] == '0';
      }



      if ($checkoutmode == 'free') {

        $nextbilled = time() + (86400 * 29) + (86400 * $calctime);
        $expiry = $nextbilled + 86400;

        $info['igusername'] = str_replace('@', '', $info['igusername']);

        $fulfillupdateq =  mysql_query("UPDATE `automatic_likes`

                              SET 
                              `al_package_id` = '{$dbpackageinfo['id']}',
                              `likes_per_post` = '{$dbpackageinfo['amount']}',
                              `min_likes_per_post` = '{$dbpackageinfo['amount']}',
                              `max_likes_per_post` = '{$max_likes_per_day}',
                              `max_post_per_day` = '{$dbpackageinfo['postlimit']}',
                              `price` = '{$dbpackageinfo['price']}',
                              `disabled` = '0',
                              `payment_id` = '{$info['payment_id_crdi']}',
                              `payment_creq_crdi` = '{$info['payment_creq_crdi']}',
                              `payment_billingname_crdi` = '{$info['payment_billingname_crdi']}',
                              `card_id` = '{$info['card_id']}',
                              `billing_country` = '{$info['billing_country']}',
                              `recurring` = '1',
                              `expires` = '$expiry',
                              `lastbilled` = '$lastbilled',
                              `nextbilled` = '$nextbilled',
                              `cardexpiringtime` = '{$info['cardexpiringtime']}',
                              $cancelbillingq
                              `freeautolikesexpiringemail` = '0',
                              `recordga` = '1',
                              `freeautolikes_session` = '',
                              `cardexpiringemail` = '0',
                              `expiredemail` = '0',
                              `lastfour` = '{$info['lastfour']}' 

                              WHERE `id` = '{$checktypeinfo['id']}' LIMIT 1");

        mysql_query("UPDATE `automatic_likes_session` SET `freeautolikes` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");

        $now = time();
        $thiscurrency = $info['country'];
        $currency =  $locas[$thiscurrency]['currencypp'];

        mysql_query("INSERT INTO `automatic_likes_billing`

                        SET 
                        `brand` = 'sv',
                        `account_id` = '{$info['account_id']}',
                        `igusername` = '{$info['igusername']}',
                        `auto_likes_id` = '{$checktypeinfo['id']}',
                        `likesperpost` = '{$dbpackageinfo['amount']}',
                        `currency` = '$currency',
                        `amount` = '{$dbpackageinfo['price']}',
                        `added` = '$now',
                        `main_payment_id` = '{$info['payment_id_crdi']}',
                        `payment_id` = '{$info['payment_id_crdi']}',
                        `lastfour` = '{$info['lastfour']}',
                        `billingname` = '{$info['payment_billingname_crdi']}'

                        ");

        //EMAIL CUSTOMER

        $subject = 'Automatic Likes #' . $checktypeinfo['id'] . ': Successfully Upgraded';

        $emailbody = '
                        <p>Hi there,</p>
                        <br>
                        <p>We\'re pleased to confirm that your payment was successful! We\'ve successfully upgraded your Automatic Likes Order from free to paid. We\'ve charged you a total of ' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . ' on the card ending with **** ' . $info['lastfour'] . '. Billing name: ' . $info['payment_billingname_crdi'] . '</p>
                        <br>
                        <p>Here is what we\'re delivering to you:</p>
                        <br>

                        <table class="ordertbl">
                          <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                          <tr><td>' . $checktypeinfo['igusername'] . '</td><td>' . $dbpackageinfo['amount'] . ' Automatic Likes</td><td>' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . '</td><td>' . date('jS F Y', $nextbilled) . '</td></tr>
                        </table>

                        <br>
                        <p>You\'ll receive the following benefits:</p>
                        <p><br>
                        - Up to ' . $dbpackageinfo['postlimit'] . '-posts per day<br>
                        - Real likes from real users<br>
                        - Free views on all videos<br>
                        - Safe & Secure since 2012<br>
                        - 24/7 customer support<br>
                        - Cancel anytime you like<br>
                        </p>
                        <br>
                        <p>You can manage your auto likes here:</p>
                        <br>

                        <br>
                          <a href="https://superviral.io/' . $loclinkforward . 'account/edit/' . $checktypeinfo['md5'] . '" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
                        <br>';

        $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
        $emailtpl = str_replace('{body}', $emailbody, $emailtpl);
        $emailtpl = str_replace('Unsubscribe', '', $emailtpl);
        $emailtpl = str_replace('{subject}', $subject, $emailtpl);

        if ($notenglish == true) {


          $result = $translate->translate($subject, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $subject = $result['text'];


          $result = $translate->translate($emailtpl, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $emailtpl = $result['text'];
        }



        emailnow($checktypeinfo['emailaddress'], 'Superviral', 'support@superviral.io', $subject, $emailtpl);

        if (!$fulfillupdateq) die('Error #32246: Approved but not updated - please contact support');



        header('Location: /' . $loclinkforward . 'account/edit/' . $checktypeinfo['md5'] . '?paymentfor=freeautolikes');
      }

      if ($checkoutmode == 'billingfailure') {

        $info['igusername'] = str_replace('@', '', $info['igusername']);

        $nextbilled = time() + (86400 * 29);
        $expiry = $nextbilled + 86400;

        $fulfillupdateq =  mysql_query("UPDATE `automatic_likes`

                              SET 
                              `al_package_id` = '{$dbpackageinfo['id']}',
                              `likes_per_post` = '{$dbpackageinfo['amount']}',
                              `min_likes_per_post` = '{$dbpackageinfo['amount']}',
                              `max_likes_per_post` = '{$max_likes_per_day}',
                              `max_post_per_day` = '{$dbpackageinfo['postlimit']}',
                              `price` = '{$dbpackageinfo['price']}',
                              `disabled` = '0',
                              `payment_id` = '{$info['payment_id_crdi']}',
                              `payment_creq_crdi` = '{$info['payment_creq_crdi']}',
                              `payment_billingname_crdi` = '{$info['payment_billingname_crdi']}',
                              `card_id` = '{$info['card_id']}',
                              `recurring` = '1',
                              `expires` = '$expiry',
                              $cancelbillingq
                              `lastbilled` = '$lastbilled',
                              `nextbilled` = '$nextbilled',
                              `cardexpiringtime` = '{$info['cardexpiringtime']}',
                              `freeautolikesexpiringemail` = '0',
                              `freeautolikes_session` = '',
                              `recordga` = '1',
                              `lastfour` = '{$info['lastfour']}',
                              `billing_country` = '{$info['billing_country']}',
                              `cardexpiringemail` = '0',
                              `expiredemail` = '0',
                              `billingfailure` = '' 

                              WHERE `id` = '{$checktypeinfo['id']}' LIMIT 1");

        mysql_query("UPDATE `automatic_likes_session` SET `billingfailure` = '' WHERE `id` = '{$info['id']}' LIMIT 1");

        if (!$fulfillupdateq) die('Error #39210: Approved but not updated - please contact support');

        $now = time();
        $thiscurrency = $info['country'];
        $currency =  $locas[$thiscurrency]['currencypp'];


        mysql_query("INSERT INTO `automatic_likes_billing`

                        SET 
                        `brand` = 'sv',
                        `account_id` = '{$info['account_id']}',
                        `igusername` = '{$info['igusername']}',
                        `auto_likes_id` = '{$checktypeinfo['id']}',
                        `likesperpost` = '{$dbpackageinfo['amount']}',
                        `currency` = '$currency',
                        `amount` = '{$dbpackageinfo['price']}',
                        `added` = '$now',
                        `main_payment_id` = '{$info['payment_id_crdi']}',
                        `payment_id` = '{$info['payment_id_crdi']}',
                        `lastfour` = '{$info['lastfour']}',
                        `billingname` = '{$info['payment_billingname_crdi']}'

                        ");


        //EMAIL CUSTOMER

        $subject = 'Automatic Likes #' . $checktypeinfo['id'] . ': Payment Issue Fixed';

        $emailbody = '
                        <p>Hi there,</p>
                        <br>
                        <p>Payment Complete! We\'ve successfully made payment for your Automatic Likes Order after we had difficulty in charging your card.</p>
                        <br>
                        <br>
                        <p>We\'ve charged you a total of ' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . ' on the card ending with **** ' . $info['lastfour'] . '. Billing name: ' . $info['payment_billingname_crdi'] . '</p>
                        <br>
                        <p>Here is what we\'re delivering to you:</p>
                        <br>

                        <table class="ordertbl">
                          <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                          <tr><td>' . $checktypeinfo['igusername'] . '</td><td>' . $dbpackageinfo['amount'] . ' Automatic Likes</td><td>' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . '</td><td>' . date('jS F Y', $nextbilled) . '</td></tr>
                        </table>

                        <br>
                        <p>You\'ll receive the following benefits:</p>
                        <p><br>
                        - Up to ' . $dbpackageinfo['postlimit'] . '-posts per day<br>
                        - Real likes from real users<br>
                        - Free views on all videos<br>
                        - Safe & Secure since 2012<br>
                        - 24/7 customer support<br>
                        - Cancel anytime you like<br>
                        </p>
                        <br>
                        <p>You can manage your auto likes here:</p>
                        <br>

                        <br>
                          <a href="https://superviral.io/' . $loclinkforward . 'account/edit/' . $checktypeinfo['md5'] . '" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
                        <br>';

        $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
        $emailtpl = str_replace('{body}', $emailbody, $emailtpl);
        $emailtpl = str_replace('Unsubscribe', '', $emailtpl);
        $emailtpl = str_replace('{subject}', $subject, $emailtpl);


        if ($notenglish == true) {


          $result = $translate->translate($subject, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $subject = $result['text'];


          $result = $translate->translate($emailtpl, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $emailtpl = $result['text'];
        }



        emailnow($checktypeinfo['emailaddress'], 'Superviral', 'support@superviral.io', $subject, $emailtpl);

        header('Location: /' . $loclinkforward . 'account/edit/' . $checktypeinfo['md5'] . '?paymentfor=billingfailure');
      }

      if ($checkoutmode == 'normal') {

        $info['igusername'] = str_replace('@', '', $info['igusername']);

        $nextbilled = time() + (86400 * 29);
        $expiry = $nextbilled + 86400;
        $now = time();

        $fulfillupdatemd5 = md5(time() . $info['igusername'] . $dbpackageinfo['amount'] . $expiry);

        $fulfillupdateq =  mysql_query("INSERT INTO `automatic_likes`

                              SET 
                              `brand` = 'sv',
                              `country` = '{$info['country']}',
                              `account_id` = '{$userinfo['id']}',
                              `al_package_id` = '{$dbpackageinfo['id']}',
                              `md5` = '$fulfillupdatemd5',
                              `igusername` = '{$info['igusername']}',
                              `autolikes_session` = '{$info['order_session']}',
                              `added` = '$now',
                              `expires` = '$expiry',
                              `likes_per_post` = '{$dbpackageinfo['amount']}',
                              `min_likes_per_post` = '{$dbpackageinfo['amount']}',
                              `max_likes_per_post` = '{$max_likes_per_day}',
                              `max_post_per_day` = '{$dbpackageinfo['postlimit']}',
                              `price` = '{$dbpackageinfo['price']}',
                              `fulfill_id` = '',
                              `disabled` = '0',
                              `payment_id` = '{$info['payment_id_crdi']}',
                              `payment_creq_crdi` = '{$info['payment_creq_crdi']}',
                              `payment_billingname_crdi` = '{$info['payment_billingname_crdi']}',
                              `card_id` = '{$info['card_id']}',
                              `billing_country` = '{$info['billing_country']}',
                              `recurring` = '1',
                              `lastbilled` = '$lastbilled',
                              `nextbilled` = '$nextbilled',
                              `cardexpiringtime` = '{$info['cardexpiringtime']}',
                              `freeautolikesexpiringemail` = '0',
                              `recordga` = '1',
                              `lastfour` = '{$info['lastfour']}',
                              `emailaddress` = '{$userinfo['email']}',
                              `cardexpiringemail` = '0',
                              `expiredemail` = '0',
                              `contactnumber` = '{$userinfo['freeautolikesnumber']}'
                              ");


        $fulfillupdateqid = mysql_insert_id();


        if (!$fulfillupdateq) {
          die('Error #33263: Approved but not updated - please contact support');
        }

        $now = time();
        $thiscurrency = $info['country'];
        $currency =  $locas[$thiscurrency]['currencypp'];

        mysql_query("INSERT INTO `automatic_likes_billing`

                        SET 
                        `brand` = 'sv',
                        `account_id` = '{$info['account_id']}',
                        `igusername` = '{$info['igusername']}',
                        `auto_likes_id` = '{$fulfillupdateqid}',
                        `likesperpost` = '{$dbpackageinfo['amount']}',
                        `currency` = '$currency',
                        `amount` = '{$dbpackageinfo['price']}',
                        `added` = '$now',
                        `main_payment_id` = '{$info['payment_id_crdi']}',
                        `payment_id` = '{$info['payment_id_crdi']}',
                        `lastfour` = '{$info['lastfour']}',
                        `billingname` = '{$info['payment_billingname_crdi']}'

                      ");




        //EMAIL CUSTOMER

        $subject = 'Automatic Likes #' . $fulfillupdateqid . ': Payment Successful';

        $emailbody = '
                        <p>Hi there,</p>
                        <br>
                        <p>Payment Complete! We\'ve successfully created a new Automatic Likes Order. We\'ve charged you a total of ' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . ' on the card ending with **** ' . $info['lastfour'] . '. Billing name: ' . $info['payment_billingname_crdi'] . '</p>
                        <br>
                        <p>Here is what we\'re delivering to you:</p>
                        <br>

                        <table class="ordertbl">
                          <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                          <tr><td>' . $info['igusername'] . '</td><td>' . $dbpackageinfo['amount'] . ' Automatic Likes</td><td>' . $locas[$loc]['currencysign'] . $dbpackageinfo['price'] . $locas[$loc]['currencyend'] . '</td><td>' . date('jS F Y', $nextbilled) . '</td></tr>
                        </table>

                        <br>
                        <p>You\'ll receive the following benefits:</p>
                        <br><p>
                        - Up to ' . $dbpackageinfo['postlimit'] . '-posts per day<br>
                        - Real likes from real users<br>
                        - Free views on all videos<br>
                        - Safe & Secure since 2012<br>
                        - 24/7 customer support<br>
                        - Cancel anytime you like<br></p>

                        <br>
                        <p>You can manage your auto likes here:</p>
                        <br>

                        <br>
                          <a href="https://superviral.io/' . $loclinkforward . 'account/edit/' . $fulfillupdatemd5 . '" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
                        <br>';

        $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
        $emailtpl = str_replace('{body}', $emailbody, $emailtpl);
        $emailtpl = str_replace('Unsubscribe', '', $emailtpl);
        $emailtpl = str_replace('{subject}', $subject, $emailtpl);
        $formattedDate = date('d/m/Y h:i A', $info['added']);
        $emailtpl = str_replace('{date_added}', $formattedDate, $emailtpl);

        if ($notenglish == true) {


          $result = $translate->translate($subject, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $subject = $result['text'];


          $result = $translate->translate($emailtpl, [
            'source' => 'en',
            'target' => $locas[$loc]['sdb'],
            'format' => 'html'
          ]);

          $emailtpl = $result['text'];
        }


        emailnow($userinfo['email'], 'Superviral', 'support@superviral.io', $subject, $emailtpl);

				sendCloudwatchData('Superviral', 'al-orders', 'ALOrders', 'al-orders-function', 1);


        header('Location: /' . $loclinkforward . 'account/edit/' . $fulfillupdatemd5 . '?paymentfor=normal');
      }





      //END OF IF SITUATIONS


    } else {
      die('APPROVED1! and <font color="red">PRICE NOT MATCHED</font>');
    }




    die;



    //ONCE FULFILLED REDIRECT


  }elseif ($status == 'pending') {//FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS
        // check if passed through 3D secure version 2
        if ($payment->isThreedsV2()) {

            // get data required to finalize payment
            $creq = $payment->getThreeds2Data()->getCreq();
            $paymentId = $payment->getId();
            $url = $payment->getThreeds2Data()->getAcsUrl();
            // finalize process should be done here.

            // update payment id to order session
            mysql_query("UPDATE `automatic_likes_session` SET `payment_id_crdi` = '{$paymentId}', `payment_creq_crdi` = '$creq' WHERE `id` = '{$info['id']}' LIMIT 1");

           //echo $creq;

            /// 3DS Redirection
            $tpl = file_get_contents('al-checkout-3ds2.html');

            // $tpl = str_replace('{body}', $body, $tpl);
            // $tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
            $tpl = str_replace('{back}', '/account/automatic-likes/', $tpl);
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
            $callback_url = 'https://superviral.io/account/al-checkout/'.$info['order_session'] .'?onetimetoken=' . $onetimetoken;
            // finalize process should be done here.
            
            // update payment id to order session
            mysql_query("UPDATE `automatic_likes_session` SET `payment_id_crdi` = '{$paymentId}', `payment_creq_crdi` = '$creq' WHERE `id` = '{$info['id']}' LIMIT 1");


            /// 3DS Redirection
            $tpl = file_get_contents('al-checkout-3ds1.html');

            // $tpl = str_replace('{body}', $body, $tpl);
            // $tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
            $tpl = str_replace('{back}', '/account/automatic-likes/', $tpl);
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

    mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");
} catch (Cardinity\Exception\Declined $exception) {
    foreach ($exception->getErrors() as $key => $error) {
        array_push($errors, 'You failed to authorize your payment through your bank: ' . $error['message']);
    }

    mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");
} catch (Cardinity\Exception\NotFound $exception) {
    foreach ($exception->getErrors() as $key => $error) {
        array_push($errors, 'The card information could not be found. ' . $error['message']);
    }

    mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");
} catch (Cardinity\Exception\Unauthorized $exception) {
    foreach ($exception->getErrors() as $key => $error) {
        array_push($errors, 'Your card information was missing or wrong: ' . $error['message']);
    }

    mysql_query("UPDATE `order_session` SET `payment_attempts` = `payment_attempts` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");
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
} catch (Exception $exception) {
    $errors = [
        $exception->getMessage(),
        //$exception->getPrevious()->getMessage()
    ];
}



if (!empty($errors)) {
  foreach ($errors as $pererror) {
    $error0content .= $pererror . '<br>';
  }
  if (!empty($error0content)) $showerror0 .= '<div class="emailsuccess emailfailed">' . $error0content . '</div>';
}
if (!empty($errors00)) {
  foreach ($errors00 as $pererror) {
    $error0content .= $pererror . '<br>';
  }
  if (!empty($error0content)) $showerror0 .= '<div class="emailsuccess emailfailed">' . $error0content . '</div>';
}
if (!empty($errors1)) {
  foreach (array_unique($errors1) as $pererror) {
    $error1content .= $pererror . '<br>';
  }
  if (!empty($error1content)) $showerror1 = '<div class="emailsuccess emailfailed">' . $error1content . '</div>';
}
if (!empty($errors2)) {
  foreach (array_unique($errors2) as $pererror) {
    $error2content .= $pererror . '<br>';
  }
  if (!empty($error2content)) $showerror2 = '<div class="emailsuccess emailfailed">' . $error2content . '</div>';
}


if ((!empty($errors)) || (!empty($errors1)) || (!empty($errors2)) || (!empty($errors00))) {

  $combineerrors = addslashes(serialize($errors) . '###' . serialize($errors1) . '###' . serialize($errors2) . '###' . serialize($errors00));


  if (!empty($info['payment_id_crdi'])) $paymentIds = $info['payment_id_crdi'];
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



////////////////////////// IF USERNAME IS NOT SET THEN GET IT FROM THE USER


if (empty($info['igusername'])) {

  $info['igusername'] = $userinfo['username'];
}


////////////////////////// IF USERNAME IS NOT SET THEN GET IT FROM THE USER



$allpackagesq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `retention` = '0' AND `brand`='sv' ORDER BY `amount` ASC");

while ($allpackages = mysql_fetch_array($allpackagesq)) {

  if ($info['packageid'] == $allpackages['id']) $packageselected = 'selected="selected"';

  $packages .= '

        <option name="packages" value="' . $allpackages['id'] . '" ' . $packageselected . '>' . $allpackages['amount'] . ' likes per post - ' . $locas[$loc]['currencysign'] . $allpackages['price'] . $locas[$loc]['currencyend'] . '/mo</option>

        ';

  unset($packageselected);
}


////////////////////////


//$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` != '0' AND `account_id` = '{$userinfo['id']}' AND `approved` = '1' ORDER BY `primarycard` DESC");
$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` != '0' AND `account_id` = '###' AND `approved` = '1' AND `brand`='sv' ORDER BY `primarycard` DESC");
//$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '' AND `approved` = '1' ORDER BY `primarycard` DESC");

if (mysql_num_rows($checkforsavedcards) !== 0) {

  while ($cardinfo = mysql_fetch_array($checkforsavedcards)) {




    if (!empty($cardinfo['card_brand'])) {

      if ($cardinfo['card_brand'] == 'Visa') $imgcardbrand = 'visa';
      if ($cardinfo['card_brand'] == 'Mastercard') $imgcardbrand = 'mastercard';
      if ($cardinfo['card_brand'] == 'American Express') $imgcardbrand = 'amex';
      if ($cardinfo['card_brand'] == 'Maestro') $imgcardbrand = 'maestro';

      if (($cardinfo['primarycard'] == '1') && (empty($info['card_id']))) { //IF PRIMARY CARD IS SET AND SELECTED CARD PAYMENT ISNT THIS ONE


        $primaryclass = 'savedcardactive';
        $showerrorshere = '{error0}{error1}{error2}';
      }


      if ((!empty($cardinfo['id'])) && ($info['card_id'] == $cardinfo['id'])) {

        //SET IT IF ITS NOT BEEN SUBMITTED
        $info['card_id'] = $cardinfo['id'];
        $primaryclass = 'savedcardactive';
        $showerrorshere = '{error0}{error1}{error2}';
      }



      if ($info['card_id'] == $cardinfo['id']) {
        $primaryclass = 'savedcardactive';
      }


      $cardbrandset = '<img class="cardbrand" src="/imgs/payment-icons/' . $imgcardbrand . '.svg"> <b>' . $cardinfo['card_brand'] . '</b> ';
    }


    $nowplus = time() + 2592000;



    //CHECK IF ITS EXPIRING WITHIN THE NEXT 30-DAYS
    if ((time() <= $cardinfo['expiryunix']) && ($cardinfo['expiryunix'] <= $nowplus)) {


      $datediff = time() - $cardinfo['expiryunix'];
      $calctime = round($datediff / (60 * 60 * 24));

      $expiredmsg = '<div class="expired expiring">Expiring in ' . str_replace('-', '', $calctime) . ' days</div>';
    }

    if (time() > $cardinfo['expiryunix']) {
      $expiredmsg = '<div class="expired">Expired</div>';
      $makeprimary = '';
    }



    $cardresults .= '


        <div onclick="document.getElementById(\'selectpaymentmethod\').value = \'' . $cardinfo['id'] . '\';" class="savedcardholder ' . $primaryclass . ' dshadow">

              <div class="savedcards ">' . $cardbrandset . '**** ' . decrypt($cardinfo['lastfourhash'], $lastfoursecretphrase) . $expiredmsg . $makeprimary . '<div class="paywiththis"><div class="paywiththisselected"></div></div></div>

              <div class="savedcardform">

                  ' . $showerrorshere . '

                  <div class="payholder" style="float:left;">
                  <ion-icon name="lock-closed-outline" class="pay-icon pay-icon-2 md hydrated pay-icon-3" role="img" aria-label="lock closed outline"></ion-icon>
                  <span class="label securityspan" data-toggle="tooltip" title="For security reasons, please re-enter your CVV:">For security reasons, please re-enter your CVC Code:</span>
                  <input id="input4" name="' . $cardinfo['id'] . '-CVC" class="field is-empty input code" placeholder="CVC" value="" autocomplete="cc-csc">
                  </div>

              </div>

        </div>';

    unset($cardbrandset);
    unset($makeprimary);
    unset($primaryclass);
    unset($expiredmsg);
    unset($showerrorshere);
  } //LOOP ENDS HERE

  $secondh2 = 'Choose how you want to pay?';
} else { //NO CARDS FOUND NOW


  $newcardprimaryclass = 'savedcardactive';
  $info['card_id'] = 'new';
  $onlymethodavailable = 'onlymethodavailable';
  $secondh2 = 'Pay securely with card';
}


$tpl = file_get_contents('al-checkout.html');

if ($info['card_id'] == 'new') { //MAKE NEW CARD ACTIVE / ERROR HANDLING:THIS COULD BE SET EITHER FROM NO CARDS FOUND FOR THE ACCOUNT OR THE USER HAS SELECTED A NEW CARD
  $newcardprimaryclass = 'savedcardactive';
} else {
  $tpl = str_replace('{error0}', '', $tpl);
  $tpl = str_replace('{error2}', '', $tpl);
  $tpl = str_replace('{error3}', '', $tpl);
  $tpl = str_replace('{inpnum}', '', $tpl);
  $tpl = str_replace('{inpdate}', '', $tpl);
  $tpl = str_replace('{inpcvc}', '', $tpl);
  $tpl = str_replace('{pan}', '', $tpl);
  $tpl = str_replace('{cardholdername}', '', $tpl);
}


$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{back}', 'https://superviral.io/' . $loclinkforward . 'account/automatic-likes/', $tpl);

$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{alexpiringmsg}', $alexpiringmsg, $tpl);
$tpl = str_replace('{autolikesusernameselected}', $autolikesusernameselected, $tpl);
$tpl = str_replace('{igusernamechangeform}', $igusernamechangeform, $tpl);
$tpl = str_replace('{maincta}', $maincta, $tpl);


$tpl = str_replace('{cardresults}', $cardresults, $tpl);
$tpl = str_replace('{newcardprimaryclass}', $newcardprimaryclass, $tpl);
$tpl = str_replace('{onlymethodavailable}', $onlymethodavailable, $tpl);
$tpl = str_replace('{secondh2}', $secondh2, $tpl);

$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{pan}', $pan, $tpl);
$tpl = str_replace('{cardbrand}', $card_brand, $tpl);
$tpl = str_replace('{cardbrand1}', $card_brand1, $tpl);

$tpl = str_replace('{packageid}', $info['packageid'], $tpl);
$tpl = str_replace('{selectpaymentmethod}', $info['card_id'], $tpl);
$tpl = str_replace('{username}', $info['igusername'], $tpl);

//SHOW ANY ERRORS
$tpl = str_replace('{error4}', $error4, $tpl); //THIS is for the username
$tpl = str_replace('{error0}', $showerror0, $tpl);
$tpl = str_replace('{error1}', $showerror1, $tpl);
$tpl = str_replace('{error2}', $showerror2, $tpl);
$tpl = str_replace('{inpnum}', $inpnumre, $tpl);
$tpl = str_replace('{inpdate}', $inpdatere, $tpl);
$tpl = str_replace('{inpcvc}', $inpcvcre, $tpl);
$tpl = str_replace('{pan}', $pan, $tpl);
$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{emailaddress}', $userinfo['emailaddress'], $tpl);
$tpl = str_replace('{commonError}', $CommonError, $tpl);
$tpl = str_replace('{cardNumError}', $CardNumError, $tpl);

$tpl = str_replace('{cardDateError}', $CardDateError, $tpl);

$tpl = str_replace('{cardCvcError}', $CardCvcError, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE  `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = '') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while ($cinfo = mysql_fetch_array($contentq)) {
  $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}



if ($notenglish == true) {





  $result = $translate->translate($tpl, [
    'source' => 'en',
    'target' => $locas[$loc]['sdb'],
    'format' => 'html'
  ]);

  $tpl = $result['text'];
}


echo $tpl;
