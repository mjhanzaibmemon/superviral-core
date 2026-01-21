<?php
// webhook-handler.php

// Retrieve the raw POST data
$webhookPayload = file_get_contents('php://input');

// Log file for webhooks
$logFile = 'webhook_logs.txt';

// Decode the JSON payload
$data = json_decode($webhookPayload, true);

// Validate the webhook (implement HMAC_SHA256 verification as per Acquired.com's documentation)

// Check the webhook type
if (in_array($data['webhook_type'], ['fraud_new', 'dispute_new'])) {
    // Log the webhook data
    $logEntry = date('Y-m-d H:i:s') . " - " . json_encode($data) . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Respond with a 200 status code
    http_response_code(200);
    echo "Webhook received and logged.";
} else {
    // Respond with a 400 status code for unsupported webhook types
    http_response_code(400);
    echo "Unsupported webhook type.";
}
?>
