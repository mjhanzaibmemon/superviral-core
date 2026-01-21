<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration

//echo $log_query_queueUrl;

$res = retriveQueue($log_query_queueUrl);

if (!empty($res)) {
    $query = $res[0]['body'];
    // echo $query;die;
    if (!empty($query) && isValidSQLQuery($query)) {

        $run = mysql_query($query);

        if ($run) {
            echo 'Done Successfully' . $query . '<br>';
        } else {
            echo 'Error' . $query . '<br>';
        }
    } else {
        echo 'Invalid SQL Query';
    }
} else {
    echo 'No data';
}

function isValidSQLQuery($query)
{
    // Define a simple pattern for common SQL commands
    $pattern = '/^(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE|USE|SHOW|DESCRIBE|REPLACE)\s/i';
    return preg_match($pattern, $query);
}
