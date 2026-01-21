<?php

include 'header.php';
include 'ordercontrol.php';
include 'common/common.php'; // AJ: include common

$webhookData = file_get_contents('php://input');
log_data($webhookData);

if(empty($webhookData)){die;}

$param = json_decode($param,true);
$acq_hash = $param['hash'];

$company_hashcode = $acquiredsecretpasscode;
// $plain = implode('', $param);
$plain = $param["id"] . $param["timestamp"] . $param["company_id"] . $param["event"];
// $plain ="C9EDECD6-D0B5-AED5-48E6-EF235ECD5A5420200626110608207dispute_new";
$temp = hash('sha256', $plain);
// $temp = "4107fb43722f1aeaaa4cb6330306b394ce84f97f5fd3e00d4aaedab9a5084d51";
$hash = hash('sha256', $temp.$company_hashcode);

if($hash !== $acq_hash){echo "Hash doesn't match";die;}

// Get details
$user = mysql_query("SELECT os.emailaddress, os.igusername, os.ipaddress
FROM payment_logs pl
JOIN order_session os ON pl.ipaddress = os.ipaddress
WHERE pl.payment_id = '".$param['transaction_id']."'
LIMIT 1;"
);

$user_arr = mysql_fetch_array($user);

// INSERT INTO BLACKLIST
mysql_query("INSERT INTO `blacklist` SET `brand`='sv',`emailaddress`='".$user_arr['emailadress']."',`igusername`='".$user_arr['igusername']."',`ipaddress`='".$user_arr['ipaddress']."'");


function log_data($webhookData){
    // Define the file path to store the webhook response
    $filePath = 'webhook_response.txt';
    try {
        // Retrieve the raw POST data

        // Validate that the input is not empty
        if (!$webhookData) {
            throw new Exception('No data received');
        }

        // Decode JSON to check the structure (optional)
        $decodedData = json_decode($webhookData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON received');
        }

        // Format the data for saving (optional prettification)
        $formattedData = json_encode($decodedData, JSON_PRETTY_PRINT);

        // Save the webhook data to the file
        file_put_contents($filePath, $formattedData . PHP_EOL, FILE_APPEND);

        // Respond to acknowledge the webhook (important)
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Webhook received successfully']);
    } catch (Exception $e) {
        // Handle errors
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    return;
}