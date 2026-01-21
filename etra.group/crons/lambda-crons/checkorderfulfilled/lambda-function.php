<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


$db = 1;
//require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration

/////////////////////

$time = time();
$timeafterhours = time() - (6000);

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND `lastchecked` < $timeafterhours ORDER BY `id` DESC LIMIT 50");

if (mysql_num_rows($q) == '0') {die('no more orders to search for');}

while ($info = mysql_fetch_array($q)) {
    
    // Define array to send to AWS
    $allData = array(
        'row'=> [],
        'dataset'=> []
    );

    $allData['row'] = $info;

    // account table details
    $searchaccountq = mysql_query("SELECT * FROM `accounts` WHERE `id` =  '{$info['account_id']}' LIMIT 1");
    $searchaccountinfo = mysql_fetch_array($searchaccountq);
    $allData['dataset']['freeautolikes'] = $searchaccountinfo['freeautolikes'];

    // user table details
    $fetchuserinfo = mysql_fetch_array(mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND brand = '$brand' LIMIT 1"));
    $md5unsub = $fetchuserinfo['md5'];
    $allData['dataset']['unsubmd5'] = $md5unsub;

    if (empty($info['brand'])) $info['brand'] == 'sv';

    $loc2 = $info['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '/';
    if ($loc2 == 'ww/') $loc2 = '';
    if ($loc2 == 'us/') $loc2 = '';
    $allData['dataset']['loc'] = $loc2;

    //
    $brand = $info['brand'];
    $allData['dataset']['brand'] = $brand;

    $socialmedia = $info['socialmedia'];

    switch ($socialmedia) {
        case 'ig':
            $keyword = "instagram";
            $socialmedia = 'Instagram';
            break;
        case 'tt':
            $keyword = "tiktok";
            $socialmedia = 'TikTok';
            break;
    }
    $allData['dataset']['keyword'] = $keyword;
    $allData['dataset']['socialmedia'] = $socialmedia;

    switch ($brand) {
        case 'sv':
            $domain = 'superviral.io';
            $website = 'Superviral';
            break;
        case 'to':
            $domain = 'tikoid.com';
            $website = 'Tikoid';
            break;
        case 'fb':
            $domain = 'feedbuzz.io';
            $website = 'Feedbuzz';
            break;
        case 'tp':
            $domain = 'tokpop.com';
            $website = 'Tokpop';
            break;
        case 'sz':
            $domain = 'swizzy.io';
            $website = 'Swizzy';
            break;
    }
    $allData['dataset']['domain'] = $domain;
    $allData['dataset']['website'] = $website;

    $time = time();
    $allData['dataset']['time'] = $time;

    mysql_query("UPDATE `orders` SET `lastchecked` = '$time' WHERE `id` = '{$info['id']}' LIMIT 1");
    
    //MAKE SURE WE GET THE DELIVERY TIME SET IN AS WELL
    $deliverytime = $time - $info['added'];
    $allData['dataset']['deliverytime'] = $deliverytime;

    if (($deliverytime <= 4600)) {
        if ($info['packageid'] !== '18') {
            echo 'Too early to mark as done - non free followers<br>';
            $tooearly = 1;
        }

        if (($info['packageid'] == '18') && ($deliverytime <= 600)) {
            echo 'Too early to mark as done - free followers<br>';
            $tooearly = 1;
        }
    }

    $morethansixmonths = $time - 15552000;

    if ($info['added'] >  $morethansixmonths && $info['added'] < ($time + 4000)) {
        echo 'Within 6-months<br>';
    } else {
        echo 'NOT Within 6-months<br>'; //mark this as done, customer lost interest at this point
    }
    
    // Insert into SQS event data
    $jsonViewData = json_encode($allData);
    echo '<pre>' . print_r($jsonViewData, true) . '</pre>';
    // die;
    
    if($tooearly !== 1){
        $res1 = AddQueue($checkorderfulfilled_queueUrl, $jsonViewData); // queuerl from common.php
        echo '<hr>';
    }
    
}
