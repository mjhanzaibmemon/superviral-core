<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../phpmailer/emailer.php';

use Bref\Context\Context;

return function (array $event, Context $context) {

     // Print the entire event received
    // echo "Received event: " . json_encode($event) . "\n";die;
    if (isset($event['Records'])) {

        foreach ($event['Records'] as $record) {

            $jsonString = $record['body'];
            $eventDataArr = json_decode($jsonString, true);

            $to = $eventDataArr['to'];
            $website = $eventDataArr['website'];
            $subject = $eventDataArr['subject'];
            $body = $eventDataArr['body'];
            $from = $eventDataArr['from'];
            // echo $from;
            // die;
            emailnow($to, $website, $from, $subject, $body);
            email_stat_insert($subject, $to, $body, 'sv');

        }

            // return;
    }
        
    else {
        echo "No records found in event.\n";
    }
};