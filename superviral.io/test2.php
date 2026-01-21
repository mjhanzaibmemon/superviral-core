<?php


$starttime = microtime(true);

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/


echo 'Lamadava<hr>';

		$username = 'Alistersuk';
	 $url = 'https://api.lamadava.com/a1/user?username='.$username;
	 $lamadavaaccess = 'SXWFHn47qcFNaWUc6JsjaQuZpaNaSPpg';

	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
	curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);


	//$get = json_decode($get);


	curl_close($curl);


echo $get.'<hr><hr><hr><hr>';


$endtime = microtime(true);

printf("Page loaded in %f seconds", $endtime - $starttime );

?>