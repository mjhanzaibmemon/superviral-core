<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration


$now = time();
$threehoursago  = $now - 82000;
$fourHoursAgo = $now - 85600;


$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `disabled` = '0' AND `expires` > $now AND `last_updated` NOT BETWEEN '$threehoursago' AND '$now' ORDER BY `last_updated` ASC LIMIT 100");

// $res = AddQueue($queueUrl, $msg);
// $res = retriveQueue($queueUrl);
// if (count($res) > 0){
//     print_r($res); die;
// }
echo "Total Count Data: " .mysql_num_rows($q);

while ($info = mysql_fetch_array($q)) {

    $info['igusername'] = trim($info['igusername']);
    
    if(str_contains($info['igusername'], "tiktok.com")){$updateq = mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");continue;}
    if(str_contains($info['igusername'], "instagram.com/p/")){$updateq = mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");continue;}    
    if(str_contains($info['igusername'], "instagram.com/reel/")){$updateq = mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");continue;}    
    if(str_contains($info['igusername'], "instagram.com/share/")){$updateq = mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");continue;}    
    
    $info['igusername'] = str_replace('@', '', $info['igusername']);
    $info['igusername'] = str_replace(' ','',$info['igusername']);
    $info['igusername'] = str_replace('https://instagram.com/','',$info['igusername']);
    $info['igusername'] = str_replace('instagram.com/','',$info['igusername']);
    $info['igusername'] = str_replace('?utm_medium=copy_link','',$info['igusername']);
    $info['igusername'] = str_replace('?r=nametag','',$info['igusername']);
    $info['igusername'] = str_replace('https://www.','',$info['igusername']);
    $info['igusername'] = str_replace('?hl=en.','',$info['igusername']);

    if(empty($info['igusername'])){
        continue;
    }

    $al_expiry = date("d/m/Y", $now);

    if ($info['al_package_id'] == '0') {

        $fulfillautolikesorderid2 = $fulfillautolikesorderid;
    } else {


        $alpackageidq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '{$info['al_package_id']}' LIMIT 1");
        $alpackageidinfo = mysql_fetch_array($alpackageidq);
        $fulfillautolikesorderid2 = $alpackageidinfo['jap1'];
        $fulfillautoviewsorderid = $alpackageidinfo['views_jap1'];
    }


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
        'username' => $info['igusername'],
        'expires' => $fulfillexpires 
    );

    $allALOrdersData = array('autolikesorderData' => $autolikesorderData, 'baseData' => $baseData);

    
    $jsonLikeData = json_encode($allALOrdersData);
    // $jsonLikeData = addslashes($jsonLikeData);
    // print_r(json_encode($jsonLikeData));die;

    $res = AddQueue($queueUrl, $jsonLikeData); // queuerl from common.php

    echo "Likes added";
  
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

    echo "views added";

    $updateq = mysql_query("UPDATE `automatic_likes` SET `last_updated` = '$fourHoursAgo', `start_fulfill` = '1',`missinglikespost` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");

    unset($fulfillids);
    unset($newfulfillid);
    unset($al_expiry);
}
