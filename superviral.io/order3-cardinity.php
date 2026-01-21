<?php

/*

remove $plogordersession
remove $lognow



*/


 // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', '/var/www/html/errors.log'); // Logging file path


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

//ERROR LOGGING

           $plogordersession = serialize($info);
           $lognow = time();


///





//LOC REDIRECT
$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));

if(!empty($info['upsell'])){

$upsellprice = explode('###',$info['upsell']);

$upsellamount = $upsellprice[0];
$upsellprice = $upsellprice[1];

$finalprice = $packageinfo['price'] + $upsellprice;
$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

}else{

$finalprice = $packageinfo['price'];

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


if (array_key_exists('submit', $_POST))$submit = addslashes($_POST['submit']);

if (array_key_exists('cardHoldername', $_POST))$cardholdername = addslashes($_POST['cardHoldername']);
if (array_key_exists('cardNumber', $_POST))$pan = addslashes($_POST['cardNumber']);
if (array_key_exists('CVC', $_POST))$cvc = addslashes($_POST['CVC']);

if (array_key_exists('expDate', $_POST)){$expdate = addslashes($_POST['expDate']);

$expdate = str_replace(' ','',$expdate);

    if( strpos( $expdate, '/' ) !== false) {$expdateexplode = explode('/', $expdate);
        $expmonth = (int)$expdateexplode[0];
        $expyear = str_replace('20','',$expdateexplode[1]);
        $expyear = '20'.$expyear;
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


if((!empty($info['payment_id_crdi']))||(!empty($info['payment_creq_crdi']))){
$paymentid = $info['payment_id_crdi'];
$creq = $info['payment_creq_crdi'];}


/////////////////////////////////////////////////////////////////// CHECK FOR FRAUDULENT PAYMENT CHECK

$paymentdetailscheck=2;//THIS IS DEFAULT

$userBlackList = 0;

if((!empty($submit))&&($paymentdetailscheck==2)){ //CHECK FOR FRAUDULENT PAYMENT CHECKS NOW

if($country=='ID')$paymentdetailscheck=1;
if($country=='MA')$paymentdetailscheck=1;
if($country=='CM')$paymentdetailscheck=1;



if($info['payment_attempts'] >= $blacklist_limit){$paymentdetailscheck=1;}

$checkforfraudq = mysql_query("SELECT `emailaddress` FROM `blacklist` WHERE `emailaddress` = '$emailaddress' LIMIT 1");
if(mysql_num_rows($checkforfraudq)=='1'){$paymentdetailscheck=1;$userBlackList = 1; }

$checkforfraudq = mysql_query("SELECT `igusername` FROM `blacklist` WHERE `igusername` = '{$info['igusername']}' LIMIT 1");
if(mysql_num_rows($checkforfraudq)=='1'){$paymentdetailscheck=1;$userBlackList = 1; }

$checkforfraudq = mysql_query("SELECT `ipaddress` FROM `blacklist` WHERE `ipaddress` = '{$info['ipaddress']}' LIMIT 1");
if(mysql_num_rows($checkforfraudq)=='1'){$paymentdetailscheck=1;$userBlackList = 1; }

/*$checkforfraudq = mysql_query("SELECT `billingname` FROM `blacklist` WHERE `billingname` LIKE '%$cardholdername%' LIMIT 1");
if(mysql_num_rows($checkforfraudq)=='1')$paymentdetailscheck=1;
*/
}



if($paymentdetailscheck==1){

$nowblacklist = time();

$currentipaddress = $info['ipaddress'];

mysql_query("INSERT INTO `blacklist` SET 
    `emailaddress` = '$emailaddress', 
    `igusername` = '{$info['igusername']}', 
    `ipaddress` = '$currentipaddress',
    `billingname` = '$cardholdername',
    `added` = '$nowblacklist'");

// // New entry table for blacklist attempts
// $res = mysql_query("INSERT INTO `blacklist_attempts` SET 
// `emailaddress` = '$emailaddress', 
// `igusername` = '{$info['igusername']}', 
// `ipaddress` = '$currentipaddress', 
// `billingname` = '$cardholdername', 
// `added` = '$nowblacklist'");

$showerror0 .= '<div class="emailsuccess emailfailed">Unfortunately, there was an error processing your payment. We cannot process your payment at this time.</div>';
unset($submit);
}

////////////////////////////////////////////////////////////////////

//// 3D Secure V2
if((!empty($cres))&&(!empty($threeDSSessionData))){$method = new Payment\Finalize($paymentid, $cres,true);}

//// 3D Secure V1
if(!empty($pares)){$method = new Payment\Finalize($paymentid, $pares);}


//// SUBMIT INFORMATION

if((!empty($submit))&&($paymentdetailscheck==2)){

//$pan ='3393339333933393';

$pan = str_replace(' ','',$pan);

if((!empty($pan))&&(is_numeric($pan))){

$lastfour = substr(str_replace(' ','',$pan), -4);
mysql_query("UPDATE `order_session` SET `lastfour` = '{$lastfour}' WHERE `id` = '{$info['id']}' LIMIT 1");
}

if(!empty($cardholdername)){

mysql_query("UPDATE `order_session` SET `payment_billingname_crdi` = '{$cardholdername}' WHERE `id` = '{$info['id']}' LIMIT 1");
}

if(empty($country)){$country = $locas[$loc]['countrycode'];}

$descriptioncardinity = 'Superviral Order ('.strtoupper(rtrim($locredirect,'.')).')';

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
        "notification_url" => 'https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3'].'/?redirectid='.$info['order_session'].'&new=true', 
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
        "cardholder_info" =>[
            "email_address" => $emailaddress
        ],
    ],
]);


}






/**
* In case payment could not be processed exception will be thrown.
* In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.
* See Error Codes section for detailed list.
*/

if((!empty($submit))||(!empty($pares))||((!empty($cres))&&(!empty($threeDSSessionData)))){

try {
    /** @type Cardinity\Method\Payment\Payment */
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

                if( abs($payment_amount1 - $priceamount1) < 5) {



                    $code='31c223b5500453655b63bf1521eb268487da3';

                    echo ' ';

                    
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
            $callback_url = 'https://superviral.io/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3'].'/?redirectid='.$info['order_session'].'&new=true';
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
            `order_session` = '$plogordersession'  
            ");

           

        }



if(!empty($inpnum == 1))$inpnumre = 'inputredoutline';
if(!empty($inpdate == 1))$inpdatere = 'inputredoutline';
if(!empty($inpcvc == 1))$inpcvcre = 'inputredoutline';



}




//////////////////////////////////////////////////////////////////////////////////////////////////////////


$showerror0 = str_replace('3000:', '', $showerror0);
$showerror0 = str_replace('3000', '', $showerror0);
$showerror1 = str_replace('3000:', '', $showerror1);
$showerror2 = str_replace('3000:', '', $showerror2);

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';

$applepayuserid = '&userid='.$userinfo['email_hash'];
}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order3-cardinity.html');

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


$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){

if($cinfo['name']=='maincta'){$cinfo['content'] = str_replace('$price',$priceamount,$cinfo['content']);}

    $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

}


echo $tpl;
?>
