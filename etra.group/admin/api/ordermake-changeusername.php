<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$changeigusername = addslashes($_POST['igusername']);
$pageType = addslashes($_POST['page']);



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
	 WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");


mysql_query("UPDATE `order_session` SET `igusername` = '$changeigusername' WHERE `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");
mysql_query("UPDATE `order_session_paid` SET `igusername` = '$changeigusername' WHERE `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");

if(!$updatethisq)exit('Query not working, contact rabban');

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");

if(mysql_num_rows($q)=='0')die('ERROR 315: No Order Has Been Found');

		$q = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' AND brand = '$brand' LIMIT 1");

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
		$finalamount = $packageinfo['amount'] + $upsellamount;

		}else{

		$finalprice = $packageinfo['price'];
		$finalamount = $packageinfo['amount'];
		}

		
		// upsell add follower

		if (!empty($info['upsell_all'])) {
		
				
		    $upsellprice1 = explode('###', $info['upsell_all']);
				
		    $upsellamount1 = $upsellprice1[0];
		
		    $upsellprice1 = $upsellprice1[1];
		
		
		
		    $finalprice = $finalprice + $upsellprice1;
		
		
		} 

		if($brand == 'sv' || $brand == 'fb')
		include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/igorderfulfill.php');

		if($brand == 'tp' || $brand == 'to'){
			include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/ttorderfulfill.php');
		}

		echo 'Order ID: '.$orderid.'<br>';

if(!empty($orderid)){

	if($pageType == "adminReport")
		header('Location: /admin/reports/?type=reported');
	else
		header('Location: /admin/check-user/?orderid='.$orderid123);


}else{die('Failed to update order, tell Rabban');}
?>