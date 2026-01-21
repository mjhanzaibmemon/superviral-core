<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/s3/S3.php';

$amazons3key = getenv('amazons3key');
$amazons3password = getenv('amazons3password');
$tiktok_key = getenv('tiktok_post_api_key') ?? 'Not set';

$s3 = new S3($amazons3key, $amazons3password);

header('Access-Control-Allow-Origin: https://superviral.io');


$now = time();

// error_reporting(E_ERROR | E_PARSE);
$PROXY_USER = "rabban_far-mn40m";
$PROXY_PASS = "Sym3vXkH7u";

$PROXY_HOST = "104.233.52.252";
$PROXY_PORT = "3199";

$username = strtolower(addslashes($_POST['username']));
$ordersession_id = strtolower(addslashes($_POST['ordersession_id']));
$ordersession = addslashes($_POST['ordersession']);
$username = str_replace('@', '', $username);
$username = str_replace(' ','',$username);
$username = str_replace('https://instagram.com/','',$username);
$username = str_replace('instagram.com/','',$username);
$username = str_replace('?utm_medium=copy_link','',$username);
$username = str_replace('?r=nametag','',$username);
$username = str_replace('https://www.','',$username);
$username = str_replace('?hl=en.','',$username);


$usernameerror = array('Error' => 'No Username');
if(empty($username))die(json_encode($usernameerror));

$res = mysql_query("INSERT INTO `tt_api_stats` SET 
                                                `ttusername` = '$username', 
                                                `added` = '$now', 
                                                `ordersession` = '$ordersession_id', 
                                                `source` = 'supernova', 
                                                `type` = 'thumbs' ");
$lastApiStatsId = mysql_insert_id();	

$starttime = microtime(true);
 $fetchuseridq = mysql_query("SELECT * FROM `tt_searchbyusername` WHERE `tt_username` = '$username' LIMIT 1");

 if(mysql_num_rows($fetchuseridq)=='1'){

 	$fetchuserinfoq = mysql_fetch_array($fetchuseridq);
 	$userId = $fetchuserinfoq['tt_id'];
 	$is_private = $fetchuserinfoq['is_private'];
     mysql_query("UPDATE `tt_searchbyusername` SET   `tt_username` = '$username' WHERE `tt_id` = '$userId'");
 	//echo 'Found: '.$userId.'<hr>';
 } else{

    
    $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/userId?username='.$username;


    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tiktok_key" ));
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    writeCloudWatchLog('preloadpost-tiktok-lambda', $username .' :Socialmedia Api response : '. $get);
    $resp = $get;

    $get = json_decode($get,true);

    curl_close($curl);

    $userId = $get['data']['uid'];

    $endtime = microtime(true);
    $loadtime = $endtime - $starttime;
    if(!empty($userId)){

        $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/profile?username='.$username . '&userId='.$userId;


        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tiktok_key" ));
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
        $get = curl_exec($curl);

        writeCloudWatchLog('preloadpost-lambda', $username .' :Socialmedia Profile Api response : '. $get);
        $resp = $get;
    
        $get = json_decode($get,true);

        $endtime = microtime(true);
        $loadtime = $endtime - $starttime;
     
        // echo '<pre>';
        // print_r($get);die;

        $users = $get['data'];
        $is_private = $users['user']['profile_tab_type'];

	    if ($is_private == '0') {
	    	$is_private = 'Public';
	    } else {
	    	$is_private = 'Private';
        }
	    $follower_count = $users['user']['follower_count'];
	    $following_count = $users['user']['following_count'];
	    $media_count = $users['user']['aweme_count'];

        $fetchuseridq = mysql_query("SELECT * FROM `tt_searchbyusername` WHERE `tt_username` = '$username' LIMIT 1");

		if(mysql_num_rows($fetchuseridq)=='1'){

            mysql_query("UPDATE `tt_searchbyusername`

            SET
            `tt_id` = '$userId',
            `is_private` = '$is_private',
			`followers` = '$follower_count',
			`following` = '$following_count',
			`media_count` = '$media_count' WHERE `tt_username` = '$username' LIMIT 1
            ");

		}else{

            mysql_query("INSERT INTO `tt_searchbyusername`
            
            SET
            `tt_username` = '$username',
            `tt_id` = '$userId',
            `is_private` = '$is_private',
			`followers` = '$follower_count',
			`following` = '$following_count',
			`media_count` = '$media_count'
            ");
		}
       

		
    }else{
        $res = mysql_query("UPDATE `tt_api_stats`
        SET 
        `count` = '0', 
        `loadtime` = '$loadtime',
        response = '$resp'
        WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1; ");

        $posts[] = "No Post";

        $useridfound = $useriderror = array('Error' => 'No User ID found');
		echo json_encode($useridfound);
        die;
    }
   
}

$url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/feed?userId='.$userId;


$curl = curl_init(); 
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tiktok_key" ));
curl_setopt($curl, CURLOPT_URL, $url); 
curl_setopt($curl, CURLOPT_TIMEOUT, 20);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$get = curl_exec($curl);
$resp = $get;

$get = json_decode($get,true);

$userPost = $get['data']['aweme_list'];

$endtime = microtime(true);
$loadtime = $endtime - $starttime;

$userPost = $userPost ?? [];

$totalresults = count($userPost);

$profile = $userPost[0]['author']['avatar_300x300']['url_list'][0]; 

writeCloudWatchLog('preloadpost-lambda', $username .' : Dp : '. $profile);

$useridfound = array('Success' => 'User ID found', 'is_private' => $is_private, 'dp' => $profile);
echo json_encode($useridfound);

if ($get && !empty($userPost)) {
    $res = mysql_query("UPDATE `tt_api_stats`
    SET 
    `count` = '$totalresults', 
    `loadtime` = '$loadtime'
    WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

} else {
   
    $res = mysql_query("UPDATE `tt_api_stats`
    SET 
    `count` = '0', 
     response = '$resp',
    `loadtime` = '$loadtime'
    WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

    $posts[] = "No Post";
    die;
}

curl_close($curl);


$postThumbLinks = $userPost;
if(empty($postThumbLinks)){
    $postThumbLinks = [];
}

$ReturnData = [];

$posts = [];

$postsLink = [];

writeCloudWatchLog('preloadpost-lambda', $username .' : Array data : '. $postThumbLinks);

if (count($postThumbLinks) > 0) {
    $postTime = time();

    

     // tt dp

     if(!empty($profile) && $profile != null){
        $dpimgname = md5($ordersession.$username);
        mysql_query("INSERT INTO `tt_dp` SET 
        `order_session` = '$ordersession',
        `dp` = '$dpimgname',
        `ttusername` = '$username', 
        `dp_url` = '$profile',
        `dnow` = 1");

      }

    foreach ($postThumbLinks as $node) {

        $postSrc = $node['video']['ai_dynamic_cover']['url_list'][0];
        $postID = $node['aweme_id'];
        $code = $postID;
        $newimgname = md5('tikoidrb' . $code);
        $postTime = $node['create_time'];
        $findpostfile = mysql_query("SELECT `shortcode` FROM `tt_thumbs` WHERE `shortcode` = '$code' LIMIT 1");
        if (mysql_num_rows($findpostfile) == 0) {

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $postSrc);
    
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    
            curl_setopt($curl, CURLOPT_TIMEOUT, 8);
    
        //	curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
            
            //curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
    
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    
            curl_setopt($curl, CURLOPT_ENCODING, '');
    
            $thumbnail = curl_exec($curl);
    
            curl_close($curl);
         

            $downloadnowornot = 'Download now';
            $putobject = S3::putObject($thumbnail, 'cdn.superviral.io', 'tt-thumbs/'.$newimgname.'.jpg', S3::ACL_PUBLIC_READ);
            $awsPath = "https://cdn.superviral.io/tt-thumbs/$newimgname.jpg";
          
            if($putobject){
                mysql_query("INSERT INTO `tt_thumbs` SET `added_on_tiktok` = '$postTime', `shortcode` = '$code', thumb_url = '$awsPath', `ttusername` = '$username', `added` = '$now', media_type='video'");

            }else{

            }
            

        } else {

            $downloadnowornot = 'DONT Download';
            mysql_query("UPDATE `tt_thumbs` SET `ttusername` = '$username' WHERE `shortcode` = '$code' LIMIT 1");


        }

    }

}
