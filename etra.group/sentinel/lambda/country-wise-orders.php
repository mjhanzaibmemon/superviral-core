<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';

use Aws\CloudWatch\CloudWatchClient;

  // Initialize CloudWatch client
  $cloudWatchClient = new CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $cloudwatchkey,
        'secret' => $cloudwatchpassword,
    ],
]);

$query = "SELECT COUNT(1) AS `count`, billing_country 
FROM orders
WHERE added >= UNIX_TIMESTAMP(DATE_FORMAT(NOW() - INTERVAL 1 HOUR, '%Y-%m-%d %H:00:00'))
                                  AND added < UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'))
GROUP BY `billing_country` ORDER BY added DESC;";

$run = mysql_query($query);



while ($data = mysql_fetch_array($run)) {

    $country = $data['billing_country'];
    $count = $data['count'];

    // if(empty($country)) $country = 'Unknown';

      try {
        // Send custom revenue metric data to CloudWatch
        $cloudWatchClient->putMetricData([
            'Namespace' => 'Sentinel/Lambda',
            'MetricData' => [
                [
                    'MetricName' => $country .'-Orders',
                    'Dimensions' => [
                        [
                            'Name' => 'CountryWiseOrders',
                            'Value' => 'hourly-'. $country .'-data-function'
                        ],
                    ],
                    'Unit' => 'None', // Or 'Currency' if appropriate
                    'Value' => $count, // Use the calculated revenue value
                ],
            ],
        ]);

        error_log('Orders metric data sent successfully');
    } catch (Exception $e) {
        error_log('Error sending revenue metric data: ' . $e->getMessage());
    }

    echo 'Orders data submitted to CloudWatch';
}
