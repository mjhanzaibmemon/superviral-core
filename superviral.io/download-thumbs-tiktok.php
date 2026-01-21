<?php



include('db.php');
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';
$s3 = new S3($amazons3key, $amazons3password);

$shortcode = addslashes($_GET['shortcode']);
$username = addslashes($_GET['username']);
$now = time();



$query = mysql_query("SELECT `thumb_url`,`added_on_tiktok` FROM `tt_thumbs` WHERE `shortcode` = '$shortcode' ORDER BY `id` DESC LIMIT 1");

$findFile = mysql_fetch_array($query);


$file = $findFile["thumb_url"];

$added_on_tiktok = $findFile['added_on_tiktok'];
if(empty($added_on_tiktok)){
	$added_on_tiktok = 0;
}

	$newimgname = md5('tikoidrb'.$shortcode);

	// echo $newimgname;die;

		$randnum = rand(0, 3);

	

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $file);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

		// curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');



		// curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);





		header('Content-Type: image/jpeg');

		$get = curl_exec($curl);

			curl_close($curl);


if(empty($get)){

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $file);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

	//	curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		//curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

		$get = curl_exec($curl);

		curl_close($curl);


}

		

		echo $get;



		//die;





		if(empty($get))
		{
			sendCloudwatchData('Superviral', 'curl-image-load-failure', 'DownloadThumbsTiktok', 'curl-image-load-failure-function', 1);

			die('no response');
		}



			

	






	
		if (!empty($get)) {

			$putobject = S3::putObject($get, 'cdn.superviral.io', 'tt-thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
			if($putobject){
				sendCloudwatchData('Superviral', 's3-image-upload-success', 'DownloadThumbsTiktok', 's3-image-upload-success-function', 1);

			}else{
				sendCloudwatchData('Superviral', 's3-image-upload-failure', 'DownloadThumbsTiktok', 's3-image-upload-failure-function', 1);

			}
			echo "putobject";
		}

	

		if (!empty($get)) {


			$findifpostexistsq = mysql_query("SELECT `id`,`shortcode`,`ttusername` FROM `tt_thumbs` WHERE  `shortcode` = '$shortcode' LIMIT 1");

			if(mysql_num_rows($findifpostexistsq)=='0'){

						mysql_query("INSERT INTO `tt_thumbs` SET `dnow` = '0',`shortcode` = '$shortcode', `ttusername` = '$username', `added` = '$now',`added_on_tiktok` = '$added_on_tiktok',`sent_email` = '1'");

			}

			else

			{

						$findifpostexistsinfo = mysql_fetch_array($findifpostexistsq);
						$downloadedPath = "https://cdn.superviral.io/tt-thumbs/$newimgname.jpg";
						mysql_query("UPDATE `tt_thumbs` SET thumb_url = '$downloadedPath' ,`dnow` = '0',`added_on_tiktok` = '$added_on_tiktok',`ttusername` = '$username' WHERE `id` = '{$findifpostexistsinfo['id']}' LIMIT 1");

			}

			// $q = mysql_query("UPDATE `downloadthumbsurl` SET `dnow` = '0' WHERE `id` = '{$findFile['id']}' LIMIT 1");
	

		}

	






?>