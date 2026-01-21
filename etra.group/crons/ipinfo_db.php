<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';

$url = "https://ipinfo.io/data/location.csv.gz?token=".$ipinfoToken;
$outputFile = "location.csv.gz";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Equivalent to `-L` in curl

$data = curl_exec($ch);
if (curl_errno($ch)) {
    echo "Curl error: " . curl_error($ch);
} else {
    file_put_contents($outputFile, $data);
    echo "File downloaded successfully.";
}

curl_close($ch);
