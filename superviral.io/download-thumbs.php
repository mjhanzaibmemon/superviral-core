<?php

include('db.php');
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';
$s3 = new S3($amazons3key, $amazons3password);

$shortcode = addslashes($_GET['shortcode']);
$username = addslashes($_GET['username']);
$now = time();



$query = mysql_query("SELECT `thumb_url`,`added_on_instagram` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' ORDER BY `id` DESC LIMIT 1");

$findFile = mysql_fetch_array($query);


$file = $findFile["thumb_url"];

$added_on_instagram = $findFile['added_on_instagram'];

if($file == "" || $file == null){

	$query = mysql_query("SELECT `thumb_url`,`added_on_instagram` FROM `downloadthumbsurl` WHERE `short_code` = '$shortcode' ORDER BY `id` DESC LIMIT 1");

	$findFile = mysql_fetch_array($query);

	$file = $findFile["thumb_url"];

	$added_on_instagram = $findFile['added_on_instagram'];


}

	
	$newimgname = md5('superviralrb'.$shortcode);

	// echo $newimgname;die;

		$randnum = rand(0, 3);

	

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $file);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

//		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
//		curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');



		// curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);





	if($_GET['rabban']!=='true')	header('Content-Type: image/jpeg');

		$get = curl_exec($curl);

			curl_close($curl);


if(empty($get)){

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $file);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

		$get = curl_exec($curl);

		curl_close($curl);


}

		

	if($_GET['rabban']!=='true')	echo $get;



		//die;



		if(empty($get))die('no response: '.$file);



if($_GET['rabban']=='true')echo 'Here 2';
			

	






	
		if (!empty($get)) {




			$putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
			
			if($_GET['rabban']=='true'){


				echo '<hr>'.$shortcode.'<hr>';

			$putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/.jpg', S3::ACL_PUBLIC_READ);




//			echo "putobject";

			if($_GET['rabban']=='true')echo 'Here 3.'.$newimgname.'<br>';
		
		}
}
	
if($_GET['rabban']=='true')echo 'Here 4';

		if (!empty($get)) {

			if($_GET['rabban']=='true')echo 'Here 7';


			$findifpostexistsq = mysql_query("SELECT `id`,`shortcode`,`igusername` FROM `ig_thumbs` WHERE  `shortcode` = '$shortcode' LIMIT 1");

			if(mysql_num_rows($findifpostexistsq)=='0'){

			if($_GET['rabban']=='true')echo 'Here 7.5';

						mysql_query("INSERT INTO `ig_thumbs` SET `dnow` = '0',`shortcode` = '$shortcode', `igusername` = '$username', `added` = '$now',`added_on_instagram` = '$added_on_instagram',`sent_email` = '1'");

			}

			else

			{

			if($_GET['rabban']=='true')echo 'Here 8';

						$findifpostexistsinfo = mysql_fetch_array($findifpostexistsq);
						mysql_query("UPDATE `ig_thumbs` SET `dnow` = '0',`added_on_instagram` = '$added_on_instagram', `igusername` = '$username' WHERE `id` = '{$findifpostexistsinfo['id']}' LIMIT 1");

			}

			$q = mysql_query("UPDATE `downloadthumbsurl` SET `dnow` = '0' WHERE `id` = '{$findFile['id']}' LIMIT 1");
	

		}

	

if($_GET['rabban']=='true')	echo '<hr>'.$get;




?>