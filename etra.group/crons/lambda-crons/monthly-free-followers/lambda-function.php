<?php
	
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/crons/lambda-crons/common.php'; // common confguration


$o = 0;

$morethanatimeago = '604800';
$morethanatimeago = time() - $morethanatimeago;

$q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");

$q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT 25");

if (mysql_num_rows($q) == '0') {

    $q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");

    $q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT 25");
    echo 'CART MODE<hr>';
}


if (mysql_num_rows($q) == 0) {
    die('no more users to search for');
} else {
    echo 'Total left:' . mysql_num_rows($q2) . '<hr>';
}

while ($info = mysql_fetch_array($q)) {

    $jsonViewData = json_encode($info);
    // echo $jsonViewData;die;
    $res1 = AddQueue($monthly_free_followers_queueUrl, $jsonViewData); // queuerl from common.php

}
