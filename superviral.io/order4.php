<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

$orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$ordersession' ORDER BY `id` DESC LIMIT 1");
$orderinfo = mysql_fetch_array($orderinfoq);


$userinfoq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
$userinfo = mysql_fetch_array($userinfoq);

//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['input'])){

$_POST['input'] = addslashes($_POST['input']);

mysql_query("UPDATE `users` SET `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
mysql_query("UPDATE `orders` SET `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$ordersession' LIMIT 1");

header('Location: /order/finish/');die;

}
/*
if((empty($orderinfo['contactnumber']))&&(!empty($userinfo['contactnumber']))&&(!empty($userinfo['sentsms'] == '2'))){

mysql_query("UPDATE `orders` SET `contactnumber` = '{$userinfo['contactnumber']}',`askednumber`= '2' WHERE `order_session` = '$ordersession' LIMIT 1");
header('Location: /order/finish/');die;
}

if(($_GET['mode']=='noupdate')||(($_POST['submit'])&&(empty($_POST['input'])))){
	mysql_query("UPDATE `users` SET `sentsms` = '1',`contactnumber` = '' WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
	mysql_query("UPDATE `orders` SET `askednumber` = '1',`contactnumber` = '' WHERE `order_session` = '$ordersession' LIMIT 1");
header('Location: /order/finish/');die;
}*/
/*
if(($orderinfo['askednumber']=='0')&&($userinfo['sentsms']=='0')&&($_GET['mode']!=='update')){
header('Location: /order/finish/?mode=update');die;
//header('Location: /testorder.php?mode=update');die;//CHANGE IT TO ABOVE WHEN LIVE
}*/

//IF MODE IS TO UPDATE IF NOT THEN REDIRECT
$body = file_get_contents('order4-3.html');

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

ga('ecommerce:send');";

mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");

}else{

$recordga = "gtag('config', 'UA-41728467-8');";

}

if(($orderinfo['askednumber']=='0')&&($userinfo['sentsms']=='0')){$askednumber = '<div id="askednumber" style="display:none;"></div>';
mysql_query("UPDATE `orders` SET `askednumber` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");
}

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}

$tpl = file_get_contents('order-template.html');

$tpl = str_replace('{body}', $body, $tpl);
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
$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order4') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>