<?php


$db = 1;
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration

//$timenow = time() - (1782000);
$timenow = time() - (2592000); //within 30 days
//$timenow = time() - (15811200);//within 163 days
$timeafterhours = time() - (90000); //once per day
$now = time();





// Specify the directory path
$sentineldirectory = '/home/etra/public_html/etra.group/sentinel/lambda';

// Check if the directory exists
if (is_dir($sentineldirectory)) {
    // Get the list of files and directories
    $contents = array_diff(scandir($sentineldirectory), array('.', '..'));

    // Output the contents as an array
 //   print_r($contents);


    foreach ($contents as $file) {


        if (empty($file)) continue;

        $file = trim($file);
        

        $res = AddQueue($sentinelSQSqueueUrl, $file); // refills_queueUrl from common.php

        echo '<b>' . $file . '</b> - <i>' . $sentinelSQSqueueUrl . '' . '</i><br>';

    }



} else {
    echo "The specified directory does not exist.";
}









?>
