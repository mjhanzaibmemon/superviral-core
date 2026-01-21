<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

//http://uk.superviral.io/orderselect-test.php?id=6

include('../db.php');
include('auth.php');
include('header.php');

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

$id = addslashes($_GET['id']);
$country = $locas[$loc]['sdb'];
if($locas[$loc]['sdb'] == 'uk')$billing_country='GB';
if($locas[$loc]['sdb'] == 'us')$billing_country='US';
$user_ip = getUserIP();
$now = time();

$order_session = md5($user_ip.time().$id);

//LOC REDIRECT
$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

//SETTING UP ORDER
if(!empty($_GET['id'])){



			
			//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
			$checkq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '$id' LIMIT 1");
			if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: https://superviral.io/'.$loclinkforward.'account/');}


			mysql_query("INSERT INTO `automatic_likes_session`

				SET 
                `brand` = 'sv',
				`account_id` = '{$userinfo['id']}',
				`country` = '$country',
                `billing_country` = '$billing_country',
				`order_session` = '$order_session',
				`packageid` = '$id',
				`added` = '$now',
				`ipaddress` = '$user_ip',
                `payment_creq_crdi` = ''

				");



			//header('Location: /order/review/');
			header('Location: https://superviral.io/'.$loclinkforward.'account/checkout/'.$order_session);


			die;

}




?>