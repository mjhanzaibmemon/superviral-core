<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';

use Aws\CloudWatch\CloudWatchClient;
$cloudWatchClient = new CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $cloudwatchkey,
        'secret' => $cloudwatchpassword,
    ],
]);

$queries = [
    "order_session_with_os"  => "SELECT * FROM `order_session` USE INDEX (`idx_order_session_composite`) WHERE `brand`='sv' AND `order_session` = '88b51847fba71e5ccbc53bbca667dc76' ORDER BY `id` DESC LIMIT 1",
    "users_with_id"  => "SELECT * FROM `users` WHERE `brand`='sv' AND `id` = '207282' ORDER BY `id` DESC LIMIT 1",
    "accounts_with_email_token_hash" => "SELECT * FROM `accounts` USE INDEX (access_accounts) WHERE `brand`='sv' AND `email_hash` = 'a437ae9962019f80fbf83a65268e51fc' AND `token_hash` = 'deed12a03ffc051f34a5a1c139512240' ORDER BY `id` DESC LIMIT 1",
    "orders_with_id"  => "SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' AND `fulfill_attempt` > '5' ORDER BY `packagetype` ASC LIMIT 100",
    "orders_with_os"  => "SELECT * FROM `orders` WHERE `order_session` = '88b51847fba71e5ccbc53bbca667dc76' LIMIT 1",
    "orders_with_like" => "SELECT * FROM `orders` WHERE (brand ='sv' AND `igusername` LIKE '%google%') ORDER BY `id` DESC",
    "al_with_join"  => "SELECT alb.added,al.id,alb.id as billid FROM automatic_likes al INNER JOIN automatic_likes_billing alb ON al.id = alb.auto_likes_id WHERE al.id = 100 AND al.brand = 'sv'",
    "accouts_with_email" => "SELECT * FROM accounts WHERE `email` = 'anuj@etra.group' order by id desc Limit 1",
];

mysql_query("SET profiling = 1");

foreach ($queries as $label => $query) {
    echo "<br><strong>[$label]</strong> $query<br>";

    // Get the current highest Query_ID before running the new query
    $lastQueryId = 0;
    $result = mysql_query("SHOW PROFILES");
    if ($result) {
        while ($row = mysql_fetch_array($result)) {
            if ($row['Query_ID'] > $lastQueryId) {
                $lastQueryId = $row['Query_ID'];
            }
        }
    }

    // Execute your query
    mysql_query($query);

    // Now fetch new profiling data
    $profileResult = mysql_query("SHOW PROFILES");
    if ($profileResult) {
        while ($row = mysql_fetch_array($profileResult)) {
            if ($row['Query_ID'] > $lastQueryId) {

                try {
                    // Send custom revenue metric data to CloudWatch USD
                    $cloudWatchClient->putMetricData([
                        'Namespace' => 'Sentinel/Lambda',
                        'MetricData' => [
                            [
                                'MetricName' => $label,
                                'Dimensions' => [
                                    [
                                        'Name' => 'DBResponseTime',
                                        'Value' => number_format($row['Duration'] * 1000, 2) .'ms'
                                    ],
                                ],
                                'Unit' => 'None', // Or 'Currency' if appropriate
                                'Value' => 1, // Use the calculated revenue value
                            ],
                        ],
                    ]);
        
                } catch (Exception $e) {
                    error_log('Error sending metric data: ' . $e->getMessage());
                }

                // echo "Query ID: {$row['Query_ID']} - Time: " . number_format($row['Duration'] * 1000, 2) . " ms<br>";
            }
        }
    } else {
        echo "No profiling data available.<br>";
    }
}

?>
