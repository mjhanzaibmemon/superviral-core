<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/s3/S3.php';

$amazons3key = getenv('amazons3key');
$amazons3password = getenv('amazons3password');
$key = getenv('post_api_key') ?? 'Not set';

$s3 = new S3($amazons3key, $amazons3password);

header('Access-Control-Allow-Origin: https://superviral.io');

$now = time();
$sixhoursago = $now - 43200;




$username = strtolower(addslashes($_POST['username']));
$ordersession = addslashes($_POST['ordersession']);
$ordersession_id = addslashes($_POST['ordersession_id']);
$datatype = addslashes($_POST['datatype']);
$videosonly = addslashes($_POST['videosonly']);
$packagetype = addslashes($_POST['packagetype']);
if (($datatype !== 'thumbs') && ($datatype !== 'dp')) {
    $useridfound = array('Error' => 'Incorrect error');
    echo json_encode($useridfound);
}

if ($videosonly == '1') $videosonly = '1';

$username = str_replace('@', '', $username);

$username = str_replace('@', '', $username);
$username = str_replace(' ', '', $username);
$username = str_replace('https://instagram.com/', '', $username);
$username = str_replace('instagram.com/', '', $username);
$username = str_replace('?utm_medium=copy_link', '', $username);
$username = str_replace('?r=nametag', '', $username);
$username = str_replace('https://www.', '', $username);
$username = str_replace('?hl=en.', '', $username);

$usernameerror = array('Error' => 'No Username');
if (empty($username)) die(json_encode($usernameerror));

if ($datatype == 'thumbs') {

    $query = mysql_query("INSERT INTO `ig_api_stats`

	SET 
	`igusername` = '$username', 
	`added` = '$now', 
	`ordersession` = '$ordersession_id',
	`source` = 'supernova',
	`type` = '$datatype'

	");

    $lastApiStatsId = mysql_insert_id();
}


$totalresults = 0;


//////////////////////////////////////////////////



$checknow = time();
$checknow2 = time() - 120;


$checkrecentigapitstat = mysql_query("SELECT * FROM `ig_api_stats` WHERE `count` > '2' AND `igusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");


if (mysql_num_rows($checkrecentigapitstat) == 1) {


    $alreadydone = array('Error' => 'Please wait 2 minutes before processing the same username again');
    // die(json_encode($alreadydone));
}


    $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $username;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $key"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    writeCloudWatchLog('preloadpost-lambda', $username .' :Socialmedia Api response : '. $get);
    $resp = $get;

    $get = json_decode($get, true);

    $users = $get['data'];
    $userId = $users['user']['pk_id'];
    $is_private = $users['user']['is_private'];

    if (empty($is_private)) {
        $is_private = 'Public';
    } else {
        $is_private = 'Private';
    }

    $follower_count = $users['user']['follower_count'];
    $following_count = $users['user']['following_count'];
    $media_count = $users['user']['media_count'];

    if (empty($userId)) {

        $url = 'https://scraper-api.smartproxy.com/v2/scrape'; //'https://scrape.smartproxy.com/v1/tasks';

        $curl = curl_init();

        $data = array(
            "target" => "universal",
            "parse" => "False",
            "url" => $igrequestidurl
        );

        $data = json_encode($data);

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            "Authorization: Basic VTAwMDAwODY1OTY6NCU1KkRSOXJ4M21r",
            "Content-Type: application/json"
        ));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $getuserid = curl_exec($curl);
        writeCloudWatchLog('preloadpost-lambda', $username .' :Smartproxy Api response : '. $getuserid);
        curl_close($curl);

        $getuserid = json_decode($getuserid);
        $users = $getuserid->users[0];
        $userId = $users->user->pk;
        $is_private = $users->user->is_private;

        if (empty($is_private)) {
            $is_private = 'Public';
        } else {
            $is_private = 'Private';
        }

        $follower_count = $users->user->follower_count;
        $following_count = $users->user->following_count;
        $media_count = $users->user->media_count;
    }

if (is_numeric($userId)) {

    $account_status = $is_private;

    $fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE `ig_username` = '$username' LIMIT 1");

    if (mysql_num_rows($fetchuseridq) == '1') {

        mysql_query("UPDATE `searchbyusername`

			SET
			`ig_id` = '$userId',
			`is_private` = '$is_private',
			`followers` = '$follower_count',
			`following` = '$following_count',
			`media_count` = '$media_count' WHERE `ig_username` = '$username'  LIMIT 1
			");
    } else {

        mysql_query("INSERT INTO `searchbyusername`

			SET
			`ig_username` = '$username',
			`ig_id` = '$userId',
			`is_private` = '$is_private',
			`followers` = '$follower_count',
			`following` = '$following_count',
			`media_count` = '$media_count'
			");
    }

} else {

    $account_status = 'User Not Found';


    $useriderror = array('Error' => 'No User ID found');
    if (empty($userId)) die(json_encode($useriderror));
}

$useriderror = array('Error' => 'No User ID found');
if (empty($userId)) {
    die(json_encode($useriderror));
}

$starttime = microtime(true);

$url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId;

$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $key"));
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_TIMEOUT, 20);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$get = curl_exec($curl);
$get = json_decode($get);


$endtime = microtime(true);

$loadtime = $endtime - $starttime;

// echo 'Load time: ' . $loadtime . '<hr>';

$arrays = $get->data->response->items;

$dp = $get->data->response->user->profile_pic_url;
writeCloudWatchLog('preloadpost-lambda', $username .' :Dp : '. $dp);

writeCloudWatchLog('preloadpost-lambda', $username .' :Array data : '. $arrays);

  // ig dp

  if (!empty($dp) && $dp != null) {
    $dpimgname = md5($ordersession . $username);

    $randnum = rand(0, 3);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $dp);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    // curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
    // curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_ENCODING, '');


    $get = curl_exec($curl);


    $base64 = base64_encode($get);
    $mime_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    $img = "data:$mime_type;base64,$base64";
    
    curl_close($curl);

    // $putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
    // if ($putobject) {
    //     mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");

    // } else {
    // }
}


$useridfound = array('Success' => 'User ID found', 'is_private' => $is_private, 'dp' => $img);
echo json_encode($useridfound);

if (isset($arrays)) { //this means we've successfully requested and received $get, and the account is on public


    foreach ($arrays as $thumbnail) {

        $totalresults++;

        $isvideo = $thumbnail->media_type;

        if (($videosonly == '1') && ($isvideo == '0')) continue;

        $thumbnailurl = $thumbnail->thumbnail_url;
        if ($isvideo == 8) {
            $thumbnailurl = $thumbnail->image_versions[1]->url;
        }
        // $thumbnailurl = $thumbnailurl[0]->src;
        $shortcode = $thumbnail->code;
        $postTime = $thumbnail->taken_at_ts;

        if ($isvideo == 2) {
            $mediaType = "video";
            // $views = $views;	
        } else {
            $mediaType = "image";
            // $views = 0;	
        }

        if (empty($shortcode)) continue;

        $newimgname = md5('superviralrb' . $shortcode);


        //CHECK IF WE ALREADY SAVED THIS THUMB

        $findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
        if (mysql_num_rows($findpostfile) == 0) {

            $downloadnowornot = 'Download now';
            saveThumb($thumbnailurl, $shortcode, $postTime, $username, $mediaType); // New save thumb function


        } else {

            $downloadnowornot = 'DONT Download';
            mysql_query("UPDATE `ig_thumbs` SET `added_on_instagram` = '$postTime',`igusername` = '$username' WHERE `shortcode` = '$shortcode' LIMIT 1");
        }

    }


    if (!empty($ordersession)) {


        $dpimgname = md5($ordersession . $username);
    }


} else {


    $notfound = 1;


	if(!empty($lastApiStatsId)){mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:655', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");}


    if ($_GET['rabban'] == 'true') {

        $useridfound = array('Error' => 'No ID found');
        echo json_encode($useridfound);
        die;
    }
}





function saveThumb($thumbnailurl, $shortcode, $postTime, $igusername, $mediaType)
{
    $added = time();
    $sql = "INSERT INTO `ig_thumbs` SET  `thumb_url` = '$thumbnailurl', `igusername` = '$igusername',`shortcode` ='$shortcode',`added_on_instagram` = '$postTime',`dnow` = '1', media_type = '$mediaType', `added` = '$added'";
    mysql_query($sql);
}



mysql_query("UPDATE `ig_api_stats`
											SET 
											`count` = '$totalresults', 
											`loadtime` = '$loadtime', `account_status` = '$account_status'
											WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");


//IF NOT FOUND THEN SHOW A CERTAIN MESSAGE FOR THE ORDER SELECT PAGE TO REDIRECT
if (($notfound == 1) && ($datatype == 'thumbs')) {

    $usernameerror = array('Error' => 'No Username');
    if (empty($username)) die(json_encode($usernameerror));


    mysql_query("UPDATE `ig_api_stats`
											SET 
											`count` = '0', 
											`loadtime` = '$loadtime', `account_status` = '$account_status'
											WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");
}
