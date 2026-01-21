<?php

$order = addslashes($_POST['order']);
$emailaddress = addslashes($_POST['emailaddress']);

$tpl = file_get_contents('track-my-order-main.html');

if(!empty($_POST['submit'])){

if(empty($order)){$error1 = '<div class="emailfailed">Please enter the order number found on your confirmation email.</div>';$error=1;}
if(empty($emailaddress)){$error2 = '<div class="emailfailed">Please enter the email address you\'ve used for your order.</div>';$error=1;}

if(empty($error)){

	$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$order' AND `emailaddress` = '$emailaddress' AND `order_session` !='' AND `brand` = 'to' LIMIT 1");

	if(mysql_num_rows($q)=='0'){$success = '<div class="emailfailed">We could not find an order number associated with this email address. Please check your order confirmation email for your order number starting with "#".</div>';}
	else{$info = mysql_fetch_array($q);header('Location: /track-my-order/'.$info['order_session']);}
	

}

}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{success}', $success, $tpl);
$tpl = str_replace('{order}', $order, $tpl);
$tpl = str_replace('{emailaddress}', $emailaddress, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('track-my-order', 'global') AND brand = 'to' ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;

?>