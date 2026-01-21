<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

/////////////

require __DIR__ . '/../common/db.php';
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

return function (array $event, Context $context) {

    $now = time();

    global $sqsClient;
    // Check if there are records (SQS messages)
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            // while ($info = mysql_fetch_array($q)) {
            $jsonString = stripslashes($record['body']);
            writeCloudWatchLog('check-account-status-lambda', 'Received `event`:'. json_encode($event));

            $info = json_decode($jsonString, true);

            $socialmedia = $info['socialmedia'];

            if($socialmedia == 'ig'){

                $data_response = call_api('', $info['igusername'], '', 'is_private', '');
                $response = json_decode($data_response);
                // print_r($response);die;
                $userId = $response->data->user->pk_id;
                $isprivate = $response->data->user->is_private;
            }else{
                $data_response = call_api('', $info['igusername'], '', 'is_private', '', 'tt');
                $response = json_decode($data_response);

                $userId = $response->data->user->uid;
                $isprivate = $response->data->user->secret;
            }

            writeCloudWatchLog('check-account-status-lambda', $info['igusername'] .' - Socialmedia api Response:'. $data_response);
            
            if (!empty($userId)) {

                if (empty($isprivate)) {
                    $isprivate = 'Public';
                    mysql_query("INSERT INTO orders_checks SET orderid='{$info['id']}', `username`='{$info['igusername']}', `username_status`='valid', is_private= 0, added = '$now', socialmedia = '$socialmedia'");

                } else {
                    $isprivate = 'Private';

                    mysql_query("INSERT INTO orders_checks SET orderid='{$info['id']}', `username`='{$info['igusername']}', `username_status`='valid', is_private= 1, added = '$now', socialmedia = '$socialmedia'");
                }
            } else {
                echo "Unable to get User data (Code 1): Unavailable: 1 " .  $info['igusername'] . "\n\n";
                writeCloudWatchLog('check-account-status-lambda', "Unable to get User data (Code 1): Unavailable: 1 " .  $info['igusername']);
                mysql_query("INSERT INTO orders_checks SET orderid='{$info['id']}', `username`='{$info['igusername']}', `username_status`='invalid', is_private= 1, added = '$now', socialmedia = '$socialmedia'");
            }
        }
    }
};
