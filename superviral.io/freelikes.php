<?php


// die('Unfortunately, due to high demand we\'re unable to provide free likes for today. Get Instagram likes <a href="https://superviral.io/buy-instagram-likes/">here</a>.');

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db = 1;
$nomaindb = 1;
include('header.php');

$id = addslashes($_GET['id']);
$hash = addslashes($_GET['id']);
$contactnumber = addslashes($_POST['input']);

 //CHECK IF ID AND SESSION EXISTS IN DATABASE
if(!empty($id)){

    $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' LIMIT 1");
    
    if(mysql_num_rows($validq)=='0'){ $error = 'Free likes are currently unavailable';}
    
    mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `md5` = '$id' LIMIT 1");
    
    $info = mysql_fetch_array($validq);
    
}else{
    
    
    $error = 'Oops something went wrong. Please click the link provided in your email again.';
    
}

$username = addslashes($_POST['username']);
$username = str_replace('@','',$username);


$emailaddress = $info['emailaddress'];
$styleIframe = "display:none;";

if (!empty(addslashes($_POST['submitForm']))&&($info['done']=='0')) {

    //PREVENT DUPLICATE INSERTS
		// $updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `md5` = '$id' ORDER BY `id` DESC LIMIT 1");

    unset($_COOKIE['ordersession']); 
    setcookie('ordersession', '', -1, '/'); 

    if (empty($username)) {
        $error1 = '<div style="color: red;">Please enter a username</div>';
    }
   
    if (empty($error1)) {

        $username = trim($username);
        $username = str_replace('@', '', $username);
        $username = str_replace('https://instagram.com/', '', $username);
        $username = str_replace('instagram.com/', '', $username);

        $username = str_replace('?utm_medium=copy_link', '', $username);
        $username = str_replace('?r=nametag', '', $username);
        $username = str_replace('https://www.', '', $username);
        $username = str_replace('?hl=en.', '', $username);


        if (strpos($username, '?') !== false) {

            $username = explode('?', $username);
            $username = $username[0];
        }

        $checkq = mysql_query("SELECT * FROM `packages` WHERE `type` = 'freelikes' LIMIT 1");
        $packages = mysql_fetch_array($checkq);
        $pid = $packages['id'];
        $socialmedia = $packages['socialmedia'];
        include('create-ordersession.php');
        global $order_session;
        $ordersession = $order_session;
        if(empty($ordersession)) $ordersession = addslashes($_COOKIE['ordersession']);
        $username = str_replace('?', '', $username);

        if(!empty($contactnumber)){


            if(substr($contactnumber, 0, 2 ) == "07")$contactnumber = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $contactnumber);
    
    
            $contactnumberupdate = ", `contactnumber` = '$contactnumber' ";
    
        }
    
        mysql_query("UPDATE `order_session` SET 
				`igusername` = '$username', 
				`emailaddress` = '$emailaddress', 
				`packageid` = '$pid',
				`chooseposts` = '',
				`upsell` = '',    
				`upsell_all` = '',
                `socialmedia` = 'ig' 

				WHERE `order_session` = '$ordersession' LIMIT 1");

        if ($loggedin == true) {

            mysql_query("UPDATE `accounts` SET `username` = '$username' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
        }

        $added = time();
        $countryuser = $locas[$loc]['sdb'];

        $updateuser = mysql_query("INSERT IGNORE INTO `users` SET 
				`country` = '$countryuser',
				`emailaddress` = '{$emailaddress}', 
				`source` = 'cart',
				`added` = '{$added}',
                `brand` = 'sv',
                `md5` = '{$id}'
                $contactnumberupdate 
				 ");

        $updateuser = mysql_query("UPDATE `users` SET 
        `contactnumber` = '$contactnumber' WHERE `emailaddress` = '{$emailaddress}' ");

        $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$pid}' LIMIT 1"));
        if (($packageinfo['type'] == 'freelikes')) {


            $form1 = '<iframe id="iframeProcessOrder" src="/' . $locas[$loc]['order'] . '/' . $locas[$loc]['order1select'] . '/" width="100%" height="1000px" frameborder="0"></iframe>';
            $styleForIframe = 'style="display:none;"';
            $styleForIframeCheckList = 'style="display:none;"';
            $styleIframe = "display:block;height:1000px !important";
        } 
    }
} else {

    if((empty($info['igusername']))&&($loggedin==true)){$username = $userinfo['username'];}
    $emailaddress = $info['emailaddress'];
    
}

if($info['done'] > 0){
    $error =  "You've claimed your free likes and they will be delivered to you soon!";
}

$form = '<form method="POST">
<div class="input-group" style="    padding-bottom: 10px;">
    {error1}
    <div class="input-container">

        <label for="username">Instagram Username:</label>

        <div class="input-wrapper" style="margin-top: 5px;padding-bottom: 35px;">

            <img class="icon" style="top:105%;" src="/imgs/insta-purple.svg">

            <input type="text" style="padding: 10px;    padding-left: 0px;font-size:18px;" id="userName" name="username" onchange = "callAjaxPreloadPost();" value="'.$info['username'].'">

        </div>

        <label for="username">Your contact number:<br><font style="font-size:14px;font-style:italic;">(for free status notification on your likes)</font></label>

        <div class="input-wrapper" style="margin-top: 5px;">

            <input type="tel" style="padding: 28px;    padding-left: 10px;font-size:18px;" id="phone1" class="input inputcontact" name="contactnumber" value="">
            <input type="hidden" id="output" name="input" value="">

        </div>

    </div>

</div>

<div class="button-wrapper">
    <button class="btn color4" type="submit" name="submitForm" value="1" style="font-family: &quot;Open Sans&quot;, sans-serif; font-size: 16px; padding: 17px 15px; border: 1px solid #bbb; width: 100%; margin-bottom: 0">Select post »</button>
</div>

</form>';

if(!empty($error)){
    $form = "<div style='text-align:center'>{$error}</div>";
}


$tpl = file_get_contents('freelikes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{form}', $form, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward, $tpl);
$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{username}', $username, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{styleForIframe}', $styleForIframe, $tpl);
$tpl = str_replace('{styleIframe}', $styleIframe, $tpl);
$tpl = str_replace('{styleForIframeCheckList}', $styleForIframeCheckList, $tpl);
$tpl = str_replace('{form1}', $form1, $tpl);
$tpl = str_replace('{ordersession}', $id, $tpl);
$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);




$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` IN ('global','freelikes')) ");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}


echo $tpl;
