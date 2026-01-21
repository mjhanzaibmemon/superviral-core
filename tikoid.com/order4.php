<?php

// start time
$start_time = microtime(true);

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');


if($_GET['setga']=='true'){
	


	//mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$orderinfo['id']}' AND `brand` = 'to' LIMIT 1");


	die('111');
}

//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')$ordersession = '3b1b3566a5ed231e4436e77b259a8894';

$orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$ordersession' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 1");
$orderinfo = mysql_fetch_array($orderinfoq);


$userinfoq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");
$userinfo = mysql_fetch_array($userinfoq);

//////////////////////// register popup on order finish
if(($orderinfo['account_id']=='0')&&($orderinfo['noaccount']=='0')){
	if($loggedin!==true)$showaccountsignup =1;
	//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo 'asd';//testing
	//if($loggedin==true)
	
}
//////////////////////////



/// RECORD GOOGLE ANALYTICS
if($orderinfo['recordga']=='0'){



	$recordga = "

  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)
  [0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');


ga('create', 'UA-41728467-9', 'auto') ; 

ga('require', 'ecommerce');

  ga('ecommerce:addTransaction', {
'id': '{ordernumber}',                     // Transaction ID. Required.
'affiliation': 'Tikoid',   // Affiliation or store name.
'revenue': '{value}',               // Grand Total.
'shipping': '0.00',                  // Shipping.
'tax': '0.00',                     // Tax.
'currency': 'USD'
}); 

ga('ecommerce:addItem', {
  'id': '{ordernumber}',      // Transaction ID. Required.
  'name': '{productname}',    // Product name. Required.
  'currency': 'USD'
});

ga('ecommerce:send');


";

$recordga2 = "var iframe = document.createElement('iframe');
iframe.style.display = \"none\";
iframe.src = \"?setga=true\";
document.body.appendChild(iframe);";


}else{

$recordga = "gtag('config', 'UA-41728467-9');";

}




//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['input'])){

$_POST['input'] = addslashes($_POST['input']);

mysql_query("UPDATE `users` SET `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");
mysql_query("UPDATE `orders` SET `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");

header('Location: /order/finish/');die;

}

if((empty($orderinfo['contactnumber']))&&(!empty($userinfo['contactnumber']))&&(!empty($userinfo['sentsms'] == '2'))){

mysql_query("UPDATE `orders` SET `contactnumber` = '{$userinfo['contactnumber']}',`askednumber`= '2' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");
header('Location: /order/finish/');die;
}

if(($_GET['mode']=='noupdate')||(($_POST['submit'])&&(empty($_POST['input'])))){
	mysql_query("UPDATE `users` SET `sentsms` = '1',`contactnumber` = '' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' LIMIT 1");
	mysql_query("UPDATE `orders` SET `askednumber` = '1',`contactnumber` = '' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");
header('Location: /order/finish/');die;
}

if(($orderinfo['askednumber']=='0')&&($userinfo['sentsms']=='0')&&($_GET['mode']!=='update')){
//header('Location: /order/finish/?mode=update');die;
//header('Location: /testorder.php?mode=update');die;//CHANGE IT TO ABOVE WHEN LIVE
}

//IF MODE IS TO UPDATE IF NOT THEN REDIRECT
if($_GET['mode']=='update'){$body = file_get_contents('order4-entermobile.html');

}else{$body = file_get_contents('order4-2.html');}

if(!empty($orderinfo['contactnumber'])){$contactnumber = $orderinfo['contactnumber'].' <a class="thehref" href="?mode=update">(Change)</a>';}else{
	$contactnumber = '<a class="thehref" href="?mode=update">Add contact number for free text notifications</a>';
}

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

$orderinfo['price'] = sprintf('%.2f', $orderinfo['price'] / 100);

$body = str_replace('{thiscontactnumber}',$orderinfo['contactnumber'],$body);

$tpl = file_get_contents('order-template.html');

if($showaccountsignup==1){$tpl = str_replace('<body>', '<body onload="signupaccount();">', $tpl);}else{
	// $showconfetti = 'realistic();';
}

$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{recordga}', $recordga, $tpl);
$tpl = str_replace('{recordga2}', $recordga2, $tpl);
$tpl = str_replace('{discountnotiffinish}', $discountnotiffinish, $tpl);
$tpl = str_replace('{discountnotifcart}', '', $tpl);
$tpl = str_replace('{productname}', $orderinfo['amount'].' '.$orderinfo['packagetype'], $tpl);
$tpl = str_replace('{back}', '#', $tpl);
$tpl = str_replace('{ordernumber}', $orderinfo['id'], $tpl);
$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{value}', $orderinfo['price'], $tpl);
$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);
$tpl = str_replace('{order_session}', $orderinfo['order_session'], $tpl);


sendCloudwatchData('Tikoid', 'order-finish', 'UserFunnel', 'user-funnel-order-finish-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Tikoid', 'page-load-order-finish', 'PageLoadTiming', 'page-load-order-finish-function', number_format($execution_time_sec, 2));

echo $tpl;
?>