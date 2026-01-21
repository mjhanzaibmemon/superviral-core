<?php

$webhookbypass = 1;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core.php'; // lambda function
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$s3 = new S3($amazons3key, $amazons3password);


$shortcode = addslashes($_GET['shortcode']);
$username = addslashes($_GET['username']);
$now = time();


$query = mysql_query("SELECT `thumb_url`,`added_on_instagram` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' ORDER BY `id` DESC LIMIT 1");

$findFile = mysql_fetch_array($query);

$file = $findFile["thumb_url"];


$added_on_instagram = $findFile['added_on_instagram'];

if ($file == "" || $file == null) {

	$query = mysql_query("SELECT `thumb_url`,`added_on_instagram` FROM `downloadthumbsurl` WHERE `short_code` = '$shortcode' ORDER BY `id` DESC LIMIT 1");

	$findFile = mysql_fetch_array($query);

	$file = $findFile["thumb_url"];

	$added_on_instagram = $findFile['added_on_instagram'];
}

$newimgname = md5('superviralrb' . $shortcode);
// header('Content-Type: image/jpeg');

$datatosend = [
	'type' => 'download_thumbs', // for what purpose requesting
	'file' => $file
];

$callBack = connectToLambda($serv . '-download-thumbs-lambda', $datatosend); // $serv coming from db.php

if (empty($callBack['body'])) {

	$callBack = connectToLambda($serv . '-download-thumbs-lambda', $datatosend); // $serv coming from db.php
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer(base64_decode($callBack['body']));
$image = base64_decode($callBack['body']);


header("Content-Type: $mimeType");
echo $image;

if (empty($callBack['body'])) die('no response: ' . $file);

if ($_GET['rabban'] == 'true') echo 'Here 2';

if (!empty($image)) {

	// echo 'https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg';
	$putobject = S3::putObject($image, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);

	if ($_GET['rabban'] == 'true') {


		echo '<hr>' . $shortcode . '<hr>';

		$putobject = S3::putObject($image, 'cdn.superviral.io', 'thumbs/.jpg', S3::ACL_PUBLIC_READ);

		if ($_GET['rabban'] == 'true') echo 'Here 3.' . $newimgname . '<br>';
	}
}

// echo 'cdn.superviral.io/thumbs/' . $newimgname . '.jpg/';

if ($_GET['rabban'] == 'true') echo 'Here 4';

if (!empty($image)) {

	if ($_GET['rabban'] == 'true') echo 'Here 7';


	$findifpostexistsq = mysql_query("SELECT `id`,`shortcode`,`igusername` FROM `ig_thumbs` WHERE  `shortcode` = '$shortcode' LIMIT 1");

	if (mysql_num_rows($findifpostexistsq) == '0') {

		if ($_GET['rabban'] == 'true') echo 'Here 7.5';

		mysql_query("INSERT INTO `ig_thumbs` SET `dnow` = '0',`shortcode` = '$shortcode', `igusername` = '$username', `added` = '$now',`added_on_instagram` = '$added_on_instagram',`sent_email` = '1'");
	} else {

		if ($_GET['rabban'] == 'true') echo 'Here 8';

		$findifpostexistsinfo = mysql_fetch_array($findifpostexistsq);
		mysql_query("UPDATE `ig_thumbs` SET `dnow` = '0',`added_on_instagram` = '$added_on_instagram', `igusername` = '$username' WHERE `id` = '{$findifpostexistsinfo['id']}' LIMIT 1");
	}

	$q = mysql_query("UPDATE `downloadthumbsurl` SET `dnow` = '0' WHERE `id` = '{$findFile['id']}' LIMIT 1");
}

if ($_GET['rabban'] == 'true')	echo '<hr>' . $image;
