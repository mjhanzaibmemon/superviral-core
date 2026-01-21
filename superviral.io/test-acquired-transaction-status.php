<?php


include('header.php');

$URL = 'https://gateway.acquired.com/api.php/status/';
// https://qaapi.acquired.com/api.php/status -- for dev
// https://gateway.acquired.com/api.php/status -- for live

$now =  date("Ymdhms");

$trans_id = 11814669; // replace transaction_id

$paydata = array(

    "timestamp" => $now,

    "company_id" => $acquiredaccountid,

    "status_request_type" => 'TRANSACTION_ID',

    "transaction_id" => $trans_id

);


$request_hash = sha256hash_status($paydata, $acquiredsecretpasscode);

$paydata1 = array(

    "timestamp" => $now,

    "company_id" => $acquiredaccountid,

    "company_pass" => $acquiredcompanypass,

    "transaction" => array(

        "status_request_type" => 'TRANSACTION_ID_CHILDREN_SUCCESS',

        "transaction_id" => $trans_id,
    ),

);

$paydata1['request_hash'] = $request_hash;

$Data = json_encode($paydata1);

echo '<pre>';
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

print_r($response);



function sha256hash_status($param, $secret)
{
    if (in_array($param['status_request_type'], array('ORDER_ID_ALL', 'ORDER_ID_FIRST', 'ORDER_ID_LAST', 'ORDER_ID_SUCCESS'))) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['merchant_order_id'];
    } elseif (in_array($param['status_request_type'], array('TRANSACTION_ID', 'TRANSACTION_ID_CHILDREN_ALL', 'TRANSACTION_ID_CHILDREN_FIRST', 'TRANSACTION_ID_CHILDREN_LAST', 'TRANSACTION_ID_CHILDREN_SUCCESS'))) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['transaction_id'];
    } elseif (in_array($param['status_request_type'], array('BIN'))) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['bin'];
    }
    return hash('sha256', $str . $secret);
}
