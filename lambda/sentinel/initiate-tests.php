<?php

require __DIR__ . '/../common/common.php';
require __DIR__ . '/../common/db.php';

 

//////////////////////////////////////////////////////////////////////

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
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

    
    global $refills_query_queueUrl, $sqsClient, $log_query_queueUrl;


    $now = time();
    //$log_msg = mysql_query("INSERT INTO `lambda_logs` SET `page` = 'initiate-tests', added ='$now', `log` = 'Received `event`:". json_encode($event) ."'";);
   // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {




            $url = 'https://etra.group/sentinel/lambda/'.$record['body'];            
            
            $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_URL, $url);
            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            
            curl_setopt($curl, CURLOPT_TIMEOUT, 8);
            
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            
            curl_setopt($curl, CURLOPT_ENCODING, '');
            
            
            
            
            $get = curl_exec($curl);
            
            curl_close($curl);

            // $log_msg = mysql_query("INSERT INTO `lambda_logs` SET `page` = 'initiate-tests', added ='$now', `log` = '".$get."'");
            writeCloudWatchLog('initiate-tests', $get);
        }
    } else {
        echo "No records found in event.\n";

        // $log_msg = mysql_query("INSERT INTO `lambda_logs` SET `page` = 'initiate-tests', added ='$now', `log` = 'No records found in event.'");
        // sendMessageToSqs($log_msg, $log_query_queueUrl, $sqsClient);

        writeCloudWatchLog('initiate-tests', 'No records found in event.');
    }


    return [
        'statusCode' => 200,
        'body' => json_encode(['message' => 'Event processed successfully']),
    ];
};


