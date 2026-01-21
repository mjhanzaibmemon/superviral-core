<?php


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink2 = 'activelink';


include('../db.php');
include('auth.php');
include('header.php');
include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php'; //TO EMAIL ONCE ORDER IS COMPLETE
 //TO EMAIL ONCE ORDER IS COMPLETE

date_default_timezone_set('Europe/London');



use Google\Cloud\Translate\V2\TranslateClient;

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

$translate = new TranslateClient(['key' => $googletranslatekey]);

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

//LOC REDIRECT
$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

$id = addslashes($_GET['id']);
$order_id = addslashes($_POST['order_id']);
$refill_session_id = addslashes($_POST['refill_session_id']);
$refill = addslashes($_POST['changestatus']);

if(!empty($refill)){

  if((empty($order_id))||(empty($refill_session_id)))die('Error 40392: Please contact support team with this error.');

  if($refill=='on'){$refillsqlchange = "0";}
  if($refill=='off'){$refillsqlchange = "1";}

  mysql_query("UPDATE `automatic_likes` SET `disabled` = '$refillsqlchange' WHERE `id` = '$order_id' AND `md5` = '$refill_session_id' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

}





if(empty($id)){die;}

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `brand`='sv' AND `md5` = '$id' AND `account_id` = '{$userinfo['id']}' LIMIT 1");
if(mysql_num_rows($q)=='0')die('No order found');

$info = mysql_fetch_array($q);



////////////////////////////#################




require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';

use Cardinity\Client;
use Cardinity\Method\Payment;
use Cardinity\Exception;
use Cardinity\Method\ResultObject;


$client = Client::create([
    'consumerKey' => $cardinitykey,
    'consumerSecret' => $cardinitysecret,
]);

////////////////////////////################# ASK USER IF THEY'RE SURE THEY WANT TO CHANGE THEIR LIKES?


if((!empty($_POST['changepackagebtn']))&&(!empty($_POST['package']))){

$changepackageid = addslashes($_POST['package']);

$fetchchangepackageq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `brand`='sv' AND `id` = '$changepackageid' LIMIT 1");
$fetchchangepackageinfo = mysql_fetch_array($fetchchangepackageq);

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1>Change Auto Likes Package?</h1>

<p class="msg">You\'re currently on the '.$info['likes_per_post'].' automatic likes post. Are you sure you want to change your package to <u>'.$fetchchangepackageinfo['amount'].' Automatic Likes</u>? We\'ll make a payment of '.$locas[$loc]['currencysign'].$fetchchangepackageinfo['price'].$locas[$loc]['currencyend'].'. Your next charge will be on the date '.date('d/m/Y',time() + (86400 * 30)).' (30-days from today) to automatically renew your <b>'.$fetchchangepackageinfo['amount'].' automatic likes</b>.</p>

<ul class="listctn">

  <li><span class="tick"></span>Cancel anytime you like</li>
  <li><span class="tick"></span>You\'ll Automatically receive the Highest Quality Likes </li>
  <li><span class="tick"></span>24/7 customer care team</li>

</ul>

<p class="msg">You can only change your package <font color="red">once a month</font>. Are you sure you want to change your package to <b>'.$fetchchangepackageinfo['amount'].' automatic likes</b>?</p>

<form method="POST">

<input type="hidden" name="packageidchange" value="'.$changepackageid.'">
<input type="submit" class="btn btn3 btntracking" name="changepackagebtn" value="Yes I want to Change This Package">

</form>

</div>';

}


////////////////////////////################# USER CONFIRMED THAT THEY WANT TO CHANGE THEIR PACKAGE.. NOW CHANGE IT

if((!empty($_POST['packageidchange']))&&($info['changenotallowed']=='0')){

//echo 'ASD';

$packageidchange = addslashes($_POST['packageidchange']);

$fetchchangepackageq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `brand`='sv' AND `id` = '$packageidchange' LIMIT 1");
$fetchchangepackageinfo = mysql_fetch_array($fetchchangepackageq);

$info['price'] = $fetchchangepackageinfo['price'];
$info['likes_per_post'] = $fetchchangepackageinfo['amount'];
$info['min_likes_per_post'] = $fetchchangepackageinfo['amount'];
$info['max_likes_per_post'] = $fetchchangepackageinfo['amount'] * 1.1;
$info['max_post_per_day'] = $fetchchangepackageinfo['postlimit'];

$packageamount = floatval($info['price']);

////////

                $method = new Payment\Create([
                    'amount' => $packageamount,
                    'currency' => $locas[$loc]['currencypp'],
                    'settle' => true,
                    'description' => 'Automatic Likes',
                    'order_id' => $info['id'],
                    'country' => $info['billing_country'],
                    'payment_method' => Payment\Create::RECURRING,
                    'payment_instrument' => [
                        'payment_id' => $info['payment_id']
                    ],
                ]);


               try {

                    $payment = $client->call($method);


                    $now = time();

                    $nextbilled = time() + (86400 * 29);
                    $expiry = $nextbilled + 86400;
                    

                    $updatebilling = mysql_query("UPDATE `automatic_likes` SET 
                      `price` = '{$info['price']}',
                      `likes_per_post` = '{$info['likes_per_post']}',
                      `min_likes_per_post` = '{$info['min_likes_per_post']}',
                      `max_likes_per_post` = '{$info['max_likes_per_post']}',
                      `max_post_per_day` = '{$info['max_post_per_day']}',
                      `lastbilled` = '$now',
                      `cancelbilling` = '0',
                      `disabled` = '0',
                      `nextbilled` = '$nextbilled',
                      `expires` = '$expiry',
                      `changenotallowed` = '1',
                      `start_fulfill` = '0'
                      
                    WHERE `id` = '{$info['id']}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

                    if(!$updatebilling){echo '<hr>Errror Changing Service ';}else{

                    $thiscurrency = $info['country'];
                    $currency333 = $locas[$thiscurrency]['currencypp'];

                    $paymentId = $payment->getId();
                    $now = time();

                      mysql_query("INSERT INTO `automatic_likes_billing`

                        SET 
                        `brand` = 'sv',
                        `account_id` = '{$info['account_id']}',
                        `igusername` = '{$info['igusername']}',
                        `auto_likes_id` = '{$info['id']}',
                        `likesperpost` = '{$info['likes_per_post']}',
                        `currency` = '$currency333',
                        `amount` = '{$info['price']}',
                        `added` = '$now',
                        `main_payment_id` = '{$info['payment_id']}',
                        `payment_id` = '$paymentId',
                        `lastfour` = '{$info['lastfour']}',
                        `billingname` = '{$info['payment_billingname_crdi']}'

                        ");

                    //EMAIL CUSTOMER

                    $subject = 'Automatic Likes #'.$info['id'].': Package Changed';

                    $emailbody = '
                    <p>Hi there,</p>
                    <br>
                    <p>We can confirm that we\'ve changed your Automatic Likes package to '.$info['likes_per_post'].' Automatic Likes. We\'ve charged you a total of '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].' on the card ending with **** '.$info['lastfour'].'. Billing name: '.$info['payment_billingname_crdi'].'</p>
                    <br>
                    <p>

                    </p>
                    <br>
                    <p>Here is what we\'ve changed your package to:</p>
                    <br>

                    <table class="ordertbl">
                      <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                      <tr><td>'.$info['igusername'].'</td><td>'.$info['likes_per_post'].' Automatic Likes</td><td>'.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'</td><td>'.date('jS F Y',time() + (86400 * 30)).'</td></tr>
                    </table>

                    <br>
                    <p>You\'ll continue to receive the following benefits with your new package:</p>
                    <br><p>
                    - Up to '.$info['max_post_per_day'].'-posts per day<br>
                    - Real likes from real users<br>
                    - Free views on all videos<br>
                    - Safe & Secure since 2012<br>
                    - 24/7 customer support<br>
                    - Cancel anytime you like<br></p>

                    <br>
                    <p>You can manage your auto likes here:</p>
                    <br>

                    <br>
                      <a href="https://superviral.io/'.$loclinkforward.'account/edit/'.$info['md5'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
                    <br>';

                    $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
                    $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
                    $emailtpl = str_replace('Unsubscribe','',$emailtpl);
                    $emailtpl = str_replace('{subject}',$subject,$emailtpl);

                    if($notenglish==true){


                          $result = $translate->translate($emailtpl, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $emailtpl = $result['text'];



                          $result = $translate->translate($subject, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $subject = $result['text'];



                    }


                    emailnow($info['emailaddress'],'Superviral','support@superviral.io',$subject,$emailtpl);

                     header('Location: /'.$loclinkforward.'account/edit/'.$info['md5'].'?changedpackage=true');

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
                        $errors[] = 'You failed to authorize your payment through your bank: '.$error['message'];
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

                if(!empty($errors)){

                  foreach($errors as $pererror){$billingfailure .= $pererror.'<br>';}

                  $errorupdatethis = mysql_query("UPDATE `automatic_likes` SET 

                    `billingfailure` = '$billingfailure' 

                    WHERE `id` = '{$info['id']}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

                  $errorupdatesession = mysql_query("UPDATE `automatic_likes_session` SET 
                    `billingfailure` = '$billingfailure' 

                    WHERE `order_session` = '{$info['autolikes_session']}' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

                  header('Location: /'.$loclinkforward.'account/checkout/'.$info['autolikes_session']);

                }


////////













}






////////////////////////////#################

if(!empty($_GET['resumeservice'])){

$resumeservice = addslashes($_GET['resumeservice']);



//$info['expires'] = 1627228343;

        if(time() < $info['expires']){//POSITIVE - dont charge them
        //DONT CHARGE UNTIL LAST DAY
//        echo '<hr>positive billing period<br>';
 //         echo 'Auto likes resumed without charge!';


              $updatebilling = mysql_query("UPDATE `automatic_likes` SET 
                `cancelbilling` = '0',
                `disabled` = '0'
                
              WHERE `id` = '{$info['id']}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

              //EMAIL CUSTOMER

              $subject = 'Automatic Likes #'.$info['id'].': Service Resumed';

              $emailbody = '
              <p>Hi there,</p>
              <br>
              <p>This is a notification email to confirm you\'ve resumed the billing on your automatic likes.</p>
              <br>
              <p>You\'ve not been charged for resuming your automatic likes. You will next be charged on the following date: '.date('jS F Y',$info['nextbilled']).'</p>
              <br>
              <p>Here is the Automatic Likes service you\'re resuming:</p>
              <br>

              <table class="ordertbl">
                <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                <tr><td>'.$info['igusername'].'</td><td>'.$info['likes_per_post'].' Automatic Likes</td><td>'.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'</td><td>'.date('jS F Y',$info['nextbilled']).'</td></tr>
              </table>

              <br>
              <p>You\'ll continue to receive the following benefits:</p>
              <br><p>
              - Up to '.$info['max_post_per_day'].'-posts per day<br>
              - Real likes from real users<br>
              - Free views on all videos<br>
              - Safe & Secure since 2012<br>
              - 24/7 customer support<br>
              - Cancel anytime you like<br></p>

              <br>
              <p>You can manage your Automatic Likes here:</p>
              <br>

              <br>
                <a href="https://superviral.io/'.$loclinkforward.'account/edit/'.$info['md5'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
              <br>';

              $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
              $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
              $emailtpl = str_replace('Unsubscribe','',$emailtpl);
              $emailtpl = str_replace('{subject}',$subject,$emailtpl);


                    if($notenglish==true){


                          $result = $translate->translate($emailtpl, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $emailtpl = $result['text'];



                          $result = $translate->translate($subject, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $subject = $result['text'];



                    }


              emailnow($info['emailaddress'],'Superviral','support@superviral.io',$subject,$emailtpl);

              header('Location: /'.$loclinkforward.'account/edit/'.$info['md5'].'?resumeservicemsg=true');die;


        }else{//NEGATVE BILLING PERIOD - bill them right now

         //  echo '<hr>negative billing period.. charge customer';

           $startoftoday = strtotime("today", time());
           $startoftomorrow = strtotime("today", time() + 86400);

          // $info['lastbilled'] = 1627490832;//FORCE BILLING TODAY
           if( ($startoftoday <= $info['lastbilled']) && ($info['lastbilled'] <= $info['nextbilled'])){//ITS WITHIN POSITIVE BILLIONG PERIOD

              //echo 'Last billed:'.$info['lastbilled'].'<hr>';
              //echo 'nextbilled billed:'.$info['nextbilled'].'<hr>';

                $message = '

                <div class="container dshadow paymentfor" style="text-align: left;">

                <h1 style="color:green">You\'ve already resumed your service</h1>

                <p class="msg">To prevent your card from being re-charged by accident, we don\'t allow it to be recharged until your billing period is over. Your next payment is on '.date('jS F Y',$info['nextbilled']).'.</p>


                </div>';

          

           }
           else
           {
              if($_GET['billingdate']!==(date("dmY")))die('Not Within Billing Date');//PREVENT VISITING THIS AT A LATER DATE

              //echo '<hr>Bill - its NOT within 24 hours<hr>';

                $packageamount = floatval($info['price']);

                $method = new Payment\Create([
                    'amount' => $packageamount,
                    'currency' => $locas[$loc]['currencypp'],
                    'settle' => true,
                    'description' => 'Automatic Likes',
                    'order_id' => $info['id'],
                    'country' => $info['billing_country'],
                    'payment_method' => Payment\Create::RECURRING,
                    'payment_instrument' => [
                        'payment_id' => $info['payment_id']
                    ],
                ]);


               try {

                    $payment = $client->call($method);

                    //DETERMINE IF IT NEEDS A PUSH TO GO DOWN THE HILL TO BEGIN AUTO LIKES
                    if(($startoftoday <= $info['last_updated']) && ($info['last_updated'] <= $startoftomorrow) ){}
                      else{$startfulfill = "`start_fulfill` = '0',";}

                    $now = time();

                    $nextbilled = time() + (86400 * 29);
                    $expiry = $nextbilled + 86400;
                    

                    $updatebilling = mysql_query("UPDATE `automatic_likes` SET 
                      $startfulfill
                      `lastbilled` = '$now',
                      `cancelbilling` = '0',
                      `disabled` = '0',
                      `nextbilled` = '$nextbilled',
                      `expires` = '$expiry'
                      
                    WHERE `id` = '{$info['id']}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

                    if(!$updatebilling){echo '<hr>Errror Resuming Service ';}else{

                      $now = time();
                      $thiscurrency = $info['country'];
                      $currency322 = $locas[$thiscurrency]['currencypp'];

                      mysql_query("INSERT INTO `automatic_likes_billing`

                        SET 
                        `brand` = 'sv',
                        `account_id` = '{$info['account_id']}',
                        `igusername` = '{$info['igusername']}',
                        `auto_likes_id` = '{$info['id']}',
                        `likesperpost` = '{$info['likes_per_post']}',
                        `currency` = '$currency322',
                        `amount` = '{$info['price']}',
                        `added` = '$now',
                        `main_payment_id` = '{$info['payment_id']}',
                        `payment_id` = '{$info['payment_id']}',
                        `lastfour` = '{$info['lastfour']}',
                        `billingname` = '{$info['payment_billingname_crdi']}'

                        ");

                        //EMAIL CUSTOMER

                        $subject = 'Automatic Likes #'.$info['id'].': Payment Successful + Service Resumed';

                        $emailbody = '
                        <p>Hi there,</p>
                        <br>
                        <p>This is a notification email to confirm you\'ve resumed the billing on your automatic likes.</p>
                        <br>
                        <p>You\'ve been charged '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].' to resume your '.$info['likes_per_post'].' Automatic Likes. You will next be charged on the following date: '.date('jS F Y',$info['nextbilled']).'</p>
                        <br>
                        <p>Here is the Automatic Likes service you\'re resuming:</p>
                        <br>

                        <table class="ordertbl">
                          <tr><td>IG Username</td><td>Service</td><td>Payment</td><td>Next Billed:</td></tr>
                          <tr><td>'.$info['igusername'].'</td><td>'.$info['likes_per_post'].' Automatic Likes</td><td>'.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'</td><td>'.date('jS F Y',$info['nextbilled']).'</td></tr>
                        </table>

                        <br>
                        <p>You\'ll continue to receive the following benefits:</p>
                        <br><p>
                        - Up to '.$info['max_post_per_day'].'-posts per day<br>
                        - Real likes from real users<br>
                        - Free views on all videos<br>
                        - Safe & Secure since 2012<br>
                        - 24/7 customer support<br>
                        - Cancel anytime you like<br></p>

                        <br>
                        <p>You can manage your Automatic Likes here:</p>
                        <br>

                        <br>
                          <a href="https://superviral.io/'.$loclinkforward.'account/edit/'.$info['md5'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Manage My Auto Likes</a>
                        <br>';

                        $emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
                        $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
                        $emailtpl = str_replace('Unsubscribe','',$emailtpl);
                        $emailtpl = str_replace('{subject}',$subject,$emailtpl);
                        $formattedDate = date('d/m/Y h:i A', $info['added']);
                        $emailtpl = str_replace('{date_added}', $formattedDate, $emailtpl);

                    if($notenglish==true){


                          $result = $translate->translate($emailtpl, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $emailtpl = $result['text'];



                          $result = $translate->translate($subject, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $subject = $result['text'];



                    }

                        emailnow($info['emailaddress'],'Superviral','support@superviral.io',$subject,$emailtpl);

                        header('Location: /'.$loclinkforward.'account/edit/'.$info['md5'].'?resumeservicemsg=true43');die;


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
                        $errors[] = 'You failed to authorize your payment through your bank: '.$error['message'];
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

                if(!empty($errors)){

                  foreach($errors as $pererror){$billingfailure .= $pererror.'<br>';}

                  $errorupdatethis = mysql_query("UPDATE `automatic_likes` SET 

                    `billingfailure` = '$billingfailure' 

                    WHERE `id` = '{$info['id']}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

                  $errorupdatesession = mysql_query("UPDATE `automatic_likes_session` SET 
                    `billingfailure` = '$billingfailure' 

                    WHERE `order_session` = '{$info['autolikes_session']}' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

                  header('Location: /'.$loclinkforward.'account/checkout/'.$info['autolikes_session']);

                }


            }

         

        }

//POSTIVIE
//1630101401

//NEGATIVE
//1627228343


//mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '0' WHERE `md5` = '$resumeservice' AND `account_id` = '{$userinfo['id']}' LIMIT 1");

$info['cancelbilling'] = '0';


}

////////////////////////////#################

if(!empty($_GET['cancelservice'])){

$cancelservice = addslashes($_GET['cancelservice']);


sendCloudwatchData('Superviral', 'al-cancel', 'CancelAL', 'al-cancel-function', 1);

mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '3' WHERE `md5` = '$cancelservice' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");
$info['cancelbilling'] = '3';


$subject = 'Automatic Likes #'.$info['id'].': Billing Cancelled';

$emailbody = '
<p>Hi there,</p>
<br>
<p>
This is to confirm that the billing for your automatic likes has been cancelled. You won\'t be billed again unless you choose to resume your '.$info['likes_per_post'].' Automatic Likes. You can resume at any time at the button below.
</p>
<br>
<p>Here is the Automatic Likes service you\'ve cancelled:</p>
<br>

<table class="ordertbl">
  <tr><td>IG Username</td><td>Service</td><td>Price</td><td>Status</td></tr>
  <tr><td>'.$info['igusername'].'</td><td>'.$info['likes_per_post'].' Automatic Likes</td><td>'.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'</td><td>Billing Cancelled</td></tr>
</table>

<br>
<p>Your 500 automatic likes will be cancelled on the following: '.date('jS F Y',$info['expiry']).'.</p>

<p>Your 500 automatic likes will continue to run until '.date('jS F Y',$info['expiry']).' as you\'ve already paid on '.date('jS F Y',$info['lastbilled']).' until it expires on '.date('jS F Y',$info['expiry']).'.</p>

<br>
<p>To resume the billing for your automatic likes, please click here:</p>
<br>

<br>
  <a href="https://superviral.io/'.$loclinkforward.'account/edit/'.$info['md5'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Resume Auto Likes</a>

<br>
<p>To pause your automatic likes, please click here:</p>
<br>

<br>
  <a href="https://superviral.io/'.$loclinkforward.'account/edit/'.$info['md5'].'?pauseautolikes=true" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Pause Auto Likes</a>
<br>'

;

$emailtpl = file_get_contents('../emailtemplate/emailtemplate.html');
$emailtpl = str_replace('{body}',$emailbody,$emailtpl);
$emailtpl = str_replace('Unsubscribe','',$emailtpl);
$emailtpl = str_replace('{subject}',$subject,$emailtpl);


                    if($notenglish==true){


                          $result = $translate->translate($emailtpl, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $emailtpl = $result['text'];



                          $result = $translate->translate($subject, [
                              'source' => 'en', 
                              'target' => $locas[$loc]['sdb'],
                              'format' => 'html'
                          ]);

                          $subject = $result['text'];



                    }


emailnow($info['emailaddress'],'Superviral','support@superviral.io',$subject,$emailtpl);

}

////////////////////////////#################

if($_GET['paymentfor']=='freeautolikes'){

$datediff = time() - $info['nextbilled'];
$calctime = round($datediff / (60 * 60 * 24));
$calctime = str_replace('-','',$calctime);

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1>Payment complete!</h1>

<p class="msg">Payment complete! Your automatic likes is active and you don\'t have to worry about any disruptions to your service.</p>

<ul class="listctn">

  <li><span class="tick"></span>We\'ve sent an email confirmation</li>
  <li><span class="tick"></span>You\'ll receive the highest quality likes automatically </li>
  <li><span class="tick"></span>We\'ll bill you next in '.$calctime.'-days to renew your Auto Likes</li>
  <li><span class="tick"></span>You can manage your Premium auto likes below</li>

</ul>

</div>';

}

///////////////////////////###################


if($_GET['paymentfor']=='billingfailure'){

$datediff = time() - $info['nextbilled'];
$calctime = round($datediff / (60 * 60 * 24));
$calctime = str_replace('-','',$calctime);

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1 style="color:green;">Payment issue fixed!</h1>

<p class="msg">Payment complete! Your automatic likes is active and you don\'t have to worry about any disruptions to your service.</p>

<ul class="listctn">

  <li><span class="tick"></span>We\'ve sent an email confirmation</li>
  <li><span class="tick"></span>You\'ll receive the highest quality likes automatically </li>
  <li><span class="tick"></span>We\'ll bill you next in '.$calctime.'-days to renew your Auto Likes</li>
  <li><span class="tick"></span>You can manage your Premium auto likes below</li>

</ul>

</div>';

}


////////////////////////////#################

if($_GET['paymentfor']=='normal'){

$datediff = time() - $info['nextbilled'];
$calctime = round($datediff / (60 * 60 * 24));
$calctime = str_replace('-','',$calctime);

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1>Payment complete!</h1>

<p class="msg">Payment complete! Your automatic likes is active and you don\'t have to worry about any disruptions to your service.</p>

<ul class="listctn">

  <li><span class="tick"></span>We\'ve sent an email confirmation</li>
  <li><span class="tick"></span>You\'ll receive the highest quality likes automatically </li>
  <li><span class="tick"></span>We\'ll bill you next in '.$calctime.'-days to renew your Auto Likes</li>
  <li><span class="tick"></span>You can manage your Premium auto likes below</li>

</ul>

</div>';

}


///////////////////////////###################



if($info['cancelbilling']=='3'){


if(time() > $info['expires']){$paymentneededtoresume = 'Pay '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].' & ';}

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1 style="color:red">Automatic Likes Cancelled</h1>

<p class="msg">Your automatic likes has been cancelled and we won\'t charge you.<br><br>You can resume your service any time you like. Just hit the resume button below and we\'ll resume your automatic likes!<br><br>Your automatic likes will continue to run until '.date("d/m/Y",$info['expires']).'. You can pause your auto likes at any moment on this page.</p>

<ul class="listctn">

  <li><span class="tick"></span><b>Real likes</b> from real users</li>
  <li><span class="tick"></span><b>24/7</b> customer support</li>
  <li><span class="tick"></span><b>Safe & Secure</b> since 2012</li>
  <li><span class="tick"></span><b>30-day</b> moneyback guarantee</li>
  <li><span class="tick"></span>Cancel <b>anytime</b></li>

</ul>

<a class="btn btn11 color4" style="width: 350px;text-align: center;margin: 0;" href="?resumeservice='.$info['md5'].'&billingdate='.date("dmY").'">'.$paymentneededtoresume.'Resume Auto Likes</a>

</div>';

$additionalstatus = ' (expires on '.date("d/m/Y",$info['expires']).')';

}

///////////////////////////###################

$threedaysfromnow = time() + (86400 * 3);

if((($info['freeautolikes_session']!=='')&&($info['recurring']=='0')&&($info['lastbilled']=='0')&&($info['expires'] > time() && $info['expires'] < $threedaysfromnow))

  ||

  ($info['freeautolikes_session']!=='')&&($info['recurring']=='0')&&($info['lastbilled']=='0')&&($info['expires'] < time())){

$findalsession = mysql_query("SELECT * FROM `automatic_likes_session` WHERE `brand`='sv' AND `order_session` = '{$info['freeautolikes_session']}' LIMIT 1");

if(mysql_num_rows($findalsession)==0)die('Free AL not Found');

$datediff = time() - $info['expires'];
$calctime = round($datediff / (60 * 60 * 24));
$calctime = str_replace('-','',$calctime);

if($info['expires'] > time()){$freeautolikesh1 = 'Free '.$info['likes_per_post'].' Automatic Likes per Post is expiring in '.$calctime.' days';}
else{$freeautolikesh1 = 'Free '.$info['likes_per_post'].' Automatic Likes per Post has expired';}

$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1 style="color:orange">'.$freeautolikesh1.'</h1>

<p class="msg">To continue your 50 Automatic Likes and keep the free likes you\'ve gained from us in the last 30-days, you can choose a paid package through the button below.</p>

<ul class="listctn">

  <li><span class="tick"></span><b>Real likes</b> from real users</li>
  <li><span class="tick"></span><b>24/7</b> customer support</li>
  <li><span class="tick"></span><b>Safe & Secure</b> since 2012</li>
  <li><span class="tick"></span><b>30-day</b> moneyback guarantee</li>
  <li><span class="tick"></span>Cancel <b>anytime</b></li>

</ul>

<a class="btn btn11 color4" style="width: 350px;text-align: center;margin: 0;" href="/account/checkout/'.$info['freeautolikes_session'].'">Upgrade now + Keep Likes >></a>

</div>';

}


///////////////////////////###########################


if(!empty($info['billingfailure'])){




$message = '

<div class="container dshadow paymentfor" style="text-align: left;">

<h1 style="color:red">Payment Issue</h1>

<p class="msg">There was an error billing your card for your automatic likes. In order to continue the following benefits, please fix the payment issue:</p>

<ul class="listctn">

  <li><span class="tick"></span><b>Real likes</b> from real users</li>
  <li><span class="tick"></span><b>24/7</b> customer support</li>
  <li><span class="tick"></span><b>Safe & Secure</b> since 2012</li>
  <li><span class="tick"></span><b>30-day</b> moneyback guarantee</li>
  <li><span class="tick"></span>Cancel <b>anytime</b></li>

</ul>

<a class="btn btn11 color4" style="width: 350px;text-align: center;margin: 0;" href="/account/checkout/'.$info['autolikes_session'].'">Fix Payment Issue</a>

</div>';



}


///////////////////////////###########################

if($info['cancelbilling']=='0'){



$cancelornot = '<a onclick="return confirm(\'Are you sure you want to cancel your '.$info['likes_per_post'].' Automatic Likes Per Post?\');" class="basiclink"  href="?cancelservice='.$info['md5'].'">Cancel Auto Likes</a>';





}

///////////////////////////###########################
if(empty($info['freeautolikes_session'])){

      $i=1;

      if($info['cancelbilling']=='0')$retentionq = "AND `retention` = '0'";
      if($info['cancelbilling']=='1')$retentionq = "AND `id` != '23'";
      if($info['cancelbilling']=='2')$retentionq = "AND `id` != '22'";

      $allpackagesq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `brand`='sv' AND `id` != '{$info['al_package_id']}' $retentionq ORDER BY `amount` ASC");

      while($allpackages = mysql_fetch_array($allpackagesq)){

        if($i==1)$packageselected = 'selected="selected"';

        $packages .= '

        <option name="changepackage" value="'.$allpackages['id'].'" '.$packageselected.'>'.$allpackages['amount'].' likes per post - '.$locas[$loc]['currencysign'].$allpackages['price'].$locas[$loc]['currencyend'].'/mo</option>

        ';

        unset($packageselected);
        $i++;
      }

  }else{$changepackageshow = 'style="display:none;"';}

  if($info['changenotallowed']=='1')$changepackageshow = 'style="display:none;"';

///////////////////////////###########################
if($_GET['resumeservicemsg']=='true'){


if(!empty($info['freeautolikes_session'])){

  $resumingmessage = 'You\'re on the free Automatic Likes - enjoy it until '.date("d/m/Y",$info['expires']).'!';

}else{
  $resumingmessage = 'You\'ll next be billed on '.date("d/m/Y",$info['nextbilled']).' for a total amount of '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'.';}

                  $message = '

          <div class="container dshadow paymentfor" style="text-align: left;">

          <h1 style="color:green">We\'ve resumed your service</h1>

          <p class="msg">'.$resumingmessage.'</p>


          </div>';}
######################################################


    if($_GET['resumeservicemsg']=='true43')  {$message = '

                      <div class="container dshadow paymentfor" style="text-align: left;">

                      <h1 style="color:green">Automatic Likes Resumed</h1>

                      <p class="msg">Payment of '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].' complete! Your automatic likes is active and you don\'t have to worry about any disruptions to your service.</p>

                        <ul class="listctn">

                          <li><span class="tick"></span>We\'ve sent an email confirmation</li>
                          <li><span class="tick"></span>You\'ll receive the highest quality likes automatically </li>
                          <li><span class="tick"></span>We\'ve already billed you just now to renew your Auto Likes</li>
                          <li><span class="tick"></span>You can manage your Premium auto likes below</li>

                        </ul>

                        </div>';}

######################################################


    if($_GET['changedpackage']=='true')  {                   $message = '

                    <div class="container dshadow paymentfor" style="text-align: left;">

                    <h1>Package changed!</h1>

                    <p class="msg">We\'ve successfully changed your package to '.$info['likes_per_post'].' Automatic Likes. We\'ve also billed your card ending with **** '.$info['lastfour'].' for a total of '.$locas[$loc]['currencysign'].$info['price'].$locas[$loc]['currencyend'].'. You\'ll be billed on '.date('d/m/Y',time() + (86400 * 30)).' (30-days from now) to renew your <b>'.$info['likes_per_post'].' Automatic Likes per post</b>.</p>

                    <ul class="listctn">

                      <li><span class="tick"></span>We\'ve sent you a confirmation email</li>
                      <li><span class="tick"></span>Cancel anytime you like</li>
                      <li><span class="tick"></span>You\'ll Automatically receive the Highest Quality Likes on each post</li>
                      <li><span class="tick"></span>24/7 customer care team</li>

                    </ul>



                    </div>';
}


######################################################



    if($_GET['freeautolikes']=='new')  {                   $message = '

                    <div class="container dshadow paymentfor" style="text-align: left;">

                    <h1>Your Free Automatic Likes Have Started!</h1>

                    <p class="msg">We\'ve successfully started your <b>'.$info['likes_per_post'].' Automatic Likes per Post</b>.</p>

                    <p class="msg">Simply upload a post on your Instagram <u>@'.$info['igusername'].'</u> and <b>we\'ll start delivering the 50-55 likes to your post</b>. You\'ll enjoy the following benefits for Free!</p>

                    <ul class="listctn">

                      <li><span class="tick"></span>Please allow upto 5-minutes for our system to start the service</li>
                      <li><span class="tick"></span>Cancel anytime you like</li>
                      <li><span class="tick"></span>You\'ll Automatically receive the Highest Quality Likes for FREE! </li>
                      <li><span class="tick"></span>24/7 customer care team</li>

                    </ul>



                    </div>';
}








######################################################



if($_POST['changestatus']=='on'){


mysql_query("UPDATE `automatic_likes` SET `disabled` = '0' WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");
$info['disabled']=0;

$message = '

                    <div class="container dshadow paymentfor" style="text-align: left;">

                    <h1 style="color:green;">Automatic Likes resumed!</h1>

                    <p class="msg">Please note, that it may take up to 24-hours for our systems to initiate the Automatic Likes again. This security measure is in place to prevent abuse of our Automatic Likes system. We highly appreciate your understanding.</p>

                    </div>';

}



if($_POST['changestatus']=='off'){


mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");
$info['disabled']=1;

$message = '

                    <div class="container dshadow paymentfor" style="text-align: left;">

                    <h1>Automatic Likes paused!</h1>

                    <p class="msg">Please note, that it may take up to 24-hours for our systems to disable the Automatic Likes. This security measure is in place to prevent abuse of our Automatic Likes system. We highly appreciate your understanding.</p>

                    </div>';

}

######################################################



if(isset($_POST['changeusernamebtn'])){

  $newusername = trim(str_replace('@','',$_POST['newusername']));
  
  mysql_query("UPDATE `automatic_likes` SET `igusername` = '".addslashes($newusername)."' WHERE `id` = '{$info['id']}' LIMIT 1");
  
  $info['igusername'] = $newusername;

  $message = '
  
                      <div class="container dshadow paymentfor" style="text-align: left;">
  
                      <h1 style="">Automatic Likes Username Updated!</h1>
  
                      <p class="msg">You have successfully updated your username on your automatic likes subscription. You will now be recieving likes to your posts on the following instagram account: @'.$newusername.'</p>
  
                      </div>';
  
  }


###################################################### BOTTOM SECTION AND SHOW




$status = '<font color="green">Automatic likes is active and on-going</font>';

if($info['refunded']=='1'){$status .= ' (refunded)';}

if($info['last_updated']!=='0')$lastupdated = '<tr><td>Last updated:</td><td>'.ago($info['last_updated']).'</td></tr>';

$packageexpiry = $info['expires'];
$now = time();

if($now < $info['expires']){

  $status = '<font color="green">Automatic likes is active and on-going</font>';

if($info['disabled']=='1'){

          $status = '<font color="orange">Automatic Likes have been paused</font>';

          $statusbtn = '<input type="hidden" name="changestatus" value="on">
            <input type="submit" class="btn btn3 btntracking" name="submit" value="Enable Automatic Likes">';

            $statustop = 'Paused'.$additionalstatus;

          }else{

          $statustop = '<span class="livebox"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="livesvg"><path d="M256 0C115.4 0 0 115.4 0 256s115.4 256 256 256 256-115.4 256-256S396.6 0 256 0z"></path></svg> Live</span> Automatic Likes enabled '.$additionalstatus;
          $statusactive = 'statusactive';
        $statusbtn = '<input type="hidden" name="changestatus" value="off">
          <input type="submit" class="btn btn3 btntracking" name="submit" value="Disable Automatic Likes">';
          
  }

$status .= '<br><form method="POST" action="#refill"><input type="hidden" name="refill_session_id" value="'.$info['md5'].'"><input type="hidden" name="order_id" value="'.$info['id'].'">
'.$statusbtn.'</form>';
}
  
  else

  {
    $status = 'Automatic likes have finished';
  }


if(($info['recurring']=='0')&&(!empty($info['freeautolikes_session'])))$billingshow = 'style="display:none;"';

$beginOfDay22 = strtotime("today", time());
$endOfDay22   = strtotime("tomorrow", $beginOfDay22) - 1;
$hoursreamining = ceil(abs($endOfDay22 - time()) / 3600);

if($info['missinglikespost']=='0'){

      $freelikesbtn = '<a onclick="signup2();return false;" href="#" class="btn btn3 btntracking">Add likes to post with missing likes</a>';

}else{

      $missinglikesshow = 'style="display:none;"';

}



$fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$info['igusername']}%' ORDER BY `id` DESC LIMIT 1");
$fetchimg = mysql_fetch_array($fetchimgq);

if(!empty($fetchimg['dp']))$dp = '<img class="dp" src="https://cdn.superviral.io/dp/'.$fetchimg['dp'].'.jpg">';

///////////////////////////###########################



$currentpackage = 'You\'re currently on the <u><b>'.$info['likes_per_post'].' automatic likes package</b></u>. You can change your package by choosing one of the following options: ';

$tpl = file_get_contents('manage-auto-likes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{currentpackage}', $currentpackage, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{ordernum}',$info['id'],$tpl);
$tpl = str_replace('{orderdesc}',$info['likes_per_post'].' Automatic Likes Per Post',$tpl);

$tpl = str_replace('{igusername}','@'.$info['igusername'],$tpl);
$tpl = str_replace('{igusername_val}',$info['igusername'],$tpl);
$tpl = str_replace('{postperday}',$info['max_post_per_day'].' posts per day (once you\'ve published 4-posts, our system will renew your limit in '.$hoursreamining.' hours)',$tpl);
$tpl = str_replace('{lastupdated}',$lastupdated,$tpl);
$tpl = str_replace('{expires}',date("l j/n/Y",$info['expires']),$tpl);
$tpl = str_replace('{status}',$status,$tpl);
$tpl = str_replace('{statusbtn}',$status,$tpl);
$tpl = str_replace('{activate}',$activate,$tpl);
$tpl = str_replace('{cancelornot}',$cancelornot,$tpl);
$tpl = str_replace('{changepackageshow}',$changepackageshow,$tpl);
$tpl = str_replace('{billingshow}',$billingshow,$tpl);
$tpl = str_replace('{lastfour}',$info['lastfour'],$tpl);
$tpl = str_replace('{nextbilledon}',date('jS F Y',$info['nextbilled']),$tpl);
$tpl = str_replace('{changecarddetailshref}','/account/checkout/'.$info['autolikes_session'],$tpl);
$tpl = str_replace('{statustop}',$statustop,$tpl);
$tpl = str_replace('{statusactive}',$statusactive,$tpl);
$tpl = str_replace('{hash}',$info['md5'],$tpl);
$tpl = str_replace('{freelikesbtn}',$freelikesbtn,$tpl);
$tpl = str_replace('{missinglikesshow}',$missinglikesshow,$tpl);
$tpl = str_replace('{dp}',$dp,$tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = '') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


if($notenglish==true){


            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}


echo $tpl;
?>