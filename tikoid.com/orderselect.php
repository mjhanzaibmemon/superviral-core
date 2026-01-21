<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

include('db.php');

if(!empty($_GET['setorder'])){

			setcookie(
			  "ordersession",
			  $_GET['setorder'],
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			setcookie(
			  "discount",
			  "on",
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			sendCloudwatchData('Tikoid', 're-order-from-sms', 'ReOrder', 're-order-from-sms-function', 1);

			header('Location: /order/review/');

			die;

}

$id = addslashes($_GET['id']);

function setnewcookie($msg){

			$id = addslashes($_GET['id']);
			$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);
			$added = time();

			$insert = mysql_query("INSERT INTO `order_session` SET `order_session`='$order_session',`packageid` = '$id',`ipaddress` = '{$_SERVER['REMOTE_ADDR']}',`igusername`='',`emailaddress`='',`done`='0',`added`='$added',`unsubscribe`='0',`abandonedemail`='0',`freefollowers`='0',`chooseposts` = '', `payment_creq_crdi` = '', `chooseposts_image` = '',  `brand` = 'to', `socialmedia` = 'tt'");
			
			$newcookieid = mysql_insert_id();

			setcookie(
			  "ordersession",
			  $order_session,
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			header('Location: /order/details/');

}

//CHECK IF ID EXISTS
if(empty($id)){die('NO ID FOUND');header('Location: /404?no-id-found');}

//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
$checkq = mysql_query("SELECT * FROM `packages` WHERE `id` = '$id' AND `brand` = 'to' LIMIT 1");
if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: /404?no-package-found');}





//IF COOKIE IS SET
if(empty($_COOKIE['ordersession'])) {


		setnewcookie('');

		$_COOKIE['ordersession'] = addslashes($newcookieid);

		echo 'New cookie: '.$_COOKIE['ordersession'];
		//CREATED A NEW AND NOW REDIRECT HEADER
		header('Location: /order/details/');

}

else

{

		$_COOKIE['ordersession'] = addslashes($_COOKIE['ordersession']);

		$checkq = mysql_query("SELECT * FROM `order_session` WHERE `id` = '{$_COOKIE['ordersession']}' AND `brand` = 'to' LIMIT 1");
		if(mysql_num_rows($checkq)=='0'){setnewcookie('');die('ORDER SESSION NOT FOUND');}
		$info = mysql_fetch_array($checkq);

		if($_SERVER['REMOTE_ADDR']!==$info['ipaddress']){

			setnewcookie('IP ADDRESS NOT MATCHING NOW REDIRECT');

		}

		echo 'Retrieved cookie: '.$_COOKIE['ordersession'];
		//NOW REDIRECT HEADER
		header('Location: /order/details/');


}




?>