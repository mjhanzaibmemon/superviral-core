<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';

global $url;

if($devstage == 'test'){
    $url = 'https://qaapi.acquired.com/api.php/status';
}else{
    $url = 'https://gateway.acquired.com/api.php/status';
}
$transaction_type = 'TRANSACTION_ID';
$date = date('YmdHis');

$multiCurl = [];
$mh = curl_multi_init();

$q = mysql_query("SELECT id, payment_id, `order_session`
                                FROM orders
                                WHERE added >= UNIX_TIMESTAMP(DATE_FORMAT(NOW() - INTERVAL 1 HOUR, '%Y-%m-%d %H:00:00'))
                                AND added < UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'))
                                ORDER BY added DESC limit 5;");


while ($data = mysql_fetch_array($q)) {


    
    $param = array(
        'timestamp' => $date,
        'status_request_type' => $transaction_type,
        'company_id' => $acquiredaccountid,
        'transaction_id' => $data['payment_id'],
    );

    $hash = sha256hash_status($param, $acquiredsecretpasscode);

    $paydata = [
        'request_hash' => $hash,
        'timestamp' => $date,
        'company_id' => $acquiredaccountid,
        'company_pass' => $acquiredcompanypass,
        'transaction' => [
            'status_request_type' => $transaction_type,
            'transaction_id' => $data['payment_id'],
        ],
    ];
    $content = json_encode($paydata);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

    curl_multi_add_handle($mh, $ch);
    $multiCurl[] = $ch;
}


$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running);

foreach ($multiCurl as $ch) {
    $response_data = curl_multi_getcontent($ch);
    $response_data = json_decode($response_data, true);

    if (strpos($response_data['transaction']['response_message'], 'fraud') !== false) {

        echo 'fraud';

        mysql_query("UPDATE orders SET `refund` = '1' , `disputed` = '0' WHERE `order_session` = '{$data['order_session']}' ORDER BY id DESC LIMIT 1");

        $checkExist = mysql_query("SELECT * FROM `blacklist` WHERE emailaddress = '{$dataOS['emailaddress']}' OR igusername = '{$dataOS['igusername']}'OR ipaddress = '{$dataOS['ipaddress']}'");
        if (mysql_num_rows($checkExist) == 0) {
            mysql_query("INSERT INTO blacklist SET emailaddress = '{$dataOS['emailaddress']}', igusername = '{$dataOS['igusername']}',ipaddress = '{$dataOS['ipaddress']}', `billingname` = '{$dataOS['payment_billingname_crdi']}', added = '$now', brand = 'sv' ");
            echo 'insert';
        }
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

function sha256hash_status($param, $secret) {
    if (in_array($param['status_request_type'], ['ORDER_ID_ALL', 'ORDER_ID_FIRST', 'ORDER_ID_LAST', 'ORDER_ID_SUCCESS'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['merchant_order_id'];
    } elseif (in_array($param['status_request_type'], ['TRANSACTION_ID', 'TRANSACTION_ID_CHILDREN_ALL', 'TRANSACTION_ID_CHILDREN_FIRST', 'TRANSACTION_ID_CHILDREN_LAST', 'TRANSACTION_ID_CHILDREN_SUCCESS'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['transaction_id'];
    } elseif (in_array($param['status_request_type'], ['BIN'])) {
        $str = $param['timestamp'] . $param['status_request_type'] . $param['company_id'] . $param['bin'];
    }
    return hash('sha256', $str . $secret);
}
