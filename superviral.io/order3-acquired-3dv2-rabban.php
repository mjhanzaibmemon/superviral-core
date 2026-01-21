<?php



include 'header.php';

include 'ordercontrol.php';

echo '1<br>';


class Common {

    public function request_hash($param, $company_hashcode)
        {

            if (in_array($param['transaction']['transaction_type'], array('AUTH_ONLY', 'AUTH_CAPTURE', 'CREDIT', 'BENEFICIARY_NEW'))) {

                $str = $param['timestamp'] . $param['transaction']['transaction_type'] . $param['company_id'] . $param['transaction']['merchant_order_id'];
            } elseif (
                in_array($param['transaction_type'], array(
                    'CAPTURE', 'VOID',
                    'REFUND', 'SUBSCRIPTION_MANAGE', 'ACCOUNT_UPDATER', 'PAY_OUT'
                ))
            ) {
                $str = $param['timestamp'] . $param['transaction_type'] . $param['company_id'] .
                    $param['original_transaction_id'];
            }

            return hash('sha256', $str . $company_hashcode);
        }

        public function curl_request($URL, $Data){
            $curl = curl_init($URL);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                array("Content-type: application/json")
            );
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $Data);
    
            $json_response = curl_exec($curl);
    
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    
            curl_close($curl);
    
            $response = json_decode($json_response, true);
            return $response;
        }

}


echo '2<br>';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

echo '3<br>';

    if (isset($_POST['cres'])) {

echo '4<br>';

        $Common = new Common();

        $threeDSSessionData = json_decode(base64_decode($_POST["threeDSSessionData"]), true);

echo '5<br>';

        $original_transaction_id = $threeDSSessionData["transaction_id"];
        $transaction_type = $threeDSSessionData["transaction_type"];
        $merchant_order_id = $threeDSSessionData["merchant_order_id"];

echo '1: '.$original_transaction_id.'<br>';
echo '2: '.$transaction_type.'<br>';
echo '3: '.$merchant_order_id.'<br>';


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

echo '6<br>';

        // Aj Called from common function
        $request_hash = $Common->request_hash($paydata, $acquiredsecretpasscode);
        $paydata['request_hash'] = $request_hash;

        $url = $TransactionURL;

        $content = json_encode($paydata);

        // Aj Called from common function
        $response = $Common->curl_request($url, $content);

echo '9<br>';

        if ($response["response_message"] == "Transaction Success") {
            // Do on Success

            echo '123';

                $code='31c223b5500453655b63bf1521eb268487da3';    

                $paymentId = $response['transaction_id'];

                    echo '32';

                include('pi/cardinitywebhook.php');

                echo '72';

                setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);



                die();

        } else {
            setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
            echo '32<br>';
            if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){echo $response["response_message"];}
        }
    } else {
            setcookie("ResponseMessage", $response["response_message"], time()+60*60*24*600);
            echo '59<br>';
            if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){echo $response["response_message"];}
    }
}
header("Location: " . $siteDomain .$loclink. "/order/payment/");
