<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db = 1;
$nomaindb = 1;
include_once('../db.php');
include('auth.php');
include('header.php');


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



//////////////////////////

if ($_GET['nothankyou'] == 'true') echo $therefresh;

//////////////////////////



$id = addslashes($_GET['id']);
$user_ip = getUserIP();

//////////////////////////

if (empty($id)) {

    $now = time();

    $order_session_random = md5($user_ip . time() . 'freeautolikes');

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


    header('Location: /' . $loclinkforward . 'account/freeautolikes/?loc=' . $loc . '&step=1&id=' . $order_session_random);
} else {

    $q = mysql_query("SELECT * FROM `automatic_likes_session` WHERE `order_session` = '$id' AND `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1");

    if (mysql_num_rows($q) == 0) $error = "Error 593921: Please contact support. There seems to be an issue with the auto likes.";


    $info = mysql_fetch_array($q);
}

////////////////////


//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '1' AND `brand`='sv' LIMIT 1 ");
if (mysql_num_rows($q) == 1) $error = '<b>Error 102</b>: Free auto likes not available for this account, please contact our support team with the error code 102.<style>body{background:#fff;}</style>'; //CLOSE WINDOW

//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER

// $q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' AND `brand`='sv' LIMIT 1 ");
// if (mysql_num_rows($q) == 0) $error = '<b>Error 202</b>: Try visiting the tracking page for your order and then refresh this page. Please contact our support team with the error code 202 if this persists.<style>body{background:#fff;}</style>'; //CLOSE WINDOW

// CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
// $q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' LIMIT 1 ");
// if (mysql_num_rows($q) == 1) $error = '<b>Error 302</b>: Free auto likes not available for this account, please contact our support team with the error code 302.<style>body{background:#fff;}</style>'; //CLOSE WINDOW

$qq = "SELECT * FROM `automatic_likes_free` WHERE (`brand`='sv' AND `emailaddress` = '{$userinfo['email']}') ";

if (!empty($userinfo['freeautolikesnumber'])) $qq .= " OR (`brand`='sv' AND `contactnumber` = '{$userinfo['freeautolikesnumber']}') ";

if (!empty($userinfo['user_ip'])) $qq .= " OR (`brand`='sv' AND `ipaddress` = '{$userinfo['user_ip']}') ";

$qq .= " LIMIT 1 ";

$q = mysql_query($qq);



//if (mysql_num_rows($q) == 1) $error = 'Error 402: Free auto likes not available for this account, please contact our support team with the error code 402.<style>body{background:#fff;}</style>'; //CLOSE WINDOW




//CONSTANTLY CHECK THROUGH ALL STEPS THAT THIS AUTO LIKES IS AVAILABLE AND CANT BE ABUSED BY CUSTOMER



$checkinfo = mysql_fetch_array($q);

if (empty($info['igusername'])) $info['igusername'] = $userinfo['username'];
$h1 = 'Free Automatic Likes for 30-days! <strike>(' . $locas[$loc]['currencysign'] . '10.94)</strike>';

$form = '<form method="POST">

    ' . $notverifiedmsg . '
    
    <div class="input-group ">

            <div class="input-container">

                <label for="username">Instagram Username:</label>

                <div class="input-wrapper" style="margin-top: 5px;">

                    <img class="icon" src="/imgs/insta-purple.svg">

                    <input type="text" name="igusername" name="igusername" value="' . $info['igusername'] . '">

                </div>

            </div>

        </div>
   
   <input type="hidden" name="submitform" value="1">
   <div class="button-wrapper">
        <button class="btn color4" style="border: none; width: 100%;" type="submit" name="submit" value="Activate Automatic Likes">ACTIVATE AUTOLIKES</button>
   </div>
   </form>';


if ($_GET['step'] == '1') {

   

    if ($_GET['notverified'] == '1') {
        $notverifiedmsg = '<div class="emailsuccess emailfailed">Incorrect verification code. Please ensure you\'ve typed in the correct 6-digit verification code.</div>';
    }


    if ($_POST['submitform'] == '1') {

        //SEND OUT CONTACT NUMBER HERE

        //if the values are the same as before then don't send it out again, to prevent abuse of the system

        // $contactnumber = addslashes($_POST['input2']);
        $igusername = addslashes($_POST['igusername']);
        $igusername = str_replace('@', '', $igusername);

        $checkexistingq = mysql_query("SELECT * FROM `automatic_likes` WHERE `igusername` LIKE '%$igusername%' AND `brand`='sv' LIMIT 1");

        //CHECK ALSO INSTAGRAM LIKES FREE TABLE
        if (mysql_num_rows($checkexistingq) == '1') $checkexistingq = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `igusername` LIKE '%$igusername%' AND `brand`='sv' LIMIT 1");


        $now = time();

        $al_username = !empty($igusername) ? $igusername : $info['igusername'];
        $al_min = 50;
        $al_max = 63;
        $al_likes_per_post = 50;
        $al_max_perday = 4;
        $al_payment_id = $uniquepaymentid;
        $al_price = $upsell_autolikesdb[4];
        $al_md5 = md5($now . $al_username . $al_min . $al_expiry);

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
        header('Location: /' . $loclinkforward . 'account/edit/' . $al_md5 . '&freeautolikes=new');die;
        // $therefresh2 = '<script>window.location.href = /' . $loclinkforward . 'account/edit/' . $al_md5 . '&freeautolikes=new"; </script>';
        // $success = "<b style='color:green;'>Done</b>";
        // echo $therefresh2;die;
    }
}

if(!empty($error)){
    $form = $error;
}

$tpl = file_get_contents('freeautolikes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward, $tpl);
$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{success}', $success, $tpl);
$tpl = str_replace('{form}', $form, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` IN ('global','freelikes')) ");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}


echo $tpl;
