<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

include('adminheader.php');

$orderid = addslashes($_POST['id']);
$orderfulfillid = addslashes($_POST['orderid']);
$update = addslashes($_POST['update']);
$now = time();
$type = addslashes($_POST['pagefrom']);
$defectpagefrom = addslashes($_POST['defectpage']);

if(!empty($defectpagefrom)){$defectpagefrom = 'defect';}


if(empty($type))$type='defect';

//JUST SAVE, DONT SET IT AS NO DEFECT
if($update=='save'){
	if(empty($orderfulfillid))die('No order number');
	if(!preg_match('/^[0-9 ]*$/', $orderfulfillid))die('Not proper number');
	$updateorder = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderfulfillid',`defect` = '0' WHERE `id` = '$orderid' LIMIT 1");}

//IGNORE THIS ORDER IS DEFECTIVE PERMANENTLY, WAIT UNTIL USER CONTACTS US
if($update=='ignore'){$updateorder = mysql_query("UPDATE `orders` SET `defect` = '5',`fulfilled` = '$now',`norefill` = '1' WHERE `id` = '$orderid' LIMIT 1");}

echo $update.'<br>';

if($updateorder){


	if($update=='ignore'){header('Location: '.$defectpagefrom.'orders.php?type='.$type.'&message=updatetrue&theid='.$orderid);}else
		{header('Location: '.$defectpagefrom.'orders.php?type='.$type.'&message=updatetrue&theid='.$orderid);}


}else{die('Failed to update order, tell Rabban');}

?>