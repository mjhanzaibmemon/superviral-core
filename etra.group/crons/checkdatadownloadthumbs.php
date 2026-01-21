<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require('../sm-db.php');
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$s3 = new S3($amazons3key, $amazons3password);


$curl = curl_init();

/*$q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1' AND `checkusers_now_id` IS NOT NULL 

 LIMIT 50");
*/

$nowq = time();
$threedaysago = time() - (86400 * 2);

// superviral/FB code
	$q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1' AND `sent_email` = '0' AND `added_on_instagram` BETWEEN '$threedaysago' AND '$nowq' ORDER BY `id` ASC LIMIT 30");

	if (mysql_num_rows($q) == 0) $q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1' AND `added_on_instagram` BETWEEN '$threedaysago' AND '$nowq' ORDER BY `id` ASC LIMIT 70");

	if (mysql_num_rows($q) == 0) $q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1' ORDER BY `id` ASC LIMIT 70");


	//$totalcountq = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1' AND `checkusers_now_id` IS NOT NULL ");
	$totalcountq = mysql_query("SELECT * FROM `ig_thumbs` WHERE `dnow` = '1'");

	$howmuchleft = mysql_num_rows($totalcountq);

	echo '<h1>How much left: ' . $howmuchleft . '</h1><br>';

	// if($howmuchleft==0)die('Done');

	

	if (mysql_num_rows($q) == 0) echo 'All Done';


	while ($info = mysql_fetch_array($q)) {


	$error = 0;

	$shortcode = $info['shortcode'];


	echo '<b>' . $geturl['id'] . '. ' . $shortcode . '</b><br>' . $geturl['thumb_url'] . '<br>';
	echo 'Check user: ' . $info['checkusers_now_id'] . '<br>';


	curl_setopt($curl, CURLOPT_URL, $info['thumb_url']);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($curl, CURLOPT_TIMEOUT, 5);

	curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');

	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	curl_setopt($curl, CURLOPT_ENCODING, '');

	$get = curl_exec($curl);






	$newimgname = md5('superviralrb' . $shortcode);

	if (strpos($get, 'signature expired') !== false) {
		echo '<font color="red">Delete this!</font><hr>';
		$error = 1;
		//MYSQL DELETE FROM IG_THUMBS AND DOWNLOADTHUMBSURL
		$deleteq2 = mysql_query("DELETE FROM `ig_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");

		// if(!$deleteq)die('Delete Query 1 Gone Wrong');
		// if(!$deleteq2)die('Delete Query 1 Gone Wrong');
	}

	if (strpos($get, 'signature expired') !== false) continue;





	if ((!empty($get)) && ($error == 0)) {

		$putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
		echo '<font color="green">Download this!</font><br>';

		if ($putobject) {
			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-success', 'CheckDataDownloadThumbs', 's3-image-upload-success-function', 1);
			$updateq2 = mysql_query("UPDATE `ig_thumbs` SET `dnow` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");

			if ($updateq2) echo 'Updated database: <a href="https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg</a></font><br>';
		}else{
			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-failure', 'CheckDataDownloadThumbs', 's3-image-upload-failure-function', 1);
		}
	} else {

		$deleteq2 = mysql_query("DELETE FROM `ig_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");


		echo '<font color="red">Don\'t download -  cant find any get!!</font><br>';
	}


	echo '<hr>';

	unset($get);
	}

	curl_close($curl);


	$q = mysql_query("SELECT * FROM `ig_dp` WHERE `dnow` = '1' ORDER BY `id` ASC LIMIT 70");

	while ($info = mysql_fetch_array($q)) {

		$dpimgname = md5(time() . $info['igusername']);
		$error = 0;

		echo '<b>' . $geturl['id'] . '. ' . $shortcode . '</b><br>' . $geturl['thumb_url'] . '<br>';

		$dp = $info['dp_url'];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $dp);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($curl, CURLOPT_ENCODING, '');


		$get = curl_exec($curl);


		if (strpos($get, 'signature expired') !== false) {
			echo '<font color="red">Delete this!</font><hr>';
			$error = 1;
			//MYSQL DELETE FROM IG_THUMBS AND DOWNLOADTHUMBSURL
			$deleteq2 = mysql_query("DELETE FROM `ig_dp` WHERE `id` = '{$info['id']}' LIMIT 1");

			// if(!$deleteq2)die('Delete Query 1 Gone Wrong');
		}

		if (strpos($get, 'signature expired') !== false) continue;





		if ((!empty($get)) && ($error == 0)) {

			$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);

			if ($putobject) {
				mysql_query("UPDATE `ig_dp` SET `dp` = '$dpimgname', `dnow` = '0' WHERE id = " . $info['id']);
				sendCloudwatchData('EtraGroupCrons', 's3-image-upload-success', 'CheckDataDownloadThumbs', 's3-image-upload-failure-success', 1);

			}else{
				sendCloudwatchData('EtraGroupCrons', 's3-image-upload-failure', 'CheckDataDownloadThumbs', 's3-image-upload-failure-function', 1);

			}
		}
		unset($get);
	}

	curl_close($curl);


// end superviral/FB code

// tikoid code
$q = mysql_query("SELECT * FROM `tt_thumbs` WHERE `dnow` = '1' AND `sent_email` = '0' AND `added_on_tiktok` BETWEEN '$threedaysago' AND '$nowq' ORDER BY `id` ASC LIMIT 70");

if (mysql_num_rows($q) == 0) $q = mysql_query("SELECT * FROM `tt_thumbs` WHERE `dnow` = '1' AND `added_on_tiktok` BETWEEN '$threedaysago' AND '$nowq' ORDER BY `id` ASC LIMIT 70");

if (mysql_num_rows($q) == 0) $q = mysql_query("SELECT * FROM `tt_thumbs` WHERE `dnow` = '1' ORDER BY `id` ASC LIMIT 70");


//$totalcountq = mysql_query("SELECT * FROM `tt_thumbs` WHERE `dnow` = '1' AND `checkusers_now_id` IS NOT NULL ");
$totalcountq = mysql_query("SELECT * FROM `tt_thumbs` WHERE `dnow` = '1'");

$howmuchleft = mysql_num_rows($totalcountq);

echo '<h1>How much left: ' . $howmuchleft . '</h1><br>';

// if($howmuchleft==0)die('Done');

if (mysql_num_rows($q) == 0) echo 'All Done';


while ($info = mysql_fetch_array($q)) {


	$error = 0;

	$shortcode = $info['shortcode'];


	echo '<b>' . $geturl['id'] . '. ' . $shortcode . '</b><br>' . $geturl['thumb_url'] . '<br>';
	echo 'Check user: ' . $info['checkusers_now_id'] . '<br>';


	curl_setopt($curl, CURLOPT_URL, $info['thumb_url']);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($curl, CURLOPT_TIMEOUT, 5);

	curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');

	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	curl_setopt($curl, CURLOPT_ENCODING, '');

	$get = curl_exec($curl);






	$newimgname = md5('superviralrb' . $shortcode);

	if (strpos($get, 'signature expired') !== false) {
		echo '<font color="red">Delete this!</font><hr>';
		$error = 1;
		//MYSQL DELETE FROM IG_THUMBS AND DOWNLOADTHUMBSURL
		$deleteq2 = mysql_query("DELETE FROM `tt_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");

		// if(!$deleteq)die('Delete Query 1 Gone Wrong');
		// if(!$deleteq2)die('Delete Query 1 Gone Wrong');
	}

	if (strpos($get, 'signature expired') !== false) continue;





	if ((!empty($get)) && ($error == 0)) {

		$putobject = S3::putObject($get, 'cdn.superviral.io', 'tt-thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
		echo '<font color="green">Download this!</font><br>';

		if ($putobject) {
			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-success', 'CheckDataDownloadThumbs', 's3-image-upload-success-function', 1);

			$updateq2 = mysql_query("UPDATE `tt_thumbs` SET `dnow` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");

			if ($updateq2) echo 'Updated database: <a href="https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg</a></font><br>';
		}else{
			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-failure', 'CheckDataDownloadThumbs', 's3-image-upload-failure-function', 1);

		}
	} else {

		$deleteq2 = mysql_query("DELETE FROM `tt_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");


		echo '<font color="red">Don\'t download -  cant find any get!!</font><br>';
	}


	echo '<hr>';

	unset($get);
}

curl_close($curl);


$q = mysql_query("SELECT * FROM `tt_dp` WHERE `dnow` = '1' ORDER BY `id` ASC LIMIT 70");

while ($info = mysql_fetch_array($q)) {

	$dpimgname = md5(time() . $info['igusername']);
	$error = 0;

	echo '<b>' . $geturl['id'] . '. ' . $shortcode . '</b><br>' . $geturl['thumb_url'] . '<br>';

	$dp = $info['dp_url'];

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $dp);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt($curl, CURLOPT_ENCODING, '');


	$get = curl_exec($curl);


	if (strpos($get, 'signature expired') !== false) {
		echo '<font color="red">Delete this!</font><hr>';
		$error = 1;
		//MYSQL DELETE FROM IG_THUMBS AND DOWNLOADTHUMBSURL
		$deleteq2 = mysql_query("DELETE FROM `tt_dp` WHERE `id` = '{$info['id']}' LIMIT 1");

		// if(!$deleteq2)die('Delete Query 1 Gone Wrong');
	}

	if (strpos($get, 'signature expired') !== false) continue;





	if ((!empty($get)) && ($error == 0)) {

		$putobject = S3::putObject($get, 'cdn.superviral.io', 'tt-dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);

		if ($putobject) {
			mysql_query("UPDATE `tt_dp` SET `dp` = '$dpimgname', `dnow` = '0' WHERE id = " . $info['id']);
		}
	}
	unset($get);
}

curl_close($curl);


// end tikoid code



	//if($howmuchleft!=='0')echo '<meta http-equiv="refresh" content="0">';
