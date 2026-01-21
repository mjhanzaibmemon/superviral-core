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
$ids = addslashes($_POST['ids']);
$reorder = addslashes($_POST['reorder']);
$update = addslashes($_POST['update']);
$pagefrom = addslashes($_POST['pagefrom']);
$key = addslashes($_POST['key']);
$defectpagefrom = addslashes($_POST['defectpage']);

if (!empty($defectpagefrom)) {
    $defectpagefrom = 'defect';
}

$orders = explode(',', $ids);

$orderCount = 0;
while ($orderCount < count($orders)) {

    $orderid123 = $orders[$orderCount];

    if (empty($orderid123)) die('No order number');

    $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1");

    if (mysql_num_rows($q) == '0') die('ERROR 315: No Order Has Been Found');


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


    if (($fetchtrialorder['packagetype'] == 'freefollowers') || ($fetchtrialorder['packagetype'] == 'freelikes')) {

        $freeorder = 1;

        if ($fetchtrialorder['packagetype'] == 'freefollowers') {

            $info['packageid'] = '18';
            $info['igusername'] = $fetchtrialorder['igusername'];
            $info['order_session'] = $fetchtrialorder['order_session'];
            $hash = $fetchtrialorder['order_session'];
        }





        if ($fetchtrialorder['packagetype'] == 'freelikes') {

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


    if ($freeorder == 0) { //IF IT ISNT A FREE ORDER THEN CONTINUE AS USUAL


        $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1");

        if (mysql_num_rows($q) == 0) die('ERROR 422: No Order Sessions Has Been Found');

        $info = mysql_fetch_array($q);

        //////CCHOOSE POSTS
        $choosepostsql = '';
        $multiamountposts = 0;

        if (!empty($info['chooseposts'])) {
            $chooseposts = explode(' ', $info['chooseposts']);

            foreach ($chooseposts as $posts1) {

                if (empty($posts1)) continue;

                $posts2 = explode('###', $posts1);

                $multiamountposts++;

                $choosepostsql .= $posts2[0] . ' ';
            }
        }

        ////////////// UPSELL ACTUAL AMOUNT TO ORDER and workout if this order is an order with UPSELL

        $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND brand = '$brand' LIMIT 1");
        $packageinfo = mysql_fetch_array($packageinfoq);

        if ($info['amount'] !== $packageinfo['amount']) { //ITS AN UPSELL

            $upsellamount = round($packageinfo['amount'] * 0.50);
            $info['upsell'] = $upsellamount . '###';
        }
    }

    //IF REORDER HAS BEEN SET FROM ORDERS.PHP POST FORM
    if ($reorder == 'yes') {

        if ($reordernumber == '3') { //$reordernumber is fetched on this PHP file

            mysql_query("UPDATE `orders` SET `reorder` = '0' WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1"); //RESTART THE ORDERS

        }       //THIS IS THE LAST COLUMN FOR SERVICE ID, SYSTEM WONT BE ABLE TO FIND THE NEXT SERVICE ID FOR THIS PARTICULAR PACKAGE
        else {

            $info['reorder']++;
            mysql_query("UPDATE `orders` SET `reorder` = `reorder` + 1 WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1");
        }

        $note = 'Supplier ID: ' . $orderid . ' order resubmitted.';
        $now = time();
    }

    $socialmedia = $info['socialmedia'];

    if($socialmedia == 'ig')
    include  $_SERVER['DOCUMENT_ROOT'] . '/admin/api/igorderfulfill.php';
    if($socialmedia == 'tt')
    include  $_SERVER['DOCUMENT_ROOT'] . '/admin/api/ttorderfulfill.php';

    mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid',`defect` = '0' WHERE `id` = '$orderid123' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");

    mysql_query("INSERT INTO `admin_order_notes` SET
    `orderid` = '$orderid123',
    `fulfill_id` = '$orderid',
    `notes` = '$note',
    `added` = '$now',
     brand = '$brand'
    ");


    if ($noorderid == 1) {
        $noorderstate = '&auto=pause';
      
    } else {
        $noorderstate = '&auto=resume';
    }

    echo 'Order ID: ' . $orderid . '<br>';

    if ($update == 'save') mysql_query("UPDATE `orders` SET `defect` = '0' WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1");

    if (empty(trim($orderid))) {
        $error[] = $orderid123;
    }
  
    $orderCount++;

}

if (empty($error)) {


    if ($defectpagefrom == 'defect') {
        header('Location: /admin/resend-orders/?type=' . $pagefrom . '&message=updatetrue&theid=' . $orderid . $noorderstate);
    }
    die;

}else{
    $amp = implode(',',$error);
    
    echo $amp;

    if ($defectpagefrom == 'defect') {
        header('Location: /admin/resend-orders/?user='. $key .'&err='.$amp);
    }
    die;
}