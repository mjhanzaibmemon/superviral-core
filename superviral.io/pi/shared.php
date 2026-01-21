<?php

require_once('../stripe-php-master/init.php');
include_once('../db.php');


header('Content-Type: application/json');

\Stripe\Stripe::setApiKey($stripeapikey);

$input = file_get_contents('php://input');
$body = json_decode($input);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || json_last_error() !== JSON_ERROR_NONE) {
	http_response_code(400);
	echo json_encode([ 'error' => 'Invalid request.' ]);
	exit;
}
