<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration


$now = time();

/* FOR QUEUE TO PREVENT OVERLAP
    lambda = 1 is ec2
    lambda = 2 is aws lambda
*/

$QueryRun = mysql_query("SELECT *
                        FROM admin_statistics 
                        WHERE `type` = 'lambda_attempts' limit 1");

$dataQueryRun = mysql_fetch_array($QueryRun);

$send_sms = $dataQueryRun['send_sms'];

if($send_sms == 1) {
  $limit = " LIMIT 1";
}else{
  $limit = " LIMIT 100";
}

$q_update = mysql_query("SELECT id FROM `orders_free` WHERE `igusername` = 'mosalah' ORDER BY `id` DESC $limit;");
while ($row = mysql_fetch_array($q_update)) {$ids[] = $row['id'];}
// @mysql_query("UPDATE `orders_free` SET `lambda`='2' WHERE `id` IN (".implode(',',$ids).") ");

$q = mysql_query("SELECT * FROM `orders_free` WHERE `igusername` = 'mosalah' ORDER BY `id` DESC $limit;");
while ($info = mysql_fetch_array($q)) {
    $jsonViewData = json_encode($info);
    $res1 = AddQueue($autofulfill_free_queueurl, $jsonViewData); // queuerl from common.php
}

// lambda = 3 sent to lambda, don't retry
// mysql_query("UPDATE `orders_free` SET `lambda`='3' WHERE `id` IN (".implode(',',$ids).") ");

?>