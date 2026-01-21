<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../messagebird/autoload.php';


use Bref\Context\Context;



return function (array $event, Context $context) {

    // Print the entire event received
    // echo "Received event: " . json_encode($event) . "\n";die;
    if (isset($event['Records'])) {

        foreach ($event['Records'] as $record) {

            $jsonString = $record['body'];
            $eventDataArr = json_decode($jsonString, true);

            $to = $eventDataArr['to'];
            $body = $eventDataArr['body'];
            $from = $eventDataArr['from'];

            // echo $from;
            // die;
            $messagebirdclient = getenv('messagebirdclient');
            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = $from;

            $Message->recipients = $to;
            $Message->body = $body;

            try {
                $MessageBird->messages->create($Message);
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "<br>";

                writeCloudWatchLog('dosms', 'Caught exception: '.  $e->getMessage());
            }

            if ($MessageBird) {
                echo 'Text Message Sent !<br>';
            }
           
        }

        // return;
    } else {
        echo "No records found in event.\n";
    }
};
