<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aws-sdk/aws-autoloader.php';

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use Bref\Context\Context;


$sqsClient = new SqsClient([
    'region'  => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => '',
        'secret' => '',
    ],
]);

echo $sqsClient;