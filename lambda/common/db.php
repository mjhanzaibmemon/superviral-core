<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aws-sdk/aws-autoloader.php';


use Aws\CloudWatch\CloudWatchClient;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Exception\AwsException;

function sendCloudwatchData($namespace, $metricName, $func, $dimensions, $data) {
    
    $cloudwatchkey = getenv('cloudwatchkey');
    $cloudwatchpassword = getenv('cloudwatchpassword');
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
        // Send custom revenue metric data to CloudWatch USD
        $cloudWatchClient->putMetricData([
            'Namespace' => $namespace,
            'MetricData' => [
                [
                    'MetricName' => $metricName,
                    'Dimensions' => [
                        [
                            'Name' => $func,
                            'Value' => $dimensions
                        ],
                    ],
                    'Unit' => 'None', // Or 'Currency' if appropriate
                    'Value' => $data, // Use the calculated revenue value
                ],
            ],
        ]);
    
        error_log($metricName . ' metric data sent successfully');
    } catch (Exception $e) {
        error_log('Error sending metric data: ' . $e->getMessage());
    }
}

function writeCloudWatchLog($page, $log, $logGroupName = 'etra-logs-lambda') {
    $cloudwatchkey = getenv('cloudwatchkey');
    $cloudwatchpassword = getenv('cloudwatchpassword');

    $client = new CloudWatchLogsClient([
        'region' => 'us-east-2',
        'version' => 'latest',
        'credentials' => [
            'key' => $cloudwatchkey,
            'secret' => $cloudwatchpassword,
        ],
    ]);

    $date = date('Y-m-d');
    $logStreamName = 'logs_' . $date;

    try {
        // 1. Create log group if not exists
        $groups = $client->describeLogGroups([
            'logGroupNamePrefix' => $logGroupName,
        ]);

        $groupExists = false;
        foreach ($groups['logGroups'] as $group) {
            if ($group['logGroupName'] === $logGroupName) {
                $groupExists = true;
                break;
            }
        }

        if (!$groupExists) {
            $client->createLogGroup([
                'logGroupName' => $logGroupName,
            ]);
        }

        // 2. Create stream if not exists
        $streams = $client->describeLogStreams([
            'logGroupName' => $logGroupName,
            'logStreamNamePrefix' => $logStreamName,
        ]);

        $streamExists = false;
        $sequenceToken = null;
        if (!empty($streams['logStreams'])) {
            $streamExists = true;
            $sequenceToken = $streams['logStreams'][0]['uploadSequenceToken'] ?? null;
        }

        if (!$streamExists) {
            $client->createLogStream([
                'logGroupName' => $logGroupName,
                'logStreamName' => $logStreamName,
            ]);
        }

        // 3. Prepare message
        $message = json_encode([
            'id' => uniqid(),
            'page' => $page,
            'log' => $log,
            'added' => time(),
        ]);

        // 4. Put log event
        $params = [
            'logGroupName' => $logGroupName,
            'logStreamName' => $logStreamName,
            'logEvents' => [
                [
                    'timestamp' => round(microtime(true) * 1000),
                    'message' => $message,
                ],
            ],
        ];

        if ($sequenceToken) {
            $params['sequenceToken'] = $sequenceToken;
        }

        $client->putLogEvents($params);

        return ['success' => true];
    } catch (AwsException $e) {
        return ['error' => $e->getAwsErrorMessage(), 'details' => $e->getMessage()];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// die();

// Load database credentials from environment variables
$dbHost = getenv('MYSQL_HOST');
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('MYSQL_DATABASE');
$dbUser = getenv('MYSQL_USER');
$dbPassword = getenv('MYSQL_PASSWORD');


// Setup database connection
global $conn;
$conn = mysql_connect ($dbHost , $dbUser , $dbPassword) or die(mysql_error());


mysql_select_db ($dbName , $conn);

date_default_timezone_set('Europe/London');

function mysql_connect($server,$username,$password){

    return mysqli_connect($server,$username,$password);

}



function mysql_select_db($database_name,$link){

    return mysqli_select_db($link,$database_name);

}



function mysql_query($query){ global $conn;

    return mysqli_query($conn,$query);

}



function mysql_fetch_array($result){

    return mysqli_fetch_assoc($result);

}



function mysql_num_rows($result){

    return mysqli_num_rows($result);

}



function mysql_insert_id(){ global $conn;

    return mysqli_insert_id($conn);

}


if($webhookbypass==0){

// if (strpos($_SERVER['SERVER_NAME'], 'etra.group') !== false){}else{die('Error');}//Protection from Nameserver imitation

}

//////////////////////////// CHANGED FROM HERE $LOC: how it's retrieveed

//MASTER 301 REDIRECT


$subdomaindetect = $_SERVER['SERVER_NAME'];

$subdomaindetect = str_replace('superviral.','',$subdomaindetect);

$subdomaindetecthttp_host = $_SERVER['HTTP_HOST'];
$subdomainhttp_host_array = explode('.', $subdomaindetecthttp_host);

$subdomainloc = array_shift(($subdomainhttp_host_array));

if(($subdomainloc=='us')||($subdomainloc=='uk')){

$newhttphost = str_replace($subdomainloc.'.','',$_SERVER['HTTP_HOST']);

//echo 'https://'.$newhttphost.'/'.$subdomainloc.$_SERVER['REQUEST_URI'];

header('Location: https://'.$newhttphost.'/'.$subdomainloc.$_SERVER['REQUEST_URI'],TRUE,301);die;

}



?>
