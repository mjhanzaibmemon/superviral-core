<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';



$type = addslashes($_POST['type']);



switch ($type) {

    case "submitAmazon":
        submitAmazon();
        break;
    case "submitUsername":
        submitUsername();
        break;
    case "submitUserId":
        submitUserId();
        break;
    case "submitResidentialIp":
        submitResidentialIp();
        break;
    case "submitUserIdSP":
        submitUserIdSP();
        break;
    case "submitWebCrawler":
        submitWebCrawler();
        break;
}

function isBinary($str) {
    return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
}

function submitAmazon()
{
    require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
    $url = addslashes($_POST['url']);
    global $amazons3key, $amazons3password;
    $s3 = new S3($amazons3key, $amazons3password);
    $newimgname = md5(time());

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    $get = curl_exec($curl);
    curl_close($curl);
    $putobject = false;
    if(isBinary($get)){
        $putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
    }
    if ($putobject) {
        $message = "success";
        $filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
        sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-success', 'PostGrabber', 's3-image-upload-success-function', 1);
    } else {
        $message = "Error, Please check url or try again";
        $filePath = "";
        sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-failure', 'PostGrabber', 's3-image-upload-failure-function', 1);
        
    }

    $dataArr = array('Message' => $message, 'path' => $filePath);

    echo json_encode($dataArr);

    die;
}



function submitUsername()
{
    global $superviralsocialscrapekey;
    $username = addslashes($_POST['username']);

    // $starttime = microtime(true);
    sendCloudwatchData('EtraGroupAdmin', 'supernova-api-post-grabber-getprofile', 'EmailSupportHandler', 'supernova-api-post-grabber-getprofile-function', 1);

    $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $username;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $resp = $get;

    $resp = json_decode($resp, true);
    $users = $resp['data'];
    $userId = $users['user']['pk_id'];

    curl_close($curl);

    if (!empty($userId)) {
        $message = "success";
    } else {
        $message = $resp['message'];
    }

    // $endtime = microtime(true);

    // $loadtime = $endtime - $starttime;


    $dataArr = array('Message' => $message, 'userId' => $userId);

    echo json_encode($dataArr);

    die;
}

function submitUserId()
{
    global $superviralsocialscrapekey;
    $userId = addslashes($_POST['userId']);

    // $starttime = microtime(true);
    sendCloudwatchData('EtraGroupAdmin', 'supernova-api-post-grabber-getposts', 'EmailSupportHandler', 'supernova-api-post-grabber-getposts-function', 1);


    $url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $get = json_decode($get);

    $arrays = $get->data->response->items;
    $i = 0;

    foreach ($arrays as $thumbnail) {

        $isvideo = $thumbnail->media_type;

		$thumbnailurl = $thumbnail->thumbnail_url;
		if($isvideo == 8){
			$thumbnailurl = $thumbnail->image_versions[1]->url;
		}
		// $thumbnailurl = $thumbnailurl[0]->src;
		$shortcode = $thumbnail->code;

        if ($isvideo == 2) {
			$mediaType = "video";
			// $views = $views;	
		} else {
			$mediaType = "image";
			// $views = 0;	
		}


        $dataAll[$i] = ['shortcode'=> $shortcode, 'url' => $thumbnailurl, 'media_type' => $mediaType];
        $i++;
    }
    $isprivate = $get->data->response->user->is_private;


    if (!empty($arrays)) {
        $message = "success";
        $count = count($arrays);
    } else {
        $message = $get['message'];
    }

    // $endtime = microtime(true);

    // $loadtime = $endtime - $starttime;


    $dataArr = array('Message' => $message, 'count' => $count, 'visible' => $isprivate, 'dataAll' => $dataAll);

    echo json_encode($dataArr);

    die;
}


function submitResidentialIp()
{
    require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
    $url = addslashes($_POST['url']);
    global $amazons3key, $amazons3password;
    $s3 = new S3($amazons3key, $amazons3password);
    $newimgname = md5(time());

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($curl, CURLOPT_TIMEOUT, 8);

    curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');

    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    curl_setopt($curl, CURLOPT_ENCODING, '');

    $get = curl_exec($curl);

    curl_close($curl);


    if (empty($get)) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 8);

        curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($curl, CURLOPT_ENCODING, '');

        $get = curl_exec($curl);

        curl_close($curl);
    }
    $putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/' . $newimgname . '.jpg', S3::ACL_PUBLIC_READ);
    if ($putobject) {
        $message = "success";
        $filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
        sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-success', 'PostGrabber', 's3-image-upload-success-function', 1);
      
    } else {
        $message = "Error, Try again";
        $filePath = "";
        sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-failed', 'PostGrabber', 's3-image-upload-failure-function', 1);
       
    }

    $dataArr = array('Message' => $message, 'path' => $filePath);

    echo json_encode($dataArr);

    die;
}


function submitUserIdSP() //smart proxy
{
    global $superviralsocialscrapekey;
    $userId = addslashes($_POST['userId']);

    // $starttime = microtime(true);
    sendCloudwatchData('EtraGroupAdmin', 'supernova-api-post-grabber-getposts', 'EmailSupportHandler', 'supernova-api-post-grabber-getposts-function', 1);


    $url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_ENCODING, '');

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $get = json_decode($get);

    $arrays = $get->data->response->items;
    $isprivate = $get->data->response->user->is_private;

    $i = 0;

    foreach ($arrays as $thumbnail) {

        $isvideo = $thumbnail->media_type;

		$thumbnailurl = $thumbnail->thumbnail_url;
		if($isvideo == 8){
			$thumbnailurl = $thumbnail->image_versions[1]->url;
		}
		// $thumbnailurl = $thumbnailurl[0]->src;
		$shortcode = $thumbnail->code;

        if ($isvideo == 2) {
			$mediaType = "video";
			// $views = $views;	
		} else {
			$mediaType = "image";
			// $views = 0;	
		}


        $dataAll[$i] = ['shortcode'=> $shortcode, 'url' => $thumbnailurl, 'media_type' => $mediaType];
        $i++;
    }
    $isprivate = $get->data->response->user->is_private;


    if (!empty($arrays)) {
        $message = "success";
        $count = count($arrays);
    } else {
        $message = $get['message'];
    }

    // $endtime = microtime(true);

    // $loadtime = $endtime - $starttime;


    $dataArr = array('Message' => $message, 'count' => $count, 'visible' => $isprivate ,'dataAll' => $dataAll);

    echo json_encode($dataArr);

    die;
}

function submitWebCrawler() 
{
    $username = addslashes($_POST['username']);
    $igrequestidurl = 'https://www.instagram.com/web/search/topsearch/?query='.$username;

    $url = 'https://scraper-api.smartproxy.com/v2/scrape'; //'https://scrape.smartproxy.com/v1/tasks';

    $curl = curl_init();

    $data = array(    
    "target"=> "universal",
    "parse"=> "False",
    "url"=> $igrequestidurl);
    
    $data = json_encode($data);
    
    curl_setopt($curl, CURLOPT_URL, $url);
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Accept: application/json' , 
        "Authorization: Basic VTAwMDAwODY1OTY6NCU1KkRSOXJ4M21r", 
        "Content-Type: application/json" 
        ));
    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    
    
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    $getuserid = curl_exec($curl);
    
    curl_close($curl);
    
    $getuserid = json_decode($getuserid);
    $users = $getuserid -> users[0];
    $userId = $users -> user -> pk;

    if (!empty($userId)) {
        $message = "success";
    
    } else {
        $message = "Error, Try again";
    }

    $dataArr = array('Message' => $message, 'userId' => $userId);

    echo json_encode($dataArr);

    die;
}
