<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aws-sdk/aws-autoloader.php';
require __DIR__ . '/../supplier_raw/supplier_raw.php';
require __DIR__ . '/../common/common.php';


use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use Bref\Context\Context;


function call_supplier($fulfillid)
{
    $fulfillment_url = getenv('fulfillment_url') ?? 'Not set';
    $fulfillment_api_key = getenv('fulfillment_api_key') ?? 'Not set';

    if ($fulfillment_url === 'Not set' || $fulfillment_api_key === 'Not set') {
        return json_encode(['error' => 'API credentials not set.']);
    }

    $api = new Api();

    $api->setApiKey($fulfillment_api_key);
    $api->setApiUrl($fulfillment_url);

    $order_response = $api->refill($fulfillid);;
    // $order_status = $api->status($order_response->order);

    // $order_response = $api->balance();

    $result_arr = array('order_response' => $order_response);
    return json_encode($result_arr);
}

global $sqsClient;
$sqsClient = new SqsClient([
    'region'  => 'us-east-2',  // Your AWS region
    'version' => 'latest',
    'credentials' => [
        'key'    => getenv('amazonLambdaKey'),
        'secret' => getenv('amazonLambdapassword'),
    ],
]);

// $s3Client = new S3Client([
//     'region'  => 'us-east-2',
//     'version' => 'latest',
//     'credentials' => [
//         'key'    => getenv('amazonLambdaKey'),
//         'secret' => getenv('amazonLambdapassword'),
//     ],
// ]);


return function (array $event, Context $context) {
    // Print the entire event received
    //echo "Received event: " . json_encode($event) . "\n";
    global $refills_query_queueUrl, $sqsClient, $log_query_queueUrl;
    // $logFilePath = sys_get_temp_dir() . '/event_log_refill_lambda_' . uniqid() . '.txt';
    // $logFile = fopen($logFilePath, 'w');
    $now = time();
    // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = 'Received `event`:". json_encode($event) ."'";
    // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

    writeCloudWatchLog('refills-lambda', "Received `event`:". json_encode($event));

    // fwrite($logFile, "Received event: " . json_encode($event) . "\n\n");

    // Check if there are records (SQS messages)
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            // fwrite($logFile, "Processing record with Message ID: " . $record['messageId'] . "\n\n");
            // fwrite($logFile, "Message Body: " . $record['body'] . "\n\n");

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = 'Processing record `with Message` ID: " . $record['messageId'] . "'";
            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

            writeCloudWatchLog('refills-lambda', "Processing record `with Message` ID: " . $record['messageId']);

            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = '`Message` Body: " . $record['body'] . "'";
            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

            writeCloudWatchLog('refills-lambda', "`Message` Body: " . $record['body']);

            //  Print each message's body and ID
            //  echo "Message ID: " . $record['messageId'] . "\n";
            //  echo "Message Body: " . $record['body'] . "\n";
            
            $jsonString = stripslashes($record['body']);
            $orderArray = json_decode($jsonString,true);
            // print_r($orderArray);die;
            // print_r($orderArray['baseData']);
            $data_response = call_supplier($orderArray['fulfill_id']);
            // fwrite($logFile, "API Response: " . $data_response . "\n\n");
           
            // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = 'Response of Supplier: " . $data_response . "'";
            // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

            writeCloudWatchLog('refills-lambda', "Response of Supplier: " . $data_response);

            $data = json_decode($data_response);

            if(!empty($data->order_response->order)){
                
                $id = $orderArray['id'];
                $brand = $orderArray['brand'];
                $now = $orderArray['now'];
                if(!empty($id)){
                    $msg = "UPDATE `orders` SET `lastrefilled` = '$now' WHERE `id` = '$id' AND brand = '$brand' LIMIT 1";
                    // Send the SQL query message to SQS
                    sendMessageToSqs($msg, $refills_query_queueUrl, $sqsClient);
                }                

            }else{
                // $errorMsg = "Something went wrong: " . $data->order_response->error . "\n\n";

                // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = 'Something went wrong: " . $data->order_response->error . "'";
                // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

                writeCloudWatchLog('refills-lambda', "Something went wrong: " . $data->order_response->error);

                // fwrite($logFile, $errorMsg);
                echo "Something went wrong.\n" . $data->order_response->error;
            }
        }
    } else {
        echo "No records found in event.\n";
        // fwrite($logFile, "No records found in event.\n\n");
        // $log_msg = "INSERT INTO `lambda_logs` SET `page` = 'refills-lambda', added ='$now', `log` = 'No records found in event.'";
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

        writeCloudWatchLog('refills-lambda', "No records found in event");
    }
    // fclose($logFile);
    
    // Upload the log file to S3
    //  $bucketName = 'etra-lest-logs'; // name of bucket
    //  uploadLogToS3($logFilePath, $bucketName, $s3Client);
 
    // Clean up the temporary log file
    // unlink($logFilePath);

    return [
        'statusCode' => 200,
        'body' => json_encode(['message' => 'Event processed successfully']),
    ];
};