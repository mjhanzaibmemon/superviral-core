<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; 

use Aws\CloudWatch\CloudWatchClient;

$query = "SELECT SUM(supplier_cost) AS total_sum
FROM orders_free
WHERE added >= UNIX_TIMESTAMP(DATE_FORMAT(NOW() - INTERVAL 1 HOUR, '%Y-%m-%d %H:00:00'))
                                  AND added < UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'));
";

$run = mysql_query($query);

$total_sum_arr = mysql_fetch_array($run);
$total_sum = $total_sum_arr['total_sum'];

if(empty($total_sum)) {
    $total_sum = 0;
}
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
                'MetricName' => 'HourlyExpense',
                'Dimensions' => [
                    [
                        'Name' => 'SupplierExpenseFreeOrder',
                        'Value' => 'hourly-expense-function'
                    ],
                ],
                'Unit' => 'None', // Or 'Currency' if appropriate
                'Value' => $total_sum, // Use the calculated revenue value
            ],
        ],
    ]);

    error_log('Expense metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending revenue metric data: ' . $e->getMessage());
}

echo 'Expense data submitted to CloudWatch';

?>