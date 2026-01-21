<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/s3/S3.php';

$amazons3key = getenv('amazons3key');
$amazons3password = getenv('amazons3password');

$s3 = new S3($amazons3key, $amazons3password);
$now = time();

// use Aws\S3\S3Client;

$shortcode = $_GET['shortcode'];
$username = $_GET['username'];

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
    
writeCloudWatchLog('download-thumbs-lambda', 'Shortcode :'. $shortcode .  ': File: '. $file);

$newimgname = md5('superviralrb'.$shortcode);
$randnum = rand(0, 3);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $file);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 8);
//curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($curl, CURLOPT_ENCODING, '');
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
'Connection: keep-alive'
));

$get = curl_exec($curl);

writeCloudWatchLog('download-thumbs-lambda', 'Curl Response:'. $get);
curl_close($curl);

if(empty($get)){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $file);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 8);
    //curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl, CURLOPT_ENCODING, '');
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	'Connection: keep-alive'
	));
    
    $get = curl_exec($curl);

    writeCloudWatchLog('download-thumbs-lambda', 'Reattempt Curl Response:'. $get);
    curl_close($curl);
}

// echo 'https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg';
if (!empty($get)) {

    $putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
    // echo $putobject . 'adad';
    writeCloudWatchLog('download-thumbs-lambda', 'Uplaod to Aws:'. json_encode($putobject));
    if($putobject){
        sendCloudwatchData('AWSLambda', 's3-image-upload-success', 'DownloadThumbs', 's3-image-upload-success-function', 1);
    }else{
        sendCloudwatchData('AWSLambda', 's3-image-upload-failed', 'DownloadThumbs', 's3-image-upload-failure-function', 1);
    }
}else{
    sendCloudwatchData('AWSLambda', 'curl-image-load-failure', 'DownloadThumbs', 'curl-image-load-failure-function', 1);

}
if (!empty($get)) {
    $findifpostexistsq = mysql_query("SELECT `id`,`shortcode`,`igusername` FROM `ig_thumbs` WHERE  `shortcode` = '$shortcode' LIMIT 1");
    if(mysql_num_rows($findifpostexistsq)=='0'){
        mysql_query("INSERT INTO `ig_thumbs` SET `dnow` = '0',`shortcode` = '$shortcode', `igusername` = '$username', `added` = '$now',`added_on_instagram` = '$added_on_instagram',`sent_email` = '1'");
    }else
    {
        $findifpostexistsinfo = mysql_fetch_array($findifpostexistsq);
        mysql_query("UPDATE `ig_thumbs` SET `dnow` = '0',`added_on_instagram` = '$added_on_instagram', `igusername` = '$username' WHERE `id` = '{$findifpostexistsinfo['id']}' LIMIT 1");
    }
    $q = mysql_query("UPDATE `downloadthumbsurl` SET `dnow` = '0' WHERE `id` = '{$findFile['id']}' LIMIT 1");
}
header('Content-Type: image/jpeg');
echo $get;
die;
