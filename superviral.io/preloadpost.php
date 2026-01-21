<?php
include 'db.php';
header('Access-Control-Allow-Origin: https://superviral.io');

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$s3 = new S3($amazons3key, $amazons3password);
$now = time();
$sixhoursago = $now - 43200;


$username = strtolower(addslashes($_POST['username']));
$ordersession = addslashes($_POST['ordersession']);
$ordersession_id = addslashes($_POST['ordersession_id']);
$datatype = addslashes($_POST['datatype']);
$videosonly = addslashes($_POST['videosonly']);
$packagetype = addslashes($_POST['packagetype']);
if (($datatype !== 'thumbs') && ($datatype !== 'dp')) {
    echo 'Incorrect Error';
}

if ($videosonly == '1') {
    $videosonly = '1';
}

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
	`source` = 'rapidapi',
	`type` = '$datatype'

	");

	$lastApiStatsId = mysql_insert_id();		
}

$totalresults = 0;

$checknow = time();
$checknow2 = time() - 40000; // Around half a day

$starttime = microtime(true);

$checkrecentigapitstat = mysql_query("SELECT * FROM `ig_api_stats` WHERE `count` > '2' AND `igusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");


if (mysql_num_rows($checkrecentigapitstat) == 1) {

    sendCloudwatchData('Superviral', 'ig-api-stats-success', 'PreloadPost', 'ig-api-stats-success-function', 1);

    $alreadydone = array('Already done' => 'Max limit reached for this');
    echo (json_encode($alreadydone));
}

$source = 'rapidapi';
$url = 'https://flashapi1.p.rapidapi.com/ig/posts_username/?user=' . $username . '&nocors=false';

//ATTEMPT TODO IT OUR WAY
$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost", "x-rapidapi-key: $rapidapikey"));
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_ENCODING, '');
curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

$get = curl_exec($curl);

$get = json_decode($get);
curl_close($curl);

$arrays = $get->items;
$users = $get->user;
// print_r($users) . '<br>';  
// print_r($arrays);  die;
$isprivate = $users->is_private;
$userId = $users->pk;

if (empty($is_private)) {
    $isprivate = 'Public';
} else {
    $isprivate = 'Private';
}

$follower_count = $users->follower_count;
$following_count = $users->following_count;
$media_count = $users->media_count;

if (is_numeric($userId)) {

    $account_status = $isprivate;

    sendCloudwatchData('Superviral', 'ig-user-id-success', 'PreloadPost', 'ig-user-id-success-function', 1);

    $fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE `ig_username` = '$username' LIMIT 1");

    if (mysql_num_rows($fetchuseridq) == '1') {

        mysql_query("UPDATE `searchbyusername`

        SET
        `ig_id` = '$userId',
        `is_private` = '$isprivate',
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



    $useridfound = array('Success' => 'User ID found');
    echo json_encode($useridfound);
} else {

    sendCloudwatchData('Superviral', 'ig-user-id-failure', 'PreloadPost', 'ig-user-id-failure-function', 1);
    $account_status = 'User Not Found';


    $useriderror = array('Error' => 'No User ID found');
    if (empty($userId)) echo(json_encode($useriderror));
}

if (isset($arrays)) { //this means we've successfully requested and received $get, and the account is on public

    foreach ($arrays as $thumbnail) {

        $totalresults++;

        $isvideo = $thumbnail->media_type;

        if (($videosonly == '1') && ($isvideo == '2')) {
            continue;
        }

        $thumbnailurl = $thumbnail->image_versions2->candidates[1];
        $thumbnailurl = $thumbnailurl->url;
        $shortcode = $thumbnail->code;
        $postTime = $thumbnail->taken_at;

        if ($isvideo == 2) {
            $mediaType = "video";
            // $views = $views;	
        } else {
            $mediaType = "image";
            // $views = 0;	
        }

        if (empty($shortcode)) {
            continue;
        }

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
        echo $shortcode . '<br>';
        echo $thumbnailurl . '<br>';
        echo $postTime . '<br>';
        echo $isvideo . '<br>';
        echo $downloadnowornot . '<br>';
        echo '<hr>';
    }
} else {

    $notfound = 1;
    mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:655', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");
    if ($_GET['rabban'] == 'true') {

        echo 'Not found';
        die;
    }
}

$endtime = microtime(true);

$loadtime = $endtime - $starttime;

echo 'Load time: ' . $loadtime . '<hr>';

if ($datatype == 'thumbs') {
    mysql_query("UPDATE `ig_api_stats`
    SET 
    `count` = '$totalresults', 
    `loadtime` = '$loadtime', `account_status` = '$account_status'
    WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");
}


function saveThumb($thumbnailurl, $shortcode, $postTime, $igusername, $mediaType)
{
    $added = time();
    $sql = "INSERT INTO `ig_thumbs` SET  `thumb_url` = '$thumbnailurl', `igusername` = '$igusername',`shortcode` ='$shortcode',`added_on_instagram` = '$postTime',`dnow` = '1', media_type = '$mediaType', `added` = '$added'";
    mysql_query($sql);
}
