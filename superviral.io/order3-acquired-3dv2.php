<?php



include 'header.php';

include 'ordercontrol.php';

include 'common/common.php'; // AJ: include common

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

                include('pi/cardinitywebhook.php');

                setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);

                die();

        } else {
            setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
        }
    } else {
        setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
    }
}
header("Location: " . $siteDomain .$loclink. "/order/payment/");
