<?php



include('../db.php');
include('auth.php');
include('header.php');
include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';  



/*NOTES


//IF NOT ELEGIBLE FOR FREE AUTOMATIC LIKES: CLOSE WINDOW

//WHEN FINISHED:
//START FULFILL MUST EQUAL 0
//REDIRECT TO THE AUTOMATIC LIKES MANAGEMENET



*/

/////////////////////////// REFRESH + SEND GA EVENT


$sendgaevent = "
<!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src=\"https://www.googletagmanager.com/gtag/js?id=G-C18K306XYW\"></script>  
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-41728467-8');
  gtag('config', 'G-C18K306XYW');  


gtag('event', 'Free AL', {
  'event_category': 'Sign Up',
  'event_label': 'AL'
});
</script>
";

//$therefresh = '<script>window.top.location.reload();</script>';
$therefresh = '<script>window.top.location.href = "https://superviral.io/'.$loclinkforward.'account/dashboard/"; </script>';

//////////////////////////





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

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}




//////////////////////////

if($_GET['nothankyou']=='true')echo $therefresh;

//////////////////////////



$id = addslashes($_GET['id']);
 $user_ip = getUserIP();

//////////////////////////

if(empty($id)){

    $now = time();
   
    $order_session_random = md5($user_ip.time().'freeautolikes');

    $insertnewautolikes = mysql_query("INSERT INTO `automatic_likes_session` SET 
      `brand` = 'sv',
      `account_id` = '{$userinfo['id']}',
      `country` = '{$locas[$loc]['sdb']}',
      `order_session` = '$order_session_random',
      `packageid` = '1',
      `ipaddress` = '$user_ip',
      `added` = '$now',
      `payment_creq_crdi` = ''
      ");


    header('Location: /'.$loclinkforward.'account/auto-likes-free.php?loc='.$loc.'&step=1&id='.$order_session_random);

}else{

    $q = mysql_query("SELECT * FROM `automatic_likes_session` WHERE `order_session` = '$id' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");
      
    if(mysql_num_rows($q)==0)die("Error 593921: Please contact support. There seems to be an issue with the auto likes.");


    $info = mysql_fetch_array($q);

}

////////////////////


//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '1' AND `brand`='sv' LIMIT 1 ");
if(mysql_num_rows($q)==1)die('<b>Error 102</b>: Free auto likes not available for this account, please contact our support team with the error code 102.<style>body{background:#fff;}</style>');//CLOSE WINDOW

//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER

// $q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' AND `brand`='sv' LIMIT 1 ");
// if(mysql_num_rows($q)==0)die('<b>Error 202</b>: Try visiting the tracking page for your order and then refresh this page. Please contact our support team with the error code 202 if this persists.<style>body{background:#fff;}</style>');//CLOSE WINDOW

// // CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
// $q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1 ");
// if(mysql_num_rows($q)==1)die('<b>Error 302</b>: Free auto likes not available for this account, please contact our support team with the error code 302.<style>body{background:#fff;}</style>');//CLOSE WINDOW

$qq = "SELECT * FROM `automatic_likes_free` WHERE (`brand`='sv' AND `emailaddress` = '{$userinfo['email']}') ";

if(!empty($userinfo['freeautolikesnumber'])) $qq .= " OR (`brand`='sv' AND `contactnumber` = '{$userinfo['freeautolikesnumber']}') ";

if(!empty($userinfo['user_ip'])) $qq .= " OR (`brand`='sv' AND `ipaddress` = '{$userinfo['user_ip']}') ";

$qq .= " LIMIT 1 ";

$q = mysql_query($qq);



if(mysql_num_rows($q)==1)die('Error 402: Free auto likes not available for this account, please contact our support team with the error code 402.<style>body{background:#fff;}</style>');//CLOSE WINDOW




//CONSTANTLY CHECK THROUGH ALL STEPS THAT THIS AUTO LIKES IS AVAILABLE AND CANT BE ABUSED BY CUSTOMER







$checkinfo = mysql_fetch_array($q);














if($_GET['step']=='1'){




      if($_GET['notverified']=='1'){$notverifiedmsg = '<div class="emailsuccess emailfailed">Incorrect verification code. Please ensure you\'ve typed in the correct 6-digit verification code.</div>';}





      if($_POST['submitform']=='1'){

      //SEND OUT CONTACT NUMBER HERE

        //if the values are the same as before then don't send it out again, to prevent abuse of the system

          // $contactnumber = addslashes($_POST['input2']);
          $igusername = addslashes($_POST['igusername']);
          $igusername = str_replace('@', '', $igusername);

          $checkexistingq = mysql_query("SELECT * FROM `automatic_likes` WHERE `igusername` LIKE '%$igusername%' AND `brand`='sv' LIMIT 1");

          //CHECK ALSO INSTAGRAM LIKES FREE TABLE
          if(mysql_num_rows($checkexistingq)=='1')$checkexistingq = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `igusername` LIKE '%$igusername%' AND `brand`='sv' LIMIT 1");

      if((empty($igusername))||(mysql_num_rows($checkexistingq)=='1')){

              if(empty($igusername))$error1 .= '<div class="emailsuccess emailfailed">Please enter an Instagram username to send the automatic likes to.</div>';

              if(mysql_num_rows($checkexistingq)=='1')$error1 .= '<div class="emailsuccess emailfailed">Please enter an Instagram username that hasn\'t been used before for free auto likes.</div>';
              
      }else{

            // //SEND OUT TEXT MESSAGE HERE
  

            // $MessageBird = new \MessageBird\Client($messagebirdclient);

            // $verify = new \MessageBird\Objects\Verify();
            // $verify->recipient = $contactnumber;

            // $extraOptions = [
            //     'originator' => 'Superviral',
            //     'timeout' => 3600,
            //     'type' => 'sms',
            //     'reference' => 'asd',
            // ];

            // try{

            // $verifyResult = $MessageBird->verify->create($verify, $extraOptions);
            // //$verifyResult = json_decode($verifyResult,FALSE);
            // $freeautolikesvericode = $verifyResult->getID();

            $now = time();

            //$al_username = $userinfo['username'];
            $al_username = $igusername;
            $al_min = 50;
            $al_max = 63;
            $al_likes_per_post = 50;
            $al_max_perday = 4;
            $al_payment_id = $uniquepaymentid;
            $al_price = $upsell_autolikesdb[4];
            $al_md5 = md5($now.$al_username.$al_min.$al_expiry);
            
            $al_endexpiry = $now + 1296000;
  
            //CREATE AUTO LIKES HERE then redirect
            $insertnewautolikes = mysql_query("INSERT INTO `automatic_likes`
              SET 
              `brand` = 'sv',
              `account_id` = '{$userinfo['id']}',
              `al_package_id` = '0',
              `country` = '{$locas[$loc]['sdb']}', 
              `md5` = '$al_md5', 
              `added` = '$now', 
              `expires` = '$al_endexpiry', 
              `last_updated` = '0', 
              `likes_per_post` = '$al_likes_per_post', 
              `min_likes_per_post` = '$al_min', 
              `max_likes_per_post` = '$al_max', 
              `max_post_per_day` = '$al_max_perday',  
              `fulfill_id` = '',
              `start_fulfill` = '0',
              `price` = '0.00',
              `igusername` = '$al_username', 
              `emailaddress` = '{$userinfo['email']}',
              `contactnumber` = '{$userinfo['freeautolikesnumber']}',
              `freeautolikes_session` = '$id',
              `autolikes_session` = '$id'
              ");
  
            $freeautolikesid = mysql_insert_id();
  
            mysql_query("UPDATE `automatic_likes_session` SET `freeautolikes` = '$freeautolikesid' 
  
              WHERE `order_session` = '$id' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");
  
           //INSERT INTO AUTOMATIC LIKES FREE SO THAT IT CANT BE USED AGAIN
            mysql_query("INSERT INTO `automatic_likes_free` SET 
              `brand` = 'sv',
              `igusername` = '$al_username', 
              `contactnumber` = '{$userinfo['freeautolikesnumber']}', 
              `emailaddress` = '{$userinfo['email']}', 
              `ipaddress` = '$user_ip', 
              `added` = '$now'
              ");
  
            mysql_query("UPDATE `accounts` SET `freeautolikes` = '1' WHERE `id` = '{$userinfo['id']}' LIMIT 1");

              $q = mysql_query("UPDATE `accounts` SET 
                `freeautolikesvericode` = '$freeautolikesvericode'
                WHERE `id` = '{$userinfo['id']}' LIMIT 1");

              $q = mysql_query("UPDATE `automatic_likes_session` SET `igusername` = '$igusername' WHERE `order_session` = '{$id}' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

              if($q){

               
                $therefresh2 = '<script>window.top.location.href = "/'.$loclinkforward.'account/edit/'.$al_md5.'&freeautolikes=new"; </script>';

                echo $therefresh2;die;
              }

            }
      }



      if(empty($info['igusername']))$info['igusername'] = $userinfo['username'];



      $h1 = 'Free Automatic Likes for 30-days! <strike>('.$locas[$loc]['currencysign'].'10.94)</strike>';

      $form = '<form method="POST">
        
            '.$notverifiedmsg.'
            
            <span class="actionenter">Please enter your Instagram username:</span>
            <div class="formholder">

              <svg class="igusernameicon" enable-background="new 0 0 24 24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12.004 5.838c-3.403 0-6.158 2.758-6.158 6.158 0 3.403 2.758 6.158 6.158 6.158 3.403 0 6.158-2.758 6.158-6.158 0-3.403-2.758-6.158-6.158-6.158zm0 10.155c-2.209 0-3.997-1.789-3.997-3.997s1.789-3.997 3.997-3.997 3.997 1.789 3.997 3.997c.001 2.208-1.788 3.997-3.997 3.997z"></path><path d="m16.948.076c-2.208-.103-7.677-.098-9.887 0-1.942.091-3.655.56-5.036 1.941-2.308 2.308-2.013 5.418-2.013 9.979 0 4.668-.26 7.706 2.013 9.979 2.317 2.316 5.472 2.013 9.979 2.013 4.624 0 6.22.003 7.855-.63 2.223-.863 3.901-2.85 4.065-6.419.104-2.209.098-7.677 0-9.887-.198-4.213-2.459-6.768-6.976-6.976zm3.495 20.372c-1.513 1.513-3.612 1.378-8.468 1.378-5 0-7.005.074-8.468-1.393-1.685-1.677-1.38-4.37-1.38-8.453 0-5.525-.567-9.504 4.978-9.788 1.274-.045 1.649-.06 4.856-.06l.045.03c5.329 0 9.51-.558 9.761 4.986.057 1.265.07 1.645.07 4.847-.001 4.942.093 6.959-1.394 8.453z"></path><circle cx="18.406" cy="5.595" r="1.439"></circle></svg>


            <input style="padding-left:55px;" type="input" class="form-control" placeholder="Instagram Username" name="igusername" value="'.$info['igusername'].'"></div>

            <!-- <span class="actionenter">Please enter your phone number to verify your auto likes:</span>
            <div class="formholder"><input type="tel" id="phone1" class="form-control" name="input2" value=""></div> -->
           
           <input type="hidden" name="submitform" value="1">
           <input class="btn color4" style="margin-top: 15px !important;" type="submit" name="submit" value="Activate Automatic Likes" >
            <a href="?&id='.$id.'&nothankyou=true" class="nothankyou">I don\'t want free automatic likes (worth '.$locas[$loc]['currencysign'].'10.94)</a>
            </form>';



}



if($_GET['step']=='2'){





          if($_POST['submitform']=='2'){//ITS SUCCESSFULL SO SHOW NOTHING ON HERE EXCEPT REDIRECT TO MANAGE AUTO LIKES PAGE


          $verificationcode = addslashes($_POST['verificationcode']);

          $MessageBird = new \MessageBird\Client($messagebirdclient);

          

          try {
            // Execute a MessageBird method
            $result = $MessageBird->verify->verify($userinfo['freeautolikesvericode'], $verificationcode);

          $now = time();

          $al_username = $info['igusername'];
          $al_min = 50;
          $al_max = 63;
          $al_likes_per_post = 50;
          $al_max_perday = 4;
          $al_payment_id = $uniquepaymentid;
          $al_price = $upsell_autolikesdb[4];
          $al_md5 = md5($now.$al_username.$al_min.$al_expiry);
          
          $al_endexpiry = $now + 1296000;

          //CREATE AUTO LIKES HERE then redirect
          $insertnewautolikes = mysql_query("INSERT INTO `automatic_likes`
            SET 
            `brand` = 'sv',
            `account_id` = '{$userinfo['id']}',
            `al_package_id` = '0',
            `country` = '{$locas[$loc]['sdb']}', 
            `md5` = '$al_md5', 
            `added` = '$now', 
            `expires` = '$al_endexpiry', 
            `last_updated` = '0', 
            `likes_per_post` = '$al_likes_per_post', 
            `min_likes_per_post` = '$al_min', 
            `max_likes_per_post` = '$al_max', 
            `max_post_per_day` = '$al_max_perday',  
            `fulfill_id` = '',
            `start_fulfill` = '0',
            `price` = '0.00',
            `igusername` = '$al_username', 
            `emailaddress` = '{$userinfo['email']}',
            `contactnumber` = '{$userinfo['freeautolikesnumber']}',
            `freeautolikes_session` = '$id',
            `autolikes_session` = '$id'
            ");

          $freeautolikesid = mysql_insert_id();

          mysql_query("UPDATE `automatic_likes_session` SET `freeautolikes` = '$freeautolikesid' 

            WHERE `order_session` = '$id' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

         //INSERT INTO AUTOMATIC LIKES FREE SO THAT IT CANT BE USED AGAIN
          mysql_query("INSERT INTO `automatic_likes_free` SET 
            `brand` = 'sv',
            `igusername` = '$al_username', 
            `contactnumber` = '{$userinfo['freeautolikesnumber']}', 
            `emailaddress` = '{$userinfo['email']}', 
            `ipaddress` = '$user_ip', 
            `added` = '$now'
            ");

          mysql_query("UPDATE `accounts` SET `freeautolikes` = '1' WHERE `id` = '{$userinfo['id']}' LIMIT 1");


          //mysql_query("UPDATE `accounts` SET `freeautolikes` = '1' WHERE `id` = '{$userinfo['id']}' LIMIT 1");


          //REDIRECT - LOCATE TO MANAGE AUTOMATIC LIKES PAGE WITH THE SESSION
          $therefresh2 = '<script>window.top.location.href = "https://superviral.io/'.$loclinkforward.'account/edit/'.$al_md5.'&freeautolikes=new"; </script>';

          echo $therefresh2;die;

          } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            // Authentication failed. Is this a wrong access_key?
          } catch (\MessageBird\Exceptions\BalanceException $e) {
            // That means that you are out of credits. Only called on creation of a object.
          } catch (\Exception $e) {
            // Request failed. More information can be found in the body.

            // Echo's the error messages, split by a comma (,)
            echo '1: '.$e->getMessage();

            header('Location: /'.$loclinkforward.'account/auto-likes-free.php?loc='.$loc.'&step=1&id='.$id.'&notverified=1');

          }


          }





  $igusername = addslashes($_POST['igusername']);


  $h1 = 'Verify your contact number';

  $form = '<form method="POST">

        <span class="actionenter">Please enter the 6-digit code sent to: '.$userinfo['freeautolikesnumber'].'</span>
        <div class="inputcode">

            <div class="inputcodelines inp1"></div>
            <div class="inputcodelines inp2"></div>
            <div class="inputcodelines inp3"></div>
            <div class="inputcodelines inp4"></div>
            <div class="inputcodelines inp5"></div>
            <div class="inputcodelines inp6"></div>

            <input class="inputcodebox" name="verificationcode" value="" type="text" maxlength="6">

        </div>


        <input type="hidden" name="submitform" value="2">
        <input  id="signUP" class="btn color4" type="submit" name="submit" value="Verify number and Start Auto Likes">
        <a href="?step=1&id='.$id.'" class="nothankyou">Change my number/Instagram username</a>
        </form>';

}

$tpl = file_get_contents('auto-likes-free.html');

$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{form}', $form, $tpl);


use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php'; 

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}



echo $tpl;

?>