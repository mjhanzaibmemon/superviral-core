<?php


$db = 1;
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration

//$timenow = time() - (1782000);
$timenow = time() - (2592000); //within 30 days
//$timenow = time() - (15811200);//within 163 days
$timeafterhours = time() - (90000); //once per day
$now = time();


$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '0' AND `disputed` = '0' AND `packagetype` = 'followers' AND `fulfilled` != '0' AND `added` > $timenow AND `lastrefilled` < $timeafterhours AND `norefill` = '0' ORDER BY `id` ASC LIMIT 4,1");

//Refresh with a number between 4 and 7 seconds
if (mysql_num_rows($q) == 0) {
    $message = 'All Refills Done For Today!';
    echo $message;
}

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];

    $info['fulfill_id'] = trim($info['fulfill_id']);
    $info['fulfill_id'] = $info['fulfill_id'] . ' ';


    $fulfills = explode(' ', $info['fulfill_id']);

    foreach ($fulfills as $fulfillid) {
        $id = $info['id'];


        if (empty($fulfillid)) continue;

        $fulfillid = trim($fulfillid);

        // $refillthis = $api->refill($fulfillid);

        $refillsData = array(
            'fulfill_id' => $fulfillid,
            'id' => $id,
            'brand' => $brand,
            'now' => $now
        );

        $jsonRefillsData = json_encode($refillsData);
        // $jsonRefillsData = addslashes($jsonRefillsData);

        $res = AddQueue($refills_queueUrl, $jsonRefillsData); // refills_queueUrl from common.php

        echo '<b>' . $info['id'] . '</b> - <i>/order/' . $fulfillid . '/refill' . '</i><br>';

        // mysql_query("UPDATE `orders` SET `lastrefilled` = '$now' WHERE `id` = '$id' AND brand = '$brand' LIMIT 1");
    }

    unset($fulfills);
}

////////////////////////////////////////////////////////////////
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$s3 = new S3($amazons3key, $amazons3password);

$i = 1;

//Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

$q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` ASC LIMIT 300");

if (mysql_num_rows($q) == '0') die('All Done');

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];

    $actualimagename = md5('superviralrb' . $info['shortcode']);

    $check = S3::getObjectInfo('cdn.superviral.io', 'thumbs/' . $actualimagename . '.jpg');



    if (!empty($check['time'])) {

        $existsornot = '<font color="green">Exists</font>';
    } else {


        $existsornot = '<font color="red">Not exist - Delete!</font>';

        mysql_query("DELETE FROM `ig_thumbs` WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

        echo $i . '. ' . $info['shortcode'] . ' - ' . $actualimagename . ': ' . $existsornot . '<hr>';
    }

    $i++;

    mysql_query("UPDATE `ig_thumbs` SET `checked` = '1' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

    unset($check);
}

?>
<style>
    body {
        font-family: arial;
    }
</style>