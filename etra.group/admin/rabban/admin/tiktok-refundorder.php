<?php

include('adminheader.php');

$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$amount = addslashes($_POST['amount']);

if(empty($orderid123))die('No order 1');
if(empty($ordersession))die('No order 2');
if(empty($amount))die('No order 4');

$now = time();

$updatethisq = mysql_query("UPDATE `orders` SET 
	`refund` = '1', `refundamount` = '$amount'  
	 WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");


if($updatethisq){


header('Location: /admin/tiktok-check-user.php?orderid='.$orderid123);


}else{die('Failed to update order, tell Rabban');}

?>