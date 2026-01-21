<?php

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
if(empty($ordersession))die('ASD: No order session');

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

if(mysql_num_rows($q)=='0')die('ERROR 315: No Order Has Been Found');


$fetchtrialorder = mysql_fetch_array($q);

$reordernumber = $fetchtrialorder['reorder'];

if(($fetchtrialorder['packagetype']=='freefollowers')||($fetchtrialorder['packagetype']=='freelikes')){

	$freeorder = 1;

	if($fetchtrialorder['packagetype']=='freefollowers'){

	$info['packageid'] = '18';
	$info['igusername'] = $fetchtrialorder['igusername'];
	$info['order_session'] = $fetchtrialorder['order_session'];
	$hash = $fetchtrialorder['order_session'];


	}





	if($fetchtrialorder['packagetype']=='freelikes'){

	$info['packageid'] = '20';
	$info['igusername'] = $fetchtrialorder['igusername'];
	$info['order_session'] = $fetchtrialorder['order_session'];
	$hash = $fetchtrialorder['order_session'];
	$freelikespost = trim($fetchtrialorder['chooseposts']);


	}

}

if($freeorder==0){//IF IT ISNT A FREE ORDER THEN CONTINUE AS USUAL

		$q = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' LIMIT 1");

		if(mysql_num_rows($q)==0)die('ERROR 422: No Order Sessions Has Been Found');

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

}


//IF REORDER HAS BEEN SET FROM ORDERS.PHP POST FORM
if($reorder=='yes'){


die('REorder');

	if($reordernumber=='3'){

	mysql_query("UPDATE `orders` SET `reorder` = '0' WHERE `id` = '$orderid123' LIMIT 1");//RESTART THE ORDERS

	}//THIS IS THE LAST COLUMN FOR SERVICE ID, SYSTEM WONT BE ABLE TO FIND THE NEXT SERVICE ID FOR THIS PARTICULAR PACKAGE
	else{
	$info['reorder']++;
	mysql_query("UPDATE `orders` SET `reorder` = `reorder` + 1 WHERE `id` = '$orderid123' LIMIT 1");}


}

include('../tiktokorderfulfill.php');

if($pagefrom =='defect'){

	$note = 'Supplier ID: '.$orderid.' order resubmitted.';
	$now = time();

	mysql_query("INSERT INTO `admin_order_notes` SET
	`orderid` = '$orderid123',
	`fulfill_id` = '$orderid',
	`notes` = '$note',
	`added` = '$now'
	");}

if($noorderid==1){$noorderstate='&auto=pause';}else{$noorderstate='&auto=resume';}

echo 'Order ID: '.$orderid.'<br>';

if($update=='save')mysql_query("UPDATE `orders` SET `defect` = '0' WHERE `id` = '$orderid123' LIMIT 1");

if(!empty($orderid)){


	if($defectpagefrom=='defect'){


		header('Location: tiktok-defectorders.php?type='.$pagefrom.'&message=updatetrue&theid='.$orderid.$noorderstate);
		die;

	}



	if($update=='nodefect'){

		header('Location: tiktok-'.$defectpagefrom.'orders.php?type='.$pagefrom.'&message=updatetrue&theid='.$orderid.$noorderstate);
		die;

	}else{

		header('Location: tiktok-'.$defectpagefrom.'orders.php?type='.$pagefrom.'&message=updatetrue2&theid='.$orderid123.$noorderstate);
	}
	die;

}
	else
{
	die('Failed to update order, tell Rabban');
}

?>