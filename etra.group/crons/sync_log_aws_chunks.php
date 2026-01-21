<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial !== "etra.") {
    $_SERVER['DOCUMENT_ROOT'] .= $subdomain;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/sm-db.php';

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Exception\AwsException;

$client = new CloudWatchLogsClient([
    'region' => 'us-east-2', 
    'version' => 'latest',
    'credentials' => [
        'key' => $amazonLoggingKey,
        'secret' => $amazonLoggingPass,
    ],
]);

echo '<pre>';

$logGroupName = 'etra-logs-lambda-archive'; // Customize if needed


try {
    $client->createLogGroup(['logGroupName' => $logGroupName]);
} catch (AwsException $e) {
    if ($e->getAwsErrorCode() !== 'ResourceAlreadyExistsException') {
        die("Error creating log group: " . $e->getMessage());
    }
}


try {
    $client->putRetentionPolicy([
        'logGroupName' => $logGroupName,
        'retentionInDays' => 180
    ]);
} catch (AwsException $e) {
    echo "Failed to set retention policy: " . $e->getMessage() . "<br>";
}

// Process logs from DB in chunks
$cutoffStart = strtotime('-12 months');
$cutoffEnd = strtotime('-1 days');
$limit = 1000;
$offset = 0;
$totalMoved = 0;

do {
    $query = "SELECT * FROM lambda_logs WHERE added BETWEEN $cutoffStart AND $cutoffEnd LIMIT $limit OFFSET $offset";
    $result = mysql_query($query);

    $chunk = [];
    while ($row = mysql_fetch_array($result)) {
        $chunk[] = $row;
    }

    $count = count($chunk);

    if ($count > 0) {
        $logStreamName = 'logs_' . date('Y-m-d_H-i-s') . "_offset_$offset";

        // Create new log stream
        try {
            $client->createLogStream([
                'logGroupName' => $logGroupName,
                'logStreamName' => $logStreamName
            ]);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() !== 'ResourceAlreadyExistsException') {
                echo "Log stream creation failed: " . $e->getMessage() . "<br>";
                break;
            }
        }
        
        // Get sequence token if needed
        $sequenceToken = null;
        try {
            $streams = $client->describeLogStreams([
                'logGroupName' => $logGroupName,
                'logStreamNamePrefix' => $logStreamName
            ]);
            if (count($streams['logStreams']) > 0 && isset($streams['logStreams'][0]['uploadSequenceToken'])) {
                $sequenceToken = $streams['logStreams'][0]['uploadSequenceToken'];
            }
        } catch (AwsException $e) {
            echo "Failed to get log stream token: " . $e->getMessage() . "<br>";
            break;
        }

        
        $timestamp = round(microtime(true) * 1000);
        $logEvents = [];
        foreach ($chunk as $row) {
            $logEvents[] = [
                'timestamp' => $timestamp,
                'message' => json_encode($row)
            ];
            $timestamp += 1; // avoid duplicate timestamps
        }
        
        $params = [
            'logGroupName' => $logGroupName,
            'logStreamName' => $logStreamName,
            'logEvents' => $logEvents
        ];
        
        if ($sequenceToken) {
            $params['sequenceToken'] = $sequenceToken;
        }

        try {
            $client->putLogEvents($params);

            // Delete uploaded logs from MySQL
            $ids = array_column($chunk, 'id');
            $ids = array_map('intval', $ids);
            $idList = implode(',', $ids);
            mysql_query("DELETE FROM lambda_logs WHERE id IN ($idList)");

            $totalMoved += $count;
            echo "Uploaded $count logs to CloudWatch and deleted from DB.<br>";
            
        } catch (AwsException $e) {
            echo "Failed to upload logs: " . $e->getMessage() . "<br>";
        }
    }

    $offset += $limit;
} while ($count === $limit);

echo "Total logs moved: $totalMoved<br>";
