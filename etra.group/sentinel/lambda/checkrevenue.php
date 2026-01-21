<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
echo '<pre>';
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';

use Aws\CloudWatch\CloudWatchClient;

function sha256hash_status($param, $secret) {
    if (in_array($param['status_request_type'], ['ORDER_ID_ALL', 'ORDER_ID_FIRST', 'ORDER_ID_LAST', 'ORDER_ID_SUCCESS'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['merchant_order_id'];
    } elseif (in_array($param['status_request_type'], ['TRANSACTION_ID', 'TRANSACTION_ID_CHILDREN_ALL', 'TRANSACTION_ID_CHILDREN_FIRST', 'TRANSACTION_ID_CHILDREN_LAST', 'TRANSACTION_ID_CHILDREN_SUCCESS'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['transaction_id'];
    } elseif (in_array($param['status_request_type'], ['BIN'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['bin'];
    }
    return hash('sha256', $str . $secret);
}

function money_convert($from, $amount, $the_key) {
    $url = "https://v6.exchangerate-api.com/v6/" . $the_key . "/latest/$from";
    $request = curl_init();
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($request);
    curl_close($request);
    $response = json_decode($response);

    if($from == 'USD')
        $converted_amount = round(($amount * $response->conversion_rates->GBP), 2);
    elseif ($from == 'GBP')
        $converted_amount = round(($amount * $response->conversion_rates->USD), 2);
    $formatted_amount = number_format($converted_amount, 2, '.', '');
    return $formatted_amount;
}


global $url;

if($devstage == 'test'){
    $url = 'https://qaapi.acquired.com/api.php/status';
}else{
    $url = 'https://gateway.acquired.com/api.php/status';
}
$transaction_type = 'TRANSACTION_ID';
$date = date('YmdHis');
// orders
$query = "SELECT id, payment_id
                                FROM orders
                                WHERE added >= UNIX_TIMESTAMP(DATE_FORMAT(NOW() - INTERVAL 1 HOUR, '%Y-%m-%d %H:00:00'))
                                  AND added < UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'))
                                ORDER BY added DESC;";
$query_run = mysql_query($query);

$sumTransaction = 0;
$multiCurl = [];
$results = [];
$mh = curl_multi_init();

while ($data = mysql_fetch_array($query_run)) {


    $param = array(
        'timestamp' => $date,
        'status_request_type' => $transaction_type,
        'company_id' => $acquiredaccountid,
        'transaction_id' => $data['payment_id'],
    );

    $hash = sha256hash_status($param, $acquiredsecretpasscode);

    $paydata = [
        'request_hash' => $hash,
        'timestamp' => $date,
        'company_id' => $acquiredaccountid,
        'company_pass' => $acquiredcompanypass,
        'transaction' => [
            'status_request_type' => $transaction_type,
            'transaction_id' => $data['payment_id'],
        ],
    ];
    $content = json_encode($paydata);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

    curl_multi_add_handle($mh, $ch);
    $multiCurl[] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running);

foreach ($multiCurl as $ch) {
    $response_data = curl_multi_getcontent($ch);
    $response_data = json_decode($response_data, true);

    if ($response_data['transaction']['response_message'] == 'Transaction Success') {

        // if GBP transaction then convert to USD
        if ($response_data['transaction']['currency_code_iso3'] == 'GBP') {
            $amount = money_convert('GBP', $response_data['transaction']['amount'], $exchangerate_api_key);
        } else {
            $amount = $response_data['transaction']['amount'];
        }

        $sumTransaction += $amount;
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

// AL
$query = "SELECT id, payment_id
                    FROM automatic_likes_billing
                    WHERE added >= UNIX_TIMESTAMP(DATE_FORMAT(NOW() - INTERVAL 1 HOUR, '%Y-%m-%d %H:00:00'))
                                  AND added < UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'))
                    ORDER BY added DESC";
$query_run = mysql_query($query);

$alSumTransaction = 0;
$multiCurl = [];
$results = [];
$mh = curl_multi_init();

while ($data = mysql_fetch_array($query_run)) {
    $param = array(
        'timestamp' => $date,
        'status_request_type' => $transaction_type,
        'company_id' => $acquiredaccountid,
        'transaction_id' => $data['payment_id'],
    );

    $hash = sha256hash_status($param, $acquiredsecretpasscode);

    $paydata = [
        'request_hash' => $hash,
        'timestamp' => $date,
        'company_id' => $acquiredaccountid,
        'company_pass' => $acquiredcompanypass,
        'transaction' => [
            'status_request_type' => $transaction_type,
            'transaction_id' => $data['payment_id'],
        ],
    ];
    $content = json_encode($paydata);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

    curl_multi_add_handle($mh, $ch);
    $multiCurl[] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running);

foreach ($multiCurl as $ch) {
    $response_data = curl_multi_getcontent($ch);
    $response_data = json_decode($response_data, true);

    if ($response_data['transaction']['response_message'] == 'Transaction Success') {
        if ($response_data['transaction']['currency_code_iso3'] == 'GBP') {
            $amount1 = money_convert('GBP', $response_data['transaction']['amount'], $exchangerate_api_key);
        } else {
            $amount1 = $response_data['transaction']['amount'];
        }
        $alSumTransaction += $amount1;
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);


$sumTransaction = $sumTransaction + $alSumTransaction;
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
        'Namespace' => 'MyApp/HourlyRevenueMetrics',
        'MetricData' => [
            [
                'MetricName' => 'HourlyRevenue',
                'Dimensions' => [
                    [
                        'Name' => 'FunctionName',
                        'Value' => 'hourly-revenue-function'
                    ],
                ],
                'Unit' => 'None', // Or 'Currency' if appropriate
                'Value' => $sumTransaction, // Use the calculated revenue value
            ],
        ],
    ]);

    error_log('Revenue metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending revenue metric data: ' . $e->getMessage());
}
$GBPAmount = money_convert('USD', $sumTransaction, $exchangerate_api_key);
// echo $sumTransaction .' ' . $GBPAmount;

try {
    // Send custom revenue metric data to CloudWatch GBP
    $cloudWatchClient->putMetricData([
        'Namespace' => 'MyApp/HourlyGBPRevenueMetrics',
        'MetricData' => [
            [
                'MetricName' => 'HourlyGBPRevenue',
                'Dimensions' => [
                    [
                        'Name' => 'FunctionName',
                        'Value' => 'hourly-gbp-revenue-function'
                    ],
                ],
                'Unit' => 'None', // Or 'Currency' if appropriate
                'Value' => $GBPAmount, // Use the calculated revenue value
            ],
        ],
    ]);

    error_log('Revenue metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending revenue metric data: ' . $e->getMessage());
}

echo 'Revenue data submitted to CloudWatch';


    // if ($sumTransaction < $objectiveAmountRev) {

    //     // echo '<br>sent';die;
    //     $MessageBird = new \MessageBird\Client($messagebirdclient);
    //     $Message = new \MessageBird\Objects\Message();
    //     $Message->originator = +447451272012;
    //     $Message->recipients = array($rfcontactnumber);

    //     $Message->body = 'Etra Group Alert: Daily revenue is not achieved £' . round($sumTransaction);

    //     $MessageBird->messages->create($Message);

    //     if ($MessageBird) {
    //         if ($showoutput == 1) echo 'Text Message Sent to Rabban !<br>';
    //     }
    // }
