<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db = 1;
$nomaindb = 1;
include('../header.php');
// for redirecting US to main site


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

if (isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {

    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];


    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if ($num_rows > 0) { //no match found, the combination of the email hash and token hash isn't found

        $loggedin = true;
    }
}


$id = addslashes($_GET['id']);
$user_ip = getUserIP();
$hash = addslashes($_GET['id']);
//CHECK IF ID AND SESSION EXISTS IN DATABASE
if (!empty($id)) {

    $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' LIMIT 1");

    if (mysql_num_rows($validq) == '0') {
        $error = 'Invalid Session';
    }

    mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `md5` = '$id' LIMIT 1");
    $info = mysql_fetch_array($validq);
} else {


    $error = 'Try clicking on the link from the email again.';
}

$igusername = addslashes($_POST['igusername']);
$igusername = str_replace('@', '', $username);

$emailaddress = $info['emailaddress'];
//CONSTANTLY CHECK THROUGH ALL STEPS THAT THIS AUTO LIKES IS AVAILABLE AND CANT BE ABUSED BY CUSTOMER

$h1 = 'Free Automatic Likes for 30-days! <strike>(' . $locas[$loc]['currencysign'] . '10.94)</strike>';

$form = '<form method="POST">

    {error1}
    
    <div class="input-group ">

            <div class="input-container">

                <label for="username">Instagram Username:</label>

                <div class="input-wrapper" style="margin-top: 5px;">

                    <img class="icon" src="/imgs/insta-purple.svg">

                    <input type="text" name="igusername" name="igusername" value="' . $igusername . '">

                </div>

            </div>

        </div>
   
   <input type="hidden" name="submitform" value="1">
   <div class="button-wrapper">
        <button class="btn color4" style="border: none; width: 100%;" type="submit" name="submit" value="Activate Automatic Likes">ACTIVATE AUTOLIKES</button>
   </div>
   </form>';

if ($_POST['submitform'] == '1' && ($info['done'] == '0')) {

    //SEND OUT CONTACT NUMBER HERE

    //if the values are the same as before then don't send it out again, to prevent abuse of the system
    // $updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `md5` = '$id' ORDER BY `id` DESC LIMIT 1");

    // $contactnumber = addslashes($_POST['input2']);
    $igusername = addslashes($_POST['igusername']);
    $igusername = str_replace('@', '', $igusername);

    if (empty($igusername)) {
        $error1 = 'Username can\'t be blank';
    }

    if (empty($error1)) {


        $now = time();

        $al_username = $igusername ;
        $al_min = 50;
        $al_max = 63;
        $al_likes_per_post = 50;
        $al_max_perday = 4;
        $al_payment_id = $uniquepaymentid;
        $al_price = $upsell_autolikesdb[4];
        $al_md5 = md5($now . $al_username . $al_min . $al_expiry);

        $al_endexpiry = $now + 1296000;


        if(empty($userinfo['id'])) $userinfo['id'] = 0;

        $insertnewautolikes = mysql_query("INSERT INTO `automatic_likes_session` SET 
              `brand` = 'sv',
              `account_id` = '{$userinfo['id']}',
              `country` = '{$locas[$loc]['sdb']}',
              `order_session` = '$id',
              `packageid` = '1',
              `ipaddress` = '$user_ip',
              `added` = '$now',
              `payment_creq_crdi` = '',
              `igusername` = '$al_username'
              ");


        //CREATE AUTO LIKES HERE then redirect
        $insertnewautolikes = mysql_query("INSERT INTO `automatic_likes`
                SET 
                `brand` = 'sv',
                `account_id` = '{$userinfo['id']}',
                `al_package_id` = '1',
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
                `emailaddress` = '{$info['emailaddress']}',
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
                `emailaddress` = '{$info['emailaddress']}', 
                `ipaddress` = '$user_ip', 
                `added` = '$now'
                ");

        $updateuser = mysql_query("INSERT IGNORE INTO `users` SET 
            `country` = '{$locas[$loc]['sdb']}',
            `emailaddress` = '{$info['emailaddress']}', 
            `source` = 'cart',
            `added` = '{$now}',
            `brand` = 'sv',
            `md5` = '{$id}'
             ");

        mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `emailaddress` = '{$info['emailaddress']}'");

        $updateuser = mysql_query("UPDATE `users` SET `monthlyfreeautolikes` = '1' WHERE `emailaddress` = '{$info['emailaddress']}' ");

        //REDIRECT - LOCATE TO MANAGE AUTOMATIC LIKES PAGE WITH THE SESSION
        header('Location: /' . $loclinkforward . 'automatic-instagram-likes/?id=' . $al_md5 . '&freeautolikes=new');

        die;
    }
}
if($info['done'] > 0){
    $error =  "You already got your free likes , you are not allowed to order more.";
}


if (!empty($error)) {
    $form = $error;
}


$tpl = file_get_contents('freeautolikes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward, $tpl);
$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{success}', $success, $tpl);
$tpl = str_replace('{form}', $form, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` IN ('global','freelikes')) ");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}


echo $tpl;
