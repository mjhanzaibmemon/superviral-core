<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

global $client;

$client = new SqsClient([
    'region'  => 'us-east-2',  // Your region
    'version' => 'latest',
    'credentials' => [
        'key'    => $amazons3key,
        'secret' => $amazons3password,
    ],
]);

// URL of the SQS Queue
// $queueUrl = 'https://sqs.us-east-2.amazonaws.com/506994122336/etra-test-account_type';


// $res = AddQueue($queueUrl, $client);
// echo $res;

// $res = retriveQueue($queueUrl, $client);
// print_r($res);

function AddQueue ($queueUrl, $msg){

    global $client;
    try {
        // Sending message to SQS
        $result = $client->sendMessage([
            'QueueUrl'    => $queueUrl,
            'MessageBody' => $msg,
        ]);
    
        // Print the result of the sendMessage request
        return "MessageId: " . $result->get('MessageId') . "\n";
    
    } catch (AwsException $e) {
        // Output error message if fails
        error_log($e->getMessage());
        return "Error sending message: " . $e->getAwsErrorMessage();
    }
}

function retriveQueue ($queueUrl){
    global $client;

    try {
        // Receive messages from the SQS queue
        $result = $client->receiveMessage([
            'QueueUrl'            => $queueUrl, 
            'MaxNumberOfMessages' => 10,         
            'WaitTimeSeconds'     => 10,        
        ]);
    
        // Check if any messages were received
        if (!empty($result->get('Messages'))) {
            $i =0;
            foreach ($result->get('Messages') as $message) {
                // Display the message
                $arr[$i]['id']   = $message['MessageId'];
                $arr[$i]['body'] = $message['Body'];
    
                // // Optionally, delete the message after processing it
                $client->deleteMessage([
                    'QueueUrl'      => $queueUrl,
                    'ReceiptHandle' => $message['ReceiptHandle'],
                ]);
    
                // echo "Message deleted successfully.\n";
                $i++;
            }
            return $arr;
        } else {
            $arr = [];
            return $arr;
        }
    } catch (AwsException $e) {
        // Output error message if something goes wrong
        return "Error receiving message: " . $e->getAwsErrorMessage();
    }
}

?>
