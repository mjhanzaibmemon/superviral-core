<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
set_time_limit(0);
ini_set('memory_limit', '-1'); // Increase to 256 MB

global $backblaze_application_key;
$backblaze_application_key = $backblaze_application_key;
global $backblaze_key_id ;
$backblaze_key_id = $backblaze_key_id;
global $backblaze_key_name ;
$backblaze_key_name = $backblaze_key_name;



// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';
$s3 = new S3($amazons3key2, $amazons3password2);

echo '<pre>';

$checkStmt = mysql_query("SELECT * FROM ext_instagram_profiles where done = 0");

while ($checkData = mysql_fetch_array($checkStmt)) {

    $username = $checkData['username'];
    $id = $checkData['id'];
    echo "Processing user: $username<br>";
    $result = fetchPosts($username);

    if ($result['msg'] == 'Success') {
        echo "✅ $username posts fetched successfully.<br>";
        $arrays = $result['arrays'];
        $isprivate = $result['isprivate'];

        // print_r($arrays);
        echo "<br>";

        if ($isprivate) {
            echo "⚠️ $username profile is private.<br>";
            continue;
        }

        $totalresults = 0;
        $count = 1;
        $uploaded_profile_url= '';
        foreach ($arrays as $thumbnail) {

            $date = time();

            // if ($totalresults > 11) continue;

            $totalresults++;

            // $isvideo = $thumbnail->media_type;
            $profile_pic_url = $thumbnail->user->profile_pic_url;
            $shortcode = $thumbnail->code;

            $checkifExists = mysql_query("SELECT * FROM `ext_socialmedia_posts` WHERE `shortcode` = '" . addslashes($shortcode) . "' limit 1");

            if (mysql_num_rows($checkifExists) > 0) {
                echo "❌ Post with shortcode $shortcode already exists.<br>";
                continue;
            }

            if (empty($shortcode)) continue;

            $key = "profile/".md5('superviralrb'.time()) . ".jpg";

            if(!empty($uploaded_profile_url)) {
               $uploaded_profile_url = $uploaded_profile_url;
            } else {
               $backblaze_profile_response = backblazeUpload($key, $profile_pic_url);
               $uploaded_profile_url = $backblaze_profile_response;
            }
            $thumbnailurl = $thumbnail->thumbnail_url;

            $caption = $thumbnail->caption->text ?? '';
            // if ($isvideo == 8) {
            //     $thumbnailurl = $thumbnail->image_versions[1]->url;
            // }

            $video_url = $thumbnail->video_versions[0]->url;

            if (empty($thumbnailurl)) {
                $thumbnailurl = $thumbnail->image_versions2->candidates[0]->url;
            }
            // $thumbnailurl = $thumbnailurl[0]->src;
           

            $key = "thumb/".md5('superviralrb'.$shortcode) . ".jpg";

            $backblaze_response = backblazeUpload($key, $thumbnailurl);
            // die;

            if(!$backblaze_response) {
                $uploaded_image_url = '';
            } else {
               $uploaded_image_url = $backblaze_response;
            }
            $postTime = $thumbnail->taken_at_ts;

            if (empty($postTime)) {
                $postTime = $thumbnail->taken_at;
            }
            $like_count = $thumbnail->like_count;

            $comment_count = $thumbnail->comment_count;

            $video_length = $thumbnail->video_duration ?? 0;

            $location = $thumbnail->location->name ?? null;


            if(!empty($video_url)) {
                
                $mediaType = 'video';

               
                $filename = basename(md5($shortcode.time()) . '.mp4');
                $key = 'video/'. $filename;

                $backblaze_video_response = backblazeUpload($key, $video_url);

                if(!$backblaze_video_response) {
                    echo "❌ Failed to upload video for shortcode $shortcode.<br>";
                    continue;
                } else {
                    $uploaded_video_url = $backblaze_video_response;
                }
                // echo $videoData;die;
              
                $insert_posts = mysql_query("INSERT INTO `ext_socialmedia_posts` 
                (`username`, `shortcode`, `thumb_url`, `video_url`, `like_count`, `comment_count`, 
                `media_type`, `added`, `profile_pic_url`, `caption`, `posted_at`, `video_length`,  `location`)
                    VALUES ('" . addslashes($username) . "', '" . addslashes($shortcode) . "', '" . addslashes($uploaded_image_url) . "', 
                    '" . addslashes($uploaded_video_url) . "', '" . addslashes($like_count) . "', '" . addslashes($comment_count) . "', 
                    '" . addslashes($mediaType) . "', '". $date ."', '" . addslashes($uploaded_profile_url) . "', '" . addslashes($caption) . "', '" . addslashes($postTime) . "', '" . addslashes($video_length) . "', '" . addslashes($location) . "')");
            }

            unset($thumbnailurl);
            unset($video_url);
            unset($shortcode);
            unset($like_count);
            unset($comment_count);
            unset($caption);
            unset($mediaType);
            unset($uploaded_image_url);
            unset($uploaded_profile_url);
            unset($backblaze_response);
            unset($backblaze_profile_response);
            unset($backblaze_video_response);
            unset($video_length);
            unset($filename);
            unset($postTime);
            unset($location);
            
            echo "-------------------------------- $count<br>";
            $count++;
        }

        mysql_query("UPDATE ext_instagram_profiles SET done = 1 WHERE id = $id");
    } else {
        echo "❌ $username posts not found or private.<br>";
    }
}

function fetchPosts($username) {
    global $superviralsocialscrapekey;

    $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $username;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['accept: application/json', "X-API-KEY: $superviralsocialscrapekey"]);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $resp = json_decode($response, true);

    $userId = $resp['data']['user']['pk_id'] ?? null;
    curl_close($curl);

    if (!empty($userId)) {
        $allItems = [];
        $isprivate = false;
        $nextPageId = null;
        do {
            $postUrl = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId;
            $postUrl .= $nextPageId ? '&page_id=' . $nextPageId : '';
            
            // echo $postUrl . "<br>";

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['accept: application/json', "X-API-KEY: $superviralsocialscrapekey"]);
            curl_setopt($curl, CURLOPT_URL, $postUrl);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $postData = curl_exec($curl);
            $postData = json_decode($postData);

            if (!empty($postData->data->response->items)) {
                $allItems = array_merge($allItems, $postData->data->response->items);
            }

            $isprivate = $postData->data->response->user->is_private ?? $isprivate;
            $nextPageId = $postData->data->next_page_id ?? null;
            // $nextPageId = null;

            // print_r($postData->data);

            echo $nextPageId . ":Next page id for $username.<br>";
            echo count($allItems) . " posts fetched so far for $username.<br>";
            curl_close($curl);

            
        } while (!empty($nextPageId)); // Continue until there's no more page IDs

        $countPost = count($allItems);
        echo "Total posts fetched for $username: $countPost<br>";
        return [
            'arrays' => $allItems,
            'isprivate' => $isprivate,
            'msg' => 'Success',
        ];
    }

    return [
        'msg' => 'Not found',
    ];
}


function backblazeUpload($filename, $thumbnailurl)
{
    global $backblaze_key_id, $backblaze_application_key, $backblaze_key_name;
    $keyId = $backblaze_key_id;
    $applicationKey = $backblaze_application_key;
    $bucketName = $backblaze_key_name;
    $cdn = 'https://foodiecdntest.b-cdn.net/';
    $uploadFileName = $filename;
    $bucketId = 'a3fa0a0b7e5014d898840417';
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $thumbnailurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 8);
	curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
    
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($curl, CURLOPT_ENCODING, '');
	$get = curl_exec($curl);
	curl_close($curl);
    if(empty($get)){
        
        		$curl = curl_init();
        
        		curl_setopt($curl, CURLOPT_URL, $thumbnailurl);
        
        		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        		curl_setopt($curl, CURLOPT_TIMEOUT, 8);
        
        		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
        
        		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        
        		curl_setopt($curl, CURLOPT_ENCODING, '');
        
        		$get = curl_exec($curl);
        
        		curl_close($curl);
        
        
    }


    $blobData = $get;
    if (!$blobData) return false;;

    // Step 1: Authorize
    $auth = base64_encode("$keyId:$applicationKey");

    // echo $keyId . " - " . $applicationKey . "<br>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.backblazeb2.com/b2api/v2/b2_authorize_account");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $auth"
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) return false;
    curl_close($ch);

    $authData = json_decode($response, true);

    // print_r($authData);
    $apiUrl = $authData['apiUrl'];
    $authToken = $authData['authorizationToken'];

    // echo "✅ Authorized successfully.<br>";
    // echo "API URL: $apiUrl<br>";
    // echo "Auth Token: $authToken<br>";
    // echo "Acc Id: {$authData['accountId']}<br>";

    // Step 2: Get upload URL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$apiUrl/b2api/v2/b2_get_upload_url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "bucketId" => $bucketId
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authToken",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) return false;
    curl_close($ch);
    // echo "Upload URL Response: $response<br>";

    $uploadData = json_decode($response, true);
    $uploadUrl = $uploadData['uploadUrl'];
    $uploadAuthToken = $uploadData['authorizationToken'];

    // Step 3: Upload Blob
    $fileSize = strlen($blobData);
    $fileSha1 = sha1($blobData);
    $fileNameB2 = str_replace('+', '%20', rawurlencode($uploadFileName));
    // echo $fileNameB2 . "<br>";die;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $blobData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $uploadAuthToken",
        "X-Bz-File-Name: $fileNameB2",
        "Content-Type: b2/x-auto",
        "Content-Length: $fileSize",
        "X-Bz-Content-Sha1: $fileSha1"
    ]);

    $response = curl_exec($ch);
    // echo "Upload Response: $response<br>";
    // die;

    if (curl_errno($ch)) die('Upload Error: ' . curl_error($ch));
    curl_close($ch);

    $result = json_decode($response, true);
    // print_r($result);
    if(!empty($result['fileId'])) {
        echo "✅ File uploaded successfully: {$result['fileId']}<br>";
        return $cdn.$uploadFileName;
    } else {
        echo "❌ Upload failed: " . $result['error'] . "<br>";

        return false;
    }
}