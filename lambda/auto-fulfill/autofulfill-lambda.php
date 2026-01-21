<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

/////////////

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../supplier_raw/supplier_raw.php';
require __DIR__ . '/../socialmedia-api/socialmedia-api-lambda.php';
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




// $q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `refund` = '0' ORDER BY `id` DESC LIMIT 20");

return function (array $event, Context $context) {

    global $api;

    $now = time();

    global $sms_QueueUrl, $log_query_queueUrl, $sqsClient;

    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Received `event`:". json_encode($event) ."'";
    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

    writeCloudWatchLog('autofulfill-lambda', 'Received `event`:'. json_encode($event));

    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'lambda_attempts' LIMIT 1");

    sendCloudwatchData('AWSLambda', 'lambda-attempts', 'AdminStats', 'lambda-attempts-function', 1);

    $count_q = mysql_query("SELECT * FROM `admin_statistics` WHERE `type` = 'lambda_attempts' LIMIT 1");
    $count_arr = mysql_fetch_array($count_q);
    if($count_arr['metric'] > 100 && $count_arr['send_sms']=='0'){
        mysql_query("UPDATE `admin_statistics` SET `send_sms` = 1 WHERE `type` = 'lambda_attempts' LIMIT 1");        
        $sms_from = "SUPERVIRAL";
        $messagebird_body = "Lambda is getting busy - autofulfill";             
        $sms_arr = array(
            'to' => array('+447872545933'),
            'from' => $sms_from,
            'body' => $messagebird_body
        );
        sendMessageToSqs(json_encode($sms_arr), $sms_QueueUrl, $sqsClient);

        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Lambda is getting busy'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);    

        writeCloudWatchLog('autofulfill-lambda', 'Lambda is getting busy');

        echo 'Inserted into SMS_SQS';
    }

    // Print the entire event received
    // echo "Received event: " . json_encode($event) . "\n";
    // die;
    // Check if there are records (SQS messages)
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            // while ($info = mysql_fetch_array($q)) {
            $jsonString = stripslashes($record['body']);
            $info = json_decode($jsonString, true);
            
            $socialmedia = $info['socialmedia'];
            $pacid = $info['packageid'];
            $username = $info['igusername'];
            $brand = $info['brand'];
            $supplier_cost = 0;

            $check_order_done_q = mysql_query("SELECT `fulfill_id` FROM `orders` WHERE `id`='".$info['id']."' LIMIT 1");
            $check_order_done_data = mysql_fetch_array($check_order_done_q);
            $check_fulfill_id = $check_order_done_data['fulfill_id'];
            if(!empty($check_fulfill_id)){
                continue;
            }

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Check if fulfilled'";
            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);       
            
            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Check if fulfilled');

            $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
            $packageinfo = mysql_fetch_array($packageinfoq);

            ///////// JAP ALGO

            mysql_query("SET @sid_out = 0");
            mysql_query("CALL sp_ts_choose_sid({$info['id']},  $pacid, @sid_out)");

            // Get OUT parameter
            $result = mysql_query("SELECT @sid_out AS sid");
            $row = mysql_fetch_array($result);
            echo json_encode($row);
            $supplierSid = $row['sid'] ?? 'jap1';

           // writeCloudWatchLog('autofulfill-lambda', 'Algo returns Jap: '. $supplierSid .' for Order Id: '. $info['id'] . '- Check if fulfilled');
            // 2. Register order for maturation tracking
            mysql_query("CALL sp_ts_register_order({$info['id']},  $pacid, '$supplierSid')");

            ///////// JAP ALGO DONE

            // $japid = $supplierSid;
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
            $thisorderpost = '';
            //FREE TRIAL / FREE FOLLOWERS 30 FOLLOWERS
            if ($packageinfo['type'] == "freetrial" || $packageinfo['type'] == "freefollowers") {

                $delivquantity = $info['amount'] * 1.1;
                $delivquantity = round($delivquantity);

                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));
                
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Free followers fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);            

                writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Free followers fulfillment');

                $orderid = $order1->order;

                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;

                mysql_query('INSERT INTO `supplier_cost` SET `type` = "followers", `amount` = '.$delivquantity.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');
            }


            if ($packageinfo['type'] == "freelikes") {

                $freelikespost = trim($info['chooseposts']);

                $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0');
                $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
                if (mysql_num_rows($checkifLastOrderDoneQuery) > 0) {
                    echo 'SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0';
                    
                    // means last order not done, queue the next same order
                    $error = date('d/m/Y') . ' - Existing order in process';

                    $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                    continue;
                }

                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $freelikespost . '/', 'quantity' => $delivquantity));

                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Free likes fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);            

                writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Free likes fulfillment');
                $orderid = $order1->order;

                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;

                mysql_query('INSERT INTO `supplier_cost` SET `type` = "likes", `amount` = '.$delivquantity.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');

                $thisorderpost .= '<br><b>Free Likes:</b><br>Post name:' . $freelikespost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
            }

            //FOLLOWERS

            if ($packageinfo['type'] == 'followers') {

            $checkifLastFreeOrderDoneQuery = mysql_query('SELECT * FROM orders_free WHERE `fulfill_id` = "" AND `igusername` = "' . $info['igusername'] . '" AND fulfill_attempt > 0 AND lambda=0');
            if (mysql_num_rows($checkifLastFreeOrderDoneQuery) > 0) {
                echo 'SELECT * FROM orders_free WHERE `fulfill_id` = "" AND `igusername` = "' . $info['igusername'] . '" AND fulfill_attempt > 0 AND lambda=0';
                
                // means last order not done, queue the next same order
                $error = date('d/m/Y') . ' - Existing order in process';

                $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `igusername` = "' . $info['igusername'] . '" AND defect=0 AND refund=0');
                $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);

                $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                continue;
            }

            $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `igusername` = "' . $info['igusername'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0');
            $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
            if (mysql_num_rows($checkifLastOrderDoneQuery) > 0) {
                echo 'SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `igusername` = "' . $info['igusername'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0';
                
                // means last order not done, queue the next same order
                $error = date('d/m/Y') . ' - Existing order in process';
                $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                
                continue;
            }
            // add current stats (followers)
            if($socialmedia == 'ig'){
                $data_response = call_api('', $username, '', 'is_private', '');
                $response = json_decode($data_response);
                $current_followers = $response->data->user->follower_count;
            }else{
                $data_response = call_api('', $username, '', 'is_private', '', 'tt');
                $response = json_decode($data_response);
                $current_followers = $response->data->user->follower_count;
            }
            mysql_query("UPDATE `orders` SET curr_engagement = '$current_followers' WHERE `id`='".$info['id']."' LIMIT 1");
            // print_r($response);die;

                if ($info['amount'] == '25') {
                    $info['amount'] = '51';
                }


                $delivquantity = $info['amount'] * 1.06;
                $delivquantity = round($delivquantity);

                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));
                
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Followers fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);  
                
                writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Followers fulfillment');

                $orderid = $order1->order;

                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;

                mysql_query('INSERT INTO `supplier_cost` SET `type` = "followers", `amount` = '.$delivquantity.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');

                $thisorderpost .= '<br><b>Followers</b>:<br>PID: ' . $packageinfo[$japid] . '<br>Amount: ' . $delivquantity . '<br>Order ID: ' . $orderid . '<br>';
            }

            //LIKES & VIDEO VIEWS

            if (($packageinfo['type'] == 'likes') || ($packageinfo['type'] == 'views')) {

                /// WORKOUT HOW MANY POSTS THE USER HAS SELECTED
                $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0');
                $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
                if (mysql_num_rows($checkifLastOrderDoneQuery) > 0) {
                    echo 'SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0';
                    
                    // means last order not done, queue the next same order
                    $error = date('d/m/Y') . ' - Existing order in process';
                    $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                    continue;
                }

                if (empty($info['chooseposts'])) {


                    $findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");

                    if (mysql_num_rows($findchoosepostsq) == 1) {
                        $findchooseposts = mysql_fetch_array($findchoosepostsq);

                        $thechoosepostsfound = $findchooseposts['chooseposts'];

                        $theupdatequery = '';
                        if (!empty($thechoosepostsfound)) {

                            $thechoosepostsfound = explode('~~~', $thechoosepostsfound);

                            foreach ($thechoosepostsfound as $posts1) {

                                if (empty($posts1)) continue;

                                $posts2 = explode('###', $posts1);

                                $theupdatequery .= $posts2[0] . ' ';
                                $info['chooseposts'] .= $posts2[0] . ' ';
                            }

                            $theupdatequery1 = ' Update query: "' . $theupdatequery . '" ';
                            $chooseposts = ' FOUND: ';
                            mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}'  LIMIT 1");
                        }
                    }
                }

                $multiamountposts = 0;
                $choosepostsql = '';
                if (!empty($info['chooseposts'])) {

                    $chooseposts = explode(' ', $info['chooseposts']);

                    foreach ($chooseposts as $posts1) {

                        if (empty($posts1)) continue;

                        $posts2 = explode('###', $posts1);

                        $multiamountposts++;

                        $choosepostsql .= $posts2[0] . ' ';
                    }
                }

                // free views
                $choosefreepostsql = '';
                if (!empty($info['freeviewsposts'])) {

                    $freeviewsposts = explode(' ', $info['freeviewsposts']);

                    foreach ($freeviewsposts as $freeposts1) {

                        if (empty($freeposts1)) continue;

                        $freeposts2 = explode('###', $freeposts1);

                        $choosefreepostsql .= $freeposts2[0] . ' ';

                        // if package is view , combining both columns
                        if ($packageinfo['type'] == 'views') {
                            $choosepostsql .= $freeposts2[0] . ' ';
                        }
                    }
                }

                if ($multiamountposts == 0) continue;

                // remove duplicates post if exist
                $substrings = explode(" ", $choosepostsql);
                $uniqueSubstrings = array_unique($substrings);
                $choosepostsql = implode(" ", $uniqueSubstrings);

                $totaladdedamount = round($delivquantity * 1.3);

                $multiamount = $totaladdedamount / $multiamountposts;
                $multiamount = round($multiamount);

                $multipleposts = explode(' ', $choosepostsql);

                $postCount = count($multipleposts) - 1;
                $current_count = '';
                foreach ($multipleposts as $eachpost) {

                    $checkifLastOrderDoneQuery = mysql_query('SELECT * FROM orders_free WHERE `fulfill_id` = "" AND `chooseposts` = "' . $eachpost . '" AND fulfill_attempt > 0 AND lambda=0');
                    
                    if (mysql_num_rows($checkifLastOrderDoneQuery) > 0) {
                        echo 'SELECT * FROM orders_free WHERE `fulfill_id` = "" AND `chooseposts` = "' . $eachpost . '" AND fulfill_attempt > 0 AND lambda=0';
                        
                        // means last order not done, queue the next same order
                        $error = date('d/m/Y') . ' - Existing order in process';
                       
                        $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "' . $info['chooseposts'] . '" AND fulfill_attempt > 0 AND lambda=0 AND defect=0 AND refund=0');
                        $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
                     
                        $status = updateFulfillAttempt($info['fulfill_attempt'], $last_order_row['next_fulfill_attempt'], $error, $info['id']);
                        continue;
                    }

                    if (empty($eachpost)) continue;


                    if ($packageinfo['type'] == 'likes') {

                         // add current stats (likes)
                        if($socialmedia == 'ig'){
                            $data_response = call_api('', $username, $eachpost, 'post', '');
                            $response = json_decode($data_response);
                            $current_count .= $response->data->items[0]->like_count . ' ';
                        }else{
                            $data_response = call_api('', $username, $eachpost, 'post', '', 'tt');
                            $response = json_decode($data_response);
                            $current_count = $response->data->aweme_detail->statistics->digg_count . ' ';
                        }
                        if ($socialmedia == 'ig') {
                            $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Likes IG fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);
                            
                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Likes IG fulfillment');
                            echo 'ig';
                        }

                        if ($socialmedia == 'tt') {
                            $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Likes TT fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                 
                            
                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Likes TT fulfillment');
                            echo 'tt';
                        }

                        $order_status = $api->status($order1->order);

                        if ($supplier_cost == 0) {
                            $supplier_cost = $order_status->charge;
                        }

                        mysql_query('INSERT INTO `supplier_cost` SET `type` = "likes", `amount` = '.$multiamount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');

                        $thisorderpost .= '<br><b>Likes:</b><br>Post name:' . $eachpost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
                    }

                    if ($packageinfo['type'] == 'views') {

                        // add current stats (views)
                        if($socialmedia == 'ig'){
                            $data_response = call_api('', $username, $eachpost, 'post', '');
                            $response = json_decode($data_response);
                            $current_count .= $response->data->items[0]->play_count . ' ';
                        }else{
                            $data_response = call_api('', $username, $eachpost, 'post', '', 'tt');
                            $response = json_decode($data_response);
                            $current_count = $response->data->aweme_detail->statistics->play_count . ' ';
                        }

                        if ($socialmedia == 'ig') {
                            $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Views IG fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);  
                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Views IG fulfillment');                          
                        }

                        if ($socialmedia == 'tt') {
                            $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Likes TT fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                            

                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Likes TT fulfillment');
                        }

                        $order_status = $api->status($order1->order);
                        if ($supplier_cost == 0) {
                            $supplier_cost = $order_status->charge;
                        }

                        mysql_query('INSERT INTO `supplier_cost` SET `type` = "views", `amount` = '.$multiamount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');

                        $thisorderpost .= '<br><b>Views:</b><br>Post name:' . $eachpost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
                    }



                    $orderid .= $order1->order;
                    $orderid .= ' ';
                }

                mysql_query("UPDATE `orders` SET curr_engagement = '$current_count' WHERE `id`='".$info['id']."' LIMIT 1");

                // free views
                $supplier_cost_free_views = 0;

                if ($packageinfo['type'] == "likes") {

                    if ($multiamount < 100) {
                        $multiamount  = 100;
                    }

                    $multiplefreeposts = explode(' ', $choosefreepostsql);

                    $packageviewinfoq = mysql_query("SELECT * FROM `packages` WHERE `socialmedia`= '$socialmedia' AND `type`= 'views' ORDER BY `amount` LIMIT 1");
                    $packageviewinfo = mysql_fetch_array($packageviewinfoq);

                    foreach ($multiplefreeposts as $eachfreepost) {

                        if (empty($eachfreepost)) continue;

                        if ($socialmedia == 'ig'){
                            $order1 = $api->order(array('service' => $packageviewinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachfreepost . '/', 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Free Views IG fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Free Views IG fulfillment');
                        }

                        if ($socialmedia == 'tt'){
                            $order1 = $api->order(array('service' => $packageviewinfo[$japid], 'link' => $eachfreepost, 'quantity' => $multiamount));
                            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Free Views TT fulfillment'";
                            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

                            writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Free Views TT fulfillment');
                        }
                        $order_status = $api->status($order1->order);
                        if ($supplier_cost_free_views == 0) {
                            $supplier_cost_free_views = $order_status->charge;
                        }

                        mysql_query('INSERT INTO `supplier_cost` SET `type` = "likes", `amount` = '.$multiamount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');

                        $thisorderpost .= '<br><b>Views:</b><br>Post name:' . $eachfreepost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
                    }
                }

                $supplier_cost = ($supplier_cost + $supplier_cost_free_views) * $postCount;
            }

            //COMMENTS
            $comments = '';
            if ($packageinfo['type'] == 'comments') {

                echo $info['id'] . '<hr>';

                $eachpost = trim($info['chooseposts']);

                $multipleposts = explode(' ', $info['choose_comments']);
                $amount = count($multipleposts);

                foreach ($multipleposts as $eachcommentid) {

                    if (empty($eachcommentid)) continue;

                    $eachcommentid = trim($eachcommentid);


                    $findcommentbyidq = mysql_query("SELECT * FROM `order_comments` WHERE `id` = '$eachcommentid' LIMIT 1");
                    $fetchcommentbyid = mysql_fetch_array($findcommentbyidq);

                    //echo $eachcommentid.' - '.$fetchcommentbyid['comment'].'<br>';
                    $comments .= $fetchcommentbyid['comment'] . "\r\n";


                    //echo '<hr>';

                }


                $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'comments' => $comments));
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Comments fulfillment'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                
                writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Comments fulfillment');
                $orderid .= $order1->order;
                $orderid .= ' ';

                $order_status = $api->status($order1->order);
                $supplier_cost += $order_status->charge;

                mysql_query('INSERT INTO `supplier_cost` SET `type` = "comments", `amount` = '.$amount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'.$order_status->charge.'", `page` = "lambda/autofulfill-lambda", timestamp = '.time().', `socialmedia` = "'.$info['socialmedia'].'", `brand` = "'.$info['brand'].'"');
                //echo $comments;

            }




            $supplier_error = $order1->error;

            if ((!empty($orderid)) && (preg_match('~[0-9]+~', $orderid)) && empty($supplier_error)) {

                $updateq = mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid', `supplier_cost` = '$supplier_cost' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");
                if ($updateq) {
                    $status = '<font color="green">Fulfilled!</font>';
                    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Success fulfillment'";
                    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

                    writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Success fulfillment');
                } else {
                    $status = '<font color="orange">Fulfilled! but failed to update DB</font>';
                    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Success fulfillment, Fail DB'";
                    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);                    

                    writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Success fulfillment, Fail DB');
                }
            } else {

                //NO ORDER ID HAS COME BACK
                $status = updateFulfillAttempt($info['fulfill_attempt'], $info['next_fulfill_attempt'], $supplier_error, $info['id']);
            }

            echo $username . ' ' . $status . '<hr>';

            unset($username);
            unset($thisorderpost);
            unset($pacid);
            unset($chooseposts);
            unset($eachpost);
            unset($orderid);
            unset($order1);
            unset($totaladdedamount);
            unset($posts1);
            unset($posts2);
            unset($multiamountposts);
            unset($choosepostsql);
            unset($packageinfo);
            unset($last_order_row);
            unset($status);
            unset($nextdelay);
            unset($next_fulfill_attempt);
            unset($findchoosepostsq);
            unset($findchooseposts);
            unset($thechoosepostsfound);
            unset($theupdatequery);
            unset($theupdatequery1);
            unset($chooseposts);
            unset($updateq);
            unset($freelikespost);
            unset($delivquantity);
            unset($fetchcommentbyid);
            unset($comments);
            unset($findcommentbyidq);
            unset($multipleposts);
            unset($multiplefreeposts);
            unset($supplier_cost);
            unset($supplier_error);
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

    $updateq = mysql_query("UPDATE `orders` SET `fulfill_attempt` = `fulfill_attempt` + 1, `next_fulfill_attempt` = '$next_fulfill_attempt', `supplier_errors` = '$error' WHERE `id` = '{$id}' LIMIT 1");

    if ($updateq) {
        return '<font color="red">Not fulfilled!</font>';
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Failed fulfillment'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);    
        
        writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Failed fulfillment');
    } else {
        return '<font color="orange">Not fulfilled! but failed to update DB</font>';
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autofulfill-lambda', added ='$now', `log` = 'Success fulfillment, failed db'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);     
        
        writeCloudWatchLog('autofulfill-lambda', 'Order Id: '. $info['id'] . '- Success fulfillment, failed db');
    }
}
