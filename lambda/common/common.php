<?php

global $sms_QueueUrl;
global $email_QueueUrl;
global $queueUrl;
global $queryQueueUrl;
global $refills_queueUrl;
global $refills_query_queueUrl;
global $checkorderfulfilled_queueUrl;
global $checkorderfulfilled_query_queueUrl;
global $log_query_queueUrl;
global $checkuserprofilestatus_queueUrl;
global $checkuserprofilestatus_query_queueUrl;
global $autofulfill_queueurl;
global $autofulfill_free_queueurl;


$sms_QueueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-sms-queue';
$email_QueueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-email-queue';
$log_query_queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-logs-query-queue';

$queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-al-queue';
$queryQueueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-al-query-queue';

$refills_queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-refills-queue';
$refills_query_queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-refills-query-queue';

$checkorderfulfilled_queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-checkorderfulfilled-queue';
$checkorderfulfilled_query_queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-checkorderfulfilled-query-queue';

$autofulfill_free_queueurl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-autofulfill-free-queue';
$autofulfill_queueurl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-live-autofulfill-queue';


function sendMessageToSqs($messageBody, $queueUrl, $client)
{
    try {
        $result = $client->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $messageBody
        ]);
        echo "Message sent to SQS with MessageId: " . $result['MessageId'] . "\n";
    } catch (AwsException $e) {
        echo "Error sending message to SQS: " . $e->getMessage() . "\n";
    }
}

function uploadLogToS3($logFilePath, $bucketName, $s3Client)
{
    try {
        $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => basename($logFilePath),
            'SourceFile' => $logFilePath,
        ]);
        echo "Log file uploaded to S3: " . basename($logFilePath) . "\n";
    } catch (AwsException $e) {
        echo "Error uploading log file to S3: " . $e->getMessage() . "\n";
    }
}


?>