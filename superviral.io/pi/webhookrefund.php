<?php

require_once 'shared.php';
include('../db.php');

$event = null;

try {
	// Make sure the event is coming from Stripe by checking the signature header
	$event = \Stripe\Webhook::constructEvent($input, $_SERVER['HTTP_STRIPE_SIGNATURE'], $stripewebhookrefundsig);
}
catch (Exception $e) {
	http_response_code(403);
	echo json_encode([ 'error' => $e->getMessage() ]);
	exit;
}

$details = '';

if (($event->type == 'charge.refunded')||($event->type == 'charge.refund.updated')) {

	$details = 'Refund issued!';

$input = json_decode($input, true);
$uniquepaymentid = $input['data']['object']['payment_intent'];

$db=1;


mysql_query("UPDATE `orders` SET `refund` = '1' WHERE `payment_id` = '$uniquepaymentid' LIMIT 1");

$output = [
	'id' => $uniquepaymentid
];



}


//////////////////////////////////////


echo json_encode($output, JSON_PRETTY_PRINT);
