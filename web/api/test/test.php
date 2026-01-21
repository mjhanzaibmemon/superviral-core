<?php


$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

$apiKey = $google_place_key;

$userLat = 51.509865;
$userLon = -0.118092;
$restLat = 52.4787;
$restLon = -1.89781;

$result = getDriveDistanceTime($userLat, $userLon, $restLat, $restLon, $apiKey);
echo '<pre>';
// print_r($result);
if ($result['status'] == 'OK') {
    // Process the result
    $distance = $result['rows'][0]['elements'][0]['distance']['text'];
    $duration = $result['rows'][0]['elements'][0]['duration']['text'];

    echo "Distance: " . $distance . "<br>";
    echo "Drive Time: " . $duration;
}else{
    echo "Error: " . $result['error_message'];
}

function getDriveDistanceTime($originLat, $originLon, $destLat, $destLon, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$originLat},{$originLon}&destinations={$destLat},{$destLon}&mode=driving&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return response as string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL check if needed (optional)

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    return $data;
}


