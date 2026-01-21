<?php
//THIS IS FOR FREE FOLLOWERS ONLY
include('adminheader.php');

$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$freeorder = 0;
$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$reorder = addslashes($_POST['reorder']);
$update = addslashes($_POST['update']);
$pagefrom = addslashes($_POST['pagefrom']);
$defectpagefrom = addslashes($_POST['defectpage']);

if(!empty($defectpagefrom)){$defectpagefrom = 'defect';}

if(empty($orderid123))die('No order number');
if(empty($ordersession))die('No order session');

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' LIMIT 1");

if(mysql_num_rows($q)=='0')die('ERROR 315: No Order Has Been Found');

$fetchorder = mysql_fetch_array($q);

if($fetchorder['packagetype']=='freefollowers'){$realtype = 'freetrial';}
else{$realtype = $fetchorder['packagetype'];}

$packageq = mysql_query("SELECT * FROM `packages` WHERE `type` = '{$realtype}' LIMIT 1");
$packageinfo = mysql_fetch_array($packageq);

include('../tiktokorderfulfillraw.php');

$totalamount = $packageinfo['amount'] * 1.1;

$order1 = $api->order(array('service' => $packageinfo['jap1'], 'link' => 'https://www.tiktok.com/@'.$fetchorder['igusername'], 'quantity' => $packageinfo['amount']));

$fulfillid = $order1->order;

echo $fulfillid;


$updateq = mysql_query("UPDATE `orders` SET `fulfill_id` = '$fulfillid' WHERE `id` = '$orderid123' LIMIT 1");

if(($updateq)&&(!empty($fulfillid))){header('Location: tiktok-'.$defectpagefrom.'orders.php?type='.$pagefrom.'&message=updatetrue2&theid='.$orderid123.$noorderstate);}



?>