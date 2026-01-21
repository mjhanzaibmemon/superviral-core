<?php

$db=1;
include('header.php');

////////////////////////////check if logged in

if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' AND `brand` = 'to' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){//match found meaning redirect
        $loggedin = true;
    //MYSQL QUERY "UPDATE orders with "
    //REFRESH THE PARENT FRAME SO THAT IT DOESNT COME UP

    }

}





/////////////////////////// REFRESH + SEND GA EVENT


$sendgaevent = "
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-41728467-8\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-41728467-8');


gtag('event', 'Details', {
  'event_category': 'Account',
  'event_label': 'Signed Up'
});
</script>
";

$therefresh = '<script>window.top.location.reload();</script>';





$id = addslashes($_GET['id']);
$order_session = addslashes($_GET['hash']);
$notextupdate = addslashes($_GET['notextupdate']);

if(empty($id))$id = addslashes($_POST['id']);
if(empty($order_session))$order_session = addslashes($_POST['hash']);

//////////////////////////


if($_GET['close']=='true'){

  sendCloudwatchData('Tikoid', 'popup-not-want-benefit', 'OrderFinish', 'user-popup-not-want-benefit-function', 1);

  mysql_query("UPDATE `orders` SET `noaccount` = '1' WHERE `id` = '$id' AND `order_session` = '$order_session' AND `brand` = 'to' LIMIT 1");

  echo $therefresh;

  die('<style>body{background:#fff}</style>Please Refresh This Page');

}


//////////////////////////



if((empty($id))||(empty($order_session)))die('Missing');


$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' AND `order_session` = '$order_session' AND `brand` = 'to' LIMIT 1 ");

if(mysql_num_rows($q)==0)die('No match found');

$info = mysql_fetch_array($q);
$emaillow = strtolower(trim($info['emailaddress']));


$checkifemailexistsq = mysql_query("SELECT * FROM `accounts` WHERE `email` = '$emaillow' AND `brand` = 'to' LIMIT 1");

$accountinfonumrows = mysql_num_rows($checkifemailexistsq);

///// TESTING
if($_GET['rabban']=='true')$accountinfo = 0;

if($accountinfonumrows >= 1){$modalshow='existing';$accountinfo = mysql_fetch_array($checkifemailexistsq);
}else{$modalshow='new';}

if($_POST['modalshow']=='existing')$modalshow='existing';
if($_POST['modalshow']=='new')$modalshow='new';

if($modalshow=='existing'){//ACCOUNT HAS PREVIOUSLY BEEN CREATED



      $email = trim(addslashes($_POST['emailaddress']));
      $password = addslashes($_POST['password']);

      if(empty($email)){$email = $accountinfo['email'];}


      if(!empty($_POST['submit'])){



              if(empty($_POST['emailaddress']))$msg = 'Please enter your email address.';
              if(empty($_POST['password']))$msg = 'Please enter your password.';


              if((!empty($_POST['emailaddress']))&&($_POST['emailaddress'])){

                $searchemailaddress = trim(strtolower(addslashes($_POST['emailaddress'])));
                $searchpassword = addslashes($_POST['password']);

                $searchaccountsq = mysql_query("SELECT * FROM `accounts` WHERE `email` = '$searchemailaddress' AND `brand` = 'to' LIMIT 1");

                if(mysql_num_rows($searchaccountsq)=='0')
                {

                  $msg = 'We couldn\'t find an account associated with this email address'; 
                
                }
                  else
                {

                  $accountinfo = mysql_fetch_array($searchaccountsq);

                  if(password_verify($searchpassword, $accountinfo['password'])){//LOG CUSTOMER IN

                    // set plus_id cookie
                    setcookie('plus_id', $accountinfo['email_hash'], time() + (86400 * 365), "/","tikoid.com"); // 86400 = 1 day
                    // set plus_token cookie
                    setcookie('plus_token', $accountinfo['token_hash'], time() + (86400 * 365), "/","tikoid.com"); // 86400 = 1 day

                    //UPDATE THIS ORDER WITH ACCOUNT ID AND `noaccount` as 2
                    mysql_query("UPDATE `orders` SET `account_id` = '{$accountinfo['id']}', `noaccount` = '2' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

                    //UPDATE THE CARD ASSOCIATED WITH ORDER WITH ACCOUNT ID ~~~
                   // mysql_query("UPDATE `card_details` SET `account_id` = '{$accountinfo['id']}' WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to'");


                    echo $therefresh;

                    die;

                  } else{

                    if(empty($_POST['password'])){
                        $msg = 'Please enter your password.';
                    }else{

                      $msg = 'The password didn\'t match this account. Please try again.';

                      $emailTpl = file_get_contents('emailtemplate/emailtemplate.html');
                      $subject = 'Tikoid - Log Back In!';
  
                      $to = $searchemailaddress;
                      $token = md5(time() . $to);
                      $tokenExpiry = time() + (3 * 86400); //3 day token expiry
                      $emailHash = $accountinfo['email_hash'];
                      $tokenHash = $accountinfo['token_hash'];
                      $accountId = $accountinfo['id'];
                      $now = time();
                      $emailSentTime =$accountinfo['password_email_sent'];
  
                      
                      // if($runTokenQuery) echo "======Inserted into auto_login for account: $accountId ====<br><br>";
  
                      $emailbody = '<p>Hi there,
                      <br><br>
          
                      <p>We can see that you\'ve tried logging in, but were unable to log back in.</p><br>
          
                      <p>Kindly click on below link to login</p><br>
                     
                      <p><a href="https://tikoid.com/login/?accessToken=' . $token . '" target="_blank">Log Back In</a></p><br>
                     
                      <p>Kind regards,</p><br>
          
                      <p>Tikoid Team</p>';
                      // <p>If you do nost wish to receive further email , Kindly <a href="' . $domain . '/turn-off-dashboard-notifs.php?id=' . $Data['email_hash'] . '" target="_blank">Unsubscribe</a> here    </p><br>
  
  
                       $emailTpl = str_replace('{body}', $emailbody, $emailTpl);
                       $emailTpl = str_replace('{subject}', $subject, $emailTpl);
                       $tpl = str_replace('<a href="https://tikoid.com/unsubscribe.php?unsub=now&id={md5unsub}">Unsubscribe</a>', '', $tpl);
  
                      if($emailSentTime == 0){
                          mysql_query("UPDATE accounts SET password_email_sent='$now' WHERE id = $accountId AND `brand` = 'to'");
                          $fiveMinLater = $now + (60*5);
                      }else{
                          $fiveMinLater = $emailSentTime + (60*5);
                      }
  
                      include('../crons/emailer.php');
  
  
                      if($emailSentTime == 0){
                          $insertTokenQuery = "INSERT INTO auto_login SET
                                                                          `access_token` = '$token',
                                                                          `expiry`  = '$tokenExpiry',    
                                                                          `account_id`        = '$accountId',
                                                                          `email_hash`     = '$emailHash',
                                                                          `token_hash`     = '$tokenHash',
                                                                          `added`     = '$now', `brand` = 'to'";
                          $runTokenQuery = mysql_query($insertTokenQuery);
                          mysql_query("UPDATE accounts SET password_email_sent='$now' WHERE id = $accountId  AND `brand` = 'to'");
                          
                          emailnow($to,'Tikoid','support@tikoid.com',$subject,$emailTpl);
    
                      }else if($now >= $fiveMinLater){
  
                          $insertTokenQuery = "INSERT INTO auto_login SET
                                                                          `access_token` = '$token',
                                                                          `expiry`  = '$tokenExpiry',    
                                                                          `account_id`        = '$accountId',
                                                                          `email_hash`     = '$emailHash',
                                                                          `token_hash`     = '$tokenHash',
                                                                          `added`     = '$now', `brand` = 'to'";
                          $runTokenQuery = mysql_query($insertTokenQuery);
  
                          mysql_query("UPDATE accounts SET password_email_sent='$now' WHERE id = $accountId AND `brand` = 'to'");
  
                          emailnow($to,'Tikoid','support@tikoid.com',$subject,$emailTpl);
                      }  
                     
  
                      $loginMsg = 'We can see that you\'ve tried logging in, but were unable to log back in. We\'ve sent an email to log back in with one click'; 
  
                      
                    }

                  
                  }


                }

                

              }

              if($loginMsg != "") $msg = $loginMsg;
              if(!empty($msg)){$msg = '<div class="emailsuccess emailfailed">'.$msg.'</div>';$searchpassword = '';}




      }else{$tinymsg = 'Enter the password to your account '.$email.' to start your order:';}



$h1 = 'Payment Complete, log back in and Start Order!';

$form = '<form method="POST">
      
      <span class="actionenter">'.$tinymsg.'</span>

      '.$msg.'
      <input type="input" class="form-control" name="emailaddress" value="'.$email.'">
      <input type="password" class="form-control"  name="password" id="password" placeholder="Password"  autocomplete="new-password" value="'.$searchpassword.'">
      <input type="hidden" name="hash" value="'.$_POST['hash'].'">
      <input type="hidden" name="id" value="'.$_POST['id'].'">
      <input type="hidden" name="modalshow" value="existing">
     <input class="btn color4" type="submit" name="submit" value="Log back in and Start Order" >
     <a  target="BLANK" href="/'.$loclinkforward.'forgot-password/" class="btn btn3">I Forgot My Password</a>
      <a onclick="return confirm(\'Are you sure, you do not want free account benefits?\');" href="?close=true&hash='.$order_session.'&id='.$id.'" class="nothankyou">I don\'t want free account benefits</a>
      </form>';

      sendCloudwatchData('Tikoid', 'existing-user-popup', 'OrderFinish', 'existing-user-relogin-function', 1);

}
















if($modalshow=='new'){ //NO ACCOUNT FOUND - create a new account

  mysql_query("UPDATE `orders` SET `noaccount` = '1' WHERE `id` = '$id' AND `order_session` = '$order_session' AND `brand` = 'to' LIMIT 1");

  if(!empty($_POST['submit'])){



          if(empty($_POST['password']))$msg = 'Please choose a password.';

          if(!empty($_POST['password'])){

            $email = trim(strtolower($info['emailaddress']));
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $passwordlength = strlen($_POST['password']);
            $email_hash = md5($email);
            $token_hash = md5($tokensecretphrase.md5($email_hash).$password.$now);
            $now = time();


            // if email not exist then create new user
          $sql = mysql_query("INSERT INTO `accounts`
            SET 
            `email` = '$email', 
            `password` = '$password', 
            `added` = '$now', 
            `email_hash` = '$email_hash', 
            `token_hash` = '$token_hash', 
            `passwlength` = '$passwordlength', 
            `passwupdated` = '$now', 
            `lastlogin` = '$now',
            `username` = '{$info['igusername']}', `brand` = 'to'
          ");


          $plus_id_value1 = $email_hash;
          $plus_id_value2 = $token_hash;

          // set plus_id cookie
          setcookie("plus_id", $plus_id_value1, time() + (86400 * 360), "/","tikoid.com"); // 86400 = 1 day
          // set plus_token cookie
          setcookie("plus_token", $plus_id_value2, time() + (86400 * 360), "/","tikoid.com"); // 86400 = 1 day              

          $newaccountid = mysql_insert_id();
        


          //UPDATE THIS ORDER WITH ACCOUNT ID AND `noaccount` as 2
          mysql_query("UPDATE `orders` SET `account_id` = '$newaccountid', `noaccount` = '2' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

          //UPDATE THE CARD ASSOCIATED WITH ORDER WITH ACCOUNT ID ~~~
          //mysql_query("UPDATE `card_details` SET `account_id` = '$newaccountid' WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to'");

          echo $therefresh;die;


          }



          if(!empty($msg)){$msg = '<div class="emailsuccess emailfailed">'.$msg.'</div>';}

          if(empty($msg))//SUCCESS
          {

            echo 'Success';

          } 

          sendCloudwatchData('Tikoid', 'new-user-popup', 'OrderFinish', 'new-user-register-function', 1);


  }else{$tinymsg = '<span class="actionenter">Please choose a password:</span>';}

  $h1 = 'Payment Complete, choose a password and Start Your Order!';

  $form = '<form onsubmit="return checksubmitbtn();" method="POST" id="form">

        '.$tinymsg.$msg.'
        <input type="password" class="form-control"  name="password" id="password" placeholder="Password"  autocomplete="new-password" value="" onclick="document.getElementById(\'passwordverifybox\').style.display = \'block\';">
        <input type="hidden" id="output" name="input" value="">
        <input type="hidden" name="hash" value="'.$_POST['hash'].'">
        <input type="hidden" name="id" value="'.$_POST['id'].'">
        <input type="hidden" name="modalshow" value="new">
        
            <div id="passwordverifybox">
                <span class="password_verify">
                    <span id="8_characters" class="tickno"></span> At least 8 characters
                </span>
            </div>

       <input id="signUP" class="btn color4" type="submit" name="submit" value="Start Order Instantly &raquo;">
        <a style="display:none;" onclick="return confirm(\'Are you sure, you do not want free benefits?\');" href="?close=true&hash='.$order_session.'&id='.$id.'" class="nothankyou">I don\'t want free benefits</a>
        </form>';

}



/*
echo $sendgaevent.$therefresh;die;
echo $therefresh;die;
*/

$tpl = file_get_contents('order4-popup.html');

$tpl = str_replace('{h1}',$h1,$tpl);
$tpl = str_replace('{form}',$form,$tpl);




echo $tpl;

?>