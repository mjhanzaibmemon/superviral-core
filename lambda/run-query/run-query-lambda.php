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

    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {

            $res = $record['body'];
            if (!empty($res)) {
                $query = $res;
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
        }
    }
};

function isValidSQLQuery($query)
{
    // Define a simple pattern for common SQL commands
    $pattern = '/^(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE|USE|SHOW|DESCRIBE|REPLACE)\s/i';
    return preg_match($pattern, $query);
}
