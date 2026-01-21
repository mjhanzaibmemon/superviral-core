<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../supplier_raw/supplier_raw.php';
require __DIR__ . '/../socialmedia-api/socialmedia-api-lambda.php';
require __DIR__ . '/../common/common.php';
require __DIR__ . '/../func/checkorderfulfilled.php';

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

return function (array $event, Context $context) {

    global $checkorderfulfilled_query_queueUrl, $email_QueueUrl, $sms_QueueUrl, $sqsClient;

    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            writeCloudWatchLog('checkorderfulfilled-lambda', 'Received `event`:'. json_encode($event));

            $jsonString = stripslashes($record['body']);
            $eventDataArr = json_decode($jsonString,true);
            $orderArray = $eventDataArr['row'];
            $orderDataset = $eventDataArr['dataset'];

            // API - check fulfillment
            $check_fulfill = check_fulfill($orderArray['fulfill_id']);

            writeCloudWatchLog('checkorderfulfilled-lambda', 'Order Id :' .$orderArray['id'] . ' Supplier Response:'. $check_fulfill);
            
            $fulfill_response = json_decode($check_fulfill);

            $completed = $fulfill_response->completed;
            $fulfillcount = $fulfill_response->fulfillcount;
            $cancelled = $fulfill_response->cancelled;
            $partial = $fulfill_response->partial;

            if ($cancelled !== 0) mysql_query("UPDATE `orders` SET `defect` = '2' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
            if ($partial !== 0) mysql_query("UPDATE `orders` SET `defect` = '3' WHERE `id` = '{$orderArray['id']}' LIMIT 1");


            if (($completed == $fulfillcount)) {
                // get new engagement
                switch($orderArray['packagetype']){
                    case 'followers':
                        // add current stats (followers)

                        if($orderArray['socialmedia'] == 'ig'){
                            $data_response = call_api('', $orderArray['igusername'], '', 'is_private', '');
                            $response = json_decode($data_response);
                            $current_followers = $response->data->user->follower_count;
                        }else{
                            $data_response = call_api('', $orderArray['igusername'], '', 'is_private', '', 'tt');
                            $response = json_decode($data_response);
                            $current_followers = $response->data->user->follower_count;
                        }

                        writeCloudWatchLog('checkorderfulfilled-lambda', 'Order Id :' .$orderArray['id'] . ' Followers Socialmedia Response: '. $data_response);

                        mysql_query("UPDATE `orders` SET new_engagement = '$current_followers' WHERE `id`='".$orderArray['id']."' LIMIT 1");

                        if(($orderArray['new_engagement'] - $orderArray['curr_engagement']) < $orderArray['amount']){

                            // update reward = 0
                            mysql_query("UPDATE `order_sid_assignments` SET `reward` = '0', note = 'difference between curr_engagement and new_engagement' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
                    
                        }

                        if(($orderArray['new_engagement'] - $orderArray['curr_engagement']) >= $orderArray['amount']){

                            // update reward = 1
                            mysql_query("UPDATE `order_sid_assignments` SET `reward` = '1' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
                    
                        }
                    break;
                    case 'likes':
                    case 'views':
                            $multipleposts = explode(' ', $orderArray['chooseposts']);
                            $current_count = '';
                            foreach ($multipleposts as $eachpost) {

                                 // add current stats (likes)
                                if($orderArray['socialmedia'] == 'ig'){
                                    $data_response = call_api('', $orderArray['igusername'], $eachpost, 'post', '');
                                }else{
                                    $data_response = call_api('', $orderArray['igusername'], $eachpost, 'post', '', 'tt');
                                }
                                writeCloudWatchLog('checkorderfulfilled-lambda', 'Order Id :' .$orderArray['id'] . ' Llikes/Views Socialmedia Response: '. $data_response);
                                $response = json_decode($data_response);
                                
                                if($orderArray['socialmedia'] == 'ig'){
                                    if($orderArray['packagetype'] == 'likes')
                                    $current_count .= $response->data->items[0]->like_count . ' ';
                                    else
                                    $current_count .= $response->data->items[0]->play_count . ' ';
                                }else{
                                    
                                    if($orderArray['packagetype'] == 'likes')
                                    $current_count = $response->data->aweme_detail->statistics->digg_count . ' ';
                                    else
                                    $current_count = $response->data->aweme_detail->statistics->play_count . ' ';
                                }
                            }
                            mysql_query("UPDATE `orders` SET new_engagement = '$current_count' WHERE `id`='".$orderArray['id']."' LIMIT 1");

                            $curr_engagement_array = explode(' ', trim($orderArray['curr_engagement']));
                            $new_engagement_array  = explode(' ', trim($orderArray['new_engagement']));

                            $totalDifference = 0;

                            // Loop through each engagement index
                            for ($i = 0; $i < count($curr_engagement_array); $i++) {
                                // Convert both values to integers
                                $curr = isset($curr_engagement_array[$i]) ? (int)$curr_engagement_array[$i] : 0;
                                $new  = isset($new_engagement_array[$i]) ? (int)$new_engagement_array[$i] : 0;

                                // Add the difference for this index
                                $totalDifference += ($new - $curr);
                            }

                        // Now compare the total difference with the required amount
                        if ($totalDifference < $orderArray['amount']) {
                            // Update reward = 0
                            mysql_query("UPDATE `order_sid_assignments` SET `reward` = '0', `note` = 'difference between curr_engagement and new_engagement' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
                        } else {
                            // Update reward = 1
                            mysql_query("UPDATE `order_sid_assignments` SET `reward` = '1' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
                        }

                        break;
                }
              
                $time = $orderDataset['time'];
                $deliverytime = $orderDataset['deliverytime'];        
                $seconds = '0.' . rand(1, 9);

                $website = $orderDataset['website'];
                $brand = $orderDataset['brand'];
                $domain = $orderDataset['domain'];
                $loc2 = $orderDataset['loc'];

                $order_response_finish = '~~~' . $time . '###'. $website .' completed delivery of ' . $orderArray['packagetype'] . ' to @' . $orderArray['igusername'] . '###' . $seconds;
                $order_response_finish = addslashes($order_response_finish);
        

                echo 'Mark as done';
                // update orders
                $q = mysql_query("UPDATE `orders` SET `fulfilled` = '$time',`deliverytime` = '$deliverytime', `order_response_finish` = '$order_response_finish' WHERE `id` = '{$orderArray['id']}' LIMIT 1");
                // update user
                $q = mysql_query("UPDATE `users` SET `delivered` = '1', `lastsent` = '$time' WHERE `emailaddress` = '{$orderArray['emailaddress']}' AND `delivered` = '0' AND brand = '$brand' LIMIT 1");
                
                // order sid 
                $q = mysql_query("UPDATE `order_sid_assignments` SET `resolved_at` = '$time' WHERE `id` = '{$orderArray['id']}' LIMIT 1");

                //////// JAP ALGO

                $sql = "CALL sp_ts_update_speed_on_fulfill({$orderArray['id']})";
                mysql_query($sql);
                
                /////// JAP ALGO DONE
                
                // check if order is delivered and account is private
                // API - private or public
                $check_privacy = check_privacy($orderArray);
                $response = json_decode(json_encode($check_privacy));
                writeCloudWatchLog('checkorderfulfilled-lambda', 'Order Id :' .$orderArray['id'] . ' Check privacy Socialmedia Response: '. $check_privacy);
        
                // SMS
                if (!empty($orderArray['contactnumber'])) {
                    ////generate BITLY CODE    
                    $bitlyhash = getRandomString();
                    $bitlyhref = 'https://'. $domain .'/' . $loc2 . 'order/choose/?setorder=' . $orderArray['order_session'] . '&discounton=no';
                    $q = mysql_query("INSERT INTO `bitly` SET `hash` = '$bitlyhash', `href` = '$bitlyhref',`added` = '$time', `brand` = '$brand'");

                    $sms_from = "SUPERVIRAL";
                    $numberCountry = detectCountryNumber($orderArray['contactnumber']);

                    if($numberCountry == 'us'){
                        $sms_from  = '+12087798450';
                    	sendCloudwatchData('AWSLambda', 'us-number-send-out', 'CheckOrderfulfilled', 'us-number-send-out-function', 1);

                    }else{
	                    sendCloudwatchData('AWSLambda', 'uk-number-send-out', 'CheckOrderfulfilled', 'uk-number-send-out-function', 1);
                        
                    }

                    $messagebird_body = smsTpl($orderArray, $orderDataset);                    
                    $sms_arr = array(
                        'to' => array($orderArray['contactnumber']),
                        'from' => $sms_from,
                        'body' => $messagebird_body
                    );
                    sendMessageToSqs(json_encode($sms_arr), $sms_QueueUrl, $sqsClient);
                    echo 'Inserted into SMS_SQS';
                }else{
                    echo 'number not found';
                }
        
                // EMAIL
                $tpl = emailTpl($orderArray, $orderDataset);
                $subject = 'Delivered: Your '. $website .' order #' . $orderArray['id'];
                $email_arr = array(
                    'to' => $orderArray['emailaddress'],
                    'website' => $website,
                    'subject' => '🤩🌟 ' . $subject,
                    'body' => $tpl,
                    'from' => 'orders@'. $domain
                );
                sendMessageToSqs(json_encode($email_arr), $email_QueueUrl, $sqsClient);
                echo 'Inserted into EMAIL_SQS';

            }

        }
        
    } else {
        echo "No records found in event.\n";
    }
    
    return [
        'statusCode' => 200,
        'body' => json_encode(['message' => 'Event processed successfully']),
    ];
}; 