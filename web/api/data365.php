<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/foodie.app/config/config.php';

$apiKey = $data365Token;

$keyword = urlencode('Chaii Garden');
echo '<pre>';

$url1 = "https://api.data365.co/v1.1/instagram/search/profiles/update?keywords=$keyword&max_profiles=10&access_token=$apiKey";

$ch = curl_init($url);  

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json"
]);


$response = curl_exec($ch);


if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {

    $data = json_decode($response, true);
    print_r($data);
}


curl_close($ch);


$url = "https://api.data365.co/v1.1/instagram/search/profiles/items?keywords=$keyword&order_by=id_desc&max_page_size=10&access_token=$apiKey";


$ch = curl_init($url);  

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json"
]);


$response = curl_exec($ch);


if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {

    $data = json_decode($response, true);
    print_r($data);
}


curl_close($ch);
