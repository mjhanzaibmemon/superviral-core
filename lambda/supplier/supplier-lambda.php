<?php

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

function call_supplier($orderArray)
{
    $fulfillment_url = getenv('fulfillment_url') ?? 'Not set';
    $fulfillment_api_key = getenv('fulfillment_api_key') ?? 'Not set';

    if ($fulfillment_url === 'Not set' || $fulfillment_api_key === 'Not set') {
        return json_encode(['error' => 'API credentials not set.']);
    }

    $api = new Api();

    $api->setApiKey($fulfillment_api_key);
    $api->setApiUrl($fulfillment_url);

    $order_response = $api->order($orderArray);
    $order_status = $api->status($order_response->order);

    // $order_response = $api->balance();

    $result_arr = array('order_response' => $order_response, 'order_status' => $order_status);
    return json_encode($result_arr);
}

    
return function (array $event, Context $context) {

    // Get the current hour and minute in 24-hour format
    $currentHour = (int)date('H');
    $currentMinute = (int)date('i');
    
    // Check if the current time is between 23:00 and 23:59
    if ( ($currentHour === 23 || $currentHour === 22) && $currentMinute >= 0 && $currentMinute <= 59 ) {
        //die("Script terminated: Current time is between 23:00 and 23:59.");
    }
    
    global $queryQueueUrl, $sqsClient, $log_query_queueUrl;

    $now = time();
    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = 'Received `event`:". json_encode($event) ."'";
    // mysql_query($log_msg);

    writeCloudWatchLog('autolikes-lambda', "Received `event`:". json_encode($event));
    // Check if there are records (SQS messages)
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {
            
            $jsonString = stripslashes($record['body']);
            $orderArray = json_decode($jsonString,true);
            // print_r($orderArray['autolikesorderData']);
            // print_r($orderArray['baseData']);

            $id = $orderArray['baseData']['autolikeId'];
            $username = $orderArray['baseData']['username'];

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Processing record `with Message` ID: " . $record['messageId'] . "'";
            // mysql_query($log_msg);

            writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") Processing record `with Message` ID: " . $record['messageId']);

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") `Message` Body: " . $record['body'] . "'";
            // mysql_query($log_msg);
            
            writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") `Message` Body: " . $record['body']);

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Sent to supplier: ".json_encode($orderArray['autolikesorderData'])."'";
            // mysql_query($log_msg);

            writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") Sent to supplier: ".json_encode($orderArray['autolikesorderData']));

            $data_response = call_supplier($orderArray['autolikesorderData']);
            
            $data = json_decode($data_response);

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Response of Supplier: " . $data_response . "'";
            // mysql_query($log_msg);
            
            writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") Response of Supplier: " . $data_response);

            if(!empty($data->order_response->order)){
                $al_fullfill_id = $data->order_response->order;
                $fulfilladded = $orderArray['baseData']['now'];
                $fulfillexpires = $orderArray['baseData']['expires'];
                if(!empty($id)){
                    $msg = "UPDATE `automatic_likes` SET 
                            `last_updated` = '$fulfilladded', 
                            `start_fulfill` = '1',
                            `missinglikespost` = '0' 
                            WHERE `id` = '$id' LIMIT 1";   

                    $msg2 = "INSERT INTO `automatic_likes_fulfill` SET 
                    `auto_likes_id` = '$id',
                    `fulfill_id` = '$al_fullfill_id',
                    `added` = '$fulfilladded',
                    `expires` = '$fulfillexpires'
                    ";

                    // Send the SQL query message to SQS
                    mysql_query($msg);
                    mysql_query($msg2);

                    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Successfully created AL'";
                    // mysql_query($log_msg);

                    writeCloudWatchLog('autolikes-lambda', "(AL-ID $id) Successfully created AL");
                // }else{
                //     $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Missing ID: ".$username."'";
                //     mysql_query($log_msg);   
                    writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") Missing ID: ".$username);                 
                }
                

            }else{
                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = '(AL-ID ".$id.") Something went wrong: " . $data->order_response->error . "'";
                // mysql_query($log_msg);

                writeCloudWatchLog('autolikes-lambda', "(AL-ID ".$id.") Something went wrong: " . $data->order_response->error);
                echo "Something went wrong.\n" . $data->order_response->error;
            }
        }
    } else {
        echo "No records found in event.\n";
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'autolikes-lambda', added ='$now', `log` = 'No records found in event.'";
        // mysql_query($log_msg);

        writeCloudWatchLog('autolikes-lambda', "No records found in event.");
    }
    
    return [
        'statusCode' => 200,
        'body' => json_encode(['message' => 'Event processed successfully']),
    ];
};
