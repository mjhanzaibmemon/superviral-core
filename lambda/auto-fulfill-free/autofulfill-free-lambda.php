<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

/////////

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../supplier_raw/supplier_raw.php';
require __DIR__ . '/../common/common.php';

use Aws\Sqs\SqsClient;
use Bref\Context\Context;

global $sqsClient;
$sqsClient = new SqsClient([
    'region'  => 'us-east-2',  // Your AWS region
    'version' => 'latest',
    'credentials' => [
        'key'    => getenv('amazonLambdaKey'),
        'secret' => getenv('amazonLambdapassword'),
    ],
]);




// $q = mysql_query("SELECT * FROM `orders_free` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `packagetype` IN ('freelikes','freetrial','freefollowers') ORDER BY `id` DESC LIMIT 10");

return function (array $event, Context $context) {

    global $api;

    $now = time();

    global $sms_QueueUrl, $log_query_queueUrl, $sqsClient;

    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Received `event`:". json_encode($event) ."'";
    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

    writeCloudWatchLog('autofulfill-free-lambda', 'Received `event`:'. json_encode($event));

    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'lambda_attempts' LIMIT 1");

    sendCloudwatchData('AWSLambda', 'lambda-attempts', 'AdminStats', 'lambda-attempts-function', 1);
    
    $count_q = mysql_query("SELECT * FROM `admin_statistics` LIMIT 1");
    $count_arr = mysql_fetch_array($count_q);
    if($count_arr['lambda_attempts'] > 300 && $count_arr['send_sms']=='0'){
        mysql_query("UPDATE `admin_statistics` SET `send_sms` = 1 WHERE `type` = 'lambda_attempts' LIMIT 1");
        $sms_from = "SUPERVIRAL";
        $messagebird_body = "Lambda is getting busy - autofulfill free";             
        $sms_arr = array(
            'to' => array('+447872545933'),
            'from' => $sms_from,
            'body' => $messagebird_body
        );
        sendMessageToSqs(json_encode($sms_arr), $sms_QueueUrl, $sqsClient);

        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Lambda is getting busy'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);    

        writeCloudWatchLog('autofulfill-free-lambda', 'Lambda is getting busy');

        echo 'Inserted into SMS_SQS';
    }

    // Print the entire event received
    // echo "Received event: " . json_encode($event) . "\n";
    //  die;
    // Check if there are records (SQS messages)
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            // while ($info = mysql_fetch_array($q)) {

            $jsonString = stripslashes($record['body']);
            $info = json_decode($jsonString, true);
                
            $socialmedia = $info['socialmedia'];
            $pacid = $info['packageid'];
            $username = $info['igusername'];
            $supplier_cost = 0;

            $check_order_done_q = mysql_query("SELECT `fulfill_id` FROM `orders_free` WHERE `id`='".$info['id']."' LIMIT 1");
            $check_order_done_data = mysql_fetch_array($check_order_done_q);
            $check_fulfill_id = $check_order_done_data['fulfill_id'];
          
            if(!empty($check_fulfill_id)){
                continue;
            }

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Check if fulfilled'";
            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);   
            
            writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'- Check if fulfilled');
            
            $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
            $packageinfo = mysql_fetch_array($packageinfoq);
            // print_r($packageinfo);die;
            $japid = 'jap1';


            $delivquantity = $info['amount'];

            switch ($socialmedia) {
                case 'ig':
                    $domain = 'instagram.com';
                    break;
                case 'tt':
                    $domain = 'tiktok.com';
                    $username = '@' . $username;
                    break;
            }

            if ($brand == 'to') {
                $socialmedia = '';
                $domain = 'tiktok.com';
                $username = '@' . $username;
            }

            //FREE TRIAL 30 FOLLOWERS
            if ($packageinfo['type'] == "freetrial" || $packageinfo['type'] == "freefollowers") {

                $delivquantity = $info['amount'] * 1.1;
                $delivquantity = round($delivquantity);

                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Free followers fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                

                writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Free followers fulfillment');

                $orderid = $order1->order;
                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;
                mysql_query('INSERT INTO `supplier_cost` SET `type` = "followers", `amount` = '.$delivquantity.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-free-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "sv"');                
            }


            if ($packageinfo['type'] == "freelikes") {

                $freelikespost = trim($info['chooseposts']);

                $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders_free WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda = 0');
                $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
                if (mysql_num_rows($checkifLastOrderDoneQuery) > 0) {
                    // means last order not done, queue the next same order
                    $error = date('d/m/Y') . ' - Existing order in process';

                    $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                    continue;
                }

                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $freelikespost . '/', 'quantity' => $delivquantity));
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Free likes fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                

                writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Free likes fulfillment');
                $orderid = $order1->order;

                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;

                $thisorderpost .= '<br><b>Free Likes:</b><br>Post name:' . $freelikespost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
            }

            $supplier_error = $order1->error;

            if ((!empty($orderid)) && (preg_match('~[0-9]+~', $orderid)) && empty($supplier_error)) {

                $updateq = mysql_query("UPDATE `orders_free` SET `done` = '1',`fulfill_id` = '$orderid', `supplier_cost` = '$supplier_cost' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");
                if ($updateq) {
                    $status = '<font color="green">Fulfilled!</font>';
                    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Success fulfillment'";
                    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

                    writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Success fulfillment');
                } else {
                    $status = '<font color="orange">Fulfilled! but failed to update DB</font>';
                    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Success fulfillment, Fail DB'";
                    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);
                    
                    writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Success fulfillment, Fail DB');
                }
            } else {

                //NO ORDER ID HAS COME BACK
                $status = updateFulfillAttempt($info['fulfill_attempt'], $info['next_fulfill_attempt'], $supplier_error, $info['id']);
            }

            echo $status;

            unset($username);
            unset($thisorderpost);
            unset($pacid);
            unset($chooseposts);
            unset($orderid);
            unset($order1);
            unset($packageinfo);
            unset($last_order_row);
            unset($status);
            unset($nextdelay);
            unset($next_fulfill_attempt);
            unset($chooseposts);
            unset($updateq);
            unset($freelikespost);
            unset($delivquantity);
            unset($supplier_cost);
            unset($supplier_error);
            // }
        }
    }
};

echo '
    <style>
    body{font-family:arial;}
    h3{font-size:16px;}
    </style>';


function updateFulfillAttempt($i, $timestamp, $error, $id)
{
    global $log_query_queueUrl, $sqsClient;

    //DELAY BASED ON STAGE OF FULFILL ATTEMPTS
    if ($i == '1') $nextdelay = '100';
    if ($i == '2') $nextdelay = '600';
    if ($i == '3') $nextdelay = '1800';
    if ($i == '4') $nextdelay = '3600';
    if ($i == '5') $nextdelay = '7200';
    if ($i == '6') $nextdelay = '15800';

    $next_fulfill_attempt = $timestamp + $nextdelay;

    $updateq = mysql_query("UPDATE `orders_free` SET `fulfill_attempt` = `fulfill_attempt` + 1, `next_fulfill_attempt` = '$next_fulfill_attempt', `supplier_errors` = '$error' WHERE `id` = '{$id}' LIMIT 1");

    if ($updateq) {
        return '<font color="red">Not fulfilled!</font>';
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Failed fulfillment'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);        

        writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Failed fulfillment');
    } else {
        return '<font color="orange">Not fulfilled! but failed to update DB</font>';
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-free-lambda', added ='$now', `log` = 'Success fulfillment, failed db'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);        

        writeCloudWatchLog('autofulfill-free-lambda', 'Order Id : '. $info['id'] .'-Not fulfilled! but failed to update DB');
    }

}
