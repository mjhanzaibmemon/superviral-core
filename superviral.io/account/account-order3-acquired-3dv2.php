<?php
include '../header.php';

include '../common/common.php'; // AJ: include common

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['cres'])) {

        $Common = new Common();

        $threeDSSessionData = json_decode(base64_decode($_POST["threeDSSessionData"]), true);

        $original_transaction_id = $threeDSSessionData["transaction_id"];
        $transaction_type = $threeDSSessionData["transaction_type"];
        $merchant_order_id = $threeDSSessionData["merchant_order_id"];

        $now = date("Ymdhms");

        $paydata = array(

            "timestamp" => $now,
            "company_id" => $acquiredaccountid,
            "company_pass" => $acquiredcompanypass,

            "transaction" => array(

                "transaction_type" => $transaction_type,
                "original_transaction_id" => $original_transaction_id,
            ),
            "tds" => array(
                "action" => "SCA_COMPLETE",
                "cres" => $_POST['cres'],
            ),
        );

        // Aj Called from common function
        $request_hash = $Common->request_hash($paydata, $acquiredsecretpasscode);
        $paydata['request_hash'] = $request_hash;

        $url = $TransactionURL;

        $content = json_encode($paydata);

        // Aj Called from common function
        $response = $Common->curl_request($url, $content);

        if ($response["response_message"] == "Transaction Success") {
            // Do on Success

                $code='31c223b5500453655b63bf1521eb268487da3';    

                $paymentId = $response['transaction_id'];

                setcookie( 'ResponseMessage', $response["response_message"], time() + 3600, '/', $_SERVER['SERVER_NAME'] );
                setcookie( 'paymentId', $paymentId, time() + 3600, '/', $_SERVER['SERVER_NAME'] );
                setcookie( 'expdate', $threeDSSessionData['expdate'], time() + 3600, '/', $_SERVER['SERVER_NAME'] );

        } else {
            setcookie( 'ResponseMessage', $response["response_message"], time() + 3600, '/', $_SERVER['SERVER_NAME'] );
        }
    } else {
        setcookie( 'ResponseMessage', $response["response_message"], time() + 3600, '/', $_SERVER['SERVER_NAME'] );
    }
}
header("Location: /$loclinkforward" . "account/checkout/".$threeDSSessionData['order_session']);
