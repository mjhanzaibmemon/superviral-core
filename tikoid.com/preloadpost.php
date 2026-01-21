<?php

require $_SERVER["DOCUMENT_ROOT"] . '/db.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';

$s3 = new S3($amazons3key, $amazons3password);

global $ordersession;
global $now;

$ordersession = addslashes($_COOKIE['ordersession']);

$now = time();

// error_reporting(E_ERROR | E_PARSE);
$PROXY_USER = "rabban_far-mn40m";
$PROXY_PASS = "Sym3vXkH7u";

$PROXY_HOST = "104.233.52.252";
$PROXY_PORT = "3199";

$username = strtolower(addslashes($_POST['username']));
$ordersession_id = strtolower(addslashes($_POST['ordersession_id']));

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

$starttime = microtime(true);
$fetchuseridq = mysql_query("SELECT * FROM `tt_searchbyusername` WHERE `tt_username` = '$username' LIMIT 1");

if(mysql_num_rows($fetchuseridq)=='1'){

	$fetchuserinfoq = mysql_fetch_array($fetchuseridq);
	$userId = $fetchuserinfoq['tt_id'];
    mysql_query("UPDATE `tt_searchbyusername` SET   `tt_username` = '$username' WHERE `tt_id` = '$userId'");
	//echo 'Found: '.$userId.'<hr>';

} else{

    sendCloudwatchData('Tikoid', 'supernova-tikok-api-userid', 'PreloadPost', 'supernova-tikok-api-userid-function', 1);
    
    $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/userId?username='.$username;


    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikoidSocialScrapeKey" ));
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $resp = $get;

    $get = json_decode($get,true);

    curl_close($curl);


    $userId = $get['data']['uid'];
    $endtime = microtime(true);
    $loadtime = $endtime - $starttime;
    
    if(!empty($userId)){
         mysql_query("INSERT INTO `tt_searchbyusername`

		SET
		`tt_username` = '$username',
		`tt_id` = '$userId'
		");

		$useridfound = array('Success' => 'User ID found');
		echo json_encode($useridfound);
    }else{
        $res = mysql_query("INSERT INTO `tt_api_stats` SET `ttusername` = '$username', `count` = '0', 	
        `added` = '$now', 	`ordersession` = '$ordersession_id', response = '$resp',  `loadtime` = '$loadtime',	
        `source` = 'socialscrape', `type` = 'thumbs' ");

        sendCloudwatchData('Tikoid', 'preloadpost-failure', 'PreloadPost', 'preloadpost-failure-function', 1); 
        $posts[] = "No Post";
        die;
    }
}

sendCloudwatchData('Tikoid', 'supernova-tikok-api-posts', 'PreloadPost', 'supernova-tikok-api-posts-function', 1);

$url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/feed?userId='.$userId;


$curl = curl_init(); 
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $tikoidSocialScrapeKey" ));
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

if ($get && !empty($userPost)) {

    $res = mysql_query("INSERT INTO `tt_api_stats` SET `ttusername` = '$username', `count` = '12', 	
    `added` = '$now', 	`ordersession` = '$ordersession_id',  `loadtime` = '$loadtime',	
    `source` = 'socialscrape', `type` = 'thumbs'  ");

    sendCloudwatchData('Tikoid', 'preloadpost-success', 'PreloadPost', 'preloadpost-success-function', 1);    
   
} else {
    $res = mysql_query("INSERT INTO `tt_api_stats` SET `ttusername` = '$username', `count` = '0', 	
    `added` = '$now', 	`ordersession` = '$ordersession_id', response = '$resp',  `loadtime` = '$loadtime',	
    `source` = 'socialscrape', `type` = 'thumbs' ");
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

if (count($postThumbLinks) > 0) {
    $postTime = time();
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

				sendCloudwatchData('Tikoid', 's3-image-upload-success', 'PreloadPost', 's3-image-upload-success-function', 1);
			}else{
				sendCloudwatchData('Tikoid', 's3-image-upload-failure', 'PreloadPost', 's3-image-upload-failure-function', 1);

			}

        } else {

            $downloadnowornot = 'DONT Download';
            mysql_query("UPDATE `tt_thumbs` SET `ttusername` = '$username' WHERE `shortcode` = '$code' LIMIT 1");


        }

    }

}

?>
