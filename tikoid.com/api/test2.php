<?php

ini_set ('display_errors', 1);
ini_set ('display_startup_errors', 1);
error_reporting (E_ALL);

include('../db.php');

/*	$username = 'therock';

	$url = 'https://api.socialscrape.com/v2/tiktok/userId?username='.$username;


	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikoidSocialScrapeKey" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);


	$get = json_decode($get,true);

	curl_close($curl);


$userId = $get['data']['data']['userId'];


echo $userId;

echo '<hr>';*/

/*	$url = 'https://api.socialscrape.com/v2/tiktok/profile?userId='.$userId;


	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikoidSocialScrapeKey" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);


	$get = json_decode($get,true);

	curl_close($curl);


echo '<pre>';
print_r($get);
echo '</pre>';
*/

echo '<hr>';

/*$max_cursor = 1669039233000;//October 2022
$max_cursor = 1695215022000;//September 25
$max_cursor = 1585215022000;*/

$userId = '6745191554350760966';

	$url = 'https://api.socialscrape.com/v2/tiktok/feed?userId='.$userId;


	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikoidSocialScrapeKey" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);


	$get = json_decode($get,true);

	curl_close($curl);

echo '<pre>';
print_r($get);
echo '</pre>';




?>