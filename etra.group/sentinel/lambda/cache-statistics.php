<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';


use Aws\CloudWatch\CloudWatchClient;

$objectiveAmount = 50;

$query = "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_reads';";

$run = mysql_query($query);

$data = mysql_fetch_array($run);

// Initialize CloudWatch client
$cloudWatchClient = new CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $cloudwatchkey,
        'secret' => $cloudwatchpassword,
    ],
]);

try {
    // Send custom revenue metric data to CloudWatch
    $cloudWatchClient->putMetricData([
        'Namespace' => 'Sentinel/Lambda',
        'MetricData' => [
            [
                'MetricName' => $data['Variable_name'],
                'Dimensions' => [
                    [
                        'Name' => 'MysqlCache',
                        'Value' => 'mysql-'. $data['Variable_name'] .'cache-function'
                    ],
                ],
                'Unit' => 'None', // Or 'Currency' if appropriate
                'Value' => $data['Value'], // Use the calculated revenue value
            ],
        ],
    ]);

    error_log('cache metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending cache metric data: ' . $e->getMessage());
}

$query = "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_read_requests';";

$run = mysql_query($query);

$data = mysql_fetch_array($run);

try {
    // Send custom revenue metric data to CloudWatch
    $cloudWatchClient->putMetricData([
        'Namespace' => 'Sentinel/Lambda',
        'MetricData' => [
            [
                'MetricName' => $data['Variable_name'],
                'Dimensions' => [
                    [
                        'Name' => 'MysqlCache',
                        'Value' => 'mysql-'. $data['Variable_name'] .'cache-function'
                    ],
                ],
                'Unit' => 'None', // Or 'Currency' if appropriate
                'Value' => $data['Value'], // Use the calculated revenue value
            ],
        ],
    ]);

    error_log('cache metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending cache metric data: ' . $e->getMessage());
}
