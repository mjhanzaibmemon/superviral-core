<?php

	include('../db.php');

	$username = 'therock';

	$url = 'https://api.tikapi.io/public/check?username='.$username;


	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikioidAccessToken" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);
	//$get = string($get);

	//echo gettype($get), "\n";

	$get = json_decode($get,true);

	curl_close($curl);




    


/*
echo '<pre>';
  print_r($get['userInfo']);
echo '</pre>';
*/
//echo $get -> userInfo -> user -> commerceUserInfo -> secUid;








?>