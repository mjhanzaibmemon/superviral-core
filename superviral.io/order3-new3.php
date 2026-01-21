<?php


 // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development 
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path



if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$alcheckout=1;//to prevent the bypass with onetimetoken occuring on any other page

include('header.php');
include('ordercontrol.php');
include('../crons/emailer.php'); //TO EMAIL ONCE ORDER IS COMPLETE


/////////////////////////////

function encrypt($data, $password){
  $iv = substr(sha1(mt_rand()), 0, 16);
  $password = sha1($password);

  $salt = sha1(mt_rand());
  $saltWithPassword = hash('sha256', $password.$salt);

  $encrypted = openssl_encrypt(
    "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
  );
  $msg_encrypted_bundle = "$iv:$salt:$encrypted";
  return $msg_encrypted_bundle;
}


function decrypt($msg_encrypted_bundle, $password){
  $password = sha1($password);

  $components = explode( ':', $msg_encrypted_bundle );
  $iv            = $components[0];
  $salt          = hash('sha256', $password.$components[1]);
  $encrypted_msg = $components[2];

  $decrypted_msg = openssl_decrypt(
    $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
  );

  if ( $decrypted_msg === false )
    return false;

  $msg = substr( $decrypted_msg, 41 );
  return $decrypted_msg;
}



/////////////////////////////


//LOC REDIRECT
$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

header('Location: /'. $loclinkforward . $locas[$loc]['order']. '/' .$locas[$loc]['order3'] .'/');die;

$id = addslashes($_GET['id']);
$submit = addslashes($_POST['submit']);



////////////////////////// VERIFY AUTO LIKES PACKAGE


$fetchpackageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");

if(mysql_num_rows($fetchpackageinfoq)==0)die('Package not found');

$dbpackageinfo = mysql_fetch_array($fetchpackageinfoq);
$packagetitle = $dbpackageinfo['amount'].' {'.ucwords($dbpackageinfo['type']).' pckg}';
$package_price = $dbpackageinfo['price'];

$totalpriceforusers = $dbpackageinfo['price'];


/// THIS IS FOR THE UPSELL

$discountamount = round($dbpackageinfo['amount'] * 0.50);
$discountoriginal = number_format(round($dbpackageinfo['price'] * 0.50,2),2);
$discountactual = number_format(round($discountoriginal * 0.75,2),2);

if(!empty($info['upsell'])){

$totalpriceforusers = $package_price + $discountactual;

$discounttitle = '{discountpopup}<div class="tickadded"><span class="tick"></span><span style="">{upselladded}</span></div>';
$discountbtn = $currency.$discountactual.$locas[$loc]['currencyend'].'<br><a class="remove" onclick="addpackage(\'1\',\''.$info['order_session'].'\'); return false;" href="#">{upsellremove}</a>';



}else{

$totalpriceforusers = $package_price;

$discounttitle = '{discountpopup}';
$discountbtn = '<a class="btn greenbtn" onclick="addpackage(\'2\',\''.$info['order_session'].'\'); return false;" href="#">{discountbtn}</a>';




}

$totalpricecardinity= floatval($totalpriceforusers);


/// THIS IS FOR FREE AUTO LIKES UPSELL


if(!empty($info['upsell_autolikes'])){//PACKGE IS ACTIVE AND SELECTED

$autolikesdesc = 'style="display:none;"';

$discounttitleautolikes = '<div class="bftag">{altag1} ❤️</div>{altag2}<div class="tickadded"><span class="tick"></span><span style="">{upselladded}</span></div>';


$upsell_autolikesdb = explode('###', $info['upsell_autolikes']);
$upsell_autolikesdbprice = $upsell_autolikesdb[1];


$discountbtnautolikes = $currency.$upsell_autolikesdbprice.$locas[$loc]['currencyend'].'<br><a class="remove" onclick="addautolikes(\'1\',\''.$info['order_session'].'\'); return false;" href="#">{upsellremove}</a>';


}else{//PACKAGE IS NOT ACTIVE, SHOW DEFAULT


$discounttitleautolikes = '<div class="bftag">{altag1} ❤️</div>{altag2}';
$discountbtnautolikes = '<a class="btn greenbtn" onclick="addautolikes(\'2\',\''.$info['order_session'].'\'); return false;" href="#">{aladdfor}</a>';


}





///

if(($dbpackageinfo['type']=='likes')||($dbpackageinfo['type']=='views')){


$back = '/'.$locas[$loc]['order'].'/'.$locas[$loc]['order1select'].'/';

    $chooseposts = explode('~~~', $info['chooseposts']);
    
    foreach($chooseposts as $posts){
    if(empty($posts))continue;

    $posts1 = explode('###',$posts);

    $profilepicture .= '<img class="dp" height="65" width="65" src="'.$posts1[1].'">';
    }



}else{


$back = '/'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/';

$dpimgname = md5($info['order_session'].$info['igusername']);

$searchfordpq = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' LIMIT 1");
if(mysql_num_rows($searchfordpq)==1){

  $profilepicture = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/dp/'.$dpimgname.'.jpg">';}

else{

  $profilepicture = '';
  $triggergetpost = 'getdp();';

  }

    $changeusernamelink = '<a class="changeusernamelink" href="/'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/">(change)</a>';


}

// IF LOGGED IN AND AUTO LIKES NOT ENABLED

if($loggedin==true){
  

if($userinfo['freeautolikes']==1){
$autolikesoffer = 'style="display:none"';

if(!empty($info['upsell_autolikes'])){mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '' WHERE `id` = '{$info['id']}' LIMIT 1");}

$info['upsell_autolikes'] = '';}

$searchordersessionsforalq = mysql_query("SELECT * FROM `order_session` WHERE `upsell_autolikes` != '' AND `order_session` != '{$info['order_session']}' AND `account_id` = '{$userinfo['id']}' LIMIT 2");

if(mysql_num_rows($searchordersessionsforalq)==2)$autolikesoffer = 'style="display:none"';


}

$autolikesoffer = 'style="display:none"';

////////////////////////// DETECT IF THE INTENTION IS TO PAY FOR FREE AUTO LIKES, PAY FOR A NEW AL PACKAGE OR RENEW CURRENT AL WITH FAULTY CARD



require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';

use Cardinity\Client;
use Cardinity\Method\Payment;
use Cardinity\Exception;
use Cardinity\Method\ResultObject;


$client = Client::create([
    'consumerKey' => $cardinitykey,
    'consumerSecret' => $cardinitysecret,
]);


if((!empty($info['payment_id_crdi']))||(!empty($info['payment_creq_crdi']))){
$paymentid = $info['payment_id_crdi'];
$creq = $info['payment_creq_crdi'];}

//3D Secure V1 paremeters
if (array_key_exists('PaRes', $_POST)){$pares = addslashes($_POST['PaRes']);}

//3d Secure V2 paremeters
if (array_key_exists('cres', $_POST))$cres = addslashes($_POST['cres']);
if (array_key_exists('threeDSSessionData', $_POST))$threeDSSessionData = addslashes($_POST['threeDSSessionData']);

//// 3D Secure V2 finalize
if((!empty($cres))&&(!empty($threeDSSessionData))){$method = new Payment\Finalize($paymentid, $cres,true);


}

//// 3D Secure V1 finalize
if(!empty($pares)){

  $method = new Payment\Finalize($paymentid, $pares,true);


}




if(!empty($submit)){//A PAYMENT METHOD HAS BEEN SELECTED and this is a user's actions

//SUBMIT VARIABLES
$info['card_id'] = addslashes($_POST['selectpaymentmethod']);




$errors = [];
$errors00 = [];
$errors1 = [];
$errors2 = [];


// detect payment here

if($info['card_id']=='new'){

         $cardholdername = addslashes($_POST['new-cardHoldername']);
         $pan = addslashes($_POST['new-cardNumber']);
         $cvc = addslashes($_POST['new-CVC']);
         $card_brand = addslashes($_POST['cardbrand']);

         $cardholdername = str_replace('}','',$cardholdername);
         $cardholdername = str_replace('{','',$cardholdername);
         $pan  = str_replace('}','',$pan);
         $pan  = str_replace('{','',$pan);
         $cvc  = str_replace('}','',$cvc);
         $cvc  = str_replace('{','',$cvc);
         $_POST['new-expDate'] = str_replace('{','',$_POST['new-expDate']);
         $_POST['new-expDate'] = str_replace('}','',$_POST['new-expDate']);
         $country = addslashes($_POST['country']);

          if(empty($card_brand))$card_brand = addslashes($_POST['cardbrand1']);

          if (array_key_exists('new-expDate', $_POST)){

            $expdate = addslashes($_POST['new-expDate']);
            $expdate2 = addslashes($_POST['new-expDate']);

          $expdate = str_replace(' ','',$expdate);

              if( strpos( $expdate, '/' ) !== false) {$expdateexplode = explode('/', $expdate);
                  $expmonth = (int)$expdateexplode[0];
                  $expyear = str_replace('20','',$expdateexplode[1]);
                  $expyear = '20'.$expyear;
                  $expyear = (int)$expyear;
              }

          }



          $pan = str_replace(' ','',$pan);



          if((!empty($pan))&&(is_numeric($pan))){

            $lastfour = substr(str_replace(' ','',$pan), -4);
            mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
            $info['lastfour'] = $lastfour;
          }

          if(!empty($cardholdername)){

          mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['payment_billingname_crdi'] = $cardholdername;
          }


          //THE EXPIRY UNIX IS SAVED AT THE BOTTOM



          if(!empty($country)){

          mysql_query("UPDATE `order_session` SET `billing_country` = '{$country}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['billing_country'] = $country;
          }          





}else{//PULL OUT EXISTING CARD INFO




      $fetchcardinfoq  = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '{$userinfo['id']}' AND `id` = '{$info['card_id']}' LIMIT 1");

      if(mysql_num_rows($fetchcardinfoq)==0){$errors[] = 'Card not found';}else{


      $dbcardinfo = mysql_fetch_array($fetchcardinfoq);


      $dbcardinfoid = $dbcardinfo['id'];
      $country = addslashes($dbcardinfo['country']);

         $cardholdername = decrypt($dbcardinfo['billingnamehash'],$billingnamesecretphrase);
         $pan = decrypt($dbcardinfo['longdigitshash'],$longdigitsecretphrase);
          $cvc = addslashes($_POST[$dbcardinfoid.'-CVC']);

          $expdate = str_replace(' ','',decrypt($dbcardinfo['exphash'],$expsecretphrase));

              if( strpos( $expdate, '/' ) !== false) {$expdateexplode = explode('/', $expdate);
                  $expmonth = (int)$expdateexplode[0];
                  $expyear = str_replace('20','',$expdateexplode[1]);
                  $expyear = '20'.$expyear;
                  $expyear = (int)$expyear;
              }

          
            $info['card_id'] = $dbcardinfoid;


          $pan = str_replace(' ','',$pan);

          if((!empty($pan))&&(is_numeric($pan))){

          $lastfour = substr(str_replace(' ','',$pan), -4);
          mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['lastfour'] = $lastfour;
          }

          if(!empty($cardholdername)){

          mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['payment_billingname_crdi'] = $cardholdername;
          }

          if(!empty($dbcardinfo['expiryunix'])){

          mysql_query("UPDATE `order_session` SET `cardexpiringtime` = '{$dbcardinfo['expiryunix']}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['cardexpiringtime'] = $dbcardinfo['expiryunix'];
          }


          if(!empty($dbcardinfo['country'])){

          mysql_query("UPDATE `order_session` SET `billing_country` = '{$dbcardinfo['billing_country']}' WHERE `id` = '{$info['id']}' LIMIT 1");
          $info['billing_country'] = $dbcardinfo['country'];//TEST THIS
          }


    }

}





// in sync regardless of new or existing card, now process the payment

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


              $screen_width = (INT)addslashes($_POST['screen_width']);
              $screen_height = (INT)addslashes($_POST['screen_height']);
              $challenge_window_size = addslashes($_POST['challenge_window_size']);
              $browser_language = addslashes($_POST['browser_language']);
              $color_depth = (INT)addslashes($_POST['color_depth']);
              $time_zone = (INT)addslashes($_POST['time_zone']);
              $emailaddress = $info['emailaddress'];


                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 35; $i++) {
                    $onetimetoken .= $characters[rand(0, $charactersLength - 1)];
                }


               mysql_query("UPDATE `order_session` SET `payment_onetime_token` = '{$onetimetoken}' WHERE `id` = '{$info['id']}' LIMIT 1");

               $info['payment_onetime_token'] = $onetimetoken;

               if($loggedin==true){$redirectoption = '?onetimetoken='.$info['payment_onetime_token'];}
               else{$redirectoption = '?redirectid='.$info['order_session'];}


              if(empty($country)){$country = $locas[$loc]['countrycode'];}



              $descriptioncardinity = 'Superviral Order ('.strtoupper(rtrim($locredirect,'.')).')';

              $method = new Payment\Create([
                  'amount' => $totalpricecardinity,
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
                      "notification_url" => 'https://'.$locredirect.'superviral.io/order/checkout/'.$redirectoption.'#redirecth2', 
                      "browser_info" => [
                          "accept_header" => "text/html",
                          "browser_language" => $browser_language,
                          "browser_language" => $browser_language,
                          "screen_width" => $screen_width,
                          "screen_height" => $screen_height,
                          'challenge_window_size' => $challenge_window_size,
                          "user_agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0",
                          "color_depth" => $color_depth,
                          "time_zone" => $time_zone
                      ],
                      "cardholder_info" =>[
                          "email_address" => $emailaddress
                      ],
                  ],
              ]);

            

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


mysql_query("UPDATE `order_session` SET `card_id` = '{$info['card_id']}'

  WHERE `id` = '{$info['id']}' LIMIT 1");


if(!empty($inpnum == 1))$inpnumre = 'inputredoutline';
if(!empty($inpdate == 1))$inpdatere = 'inputredoutline';
if(!empty($inpcvc == 1))$inpcvcre = 'inputredoutline';


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


/// NOW ITS TIME TO PROCESS THE PAYMENT, OUR SYSTEM WILL AUTOMATICALLY DETECT IF WE NEED TODO ANYTHING WITH 3DS SECURE V1 + V2


if((!empty($submit))||(!empty($pares))||((!empty($cres))&&(!empty($threeDSSessionData)))){

try {


    $payment = $client->call($method);

///////////    

  if(!empty($submit)) {

    $status = $payment->getStatus();
    if($status == 'pending')$creq = $payment->getThreeds2Data()->getCreq();
    $paymentId = $payment->getId();
    $info['payment_id_crdi'] = $paymentId;



    // SAVE CARD DETAILS HERE AUTOMATICALLY REGARDLESS SUCCESSFUL OR NOT
    if($info['card_id']=='new'){



          $info['lastfour'] = $lastfour;
          $info['payment_billingname'] = $cardholdername;

          $billingnamehash = encrypt($cardholdername,$billingnamesecretphrase);
          $longdigitshash = encrypt($pan,$longdigitsecretphrase);
          $lastfourhash = encrypt($lastfour,$lastfoursecretphrase);
          $exphash = encrypt($expdate2,$expsecretphrase);
          

          $expirydate4444 = explode('/', $expdate2);
          $expmonthhash = trim(str_replace(' ','',$expirydate4444[0]));
          $expyearhash = trim(str_replace(' ','',$expirydate4444[1]));
          $expyearhash = str_replace('20','',$expyearhash);
          $expyearhash = str_replace('20','',$expyearhash);
          $expyearhash = '20'.$expyearhash;


          if(iconv_strlen($expmonthhash)==1)$expmonthhash = '0'.$expmonthhash;
          $expirydays = cal_days_in_month(CAL_GREGORIAN, $expmonthhash, $expyearhash );
          $expiryunix = mktime(23, 59, 59, $expmonthhash, $expirydays, $expyearhash);

          $cardexpiryinsertq = "`cardexpiringtime` ='$expiryunix',";

          $info['cardexpiringtime'] = $expiryunix;

          if($userinfo['disablesavepayments']!=='1'){
          //SAVE CARD DETAILS HERE

            if(empty($userinfo['id']))$userinfo['id']=0;

          mysql_query("INSERT INTO `card_details`

            SET 
            `account_id` = '{$userinfo['id']}', 
            `payment_id` = '$paymentId', 
            `order_session` = '{$info['order_session']}', 
            `card_brand` = '$card_brand', 
            `billingnamehash` = '$billingnamehash', 
            `longdigitshash` = '$longdigitshash', 
            `lastfourhash` = '$lastfourhash', 
            `exphash` = '$exphash', 
            `expiryunix` = '$expiryunix',
            `country` = '$country'
            ");}



      }else{



      }



          mysql_query("UPDATE `order_session` SET 
            `payment_id_crdi` = '{$paymentId}', 
            `payment_creq_crdi` = '$creq' 
            WHERE `id` = '{$info['id']}' LIMIT 1");




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

/////////////


    
$status = $payment->getStatus();

    if ($status == 'approved') {



                $method = new Payment\Get($paymentId);

                $payment = $client->call($method);
                $payment_amount = $payment->getAmount();

  

                //FULFILL ORDER IF AMOUNT IS THE SAME

                if(empty($info['card_id']))$info['card_id']='new';

                $payment_amount1 = $payment_amount * 100;
                $priceamount1 = $totalpricecardinity * 100;

                if( abs($payment_amount1 - $priceamount1) < 5) {//SUCCESFULLY MATCHED WITH THE SAME PRICES, with Cardinity and Us


                    ////////////////////#################### PAYMENT SCENERARIOS

                    if($info['card_id']=='new'){//SAVE NEW CARD DETAILS HERE



                      if($userinfo['disablesavepayments']!=='1'){
                      //MYSQL QUERY: SET ALL OF THE CARDHOLDER'S ACCOUNT AS NOT PRIMARY



                        if($loggedin==true){

 

                      mysql_query("UPDATE `card_details` SET `primarycard` = '0' WHERE `account_id` = '{$userinfo['id']}' ");

                      mysql_query("UPDATE `card_details` SET `approved` = '1',`primarycard` = '1' WHERE `account_id` = '{$userinfo['id']}' ORDER BY `id` DESC LIMIT 1");

                    }  else{


                        mysql_query("UPDATE `card_details` SET `approved` = '1' WHERE `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");

                      }
                    

                     }

                    }else{//UPDATE CARD DETAILS THAT ITS BEEN USED AGAIN

                              
                    mysql_query("UPDATE `card_details`

                      SET 
                      `used` = `used` + 1

                      WHERE `id` = '{$info[card_id]}'

                      LIMIT 1

                      ");

                    }



                    //SETTING THE CARD FOR DATABASE INSERTION
                    if($info['card_id']=='new'){
                    
                      if($userinfo['disablesavepayments']!=='1'){
                      $fetchlatestcardq = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '{$userinfo['id']}' ORDER BY `id` DESC LIMIT 1");

                      if(mysql_num_rows($fetchlatestcardq)==1){

                      $fetchlatestcardinfo = mysql_fetch_array($fetchlatestcardq);
                      $info['card_id'] = $fetchlatestcardinfo['id'];

                        }else{$info['card_id'] = '0';}


                      }
                        else{$info['card_id']='0';}

                    }

                   

                      //FULFILL HERE

                    $code='31c223b5500453655b63bf1521eb268487da3';




                    
                    include('pi/cardinitywebhook-2.php');
                    

                }else{die( 'APPROVED1! and <font color="red">PRICE NOT MATCHED</font>');}




           die;



                        //ONCE FULFILLED REDIRECT


    } elseif ($status == 'pending') {//FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS


        mysql_query("UPDATE `order_session` SET `payment_onetime_token_active` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

            // check if passed through 3D secure version 2
            if ($payment->isThreedsV2()) {



                // get data required to finalize payment
                $creq = $payment->getThreeds2Data()->getCreq();
                $paymentId = $payment->getId();
                $url = $payment->getThreeds2Data()->getAcsUrl();
                // finalize process should be done here.

     

          /// 3DS Redirection

          $tpl = file_get_contents('order-template.html');
          $body = file_get_contents('order3-3dsv2.html');

          $tpl = str_replace('{body}', $body, $tpl);
          $tpl = str_replace('{back}', 'https://'.$locredirect.'superviral.io/order/checkout/', $tpl);
          $tpl = str_replace('{creq}', $creq, $tpl);
          $tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
          $tpl = str_replace('{acs_url}', $url, $tpl);
          $tpl = str_replace('{orderid}', $info['order_session'], $tpl);
          $tpl = str_replace('<body>', '<body onload="OnLoadEvent();">', $tpl);

          $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
          while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

          echo $tpl;


          die;

      } elseif ($payment->isThreedsV1()) {//FOUND OUT THE PAYMENT IS PENDING - NOW REDIRECT TO 3D SECURE ACS
          // Retrieve information for 3D-Secure V1 authorization

           if($loggedin==true){$redirectoption = '?onetimetoken='.$info['payment_onetime_token'];}
               else{$redirectoption = '?redirectid='.$info['order_session'];}

          $url = $payment->getAuthorizationInformation()->getUrl();
          $data = $payment->getAuthorizationInformation()->getData();
          $callback_url = 'https://'.$locredirect.'superviral.io/order/checkout/'.$redirectoption.'#redirecth2';
          // finalize process should be done here.


          /// 3DS Redirection

          $tpl = file_get_contents('order-template.html');
          $body = file_get_contents('order3-3dsv1.html');

          $tpl = str_replace('{body}', $body, $tpl);
          $tpl = str_replace('{back}', 'https://'.$locredirect.'superviral.io/order/checkout/', $tpl);
          $tpl = str_replace('{data}', $data, $tpl);
          $tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
          $tpl = str_replace('{acs_url}', $url, $tpl);
          $tpl = str_replace('{callback_url}', $callback_url, $tpl);
          $tpl = str_replace('<body>', '<body onload="OnLoadEvent();">', $tpl);

          $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
          while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

          echo $tpl;

         $plogordersession = serialize($info);
         $lognow = time();

          die;


      }
      


    }


        } catch (Cardinity\Exception\InvalidAttributeValue $exception) {
            foreach ($exception->getViolations() as $key => $violation) {

                $ii =0;

                $propertypath = $violation->getPropertyPath();

                if((strpos($propertypath, 'pan') !== false)){$errors1[] = 'Long card digits: '.$violation->getMessage();$ii=1;$inpnum = 1;}
                if((strpos($propertypath, 'exp_year') !== false)){$errors2[] = 'Expiry Date: '.$violation->getMessage();$ii=1;$inpdate = 1; }
                if((strpos($propertypath, 'exp_month') !== false)){$errors2[] = 'Expiry Date: '.$violation->getMessage();$ii=1;$inpdate = 1; }
                if((strpos($propertypath, 'cvc') !== false)){$errors2[] = 'CVC/CVV: '.$violation->getMessage();$ii=1;$inpcvc = 1; }

                if($ii==0){array_push($errors, $violation->getPropertyPath() . ' ' . $violation->getMessage());}

                $ii = 0;

            }


        } catch (Cardinity\Exception\ValidationFailed $exception) {
            foreach ($exception->getErrors() as $key => $error) {
              if($error['message']=='3D Secure V2 authorization was already attempted'){
                $errors[] = 'You failed to authenticate this payment with your bank. Please try again.';
              }else{
                $errors[] = $error['message'];
              }
            }
        } catch (Cardinity\Exception\Declined $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'Your bank declined the payment, either you\'ve entered incorrect card details, or you need to contact your bank with the following code: '.$error['message'];
            }
        } catch (Cardinity\Exception\NotFound $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'The card information could not be found. '.$error['message'];
            }
        } catch (Cardinity\Exception\Unauthorized $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'Your card information was missing or wrong: '.$error['message'];
            }
        } catch (Cardinity\Exception\Forbidden $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'You do not have access to this resource: '.$error['message'];
            }
        } catch (Cardinity\Exception\MethodNotAllowed $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] ='You tried to access a resource using an invalid HTTP method: '.$error['message'];
            }
        } catch (Cardinity\Exception\InternalServerError $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'We had a problem on our end. Try again later: '.$error['message'];
            }
        } catch (Cardinity\Exception\NotAcceptable $exception) {
            foreach ($exception->getErrors() as $key => $error) {
               $errors[] ='Wrong Accept headers sent in the request: '.$error['message'];

            }
        } catch (Cardinity\Exception\ServiceUnavailable $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                $errors[] = 'We\'re temporarily off-line for maintenance. Please try again later: '.$error['message'];
            }
        }


      

   if (!empty($errors)) {
            foreach ($errors as $pererror){$error0content .= $pererror.'<br>';}
            if(!empty($error0content))$showerror0 .= '<div class="emailsuccess emailfailed">'.$error0content.'</div>';
        }
    if (!empty($errors00)) {
            foreach ($errors00 as $pererror){$error0content .= $pererror.'<br>';}
            if(!empty($error0content))$showerror0 .= '<div class="emailsuccess emailfailed">'.$error0content.'</div>';
        }
     if (!empty($errors1)) {
            foreach (array_unique($errors1) as $pererror){$error1content .= $pererror.'<br>';}
            if(!empty($error1content))$showerror1 = '<div class="emailsuccess emailfailed">'.$error1content.'</div>';
        }
     if (!empty($errors2)) {
            foreach (array_unique($errors2) as $pererror){$error2content .= $pererror.'<br>';}
            if(!empty($error2content))$showerror2 = '<div class="emailsuccess emailfailed">'.$error2content.'</div>';
        }


        if((!empty($errors))||(!empty($errors1))||(!empty($errors2))||(!empty($errors00))){

           $combineerrors = addslashes(serialize($errors).'###'.serialize($errors1).'###'.serialize($errors2).'###'.serialize($errors00));
            

           if(!empty($info['payment_id_crdi']))$paymentIds = $info['payment_id_crdi'];

          mysql_query("INSERT INTO `payment_logs_checkout`
            SET 
            `msg` = '$combineerrors',
            `account_id` = '{$userinfo['id']}',
            `card_id` = '{$info['card_id']}'
            ");


        }



if(!empty($inpnum == 1))$inpnumre = 'inputredoutline';
if(!empty($inpdate == 1))$inpdatere = 'inputredoutline';
if(!empty($inpcvc == 1))$inpcvcre = 'inputredoutline';



}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////// ~~~


//$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` != '0' AND  `account_id` = '{$userinfo['id']}' AND `approved` = '1' ORDER BY `primarycard` DESC");
$checkforsavedcards = mysql_query("SELECT * FROM `card_details` WHERE `account_id` = '100000000000000000000' ORDER BY `primarycard` DESC");


if(mysql_num_rows($checkforsavedcards)!==0)
{

        while($cardinfo = mysql_fetch_array($checkforsavedcards)){

          if(!empty($cardinfo['card_brand'])){

            if($cardinfo['card_brand']=='Visa')$imgcardbrand = 'visa';
            if($cardinfo['card_brand']=='Mastercard')$imgcardbrand = 'mastercard';
            if($cardinfo['card_brand']=='American Express')$imgcardbrand = 'amex';
            if($cardinfo['card_brand']=='Maestro')$imgcardbrand = 'maestro';

            if(($cardinfo['primarycard']=='1')&&(empty($info['card_id']))){//IF PRIMARY CARD IS SET AND SELECTED CARD PAYMENT ISNT THIS ONE
              

              $primaryclass = 'savedcardactive';
              $showerrorshere = '{error0}{error1}{error2}';
            }


            if((!empty($cardinfo['id']))&&($info['card_id']==$cardinfo['id'])){

              //SET IT IF ITS NOT BEEN SUBMITTED
              $info['card_id'] = $cardinfo['id'];
              $primaryclass = 'savedcardactive';
              $showerrorshere = '{error0}{error1}{error2}';
            }



            if($info['card_id']==$cardinfo['id']){$primaryclass = 'savedcardactive';}
          

            $cardbrandset = '<img class="cardbrand" src="/imgs/payment-icons/'.$imgcardbrand.'.svg"> <b>'.$cardinfo['card_brand'].'</b> ';
          }

            
            $nowplus = time() + 2592000;



            //CHECK IF ITS EXPIRING WITHIN THE NEXT 30-DAYS
            if((time() <= $cardinfo['expiryunix']) && ($cardinfo['expiryunix'] <= $nowplus)){


              $datediff = time() - $cardinfo['expiryunix'];
              $calctime = round($datediff / (60 * 60 * 24));

              $expiredmsg = '<div class="expired expiring">Expiring in '.str_replace('-','',$calctime).' days</div>';}

            if(time() > $cardinfo['expiryunix']){$expiredmsg = '<div class="expired">Expired</div>';$makeprimary = '';}

             

        $cardresults .= '


        <div onclick="document.getElementById(\'selectpaymentmethod\').value = \''.$cardinfo['id'].'\';" class="savedcardholder '.$primaryclass.' dshadow">

              <div class="savedcards ">'.$cardbrandset.'**** '.decrypt($cardinfo['lastfourhash'],$lastfoursecretphrase).$expiredmsg.$makeprimary.' <span class="svglock svglockcard"></span><div class="paywiththis"><div class="paywiththisselected"></div></div></div>

              <div class="savedcardform">

                  '.$showerrorshere.'

                  <div class="payholder" style="float:left;">
                  <ion-icon name="lock-closed-outline" class="pay-icon pay-icon-2 md hydrated pay-icon-3" role="img" aria-label="lock closed outline"></ion-icon>
                  <span class="label securityspan" data-toggle="tooltip" title="For security reasons, please re-enter your CVV:">For security reasons, please re-enter your CVC/CVV Code. Usually <b>3-digits</b>, <b>located on the back of your card</b>:</span>
                  <input id="input4" name="'.$cardinfo['id'].'-CVC" class="field is-empty input code" placeholder="***" value="" autocomplete="cc-csc">
                  </div>

              </div>

        </div>';

          unset($cardbrandset);
          unset($makeprimary);
          unset($primaryclass);
          unset($expiredmsg);
          unset($showerrorshere);

          }//LOOP ENDS HERE

          $secondh2 = 'Select payment method:';

} else {//NO CARDS FOUND NOW


          $newcardprimaryclass = 'savedcardactive';
          $info['card_id'] = 'new';
          $onlymethodavailable = 'onlymethodavailable';
          $secondh2 = 'Pay securely with debit / credit card ';


}



if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order3-new2.html');

if($info['card_id']=='new'){//MAKE NEW CARD ACTIVE / ERROR HANDLING:THIS COULD BE SET EITHER FROM NO CARDS FOUND FOR THE ACCOUNT OR THE USER HAS SELECTED A NEW CARD
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


$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);
$tpl = str_replace('{back}',$back, $tpl);

$tpl = str_replace('{packagetitle}',$packagetitle,$tpl);
$tpl = str_replace('{currency}',$currency,$tpl);
$tpl = str_replace('{currencyend}',$locas[$loc]['currencyend'],$tpl);
$tpl = str_replace('{igusername}', $info['igusername'], $tpl);
$tpl = str_replace('{price}',$dbpackageinfo['price'],$tpl);
$tpl = str_replace('{profilepicture}',$profilepicture,$tpl);
$tpl = str_replace('{discounttitle}',$discounttitle,$tpl);
$tpl = str_replace('{discountbtn}',$discountbtn,$tpl);
$tpl = str_replace('{discounttitleautolikes}',$discounttitleautolikes,$tpl);
$tpl = str_replace('{discountbtnautolikes}',$discountbtnautolikes,$tpl);
$tpl = str_replace('{autolikesoffer}',$autolikesoffer,$tpl);
$tpl = str_replace('{autolikesdesc}',$autolikesdesc,$tpl);
$tpl = str_replace('{triggergetpost}',$triggergetpost,$tpl);
$tpl = str_replace('{changeusernamelink}',$changeusernamelink,$tpl);

$tpl = str_replace('{maincta}', 'Pay '.$locas[$loc]['currencysign'].$totalpriceforusers, $tpl);

$tpl = str_replace('{cardresults}', $cardresults, $tpl);
$tpl = str_replace('{newcardprimaryclass}', $newcardprimaryclass, $tpl);
$tpl = str_replace('{onlymethodavailable}', $onlymethodavailable, $tpl);
$tpl = str_replace('{secondh2}', $secondh2, $tpl);

$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{pan}', $pan, $tpl);
$tpl = str_replace('{cardbrand}', $card_brand, $tpl);
$tpl = str_replace('{cardbrand1}', $card_brand1, $tpl);

$tpl = str_replace('{selectpaymentmethod}', $info['card_id'], $tpl);


//SHOW ANY ERRORS
$tpl = str_replace('{error4}', $error4, $tpl);//THIS is for the username
$tpl = str_replace('{error0}', $showerror0, $tpl);
$tpl = str_replace('{error1}', $showerror1, $tpl);
$tpl = str_replace('{error2}', $showerror2, $tpl);
$tpl = str_replace('{inpbn}', $inpbnre, $tpl);
$tpl = str_replace('{inpnum}', $inpnumre, $tpl);
$tpl = str_replace('{inpdate}', $inpdatere, $tpl);
$tpl = str_replace('{inpcvc}', $inpcvcre, $tpl);
$tpl = str_replace('{pan}', $pan, $tpl);
$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);


$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE 
  (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR 
  (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");


while($cinfo = mysql_fetch_array($contentq)){

$foundcontent=0;

if($foundcontent==0)

  {

    $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

  }

}

echo $tpl;
?>