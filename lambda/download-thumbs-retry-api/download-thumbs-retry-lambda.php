<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/s3/S3.php';

$amazons3key = getenv('amazons3key');
$amazons3password = getenv('amazons3password');
$rapidapikey = getenv('rapidapikey');
$rapidapihost = getenv('rapidapihost');
$superviralsocialscrapekey = getenv('superviralsocialscrapekey');

global $base_url, $bucket;

$s3 = new S3($amazons3key, $amazons3password);

$username = $_POST['username']; 
$shortcode = $_POST['missedImage'];
$orderSession = $_POST['ordersession'];

writeCloudWatchLog('download-thumbs-retry-lambda', 'Shortcode :'. $shortcode .  ': Username :' . $username . ' OS: ' . $orderSession);
// $bucket = "etra-test-japqc";
// $base_url= "https://etra-test-japqc.s3.us-east-2.amazonaws.com/";
$bucket = "cdn.superviral.io"; // for live
$base_url = "https://cdn.superviral.io"; // for live
if (empty($shortcode)) {
    echo json_encode(['error' => 'No shortcode provided']);
    exit();
}

$try_alternate_methods = false;

$query = mysql_query("SELECT thumb_url
                              FROM ig_thumbs 
                              WHERE igusername = '$username' AND shortcode = '$shortcode'
                              ORDER BY added_on_instagram DESC LIMIT 1");


if(mysql_num_rows($query) > 0){
    $row = mysql_fetch_array($query);

    writeCloudWatchLog('download-thumbs-retry-lambda', 'Records for shortcode '. $shortcode .' : ' . json_encode($row));
    $thumb_url = $row['thumb_url'];
    $newimgname = md5('superviralrb' . $shortcode);

    if(!empty($thumb_url)){

        $imageData = getBlob($thumb_url);

        writeCloudWatchLog('download-thumbs-retry-lambda', 'Blob for shortcode '. $shortcode .' : ' . $imageData);
        //header('Content-Type: image/jpeg');
        //echo $imageData;die;

        $putobject = S3::putObject($imageData, $bucket, '/thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
        if($putobject){
            sendCloudwatchData('AWSLambda', 's3-image-upload-success', 'DownloadThumbsRetry', 's3-image-upload-success-function', 1);
            $url = "$base_url/thumbs/{$newimgname}.jpg";            
            $final = true;
            writeCloudWatchLog('download-thumbs-retry-lambda', 'Uploaded to AWS'. $shortcode);
        }else{
            sendCloudwatchData('AWSLambda', 's3-image-upload-failed', 'DownloadThumbsRetry', 's3-image-upload-failure-function', 1);
            $final = false;
            writeCloudWatchLog('download-thumbs-retry-lambda', 'Failed upload to AWS'. $shortcode);
        }

        if($final){
            header("Content-Type: image/png");
            echo $imageData;
            exit();
            echo json_encode([
                'src' => $url,
            ]);
            exit();
        }else{
            echo json_encode([
                //'src' => '',
            ]);
            exit();
        }

    }else{
        $try_alternate_methods = true;
    }
}

//if(!$try_alternate_methods){  echo json_encode(['src' => '']);exit(); }

// Function to fetch from RapidAPI FlashAPI
function fetchFromRapidAPI($username, $shortcode, $rapidapihost, $rapidapikey) {
    
    global $base_url, $bucket;
   
    $newimgname = md5('superviralrb' . $shortcode);
    $url = 'https://flashapi1.p.rapidapi.com/ig/post_info/?shortcode='. $shortcode .'&nocors=false';
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "x-rapidapi-host: " . $rapidapihost,
        "x-rapidapi-key: " . $rapidapikey
    ));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $response = curl_exec($curl);
    writeCloudWatchLog('download-thumbs-retry-lambda', 'RapidApi response '. $response);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $resp = json_decode($response);
    //$username = $resp->items[0]->user->username;
    $img_url = $resp->items[0]->image_versions2->candidates[0]->url;
    writeCloudWatchLog('download-thumbs-retry-lambda', 'RapidApi image url '. $img_url);
    // $media_type = $resp->items[0]->media_type;
    // $video_url = $resp->items[0]->video_versions[2]->url; //video

   if ($img_url) {
        updateThumbForMissing($username, $shortcode, $img_url);
        $imageData = getBlob($img_url);
        writeCloudWatchLog('download-thumbs-retry-lambda', 'RapidApi blob '. $imageData);
        $path = parse_url($img_url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
        //$key = "thumbs/{$shortcode}.{$ext}";

        $putobject = S3::putObject($imageData, $bucket, '/thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
        if($putobject){
            sendCloudwatchData('AWSLambda', 's3-image-upload-success', 'DownloadThumbsRetry', 's3-image-upload-success-function', 1);
            return $imageData;
            $url = "$base_url/thumbs/{$newimgname}.jpg";
            return array('src'=>$url);
        }else{
            sendCloudwatchData('AWSLambda', 's3-image-upload-failed', 'DownloadThumbsRetry', 's3-image-upload-failure-function', 1);
            return array();
        }
       
    }else {
        return array();
    }

}

// Function to fetch from Supernova API
function fetchFromSupernova($username, $shortcode, $superviralsocialscrapekey) {

    global $base_url, $bucket;
    $newimgname = md5('superviralrb' . $shortcode);
    $url = 'https://i.supernova-493.workers.dev/api/v3/post?shortcode=' . $shortcode;
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'accept: application/json',
        "X-API-KEY: " . $superviralsocialscrapekey
    ));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curl);
    writeCloudWatchLog('download-thumbs-retry-lambda', 'Supernova response '. $response);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $resp = json_decode($response, true);
    if (isset($resp['thumbnail_url'])) {
        $img_url = $resp['thumbnail_url'];
    } elseif (isset($resp['image_versions'][1]['url'])) {
        $img_url = $resp['image_versions'][1]['url'];
    }else{
        $img_url = '';
    }

    writeCloudWatchLog('download-thumbs-retry-lambda', 'Supernova image url '. $img_url);
    if ($img_url) {
        updateThumbForMissing($username, $shortcode, $img_url);
        $imageData = getBlob($img_url);
        $path = parse_url($img_url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
        //$key = "thumbs/{$shortcode}.{$ext}";
        writeCloudWatchLog('download-thumbs-retry-lambda', 'Supernova blob '. $imageData);
        $putobject = S3::putObject($imageData, $bucket, '/thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
        if($putobject){
            sendCloudwatchData('AWSLambda', 's3-image-upload-success', 'DownloadThumbsRetry', 's3-image-upload-success-function', 1);
            return $imageData;
            $url = "$base_url/thumbs/{$newimgname}.jpg";
            return array('src'=>$url);
        }else{
            sendCloudwatchData('AWSLambda', 's3-image-upload-failed', 'DownloadThumbsRetry', 's3-image-upload-failure-function', 1);
            return array();
        }
    }else {
        return array();
    }

}

function updateThumbForMissing($username, $shortcode, $thumbnailurl)
{
	$added = time();
    $sql = "UPDATE ig_thumbs SET thumb_url = '".$thumbnailurl."' WHERE shortcode = '".$shortcode."' AND igusername = '".$username."'";
	mysql_query($sql);
}

function getBlob($thumb_url){

     $curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $thumb_url);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_TIMEOUT, 8);

		//curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

		$imageData = curl_exec($curl);

		curl_close($curl);
        return $imageData;
}

if($try_alternate_methods){

    // Try RapidAPI first
    $result = fetchFromRapidAPI($username, $shortcode, $rapidapihost, $rapidapikey);

    // If RapidAPI fails, try Supernova
    if (!$result) {
        $result = fetchFromSupernova($username, $shortcode, $superviralsocialscrapekey);
    }

    // Return result
    if ($result) {
        header("Content-Type: image/png");
        echo $result;
        exit();
    } else {
        sendCloudwatchData('AWSLambda', 'curl-image-load-failure', 'DownloadThumbsRetry', 'curl-image-load-failure-function', 1);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch post data from both APIs',
            'shortcode' => $shortcode,
            'src' => ''
        ]);
    }

}
exit();
?>
