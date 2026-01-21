<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$amount = addslashes($_POST['amount']);
$pageType = addslashes($_POST['page']);

if(empty($orderid123))die('No order 1');
if(empty($ordersession))die('No order 2');
if(empty($amount))die('No order 4');

$now = time();

$updatethisq = mysql_query("UPDATE `orders` SET 
	`refund` = '1', `refundamount` = '$amount'  
	 WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");


if($updatethisq){

if($pageType == "adminReport")
	header('Location: /admin/reports/?type=reported');
else
	header('Location: /admin/check-user/?orderid='.$orderid123);


}else{die('Failed to update order, tell Rabban');}

?>