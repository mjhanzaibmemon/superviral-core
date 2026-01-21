<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/common.php';

use Aws\Sqs\SqsClient;

// use Aws\S3\S3Client;
global $sqsClient;
$sqsClient = new SqsClient([
    'region'  => 'us-east-2',  // Your AWS region
    'version' => 'latest',
    'credentials' => [
        'key'    => getenv('amazonLambdaKey'),
        'secret' => getenv('amazonLambdapassword'),
    ],
]);

sendMessageToSqs('test', $asdasds_QueueUrl, $sqsClient);

