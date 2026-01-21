<?php

require __DIR__ . '/../common/db.php';
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

return function (array $event, Context $context) {
    global $sms_QueueUrl,$sqsClient;

     // Print the entire event received
    // echo "Received event: " . json_encode($event) . "\n";die;
    if (isset($event['Records'])) {

        foreach ($event['Records'] as $record) {
            $sms_arr = array(
                'to' => "+447932456864",
                'from' => "SUPERVIRAL",
                'body' => 'Lambda ERRORS'
            );
            sendMessageToSqs(json_encode($sms_arr), $sms_QueueUrl, $sqsClient);
        }
        //return;
    }
        
    else {
        echo "No records found in event.\n";
    }
};