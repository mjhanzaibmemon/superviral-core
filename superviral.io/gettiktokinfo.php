<?php

require 'db.php';

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';


global $ordersession;
global $now;
global $tikAccessToken;
global $username;
global $datatype;
$tikAccessToken = $tikioidAccessToken; // from db.php
$ordersession = addslashes($_COOKIE['ordersession']);

$now = time();

// error_reporting(E_ERROR | E_PARSE);


$PROXY_HOST = "104.233.52.252";
$PROXY_PORT = "3199";

$username = strtolower(addslashes($_POST['username']));
$ordersession_id = strtolower(addslashes($_POST['ordersession_id']));
$datatype = addslashes($_GET['datatype']);
if(addslashes($_GET['username'])){
    $username = addslashes($_GET['username']);
}

if(addslashes($_GET['oredrsession'])){
    $ordersession = addslashes($_GET['oredrsession']);
}

$username = str_replace('@', '', $username);
$username = str_replace(' ','',$username);
$username = str_replace('?utm_medium=copy_link','',$username);
$username = str_replace('?r=nametag','',$username);
$username = str_replace('https://www.','',$username);
$username = str_replace('?hl=en.','',$username);


$usernameerror = array('Error' => 'No Username');
if(empty($username)){
	sendCloudwatchData('Superviral', 'error-post-unavailable', 'GetTiktokPost', 'error-post-unavailable-function', 1);

    die(json_encode($usernameerror));
}


// dp

if($datatype == 'dp'){

    $dpimgname = md5($ordersession . $username);
		
    $finddpfile = mysql_query("SELECT `dp` FROM `tt_dp` WHERE `dp` = '$dpimgname' AND `ttusername` = '$username' AND dnow = 0 order by id desc LIMIT 1");

    if (mysql_num_rows($finddpfile) == 0) { //no thumbnailfound in database

	sendCloudwatchData('Superviral', 'supernova-tiktok-api-getdp', 'GetTiktokPost', 'supernova-tiktok-api-getdp-function', 1);
    
    $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/profile?username=' . $username;

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $tikoidSocialScrapeKey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $resp = json_decode($get);

    // echo '<pre>';
    //dp save
    $profile = $resp -> data -> user -> avatar_thumb -> url_list[0]; 
    Dp($profile, $dpimgname);
    }
die;
}

$res = mysql_query("INSERT INTO `tt_api_stats` SET 
                                                `ttusername` = '$username', 
                                                `added` = '$now', 
                                                `ordersession` = '$ordersession_id', 
                                                `source` = 'supernova', 
                                                `type` = 'thumbs' ");
$lastApiStatsId = mysql_insert_id();		
	
$checknow = time();
$checknow2 = time() - 60;
$starttime = microtime(true);
$checkrecentttapitstat = mysql_query("SELECT * FROM `tt_api_stats` WHERE `count` > '2' AND `ttusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");


if(mysql_num_rows($checkrecentttapitstat)==0){

// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_getpost_userid' AND `brand` = 'sv' LIMIT 1");
sendCloudwatchData('Superviral', 'supernova-tiktok-api-getpost_userid', 'GetTiktokInfo', 'supernova-tiktok-api-getpost_userid-function', 1);

$url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/userId?username=' . $username;

//ATTEMPT TODO IT OUR WAY
$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $tikoidSocialScrapeKey"));
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_TIMEOUT, 20);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$get = curl_exec($curl);
$resp = $get;

//echo gettype($get), "\n";

$get = json_decode($get);

curl_close($curl);

$endtime = microtime(true);
$loadtime = $endtime - $starttime;

if ($get && $get->status != 'error') {
  
} else {
    $res = mysql_query("UPDATE `tt_api_stats`
											SET 
											`count` = '0', 
											`loadtime` = '$loadtime',
                                            response = '$resp'
											WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");
   
    $posts[] = "No Post";
    die;
}


$secUID = $get->data->uid;


    if ($secUID) {

        
        // $userData = $get -> data -> stats;
        // $following = $userData->followingCount;
        // $followers = $userData->followerCount;
        // $likes = $userData->heart;
        // $profile = $get->userInfo->user->avatarThumb;
        $response = tikAPI($secUID, $username);

        if($response['Posts'][0]== 'No Post') {
         
           $res = mysql_query("UPDATE `tt_api_stats`
											SET 
											`count` = '0', 
											`loadtime` = '$loadtime'
											WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");
            $posts[] = "No Post";
            $ReturnData["Posts"] = $posts;
            $data = json_encode($ReturnData);

            echo $data;
        
            die;
     
        }else{

          $res =  mysql_query("UPDATE `tt_api_stats`
            SET 
            `count` = '12', 
            `loadtime` = '$loadtime',
             response = '$resp'
            WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1; ");
        }
        // $response["Image"] = $profile;

        // $response["Followers"] = $followers;

        // $response["Following"] = $following;

        // $response["Likes"] = $likes;

        $data = json_encode($response);

        echo $data;
    
        die;
    }
    else {

       $res = mysql_query("UPDATE `tt_api_stats`
        SET 
        `count` = '0', 
        `loadtime` = '$loadtime'
        WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

    $posts[] = "No Post";
    $ReturnData["Posts"] = $posts;
    $data = json_encode($ReturnData);

    echo $data;
    die;
    }

}else{

    sendCloudwatchData('Superviral', 'tt-api-stats-success', 'GetTiktokInfo', 'tt-api-stats-success-function', 1);

    $Query = "SELECT id, shortcode, thumb_url FROM tt_thumbs WHERE ttusername = '" . $username . "'
    ORDER BY added_on_tiktok DESC LIMIT 12";
    $runQuery = mysql_query($Query);

    if(mysql_num_rows($runQuery) > 0){
      
        $ReturnData = [];

        $posts = [];
    
        $postsLink = [];
        while($data = mysql_fetch_array($runQuery)){
            $shortcode = $data['shortcode'];
            
            //CHECK IF WE ALREADY SAVED THIS THUMB
            $filePath = $data['thumb_url'];
            $postLink = "https://www.tiktok.com/@$username/video/$shortcode";
            $postsLink[] = $postLink;
            $posts[] = $filePath;
            
        }

        
        $ReturnData["User"] = 1;

        $ReturnData["Posts"] = $posts;

        $ReturnData["awsPaths"] = $posts;

        $ReturnData["PostsLink"] = $postsLink;
      
        $data = json_encode($ReturnData);

        echo $data;
        die;
    }
}

function tikAPI($uid, $username){

    sendCloudwatchData('Superviral', 'supernova-tiktok-api-getpost-posts', 'GetTiktokInfo', 'supernova-tiktok-api-getpost-posts-function', 1);
	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_getpost_posts' AND `brand` = 'sv' LIMIT 1");

    global $tikoidSocialScrapeKey;
    global $now;
    global $ordersession;
    $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/feed?userId=' . $uid;

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $tikoidSocialScrapeKey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
 
 
    $get = json_decode($get);

    curl_close($curl);

    $userPost = $get->data->aweme_list;
    $postThumbLinks = $userPost;

    if(empty($postThumbLinks)){
        $postThumbLinks = [];
    }
    $ReturnData = [];

    $posts = [];

    $postsLink = [];
    $countImage = 0;

    if (count($postThumbLinks) > 0) {

        $postTime = time();
        foreach ($postThumbLinks as $node) {

            if($countImage > 11) continue;

            $postSrc = $node->video->ai_dynamic_cover->url_list[0];
            $postID = $node->aweme_id;
            $postLink = "https://www.tiktok.com/@$username/video/$postID";
            $postTime = $postTime - 10;
            $postsLink[] = $postLink;
            $code = $postID;
            $newimgname = md5('tikoidrb' . $code);

            $like_count = $node->statistics->digg_count;
            $comment_count = $node->statistics->comment_count;

            $awsPath = "https://cdn.superviral.io/tt-thumbs/$newimgname.jpg";
            $awsPaths[] = $awsPath;

            $findpostfile = mysql_query("SELECT `shortcode`,thumb_url FROM `tt_thumbs` WHERE `shortcode` = '$code' LIMIT 1");
            $foundPost = mysql_fetch_array($findpostfile);
           
            if (mysql_num_rows($findpostfile) == 0) {
    
                $downloadedPath = '/download-thumb/'.$code.'?username='.$username;
                
                $posts[] = $downloadedPath;
                mysql_query("INSERT INTO `tt_thumbs` SET `comment_count` = '$comment_count' , `like_count` = '$like_count', `added_on_tiktok` = '$postTime', `shortcode` = '$code',thumb_url = '$postSrc', `ttusername` = '$username', `added` = '$now', media_type='video'");
    
            } else {

                $downloadedPath = $foundPost['thumb_url'];
                $posts[] = $downloadedPath;
                $downloadnowornot = 'DONT Download';
                mysql_query("UPDATE `tt_thumbs` SET `comment_count` = '$comment_count', `like_count` = '$like_count', `added_on_tiktok` = '$postTime',`ttusername` = '$username' WHERE `shortcode` = '$code' LIMIT 1");
    
    
            }
            $countImage ++;
        }

        $ReturnData["User"] = 1;

        $ReturnData["Posts"] = $posts;

        $ReturnData["awsPaths"] = $awsPaths;

        $ReturnData["PostsLink"] = $postsLink;
       
        return $ReturnData;
    }else{

        $posts[] = "No Post";
        $ReturnData["Posts"] = $posts;
        return $ReturnData;
    } 
}

function Dp($profile, $dpimgname){

            global $ordersession;
            global $username;
            global $amazons3key;
            global $amazons3password;
            global $datatype;
		
			$s3 = new S3($amazons3key, $amazons3password);

		
			$dp = $profile;

			$randnum = rand(0, 3);

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $dp);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
			curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($curl, CURLOPT_ENCODING, '');


			$get = curl_exec($curl);

            if(empty($get)){
				sendCloudwatchData('Superviral', 'tt-dp-load', 'OrderReview', 'order-review-tt-dp-failure-function', 1);
			}

			curl_close($curl);

			$putobject = S3::putObject($get, 'cdn.superviral.io', 'tt-dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
            if($putobject){
                mysql_query("INSERT INTO `tt_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `ttusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");

				sendCloudwatchData('Superviral', 's3-image-upload-success', 'GetTiktokPost', 's3-image-upload-success-function', 1);
			}else{
				sendCloudwatchData('Superviral', 's3-image-upload-failure', 'GetTiktokPost', 's3-image-upload-failure-function', 1);

			}
		
            if ($datatype == 'dp') {
                $finddpfile = mysql_query("SELECT * FROM `tt_dp` WHERE `ttusername` = '$username' ORDER BY `id` DESC LIMIT 1");
                $fetchdpfile = mysql_fetch_array($finddpfile);
        
        
                echo '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/tt-dp/' . $fetchdpfile['dp'] . '.jpg">';
                die;
            }


}