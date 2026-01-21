<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
set_time_limit(0);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
$key = $brightdata_key;

echo '<pre>';

function isExcluded($domain, $exclude) {
    foreach ($exclude as $blocked) {
        if (stripos($domain, $blocked) !== false) {
            return true;
        }
    }
    return false;
}

$checkRestStmt = mysql_query("SELECT * FROM ext_restaurants WHERE source = 'google' GROUP BY address;");

$filters_address = [];

$exclude = ['High St', 'Unit'];

while ($checkRestData = mysql_fetch_array($checkRestStmt)) {
    $address = explode(',', $checkRestData['address'])[0];

    if (!empty($address) && !isExcluded($address, $exclude)) {
       $filters_address[] = [
           "name" => "business_address",
           "value" => $address,
           "operator" => "includes"
       ];
    }
}

// Split into chunks of 20 and send requests
$chunks = array_chunk($filters_address, 20);
$index = 1;

foreach ($chunks as $chunk) {
    echo "Sending batch $index:<br>";
    filterDataset($key, $chunk);
    echo str_repeat("-", 50) . "<br>";

    if ($index < count($chunks) - 1) {
        echo "Sleeping for 30 minutes...<br>";
        sleep(1800); // 1800 seconds = 30 minutes
    }
    $index++;
}

function filterDataset($key, $filter = []) {
    $url = "https://api.brightdata.com/datasets/filter";
    $token = $key;

    $payload = [
        "dataset_id" => "gd_l1vikfch901nx3by4",
        "filter" => [
            "operator" => "or",
            "filters" => $filter
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode >= 200 && $httpcode < 300) {
        echo "Request succeeded:<br>";
        $data = json_decode($response, true);
        print_r($data);
    } else {
        echo "Request failed:<br>$response<br>";
    }
}

function downloadData($snapshotId, $token) {
    $url = "https://api.brightdata.com/datasets/filter";
    $curl = curl_init();
    echo $snapshotId;
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.brightdata.com/datasets/snapshots/$snapshotId/download",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"

      ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      echo $response;
    }
}

// filterDataset($key);
?>
