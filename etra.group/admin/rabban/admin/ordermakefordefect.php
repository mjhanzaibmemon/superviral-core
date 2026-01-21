<?php

include('adminheader.php');

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



/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////


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

/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////


if($freeorder==0){//IF IT ISNT A FREE ORDER THEN CONTINUE AS USUAL


        $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' LIMIT 1");

        if(mysql_num_rows($q)==0)die('ERROR 422: No Order Sessions Has Been Found');

        $info = mysql_fetch_array($q);

        //////CCHOOSE POSTS
        $choosepostsql = '';
        $multiamountposts = 0;

        if(!empty($info['chooseposts'])){
        

        $chooseposts = explode(' ', $info['chooseposts']);

        foreach($chooseposts as $posts1){

        if(empty($posts1))continue;

        //$posts2 = explode('###', $posts1);

        $multiamountposts++;//INCREMENT BY 1

        $choosepostsql .= $posts1.' ';

        }


        }

        ////////////// UPSELL ACTUAL AMOUNT TO ORDER and workout if this order is an order with UPSELL

        $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
        $packageinfo = mysql_fetch_array($packageinfoq); 

        if($info['amount']!==$packageinfo['amount']){//ITS AN UPSELL

            $upsellamount = round($packageinfo['amount'] * 0.50);
            $info['upsell'] = $upsellamount.'###';

        }


}

//IF REORDER HAS BEEN SET FROM ORDERS.PHP POST FORM
if($reorder=='yes'){


    if($reordernumber=='3'){//$reordernumber is fetched on this PHP file

      mysql_query("UPDATE `orders` SET `reorder` = '0' WHERE `id` = '$orderid123' LIMIT 1");//RESTART THE ORDERS

    }       //THIS IS THE LAST COLUMN FOR SERVICE ID, SYSTEM WONT BE ABLE TO FIND THE NEXT SERVICE ID FOR THIS PARTICULAR PACKAGE
    else{

        $info['reorder']++;
        mysql_query("UPDATE `orders` SET `reorder` = `reorder` + 1 WHERE `id` = '$orderid123' LIMIT 1");

    }

    $note = 'Supplier ID: '.$orderid.' order resubmitted.';
    $now = time();

}

include('../orderfulfill.php');

mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid',`defect` = '0' WHERE `id` = '$orderid123' ORDER BY `id` DESC LIMIT 1");

    mysql_query("INSERT INTO `admin_order_notes` SET
    `orderid` = '$orderid123',
    `fulfill_id` = '$orderid',
    `notes` = '$note',
    `added` = '$now'
    ");






if($noorderid==1){$noorderstate='&auto=pause';}else{$noorderstate='&auto=resume';}

echo 'Order ID: '.$orderid.'<br>';

if($update=='save')mysql_query("UPDATE `orders` SET `defect` = '0' WHERE `id` = '$orderid123' LIMIT 1");

if(!empty($orderid)){


    if($defectpagefrom=='defect'){

        echo 'redirect now <br>';
    
        echo '<meta http-equiv="refresh" content="0;url=defectorders.php?type='.$pagefrom.'&message=updatetrue&mainid='.$orderid123.'&theid='.$orderid.$noorderstate.'" />';


    }


    die;

}
 
?>