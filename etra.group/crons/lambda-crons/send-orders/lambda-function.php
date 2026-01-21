<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration

$now = time();

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` != '' AND (`fulfilled` = 0 OR `fulfilled` = '') AND `added` >= unix_timestamp(CURRENT_DATE - interval 2 day)");

while ($info = mysql_fetch_array($q)) {

    $jsonViewData = json_encode($info);
    // echo $jsonViewData;die;
    $res1 = AddQueue($check_account_status_queueUrl, $jsonViewData); // queuerl from common.php

}



?>
