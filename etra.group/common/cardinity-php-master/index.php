<?php 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);



require_once("vendor/autoload.php"); 


/* Start to develop here. Best regards https://php-download.com/ */

use Cardinity\Client;
use Cardinity\Method\Payment;
use Cardinity\Exception;


$client = Client::create([
    'consumerKey' => '',
    'consumerSecret' => '',
]);



$method = new Payment\Create([
    'amount' => 50.00,
    'currency' => 'EUR',
    'settle' => true,
    'description' => 'some description',
    'order_id' => '12345678',
    'country' => 'LT',
    'payment_method' => Payment\Create::CARD,
    'payment_instrument' => [
        'pan' => '4111111111111111',
        'exp_year' => 2021,
        'exp_month' => 12,
        'cvc' => '456',
        'holder' => 'Mike Dough'
    ],
    'threeds2_data' =>  [
        "notification_url" => "your_url_for_handling_callback", 
        "browser_info" => [
            "accept_header" => "text/html",
            "browser_language" => "en-US",
            "screen_width" => 600,
            "screen_height" => 400,
            'challenge_window_size' => "600x400",
            "user_agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0",
            "color_depth" => 24,
            "time_zone" => -60
        ],
    ],
]);

/**
* In case payment could not be processed exception will be thrown.
* In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.
* See Error Codes section for detailed list.
*/
try {
    /** @type Cardinity\Method\Payment\Payment */
    $payment = $client->call($method);
    $status = $payment->getStatus();

    if ($status == 'approved') {
      // Payment is approved




    } elseif ($status == 'pending') {
        // check if passed through 3D secure version 2
        if ($payment->isThreedsV2()) {
            // get data required to finalize payment
            $creq = $payment->getThreeds2Data()->getCreq();
            $paymentId = $payment->getId();
            // finalize process should be done here.
        } elseif ($payment->isThreedsV1()) {
            // Retrieve information for 3D-Secure V1 authorization
            $url = $payment->getAuthorizationInformation()->getUrl();
            $data = $payment->getAuthorizationInformation()->getData();
            // finalize process should be done here.
        }
    }
} catch (Exception\Declined $exception) {
    /** @type Cardinity\Method\Payment\Payment */
    $payment = $exception->getResult();
    $status = $payment->getStatus(); // value will be 'declined'
    $errors = $exception->getErrors(); // list of errors occurred
} catch (Exception\ValidationFailed $exception) {
    /** @type Cardinity\Method\Payment\Payment */
    $payment = $exception->getResult();
    $status = $payment->getStatus(); // value will be 'declined'
    $errors = $exception->getErrors(); // list of errors occurred
}

?>