<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$freeorder = 0;
$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$reorder = addslashes($_POST['reorder']);
$update = addslashes($_POST['update']);
$pagefrom = addslashes($_POST['pagefrom']);
$defectpagefrom = addslashes($_POST['defectpage']);
$brand = addslashes($_POST['brand']);

if(!empty($defectpagefrom)){$defectpagefrom = 'defect';}

if(empty($orderid123))die('No order number');
if(empty($ordersession))die('ASD: No order session');


$now = time();

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

if(mysql_num_rows($q)=='0'){
	header('Location: /admin/missing-orders/?type=missing&message=0&theid='.$orderid123);
	die();
}

$fetchtrialorder = mysql_fetch_array($q);


$query = mysql_query("UPDATE `orders` SET `fulfill_attempt` = '0', `next_fulfill_attempt` = '$now' WHERE `id` = '$orderid123' LIMIT 1");//RESTART THE ORDERS

if($query){
	
	header('Location: /admin/missing-orders/?type=missing&message=1&theid='.$orderid123);
	die;
}else{
	header('Location: /admin/missing-orders/?type=missing&message=2&theid='.$orderid123);
	die;
}