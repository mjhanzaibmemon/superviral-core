<?php

require __DIR__ . '/../vendor/autoload.php';


// Get the input from the Lambda event payload
//$input = json_decode(file_get_contents('php://input'), true);


// $key = $input['key'];
// $username = $input['username'];
// $short_code = $input['short_code'];
// $type = $input['type'];
// $user_id = $input['user_id'];

//$data_response = call_api($key, $username, $short_code, $type, $user_id);
//echo $data_response;
// die;

function call_api($key='', $username, $short_code ,$type, $user_id, $source = 'ig')
{
    $key = getenv('post_api_key') ?? 'Not set';
    $tiktok_key = getenv('tiktok_post_api_key') ?? 'Not set';

    if($source == 'ig')
    $url = get_url($type, $username, $short_code, $user_id);
    else
    $url = get_tiktok_url($type, $username, $short_code, $user_id);

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $key"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    // $result_arr = array('order_response' => $order_response, 'order_status' => $order_status);
    return $get;
}



function get_url($type, $username, $short_code, $user_id){

    switch ($type){
        case 'follower_count':
        case 'dp':
        case 'is_private':
            $url = 'https://i.supernova-493.workers.dev/api/v3/profile?username=' . $username;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_lambda_getprofile' AND `brand` = 'sv' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-lambda-getprofile', 'SocialMediaApi', 'supernova-api-lambda-getprofile-function', 1);

        break;
        case 'post':
            $url = 'https://i.supernova-493.workers.dev/api/v3/post?short_code=' . $short_code;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_lambda_getpost' AND `brand` = 'sv' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-lambda-getpost', 'SocialMediaApi', 'supernova-api-lambda-getpost-function', 1);

        break;
        case 'stories':
            $url = 'https://i.supernova-493.workers.dev/api/v3/stories?username=' . $username . '&userId=' . $user_id;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_lambda_getstories' AND `brand` = 'sv' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-lambda-getstories', 'SocialMediaApi', 'supernova-api-lambda-getstories-function', 1);

        break;    
        case 'posts':
            $url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $user_id;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_lambda_getposts' AND `brand` = 'sv' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-lambda-getposts', 'SocialMediaApi', 'supernova-api-lambda-getposts-function', 1);

        break;        
    }

    return $url;
}

function get_tiktok_url($type, $username, $short_code, $user_id){

    switch ($type){
        case 'follower_count':
        case 'dp':
        case 'is_private':    
            $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/profile?username=' . $username;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_lambda_getprofile' AND `brand` = 'to' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-tiktok-lambda-getprofile', 'SocialMediaApi', 'supernova-api-tiktok-lambda-getprofile-function', 1);

        break;
        case 'post':

            $url = rtrim($short_code, '/');
            $lastSegment = substr($url, strrpos($url, '/') + 1);
            $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/video?videoId=' . $lastSegment;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_lambda_getpost' AND `brand` = 'to' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-tiktok-lambda-getpost', 'SocialMediaApi', 'supernova-api-tiktok-lambda-getpost-function', 1);

        break;
        case 'posts':
            $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/feed?userId=' . $user_id;

        	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_lambda_getposts' AND `brand` = 'to' LIMIT 1");
            sendCloudwatchData('AWSLambda', 'supernova-api-tiktok-lambda-getposts', 'SocialMediaApi', 'supernova-api-tiktok-lambda-getposts-function', 1);

        break;        
    }

    return $url;
}