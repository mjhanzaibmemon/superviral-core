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

function purgeQueue($queueUrl, $sqsClient)
{
    try {
        $sqsClient->purgeQueue([
            'QueueUrl' => $queueUrl
        ]);

        mysql_query("UPDATE admin_statistics 
        SET `send_sms` = 1
        WHERE `type` = 'lambda_attempts' 
        limit 1");
        echo "Queue successfully purged.";
    } catch (\Aws\Exception\AwsException $e) {
        echo "Error purging queue: " . $e->getMessage();
    }
}

// Purge the queue
purgeQueue($autofulfill_free_queueurl, $sqsClient);
