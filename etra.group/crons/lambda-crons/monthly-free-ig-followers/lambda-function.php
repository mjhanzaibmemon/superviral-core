<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php';

$rowslimit = 1000;
$o = 0;
$totalTimeSeconds = 25;
$morethanatimeago = time() - 604800;

echo 'X amount of rows processing per minute<br>';

// Query setup
$q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");

$q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT $rowslimit");

if (mysql_num_rows($q) == 0) {
    $q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");
    $q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT $rowslimit");
    echo 'CART MODE<hr>';
}

if (mysql_num_rows($q) == 0) {
    die('no more users to search for');
} else {
    echo 'Total left: ' . mysql_num_rows($q2) . '<hr>';
}

// -----------------------------------
// Main logic - dynamic throttling
// -----------------------------------
$startTime = microtime(true);
$rowIndex = 0;

$rows = [];
while ($info = mysql_fetch_array($q)) {
    $rows[] = $info; // store all rows first
}
$totalRows = count($rows);

foreach ($rows as $info) {
    $rowIndex++;

    $jsonViewData = json_encode($info);
    if ($_SERVER['HTTP_X_FORWARDED_FOR'] == '212.159.178.222') {
        echo $rowIndex . ': ' . $jsonViewData . '<hr>';
    }

    mysql_query("UPDATE `users` SET `monthlyfreefollowers` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");
    $res1 = AddQueue($monthly_free_followers_queueUrl, $jsonViewData);

    // Calculate expected time per row
    $expectedElapsed = ($totalTimeSeconds / $totalRows) * $rowIndex;
    $actualElapsed = microtime(true) - $startTime;
    $sleep = $expectedElapsed - $actualElapsed;

    if ($sleep > 0) {
        usleep($sleep * 1_000_000);
    }
}

// Final runtime
$totalUsed = round(microtime(true) - $startTime, 3);
echo "<hr>Total time used: {$totalUsed}s";

?>
