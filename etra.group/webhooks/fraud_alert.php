<?php
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';
// webhook-handler.php
echo '<pre>';
// Retrieve the raw POST data
$webhookPayload = file_get_contents('php://input');

// eg data
$now = time();
// Decode the JSON payload
$data = json_decode($webhookPayload, true);

$list = $data['list'];
$i = 0;

echo count($list);
echo '<br>';

while ($i < count($list)) {
    $merchant_order_id = $data['list'][$i]['merchant_order_id'];

    $orderSession = trim(explode('-', $merchant_order_id)[0]);

    
    $checkOsQuery = mysql_query('SELECT * FROM `order_session` WHERE `order_session` = "' . $orderSession . '" ORDER BY id DESC limit 1');
    $dataOS = mysql_fetch_array($checkOsQuery);

    // Check if the webhook is a fraud alert
    if ($data['event'] == 'fraud_new') {
        // Process the fraud alert data

        if (mysql_num_rows($checkOsQuery) > 0) {
            mysql_query("UPDATE orders SET `refund` = '1' , `disputed` = '0' WHERE `order_session` = '{$orderSession}' ORDER BY id DESC LIMIT 1");

            $checkExist = mysql_query("SELECT * FROM `blacklist` WHERE emailaddress = '{$dataOS['emailaddress']}' OR igusername = '{$dataOS['igusername']}'OR ipaddress = '{$dataOS['ipaddress']}'");
            if (mysql_num_rows($checkExist) == 0) {
                mysql_query("INSERT INTO blacklist SET emailaddress = '{$dataOS['emailaddress']}', igusername = '{$dataOS['igusername']}',ipaddress = '{$dataOS['ipaddress']}', `billingname` = '{$dataOS['payment_billingname_crdi']}', added = '$now', brand = 'sv', `source` = 'webhook-fraud-alert' ");
                echo 'insert';
                sendCloudwatchData('EtraGroupWebhook', 'blacklist-insert', 'FraudAlert', 'blacklist-insert-function', 1);

            }
        }
    }

    if ($data['event'] == 'dispute_new') {
        // Process the dispute data

        if (mysql_num_rows($checkOsQuery) > 0) {

            $checkExist = mysql_query("SELECT * FROM `blacklist` WHERE emailaddress = '{$dataOS['emailaddress']}' OR igusername = '{$dataOS['igusername']}'OR ipaddress = '{$dataOS['ipaddress']}'");
            if (mysql_num_rows($checkExist) == 0) {
                mysql_query("INSERT INTO blacklist SET emailaddress = '{$dataOS['emailaddress']}', igusername = '{$dataOS['igusername']}',ipaddress = '{$dataOS['ipaddress']}', `billingname` = '{$dataOS['payment_billingname_crdi']}', added = '$now', brand = 'sv', `source` = 'webhook-fraud-alert' ");
                echo 'insert';
                sendCloudwatchData('EtraGroupWebhook', 'blacklist-insert', 'FraudAlert', 'blacklist-insert-function', 1);

            }
        }
    }

    $i++;
}
