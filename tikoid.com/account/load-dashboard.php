<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;

include_once('../db.php');
include('auth.php');
include('header.php');

$tpl = file_get_contents('load-dashboard.html');


$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{loc}', $loc, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='to' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = 'ww' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

$getUserid = addslashes($_GET['userid']);
$accountId = $userinfo['id'];

if($getUserid == "" || $getUserid == null) {
	$userName = $userinfo['current_ig_username'];
	if($userName == "" || $userName == null) {
		$checkUserExistQuery = mysql_query("SELECT username from account_usernames where account_id ='$accountId' AND `brand` = 'to' order by id desc limit 1");
		$checkUserExistData = mysql_fetch_array($checkUserExistQuery);
		$userName = $checkUserExistData['username'];
	}
}else{

	$checkUserExistQuery = mysql_query("SELECT username from account_usernames where id='$getUserid' AND `brand` = 'to'");
	$checkUserExistData = mysql_fetch_array($checkUserExistQuery);
	$userName = $checkUserExistData['username'];
}

if($userinfo['viewed_dashboard'] == 0){
	
	mysql_query("UPDATE accounts SET viewed_dashboard = 1 WHERE `id` = '{$userinfo['id']}' AND `brand` = 'to'");
}

$ViewedQuery = mysql_query("UPDATE checkusers SET viewed = 1 WHERE ig_username='$userName' AND `brand` = 'to'");

$data = mysql_query("UPDATE post_notif_schedule SET
													email_sent = '2'
													WHERE account_id = '$accountId' AND email_sent = '0' AND `brand` = 'to' LIMIT 1");

echo $tpl;



?>
