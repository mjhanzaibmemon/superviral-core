<?php

include('adminheader.php');

$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$changeigusername = addslashes($_POST['igusername']);

if(empty($orderid123))die('No order 1');
if(empty($ordersession))die('No order 2');
if(empty($changeigusername))die('No order 4');

$now = time();

$updatethisq = mysql_query("UPDATE `orders` SET 
	`igusername` = '$changeigusername', 
	`defect` = '0',  
	`added` = '$now',
	`order_response` = '',
	`order_response_finish` = '', 
	`fulfilled` = '0',
	`lastrefilled` = '0' 
	 WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

mysql_query("UPDATE `order_session` SET `igusername` = '$changeigusername' WHERE `order_session` = '$ordersession' LIMIT 1");
mysql_query("UPDATE `order_session_paid` SET `igusername` = '$changeigusername' WHERE `order_session` = '$ordersession' LIMIT 1");

if(!$updatethisq)exit('Query not working, contact rabban');

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

if(mysql_num_rows($q)=='0')die('ERROR 315: No Order Has Been Found');

		$q = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' LIMIT 1");

		if(mysql_num_rows($q)=='0')die('ERROR 422: No Order Sessions Has Been Found');

		$info = mysql_fetch_array($q);

		//////CCHOOSE POSTS
		$choosepostsql = '';
		$multiamountposts = 0;

		if(!empty($info['chooseposts'])){
		$chooseposts = explode('~~~', $info['chooseposts']);

		foreach($chooseposts as $posts1){

		if(empty($posts1))continue;

		$posts2 = explode('###', $posts1);

		$multiamountposts++;

		$choosepostsql .= $posts2[0].' ';}

		}

		////////////// UPSELL ACTUAL AMOUNT TO ORDER

		if(!empty($info['upsell'])){

		$upsellprice = explode('###',$info['upsell']);

		$upsellamount = $upsellprice[0];
		$upsellprice = $upsellprice[1];

		$finalprice = $packageinfo['price'] + $upsellprice;

		}else{

		$finalprice = $packageinfo['price'];

		}

		//not to worry about likes for Instagram, as this 
		include('../tiktokorderfulfill.php');


echo 'Order ID: '.$orderid.'<br>';

if(!empty($orderid)){


header('Location: /admin/tiktok-check-user.php?orderid='.$orderid123);


}else{die('Failed to update order, tell Rabban');}

?>