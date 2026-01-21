<?php

include 'header.php';
include 'ordercontrol.php';
include 'common/common.php'; // AJ: include common

$webhookData = file_get_contents('php://input');
log_data($webhookData);

if(empty($webhookData)){die;}

$param = json_decode($param,true);
$company_hashcode = $acquiredsecretpasscode;
$plain = "execute" . $param["webhook_body"]["transaction_id"] . $param["webhook_body"]["order_id"] . $param["timestamp"];
$str = hash('sha256', $plain);
$hash = hash('sha256', $str.$acquiredapikey);


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