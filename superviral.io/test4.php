<?php

die;

$starttime = microtime(true);

echo 'Oxylabs<hr>';




//4%5*DR9rx3mk

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

	$url  = 'https://www.instagram.com/graphql/query/?query_hash=f2405b236d85e8296cf30347c9f08c2a&variables=%7B%22id%22%3A173560420%2C%22first%22%3A12%2C%22after%22%3A%22QVFBWERJdHQ4RktDakVpZHk0WVNMcDBiYUZreUZxYjluSHJOSTZqT3VLcW8ybmV2S0o1RTgyM09GaVcxelJHeThpRFVLWHhNc2kxR1luM1laZUloZTUtSQ%3D%3D%22%7D';

$params = array(
    'source' => 'universal',
    'url' => $url,
    //'render' => 'html', // If page type requires
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://realtime.oxylabs.io/v1/queries");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, "livecouk" . ":" . "sVdCMYabh6"); //Your credentials go here

$headers = array();
$headers[] = "Content-Type: application/json";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
echo $result;

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);





echo $get.'<hr><hr><hr><hr>';


$endtime = microtime(true);

printf("Page loaded in %f seconds", $endtime - $starttime );

?>