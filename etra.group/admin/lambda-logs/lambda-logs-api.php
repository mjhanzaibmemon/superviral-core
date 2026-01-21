<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

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

$logGroupName = 'etra-logs-lambda';
    $client = new CloudWatchLogsClient([
        'region' => 'us-east-2',
        'version' => 'latest',
        'credentials' => [
            'key' => $amazonLoggingKey,
            'secret' => $amazonLoggingPass,
        ],
    ]);
    
    
    $page = addslashes($_POST['page'] ?? '');
    $search = addslashes($_POST['search'] ?? '');
    $dateFrom = trim($_POST['dateFrom'] ?? '');
    $dateTo = trim($_POST['dateTo'] ?? '');
    
    $last24Hour = strtotime('-24 hours');
    $data = [];
    
    // Default to last 24 hours if no range provided
    if (empty($dateFrom) || empty($dateTo)) {
        $dateToUnix = time();
        $dateFromUnix = $dateToUnix - 86400;
    } else {
        $dateFromUnix = strtotime($dateFrom);
        $dateToUnix = strtotime($dateTo) + 86399;
    }
    
    $params = [
        'logGroupName' => $logGroupName,
        'startTime' => $dateFromUnix * 1000,
        'endTime' => $dateToUnix * 1000,
        'limit' => 10000,
    ];
    
    if (!empty($search) && empty($page)) {
        $params['filterPattern'] = '"' . str_replace('"', '\"', $search) . '"';
    }
    
    try {
        $nextToken = null;
    
        do {
            if ($nextToken) {
                $params['nextToken'] = $nextToken;
            }
    
            $result = $client->filterLogEvents($params);
            // echo '<pre>';
            // print_r($result);
    
            foreach ($result['events'] as $event) {

                $msg = json_decode($event['message'], true);

                // print_r($msg);
                if (!is_array($msg)) continue;
    
                // $added = isset($msg['added']) ? (int)$msg['added'] : 0;

                // echo "Added: $added, From: $dateFromUnix, To: $dateToUnix <br>";
                // if ($added < $dateFromUnix || $added > $dateToUnix) continue;

                if (!empty($page) && (!isset($msg['page']) || $msg['page'] !== $page)) continue;
                if (!empty($search) && strpos(json_encode($msg), $search) === false) continue;
    
                $data[] = $msg;
                if (count($data) >= 500) break 2;
    
            }
    
            $nextToken = $result['nextToken'] ?? null;
        } while ($nextToken);
    
        echo !empty($data)
            ? json_encode(['info' => $data])
            : json_encode(['error' => 'No logs found']);
        die;
    
    } catch (AwsException $e) {
        echo json_encode(['error' => 'CloudWatch Error: ' . $e->getMessage()]);
        die;
    }
    
