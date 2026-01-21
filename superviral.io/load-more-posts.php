<?php

error_reporting(E_ERROR | E_PARSE);
$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.', '', $loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));

if (empty($loc)) {
    $loc = '';
}

if ($loc == 'superviral') {
    $loc = '';
}

if ($loc == 'www') {
    $loc = '';
}

if (!empty($loc)) {
    $loc = $loc . '.';
}

header('Access-Control-Allow-Origin: https://' . $loc . 'superviral.io');

$now = time();
$sixhoursago = $now - 43200;
/*

can specify on query string:

videsonly=1
ordersession=1 (this is to get the profile picture)

BY DEFAULT WE RETRIEVE THE DP

only option to get is the thumbs

//either dp or thumbs
//when thumbs is selected, download a DP regardless

 */

include 'db.php';

$username = strtolower(addslashes($_GET['username']));
$ordersession = addslashes($_GET['ordersession']);
$ordersession_id = addslashes($_GET['ordersession_id']);
$datatype = addslashes($_GET['datatype']);
$videosonly = addslashes($_GET['videosonly']);
$packagetype = addslashes($_GET['packagetype']);
$iteration = addslashes($_POST['iteration']);
$userId = addslashes($_POST['userId']);
$thirdItrData = addslashes($_POST['thirdItrData']);
$nextPageId = addslashes($_POST['nextPageId']);
$type = addslashes($_GET['type']);

if (($datatype !== 'thumbs') && ($datatype !== 'dp')) {
    echo 'Incorrect Error';
}

if ($videosonly == '1') {
    $videosonly = '1';
}

if (empty($username)) {
    die('1');
}

//either dp or thumbs
//when thumbs is selected, download a DP regardless

/*if(!empty($ordersession)){
//MAKE THIS LIVE WHEN WE'VE GONE LOCAL SERVER AGAIN

}*/

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

$rotatingips = array(
    '173.208.150.242:15002',
    '173.208.213.170:15002',
    '173.208.239.10:15002',
    '173.208.136.2:15002'
);

$source = 'socialscrape';

$totalresults = 0;
$starttime = microtime(true);
// new API //////////////////////////////////////////////////////////////////////////////////////////////////

// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_loadmorepost_posts' AND `brand` = 'sv' LIMIT 1");
sendCloudwatchData('Superviral', 'supernova-api-loadmorepost-posts', 'LoadMorePosts', 'supernova-api-loadmorepost-posts-function', 1);

$url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId . '&page_id=' . $nextPageId;

//ATTEMPT TODO IT OUR WAY
$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_TIMEOUT, 20);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$get = curl_exec($curl);

$get = json_decode($get);
$nextPageId = $get->data->next_page_id;
$arrays = $get->data->response->items;

$countPost = count($arrays);

if(empty($countPost)) $countPost = 0; 

curl_close($curl);

if ($countPost == 0) {

    // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_loadmorepost_posts' AND `brand` = 'sv' LIMIT 1");
    sendCloudwatchData('Superviral', 'supernova-api-loadmorepost-posts', 'LoadMorePosts', 'supernova-api-loadmorepost-posts-function', 1);

    $url = 'https://i.supernova-493.workers.dev/api/v3/posts?userId=' . $userId . '&page_id=' . $nextPageId;

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    $get = json_decode($get);
    $nextPageId = $get->data->next_page_id;
    $arrays = $get->data->response->items;
    $countPost = count($arrays);
    $source = 'socialscrape';
    curl_close($curl);
}
$api_type=  '';
if($countPost == 0){

    mysql_query("UPDATE `ig_api_stats`
									SET 
									`source` = 'rapidapi'
									WHERE id = '$lastApiStatsId' LIMIT 1;");
    
		$url = 'https://flashapi1.p.rapidapi.com/ig/posts_username/?user='. $username .'&end_cursor='. $nextPageId .'&nocors=false';

		//ATTEMPT TODO IT OUR WAY
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		$get = curl_exec($curl);

		$get = json_decode($get);


		$arrays = $get->items;
		// print_r($arrays);  die;
		$userId = $user->pk;
        $countPost = count($arrays);
        $nextPageId = $get->next_max_id;
        $source = 'ig-scraper-2022';
        $api_type=  'backup';

        curl_close($curl);
}

$endtime = microtime(true);

$loadtime = $endtime - $starttime;
$nextIterationCount = 0;
$secondIterationArr = [];
$thirdIterationArr = [];


if ($countPost > 0) { //this means we've successfully requested and received $get, and the account is on public
    $htm = "";

    sendCloudwatchData('Superviral', 'load-more-posts-success', 'LoadMorePosts', 'load-more-posts-success-function', 1);

    $secondItrKey = 0;
    $thirdItrKey = 0;

    $htmlData = "";
    $returnArr = [];
    foreach ($arrays as $thumbnail) {

        if ($iteration == "third"){
            if($totalresults > 11) continue;
        } 

        $totalresults++;
        // $isvideo = $thumbnail -> node -> is_video;
        $isvideo = $thumbnail->media_type;
        // $thumbnailurl = $thumbnail -> node -> thumbnail_resources;
        $thumbnailurl = $thumbnail->thumbnail_url;

        if (($videosonly == '1') && ($isvideo == '0')) {
            continue;
        }
        $insideCall = false;
        if ($thumbnailurl == null) {
            $thumbnailurl = $thumbnail->image_versions[1]->url;
            $insideCall = true;
        }

        if(empty($thumbnailurl)){
			$thumbnailurl = $thumbnail->image_versions2->candidates[2]->url;
		}

        $shortcode = $thumbnail->code;
		$postTime = $thumbnail->taken_at_ts;

        if(empty($postTime)){
			$postTime = $thumbnail->taken_at;
		}

		if ($isvideo == 2) {
			$mediaType = "video";
			// $views = $views;	
		} else {
			$mediaType = "image";
			// $views = 0;	
		}

        $like_count = $thumbnail->like_count;
        
        if (empty($shortcode)) continue;

        $newimgname = md5('superviralrb' . $shortcode);

        if ($_GET['rabban'] == 'true') {
            echo 'Image name: ' . $newimgname . '<br>';
        }

        if ($_GET['rabban'] == 'true') {
            echo 'Thumbnail URL: ' . $thumbnailurl . '<br>';
        }

        //CHECK IF WE ALREADY SAVED THIS THUMB
        $findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
        if (mysql_num_rows($findpostfile) == 0) {

            // downloadThumb($thumbnailurl, $rotatingips, $newimgname, $username, $shortcode, $now); // New download thumb function
            saveThumb($thumbnailurl, $shortcode, $postTime, $username, $mediaType, $like_count); // New save thumb function

            if ($datatype == 'thumbs'){
                if($type == "freeTenLikes"){
                    $htm .= '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
                                <div class="img-loader"></div>
                                <img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '">
                                <div class="icon-container">
                                    <img src="/imgs/white-tick.svg" alt="checked" class="check">
                                </div>
                            </div>';
                }else{
                    $htm .= '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
                                 <div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '" /><div class="img-loader"></div></div>';
                }
            } 
        } else {


            mysql_query("UPDATE `ig_thumbs`  SET  `like_count` = '$like_count', `added_on_instagram` = '$postTime',`igusername` = '$username' WHERE `shortcode` = '$shortcode' LIMIT 1");

            $getthumbdatainfo = mysql_fetch_array($findpostfile);
            if ($getthumbdatainfo['dnow'] == '1') {

                if ($datatype == 'thumbs') {
                    if($type == "freeTenLikes"){
                        $htm .= '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
                                    <div class="img-loader"></div>
                                    <img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '">
                                    <div class="icon-container">
                                        <img src="/imgs/white-tick.svg" alt="checked" class="check">
                                    </div>
                                </div>';
                    }else{
                        $htm .= '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
                                             <div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '" /><div class="img-loader"></div></div>';
                    }

                }
            } else {

                if ($_GET['rabban'] == 'true') {
                    echo 'already in system<hr>';
                }
                $filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
                if ($datatype == 'thumbs'){
                    if($type == "freeTenLikes"){
                        $htm .= '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
                                    <div class="img-loader"></div>
                                    <img class="main-img" src="' . $filePath . '">
                                    <div class="icon-container">
                                        <img src="/imgs/white-tick.svg" alt="checked" class="check">
                                    </div>
                                </div>';
                    }else{
                        $htm .= '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
                            <div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="' . $filePath . '" /><div class="img-loader"></div></div>';
                    } 
                } 
            }
        }

        if ($_GET['rabban'] == 'true') {
            echo 'URL: ' . $thumbnailurl . '<br>';
        }
       
        unset($get);
    }
   
    if ($iteration == "second") {
        
        $btn = '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center; padding: 4px 15px !important;margin: auto;margin-top: 10px;border-radius: 30px;color: white;" onclick="loadPosts(\'third\');">Load more post</a>';
        if($type == "freeTenLikes"){	
			$btn = '<div class="btn-container"><button type="button" id="loadMoreBtnId" onclick="loadPosts(\'third\');" class="btn-transparent">LOAD MORE POSTS</button></div>';

		}
        $htmlData = '<input type="hidden" id="nextPageId2" value="' . $nextPageId . '"><div id="loaderLoadMoreId"></div>';
    } else {
        $btn = "";
    }

    $returnArr["htmlPost"] = $htm;
    $returnArr["htmlBtn"] = $btn;
    $returnArr["htmlData"] = $htmlData;
} else {

    sendCloudwatchData('Superviral', 'load-more-posts-failure', 'LoadMorePosts', 'load-more-posts-failure-function', 1);
    mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:655', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");
    $notfound = 1;

    if ($_GET['rabban'] == 'true') {

        echo 'Not found';
        die;
    }
}

function saveThumb($thumbnailurl, $shortcode, $postTime, $username, $mediaType, $like_count)
{
    $added = time();
    $sql = "INSERT INTO `ig_thumbs` SET `like_count` = '$like_count', `thumb_url` = '$thumbnailurl',`shortcode` ='$shortcode',`added_on_instagram` = '$postTime', igusername = '$username', media_type='$mediaType', `added` = '$added'";
    mysql_query($sql);
    // echo $sql; die;
}




//IF NOT FOUND THEN SHOW A CERTAIN MESSAGE FOR THE ORDER SELECT PAGE TO REDIRECT
if ((($notfound == 1) && ($datatype == 'thumbs')) || ($isprivate == '1')) {
    // echo 'Error 3420';
    //$returnArr["htmlPost"] = "Error 3420";
}

if ($datatype == 'thumbs') {

	mysql_query("UPDATE `ig_api_stats`
									SET 
									`count` = '$totalresults', 
									`loadtime` = '$loadtime', `account_status` = 'Public'
									WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

}

echo json_encode($returnArr);
die;
