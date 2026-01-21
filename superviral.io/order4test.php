<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

$ordersession = '2aa0a9f045f4a1a9ce0c96155273defb';

$orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$ordersession' ORDER BY `id` DESC LIMIT 1");
$orderinfo = mysql_fetch_array($orderinfoq);


$userinfoq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
$userinfo = mysql_fetch_array($userinfoq);



if($_GET['setga']=='true'){mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");die('Recorded');}

//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['input'])){

$_POST['input'] = addslashes($_POST['input']);

mysql_query("UPDATE `users` SET `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
mysql_query("UPDATE `orders` SET `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$ordersession' LIMIT 1");

header('Location: /order/finish/');die;

}


//IF MODE IS TO UPDATE IF NOT THEN REDIRECT
$body = file_get_contents('order4-4.html');
if($_GET['new']=='true')$body = file_get_contents('order4-confetti.html');

if(!empty($orderinfo['contactnumber'])){$contactnumber = $orderinfo['contactnumber'].' <a class="thehref" href="?mode=update">(Change)</a>';}else{
	$contactnumber = '<a class="thehref" href="?mode=update">Add contact number for free text notifications</a>';
}

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

if(empty($orderinfo['contactnumber'])){$redoutline = 'style="border:1px solid red;"';}

$body = str_replace('{thiscontactnumber}',$orderinfo['contactnumber'],$body);

$orderinfo['price'] = sprintf('%.2f', $orderinfo['price'] / 100);

/// RECORD GA

/// RECORD GOOGLE ANALYTICS
if($orderinfo['recordga']=='0'){$recordga = "ga('create', 'UA-41728467-8', 'auto') ; 

ga('require', 'ecommerce');

  ga('ecommerce:addTransaction', {
'id': '{ordernumber}',                     // Transaction ID. Required.
'affiliation': 'Superviral',   // Affiliation or store name.
'revenue': '{value}',               // Grand Total.
'shipping': '0.00',                  // Shipping.
'tax': '0.00',                     // Tax.
'currency': 'GBP'
}); 

ga('ecommerce:addItem', {
  'id': '{ordernumber}',      // Transaction ID. Required.
  'name': '{productname}',    // Product name. Required.
  'currency': 'GBP'
});

ga('ecommerce:send');


var iframe = document.createElement('iframe');
iframe.style.display = \"none\";
iframe.src = \"?setga=true\";
document.body.appendChild(iframe);



";


}else{

$recordga = "gtag('config', 'UA-41728467-8');";

}

if(($orderinfo['askednumber']=='0')&&($userinfo['sentsms']=='0')){$askednumber = '<div id="askednumber" style="display:none;"></div>';
mysql_query("UPDATE `orders` SET `askednumber` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");
}


//////////////////////////

if(($orderinfo['account_id']=='0')&&($orderinfo['noaccount']=='0')){

	if($loggedin!==true)$showaccountsignup =1;
	//if($loggedin==true)


}

//////////////////////////


if(($orderinfo['account_id']!=='0')&&($loggedin==true)){

$mainctabtnoption = '<a class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/account/orders/">View My Growth History</a>';

}else{

$mainctabtnoption = '<a onclick="ga(\'send\', \'event\', \'View\', \'Click\', \'Trackingpage\',\'1\');" class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/track-my-order/{order_session}/{ordernumber}">{trackingdetailscta}</a>';

}

///~~~
$mainctabtnoption = '<a onclick="ga(\'send\', \'event\', \'View\', \'Click\', \'Trackingpage\',\'1\');" class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/track-my-order/{order_session}/{ordernumber}">{trackingdetailscta}</a>';


//////////////////////////

if(($loggedin==true)&&(!empty($info['upsell_autolikes']))){


$autolikesfree = '<div style="border-top: 1px solid #e8e8e8;display: inline-block; width: 100%;padding: 22px 0;">

	

<a class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/account/orders/?loadfreeautolikes=true">Activate Free Auto Likes Now</a>

</div>';

$dontdisplayfreealbox = 0;

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '0' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
$q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
  `contactnumber` = '{$userinfo['freeautolikesnumber']}' OR 
  `emailaddress` = '{$userinfo['email']}' OR 
  `ipaddress` = '{$userinfo['user_ip']}' LIMIT 1 ");

	if(mysql_num_rows($q)==1)$dontdisplayfreealbox = 1;



}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' LIMIT 1 ");
if(mysql_num_rows($q)==1){$dontdisplayfreealbox = 1;}

}


if($dontdisplayfreealbox == 1){$autolikesfree = '';}

}


//////////////////////////


$tpl = file_get_contents('order-template-new.html');

$tpl = str_replace('{body}', $body, $tpl);

if($showaccountsignup==1){$tpl = str_replace('<body>', '<body onload="signupaccount();">', $tpl);}else{

	$showconfetti = 'realistic();';
}

$tpl = str_replace('{mainctabtnoption}', $mainctabtnoption, $tpl);
$tpl = str_replace('{recordga}', $recordga, $tpl);
$tpl = str_replace('{discountnotiffinish}', $discountnotiffinish, $tpl);
$tpl = str_replace('{discountnotifcart}', '', $tpl);
$tpl = str_replace('{order_session}', $orderinfo['order_session'], $tpl);
$tpl = str_replace('{back}', '#', $tpl);
$tpl = str_replace('{ordernumber}', $orderinfo['id'], $tpl);
$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{redoutline}', $redoutline, $tpl);
$tpl = str_replace('{productname}', $orderinfo['amount'].' '.$orderinfo['packagetype'], $tpl);
$tpl = str_replace('{value}', $orderinfo['price'], $tpl);
$tpl = str_replace('{askednumber}', $askednumber, $tpl);
$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);
$tpl = str_replace('{autolikesfree}', $autolikesfree, $tpl);
$tpl = str_replace('{showconfetti}', $showconfetti, $tpl);


$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order4') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>