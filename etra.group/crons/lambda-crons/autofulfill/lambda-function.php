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

$q_update = mysql_query("SELECT * FROM orders WHERE 
    fulfill_id = '' 
    AND fulfill_attempt < '7' 
    AND next_fulfill_attempt != '0' 
    AND lambda = '0' 
    AND refund = '0' 
    AND (
        added >= (UNIX_TIMESTAMP() - 86400) 
        OR next_fulfill_attempt >= (UNIX_TIMESTAMP() - 86400)
    ) 
    ORDER BY id DESC 
    $limit
");

while ($row = mysql_fetch_array($q_update)) {$ids[] = $row['id'];}
if($ids){
  mysql_query("UPDATE `orders` SET `lambda`='2' WHERE `id` IN (".implode(',',$ids).") ");
}else{
  die;
}


$q = mysql_query("SELECT * 
                            FROM `orders` 
                            WHERE `fulfill_id` = '' 
                              AND `fulfill_attempt` < '7' 
                              AND `next_fulfill_attempt` < $now 
                              AND `next_fulfill_attempt` != '0' 
                              AND `lambda` = '2' 
                              AND `refund` = '0' 
                              AND `added` >= (UNIX_TIMESTAMP() - 604800)
                            ORDER BY `id` DESC 
                            $limit;
");

while ($info = mysql_fetch_array($q)) {

    $jsonViewData = json_encode($info);
    // echo $jsonViewData;die;
    $res1 = AddQueue($autofulfill_queueurl, $jsonViewData); // queuerl from common.php

}


mysql_query("UPDATE `orders` SET `lambda`='3' WHERE `id` IN (".implode(',',$ids).") ");


?>
