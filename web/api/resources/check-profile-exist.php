<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
  $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

$checkStmt = mysql_query("SELECT * FROM instagram_profiles");

while ($checkData = mysql_fetch_array($checkStmt)) {

    $username = $checkData['username'];
    $id = $checkData['id'];
    echo "Processing user: $username<br>";
    if (checkprofile($username)) {
        echo "✅ $username page exists.<br>";
    } else {
        echo "❌ $username page does NOT exist.<br>";

        mysql_query("DELETE FROM instagram_profiles WHERE id = $id");

    }
  
}

function checkprofile($username) {
    global $superviralsocialscrapekey;
   $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $username;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);
	$resp = $get;

	$resp = json_decode($resp, true);
	$users = $resp['data'];
	$userId = $users['user']['pk_id'];

	curl_close($curl);

    return !empty($userId) && $userId > 0 ? true : false;
}
