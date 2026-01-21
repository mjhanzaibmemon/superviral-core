<?php

include('db.php');

$starttime = microtime(true);

echo 'Combined<hr>';






	$username = urlencode('curlycpmum');


/*
	 $url = 'https://api.datalama.io/v1/user/by/username?username='.$username;
	 

	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $datalamaaccess" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);

	$get = json_decode($get);



	$userId = $get -> pk;	*/
	$userId = '36640654173';	

	echo '@'.$username.' - '.$userId.'<hr>';



$igurl = 'https://www.instagram.com/graphql/query/?query_hash=f2405b236d85e8296cf30347c9f08c2a&variables=%7B%22id%22%3A'.$userId.'%2C%22first%22%3A12%2C%22after%22%3A%22QVFBWERJdHQ4RktDakVpZHk0WVNMcDBiYUZreUZxYjluSHJOSTZqT3VLcW8ybmV2S0o1RTgyM09GaVcxelJHeThpRFVLWHhNc2kxR1luM1laZUloZTUtSQ%3D%3D%22%7D';



		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $igurl);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);

	curl_close($curl);



///////////////////////////////////////////////////////////////////////////////////////////////

//

if ((strpos($get, 'Please wait a few minutes before you try again.') !== false)||
	(strpos($get, ',"page_info":{"has_next_page":false,"end_cursor":""},"edges":[]}}},"status":"ok"}') !== false)) {


echo 'Block detected<hr>';

unset($code);
unset($get);


	$url = 'https://scrape.smartproxy.com/v1/tasks';

	$curl = curl_init();

		$data = array(    
	"target"=> "universal",
    "parse"=> "False",
    "url"=> $igurl);


		$data = json_encode($data);

		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Accept: application/json' , 
			"Authorization: Basic VTAwMDAwODY1OTY6NCU1KkRSOXJ4M21r", 
			"Content-Type: application/json" 
			));

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 15);

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);

	curl_close($curl);

}








echo $get.'<hr>';


$endtime = microtime(true);

printf("Page loaded in %f seconds", $endtime - $starttime );

?>