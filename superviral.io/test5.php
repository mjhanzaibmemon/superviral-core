<?php

die;

$starttime = microtime(true);

echo 'Smartproxy - residential<hr>';


$spusername = 'svgroup'; 
$sppassword = '6c&J9T8w5ZDP'; 


//4%5*DR9rx3mk

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

$file  = 'https://www.instagram.com/graphql/query/?query_hash=f2405b236d85e8296cf30347c9f08c2a&variables=%7B%22id%22%3A173560420%2C%22first%22%3A12%2C%22after%22%3A%22QVFBWERJdHQ4RktDakVpZHk0WVNMcDBiYUZreUZxYjluSHJOSTZqT3VLcW8ybmV2S0o1RTgyM09GaVcxelJHeThpRFVLWHhNc2kxR1luM1laZUloZTUtSQ%3D%3D%22%7D';
	//$file  = 'https://www.instagram.com/therock/?__a=1';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $file);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);

	curl_close($curl);





echo $get.'<hr><hr><hr><hr>';


$endtime = microtime(true);

printf("Page loaded in %f seconds", $endtime - $starttime );

?>