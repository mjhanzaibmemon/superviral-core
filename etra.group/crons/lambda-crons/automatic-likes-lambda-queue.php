<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/common.php'; // common confguration


$now = time();
$threehoursago  = $now - 82000;

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `disabled` = '0' AND `expires` > $now AND `last_updated` NOT BETWEEN '$threehoursago' AND '$now' ORDER BY `id` DESC LIMIT 2");
//$q = mysql_query("SELECT * FROM `automatic_likes` WHERE id='499711'");

// $res = AddQueue($queueUrl, $msg);
// $res = retriveQueue($queueUrl);
// if (count($res) > 0){
//     print_r($res); die;
// }
echo "Total Count Data: " .mysql_num_rows($q);
while ($info = mysql_fetch_array($q)) {

    $info['igusername'] = trim($info['igusername']);
    $info['igusername'] = str_replace('@', '', $info['igusername']);

    $al_expiry = date("d/m/Y", $now);

    if ($info['al_package_id'] == '0') {

        $fulfillautolikesorderid2 = $fulfillautolikesorderid;
    } else {


        $alpackageidq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '{$info['al_package_id']}' LIMIT 1");
        $alpackageidinfo = mysql_fetch_array($alpackageidq);
        $fulfillautolikesorderid2 = $alpackageidinfo['jap1'];
        $fulfillautoviewsorderid = $alpackageidinfo['views_jap1'];
    }


    // $autolikesorder = $api->order(array(
    //     'service' => $fulfillautolikesorderid2, 
    //     'username' => $info['igusername'], 
    //     'min' => $info['likes_per_post'],
    //     'max' => $info['likes_per_post'] * 1.10,
    //     'posts' => $info['max_post_per_day'],
    //     'delay' => '5',
    //     'expiry' => $al_expiry
    //     ));


    $autolikesorderData = array(
        'service' => $fulfillautolikesorderid2,
        'username' => $info['igusername'],
        'min' => $info['likes_per_post'],
        'max' => $info['likes_per_post'] * 1.10,
        'posts' => $info['max_post_per_day'],
        'delay' => '5',
        'expiry' => $al_expiry
    );

    $fulfillexpires = strtotime("tomorrow", $now);
    $baseData = array(
        'autolikeId' => $info['id'], 
        'now' => $now, 
        'expires' => $fulfillexpires 
    );
    $allALOrdersData = array('autolikesorderData' => $autolikesorderData, 'baseData' => $baseData);

    
    $jsonLikeData = json_encode($allALOrdersData);
    
    // $jsonLikeData = addslashes($jsonLikeData);
    // print_r(json_encode($jsonLikeData));die;

    $res = AddQueue($queueUrl, $jsonLikeData); // queuerl from common.php
    
    // $res = retriveQueue($queueUrl);
    // $al_fullfill_id = trim($autolikesorder->order);

    // if (empty($al_fullfill_id)) continue;

    // $autoviewsorder = $api->order(array(
    // 'service' => $fulfillautoviewsorderid, 
    // 'username' => $info['igusername'], 
    // 'min' => $info['likes_per_post'] * 10,
    // 'max' => $info['likes_per_post'] * 12,
    // 'posts' => $info['max_post_per_day'],
    // 'delay' => '5',
    // 'expiry' => $al_expiry
    // ));

    $autoviewsorderData = array(
        'service' => $fulfillautoviewsorderid,
        'username' => $info['igusername'],
        'min' => $info['likes_per_post'] * 10,
        'max' => $info['likes_per_post'] * 12,
        'posts' => $info['max_post_per_day'],
        'delay' => '5',
        'expiry' => $al_expiry
    );

    $allALOrdersData = array('autolikesorderData' => $autoviewsorderData, 'baseData' => []);

    $jsonViewData = json_encode($allALOrdersData);
    // $jsonViewData = addslashes($jsonViewData);
    $res1 = AddQueue($queueUrl, $jsonViewData); // queuerl from common.php
    // print_r(json_encode($res1));

    $updateq = mysql_query("UPDATE `automatic_likes` SET `last_updated` = '$now', `start_fulfill` = '1',`missinglikespost` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");

    // if ($updateq) {


        // $fulfilladded = $now;
        // $fulfillexpires = strtotime("tomorrow", $now);

        echo 'ID: ' . $info['id'] . '<br>Username: ' . $info['igusername'] . '<br>Fulfill ID: ' . $al_fullfill_id . '<br>Fulfill Added: ' . $fulfilladded . '<br>Fulfill Expired: ' . $fulfillexpires . '<hr>';

        // mysql_query("INSERT INTO `automatic_likes_fulfill` SET 
        //         `auto_likes_id` = '{$info['id']}',
        //         `fulfill_id` = '$al_fullfill_id',
        //         `added` = '$fulfilladded',
        //         `expires` = '$fulfillexpires'
        //         ");
    // } else {

    //     echo 'Error updating';
    // }

    unset($fulfillids);
    unset($newfulfillid);
    unset($al_expiry);
}
