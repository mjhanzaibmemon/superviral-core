<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

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
			header('Location: /'.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/?auch=1');

			die;

}

$id = $pid;

$user_ip = getUserIP();

function setnewcookie($msg,$country,$user_ip, $id, $socialmedia){

		global $locas;
		global $loc;
        global $order_session;

			$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);
			$added = time();

			$insert = mysql_query("INSERT INTO `order_session` SET `country` = '{$country}',`order_session`='$order_session',`packageid` = '$id',`ipaddress` = '{$user_ip}',`igusername`='',`emailaddress`='',`done`='0',`added`='$added',`unsubscribe`='0',`abandonedemail`='0',`freefollowers`='0',`chooseposts` = '',`payment_creq_crdi` = '', brand= 'sv', socialmedia = '$socialmedia'");
			
			$newcookieid = mysql_insert_id();

			setcookie(
			  "ordersession",
			  $order_session,
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

}

//CHECK IF ID EXISTS
if(empty($id)){die('NO ID FOUND');header('Location: /404?no-id-found');}

//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
$checkq = mysql_query("SELECT * FROM `packages` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: /404?no-package-found');}


$id = $pid;

//IF COOKIE IS SET
if(empty($_COOKIE['ordersession'])) {


		setnewcookie('',$country,$user_ip, $id, $socialmedia);

		$_COOKIE['ordersession'] = addslashes($newcookieid);

		// echo 'New cookie: '.$_COOKIE['ordersession'];
		
}

else

{

		$_COOKIE['ordersession'] = addslashes($_COOKIE['ordersession']);

		$checkq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$_COOKIE['ordersession']}' AND `packageid` = '$id' LIMIT 1");
		if(mysql_num_rows($checkq)=='0'){setnewcookie('',$country,$user_ip, $id, $socialmedia);die('ORDER SESSION NOT FOUND');}
		$info = mysql_fetch_array($checkq);

}




?>