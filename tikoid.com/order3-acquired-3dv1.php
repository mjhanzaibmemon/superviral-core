<?php
include 'header.php';

include 'ordercontrol.php';

include 'common/common.php'; // AJ: include common

if ($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST['PaRes'])) {
    $Common = new Common();

    $md = json_decode(base64_decode($_POST['MD']), true);

    $now = date("Ymdhms");

    $paydata = array(

        "timestamp" => $now,
        "company_id" => $tikoidacquiredaccountid,
        "company_pass" => $tikoidacquiredcompanypass,
        "company_mid_id" => $locas[$loc]['mid'],

        "transaction" => array(

            "merchant_order_id" => $md['merchant_order_id'],
            "transaction_type" => $md['transaction_type'],
            "amount" => $md['amount'],
            "currency_code_iso3" => $country_currency_code,
            "original_transaction_id" => $md['original_transaction_id'],
        ),
        "tds" => array(
            "action" => "SETTLEMENT",
            "pares" => $_POST['PaRes'],
        ),
    );

    // Aj Called from common function
    $request_hash = $Common->request_hash($paydata, $tikoidacquiredsecretpasscode);

    $paydata['request_hash'] = $request_hash;

    $url = $TransactionURL;

    $content = json_encode($paydata);

    // Aj Called from common function
    $response = $Common->curl_request($url, $content);

    if ($response["response_message"] == "Transaction Success") {
       // Do on Success

        $code='31c223b5500453655b63bf1521eb268487da3';

        $paymentId = $response['transaction_id'];

        include('pi/cardinitywebhook.php');

          setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);

        die();
       
     

    } else {
        setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
    }
} else {
    setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
}
header("Location: " . $siteDomain . "/order/payment-new/");
