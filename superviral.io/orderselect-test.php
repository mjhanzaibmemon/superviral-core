<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

//http://uk.superviral.io/orderselect-test.php?id=6

include('db.php');

$country = $locas[$loc]['sdb'];

//SETTING UP ORDER
if(!empty($_GET['setorder'])){

			setcookie(
			  "ordersession",
			  $_GET['setorder'],
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			if($_GET['discounton']!=='no'){
			setcookie(
			  "discount",
			  "on",
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);}

			//header('Location: /order/review/');
			header('Location: /'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/');


			die;

}

$id = addslashes($_GET['id']);

function setnewcookie($msg,$country){

		global $locas;
		global $loc;

			$id = addslashes($_GET['id']);
			$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);
			$added = time();





			$insert = mysql_query("INSERT INTO `order_session` SET `country` = '{$country}',`order_session`='$order_session',`packageid` = '$id',`ipaddress` = '{$_SERVER['REMOTE_ADDR']}',`igusername`='',`emailaddress`='',`done`='0',`added`='$added',`unsubscribe`='0',`abandonedemail`='0',`freefollowers`='0',`chooseposts` = ''");
			
			$newcookieid = mysql_insert_id();

			setcookie(
			  "ordersession",
			  $order_session,
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			//header('Location: /order/details/');
			header('Location: /'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/');

}

//CHECK IF ID EXISTS
if(empty($id)){die('NO ID FOUND');header('Location: /404?no-id-found');}

//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
$checkq = mysql_query("SELECT * FROM `packages` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: /404?no-package-found');}



$id = addslashes($_GET['id']);

//IF COOKIE IS SET
if(empty($_COOKIE['ordersession'])) {


		setnewcookie('',$country);

		$_COOKIE['ordersession'] = addslashes($newcookieid);

		echo 'New cookie: '.$_COOKIE['ordersession'];
		//CREATED A NEW AND NOW REDIRECT HEADER
		//header('Location: /order/details/');
		header('Location: /'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/');

}

else

{

		$_COOKIE['ordersession'] = addslashes($_COOKIE['ordersession']);

		$checkq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$_COOKIE['ordersession']}' AND `packageid` = '$id' LIMIT 1");
		if(mysql_num_rows($checkq)=='0'){setnewcookie('',$country);die('ORDER SESSION NOT FOUND');}
		$info = mysql_fetch_array($checkq);

/*		if($_SERVER['REMOTE_ADDR']!==$info['ipaddress']){

			setnewcookie('IP ADDRESS NOT MATCHING NOW REDIRECT',$country);

		}*/

		echo 'Retrieved cookie: '.$_COOKIE['ordersession'];
		//NOW REDIRECT HEADER
		//header('Location: /order/details/');
		header('Location: /'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/');


}




?>