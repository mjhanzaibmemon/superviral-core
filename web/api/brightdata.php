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

function isExcluded($domain, $exclude)
{
    foreach ($exclude as $blocked) {
        if (stripos($domain, $blocked) !== false) {
            return true;
        }
    }
    return false;
}

$limit = 80; // 
$batchSize = 20;
$processedCount = 0;
$checkStmt = mysql_query("SELECT * FROM ext_restaurants_website WHERE source = 'google' AND done = 0 GROUP BY website LIMIT $limit");

$filters = [];
$idsToUpdate = [];

$exclude = ['birmingham.gov.uk','chick-fil-a','instagram','burgerking','pepes','fiveguys', 'wingstop','wagamama','ubereats','tortilla','timhortons','tiktok','tgifridays','tesco','tacobell','subway','starbucks','spar','service.gov.uk','pret','popeyes','pizzahut','papajohns',
'order','nandos','myfoodhub','metrosbirmingham', 'Now','mcdonalds','linktr.ee','kfc','just-eat','greggs','deliveroo','costa', 'leon'];

$count = 1;
while ($checkData = mysql_fetch_array($checkStmt)) {

    // echo "$count.) Processing website: " . $checkData['website'] . "<br>";
    $website = $checkData['website'];

    if (!empty($website) && !isExcluded($website, $exclude)) {
        $tasks[] =
            [
                "name" => "external_url",
                "value" => $website,
                "operator" => "includes"
            ];

        $idsToUpdate[] = $checkData['id'];
    }
    $processedCount++;
    if(empty($tasks)) {
        // echo "No valid tasks found for website: $website<br>";
        continue;
    }

    // After processing each batch of 20, send for filtering and reset tasks
    if (count($tasks) == $batchSize) {
        if (filterDataset($key, $tasks)) {
            $idsString = implode(",", $idsToUpdate);
            mysql_query("UPDATE ext_restaurants_website SET done = 1 WHERE id IN ($idsString)");
            echo "Database updated successfully for " . count($idsToUpdate) . " records.<br>";
        }
        $tasks = []; 
        $idsToUpdate = []; 
    }

    $count++;
}

// Process any remaining tasks if less than batch size
if (!empty($tasks)) {
    if (filterDataset($key, $tasks)) {
        $idsString = implode(",", $idsToUpdate);
        mysql_query("UPDATE ext_restaurants_website SET done = 1 WHERE id IN ($idsString)");
        echo "Database updated successfully for " . count($idsToUpdate) . " records.<br>";
    }
}

echo "Total websites processed: $processedCount<br>";

// $totalFilters = count($tasks);
// echo "Total filters to process: $totalFilters<br>";

// Break filters into chunks of 20
// $chunks = array_chunk($filters, 20);
// $index = 1;

// foreach ($chunks as $chunk) {
//     echo "Sending batch $index:<br>";
//     filterDataset($key, $chunk);
//     echo str_repeat("-", 50) . "<br>";

//     if ($index < count($chunks) - 1) {
//         echo "Sleeping for 30 minutes...<br>";
//         sleep(1800); // 1800 seconds = 30 minutes
//     }
//     $index++;
// }

function filterDataset($key, $filter = [])
{
    $url = "https://api.brightdata.com/datasets/filter";
    $token = $key;

    $payload = [
        "dataset_id" => "gd_l1vikfch901nx3by4",
        "filter" => [
            "operator" => "or",
            "filters" =>  $filter
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
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode >= 200 && $httpcode < 300) {
        echo "Request succeeded:<br>";
        $data = json_decode($response, true);
        return true;
        // print_r($data);
    } else {
        echo "Request failed:<br>$response<br>";
        return false;
    }
}

?>

<script>
setTimeout(function() {
    window.location.reload();
}, 1800000); // Refresh page every 30 minutes
</script>