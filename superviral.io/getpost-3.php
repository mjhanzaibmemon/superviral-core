<?php

$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.', '', $loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));

if (empty($loc)) $loc = '';
if ($loc == 'superviral') $loc = '';
if ($loc == 'www') $loc = '';
if (!empty($loc)) $loc = $loc . '.';

header('Access-Control-Allow-Origin: https://' . $loc . 'superviral.io');

$now = time();
$sixhoursago = $now - 43200;


include('db.php');

$postDetectionFlag = addslashes($_GET["postdetection"]);



$username = strtolower(addslashes($_GET['username']));
$ordersession = addslashes($_GET['ordersession']);
$ordersession_id = addslashes($_GET['ordersession_id']);
$datatype = addslashes($_GET['datatype']);
$videosonly = addslashes($_GET['videosonly']);
$packagetype = addslashes($_GET['packagetype']);
if (($datatype !== 'thumbs') && ($datatype !== 'dp')) {

	sendCloudwatchData('Superviral', 'error-post-unavailable', 'GetPost', 'error-post-unavailable-function', 1);
	
	echo 'Incorrect Error';
}


$username = str_replace('@', '', $username);
$type = addslashes($_GET['type']);

if ($datatype == 'dp') {
	$finddpfile = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` = '$username' ORDER BY `id` DESC LIMIT 1");
	$fetchdpfile = mysql_fetch_array($finddpfile);

	$dp = 'https://cdn.superviral.io/dp/' . $fetchdpfile['dp'] . '.jpg';
	if (mysql_num_rows($finddpfile) == 0) {

		$url = 'https://i.supernova-493.workers.dev/api/v3/profile?username=' . $username;

		//ATTEMPT TODO IT OUR WAY
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$get = curl_exec($curl);
		$resp = json_decode($get);
		$dp = $resp->data->user->profile_pic_url;
		$dpimgname = md5($ordersession . $username);

		require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

		$s3 = new S3($amazons3key, $amazons3password);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $dp);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 12);
		curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($curl, CURLOPT_ENCODING, '');

		$get = curl_exec($curl);

		if (empty($get)) {
			sendCloudwatchData('Superviral', 'ig-dp-load', 'OrderReview', 'order-review-ig-dp-failure-function', 1);
		}
		$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
		if ($putobject) {
			mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");
			sendCloudwatchData('Superviral', 's3-image-upload-success', 'PreloadPost2', 's3-image-upload-success-function', 1);
		} else {
			sendCloudwatchData('Superviral', 's3-image-upload-failure', 'PreloadPost2', 's3-image-upload-failure-function', 1);
		}
		curl_close($curl);

		$dp = 'https://cdn.superviral.io/dp/' . $dpimgname . '.jpg';
	}
	echo '<img class="dp dp2" height="65" width="65" src="'. $dp .'">';
	die;
}

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

if($type == "freeTenLikes"){
	$checkq = mysql_query("SELECT * FROM orders WHERE igusername = '$username' 
	AND `brand` = 'sv' 
	AND packagetype = 'freelikes' 
	AND added >= UNIX_TIMESTAMP(NOW()) - 86400 
	LIMIT 1");
	 if (mysql_num_rows($checkq) > 0) {

		echo "Already Claimed";
		die;
	 }

}

if ($videosonly == '1') $videosonly = '1';

if (empty($username)) die('1');

// Added code : to get data from DB insead socialscrape

if (!empty($postDetectionFlag)) {

	$Query = "SELECT id, shortcode, thumb_url FROM ig_thumbs WHERE igusername = '" . $username . "'
				 ORDER BY added_on_instagram DESC LIMIT 12";
	$runQuery = mysql_query($Query);

	$checkCnt = mysql_num_rows($runQuery);

	while ($data = mysql_fetch_array($runQuery)) {
		$shortcode = $data['shortcode'];

		$newimgname = md5('superviralrb' . $shortcode);

		//CHECK IF WE ALREADY SAVED THIS THUMB
		$filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";

		if($type == "freeTenLikes"){
			echo '  <div class="img-responsive post" data-media_type = "'. $data['media_type'] .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
						<div class="img-loader"></div>
						<img class="main-img" src="' . $filePath . '">
						<div class="icon-container">
							<img src="/imgs/white-tick.svg" alt="checked" class="check">
						</div>
					</div>';
		}else{
			echo '<div data-media_type = "'. $data['media_type'] .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive">
					<div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="' . $filePath . '" /><div class="img-loader"></div></div>';

		}
	}

	$userQuery = "SELECT * FROM searchbyusername WHERE ig_username = '" . $username . "' LIMIT 1";
	$runUserQuery = mysql_query($userQuery);
	$userData = mysql_fetch_array($runUserQuery);
	
	if(empty($nextPageId)) $nextPageId = $_COOKIE['nextPageId1'];
	echo '<input type="hidden" id="nextPageId" value="' . $nextPageId . '">';
	echo '<input type="hidden" id="instaUserId" value="' . $userData['ig_id'] . '">';

	if($checkCnt > 11){
		if($packagetype != 'freelikes' && $type !="freeTenLikes"){
			echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center;font-weight: 600;padding: 9px 15px !important;margin: auto;margin-top: 10px;border-radius: 21px;color: white;" onclick="loadPosts(\'second\');">Load more post</a>';
		}else if($type == "freeTenLikes"){	
			echo '<div class="btn-container"><button type="button" id="loadMoreBtnId" onclick="loadPosts(\'second\');" class="btn-transparent" style="padding: 9px;font-size: 16px;font-weight: 600;border-radius: 21px !important;">LOAD MORE POSTS</button></div>';
	
		}
	}
	
	echo '<div id="loaderLoadMoreId"></div>';
	die;
}

$rotatingips = array(
	'173.208.150.242:15002',
	'173.208.213.170:15002',
	'173.208.239.10:15002',
	'173.208.136.2:15002'
);

$totalresults = 0;

// new API //////////////////////////////////////////////////////////////////////////////////////////////////

$checknow = time();
$checknow2 = time() - 60;
$notfound = 0;

$checkrecentigapitstat = mysql_query("SELECT * FROM `ig_api_stats` WHERE `count` > '2' AND `igusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");


if (mysql_num_rows($checkrecentigapitstat) == 0 || $type == 'testPG') {
	
	$starttime = microtime(true);

	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_getpost_userid' AND `brand` = 'sv' LIMIT 1");
	sendCloudwatchData('Superviral', 'supernova-api-getpost-userid', 'GetPost', 'supernova-api-getpost-userid-function', 1);

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
	
	if(!empty($userId)){
		
	sendCloudwatchData('Superviral', 'ig-user-id-success', 'GetPost', 'ig-user-id-success-function', 1);

	// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_getpost_posts' AND `brand` = 'sv' LIMIT 1");
	sendCloudwatchData('Superviral', 'supernova-api-getpost-posts', 'GetPost', 'supernova-api-getpost-posts-function', 1);
		
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

	$nextPageId = $get->data->next_page_id;

	setcookie(
		"nextPageId1",
		$nextPageId,
		time() + (10 * 365 * 24 * 60 * 60),
		"/"
	  );
	$arrays = $get->data->response->items;
	$isprivate = $get->data->response->user->is_private;
	
	}else{

		mysql_query("UPDATE `ig_api_stats`
									SET 
									`source` = 'rapidapi'
									WHERE id = '$lastApiStatsId' LIMIT 1;");

		$url = 'https://flashapi1.p.rapidapi.com/ig/posts_username/?user='. $username .'&nocors=false';
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
		curl_close($curl);

		$arrays = $get->items;
		$users = $get->user;
		// print_r($arrays);  die;
		$isprivate = $user->is_private;
		$userId = $user->pk;

		if(!empty($userId)){

			sendCloudwatchData('Superviral', 'ig-user-id-success', 'GetPost', 'ig-user-id-success-function', 1);

			$nextPageId = $get->next_max_id;

			setcookie(
				"nextPageId1",
				$nextPageId,
				time() + (10 * 365 * 24 * 60 * 60),
				"/"
			  );

		} else {

			sendCloudwatchData('Superviral', 'ig-user-id-failure', 'GetPost', 'ig-user-id-failure-function', 1);

			$notfound = 1;
			// if not found at all

			$checkrecentigapitstat = mysql_query("SELECT * FROM `ig_api_stats` WHERE `count` > '2' AND `igusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");
			if (mysql_num_rows($checkrecentigapitstat) > 0) {

				//echo 'Not found at all';
				die;
			}

			if ($_GET['rabban'] == 'true') {

				echo 'Not found';
				die;
			}
		}

		

	}


	

	if(!empty($userId)){
		if($isprivate == '1'){
			$account_status = 'Private';
		}else{
			$account_status = 'Public';
		}
	}else{
		$account_status = 'User Not Found';
	}

	$endtime = microtime(true);

	$loadtime = $endtime - $starttime;
}

	if(!empty($type) && $type == 'testPG'){
		echo  "<p style='width: 100%;font-size:30px;font-weight:600'>User: ". $username ."</p>";
		echo  "<p style='width: 100%;'>Average time to load posts: <br/><span style='font-size:50px;font-weight:600;color:black;'>" .$discountactual = number_format(floatval($loadtime),2) . "s</span></p>";
		echo  "<p id='postStatsId'></p>";
	}

if ($_GET['rabban'] == 'true') {

	echo $code . '<hr>';

	echo $get;
}

if(empty($type) || $type != 'testPG'){
	if ($notfound == 1 || mysql_num_rows($checkrecentigapitstat) > 0) { //MEANS WE CANT FIND THIS, WE CANT FIND THE CORRECT RESPONSE

	// Added code : to get data from DB insead socialscrape
	sendCloudwatchData('Superviral', 'ig-api-stats-success', 'GetPost', 'ig-api-stats-success-function', 1);

	if ($_GET['rabban']) echo 'Superviral Cache Mode<hr>';

	$runQuery = mysql_query("SELECT id, shortcode, thumb_url,dnow,media_type FROM ig_thumbs WHERE igusername = '" . $username . "' 
				 ORDER BY added_on_instagram DESC LIMIT 12");

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if ((mysql_num_rows($runQuery) == '0') && ($datatype == 'thumbs')) {
		sendCloudwatchData('Superviral', 'error-post-unavailable', 'GetPost', 'error-post-unavailable-function', 1);
		if(!empty($lastApiStatsId)){mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:324', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");}
		echo 'Error 3420';
		die;
	}

	while ($data = mysql_fetch_array($runQuery)) {
		$shortcode = $data['shortcode'];

		$newimgname = md5('superviralrb' . $shortcode);


		if ($data['dnow'] == '1') {

			//CHECK IF WE ALREADY SAVED THIS THUMB
			$filePath = 'https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username;
			//$filePath = $data['thumb_url'];
			
		} else {

			//CHECK IF WE ALREADY SAVED THIS THUMB
			$filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
		}

		if ($datatype == 'thumbs'){
			if($type == "freeTenLikes"){
				echo '  <div class="img-responsive post" data-media_type = "'. $data['media_type'] .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
							<div class="img-loader"></div>
							<img class="main-img" src="' . $filePath . '">
							<div class="icon-container">
								<img src="/imgs/white-tick.svg" alt="checked" class="check">
							</div>
						</div>';
			}else{
				echo '<div data-media_type = "'. $data['media_type'] .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
				<div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="' . $filePath . '" /><div class="img-loader"></div></div>';

			}
		} 
	}
	$totalresults = mysql_num_rows($runQuery);
	$account_status = 'Public';
	if ($datatype == 'thumbs') {
		if(empty($loadtime)) $loadtime = 0;
		mysql_query("UPDATE `ig_api_stats`
									SET 
									`count` = '$totalresults', 
									`loadtime` = '$loadtime', `account_status` = '$account_status'
									WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

	
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////

	$userQuery = "SELECT * FROM searchbyusername WHERE ig_username = '" . $username . "' LIMIT 1";
	$runUserQuery = mysql_query($userQuery);
	$userData = mysql_fetch_array($runUserQuery);


	if ($datatype !== 'dp') {
		echo '<input type="hidden" id="instaUserId" value="' . $userData['ig_id'] . '">';
		if(empty($nextPageId)) $nextPageId = $_COOKIE['nextPageId1'];
		echo '<input type="hidden" id="nextPageId" value="' . $nextPageId . '">';

		if(mysql_num_rows($runQuery) > 11) {
			if($packagetype != 'freelikes' && $type != "freeTenLikes"){
				echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center;font-weight: 600;padding: 9px 15px !important;margin: auto;margin-top: 10px;border-radius: 21px;color: white;" onclick="loadPosts(\'second\');">Load more post</a>';
			}else if($type == "freeTenLikes"){	
				echo '<div class="btn-container"><button type="button" id="loadMoreBtnId" onclick="loadPosts(\'second\');" class="btn-transparent" style="padding: 9px;font-size: 16px;font-weight: 600;border-radius: 21px !important;">LOAD MORE POSTS</button></div>';
	
			}
		}
		
		echo '<div id="loaderLoadMoreId"></div>';
	}


	die;
	}
}


if (!empty($ordersession)) {


	$dpimgname = md5($ordersession . $username);
	

	//by default acquire the thumbnail
	$finddpfile = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' AND `igusername` = '$username' ORDER BY `id` DESC LIMIT 1");
	if (mysql_num_rows($finddpfile) == 0) { //no thumbnailfound in database

		
		require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

		$s3 = new S3($amazons3key, $amazons3password);

	
		$dp = $get->data->response->user->profile_pic_url;

		$randnum = rand(0, 3);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $dp);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 12);
		curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($curl, CURLOPT_ENCODING, '');


		$get = curl_exec($curl);

		curl_close($curl);

		$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);

		if($putobject){
			mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");
			sendCloudwatchData('Superviral', 's3-image-upload-success', 'GetPost', 's3-image-upload-success-function', 1);
		}else{
			sendCloudwatchData('Superviral', 's3-image-upload-failure', 'GetPost', 's3-image-upload-failure-function', 1);

		}
	}



	if ($datatype == 'dp') echo '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/dp/' . $dpimgname . '.jpg">';
}
//checkfirst its not on private


if (isset($arrays)) { //this means we've successfully requested and received $get, and the account is on public

	//////////////////////
	$account_status = 'Public';

	foreach ($arrays as $thumbnail) {


		if($totalresults > 11) continue;

		$totalresults++;

		$isvideo = $thumbnail->media_type;

		if (($videosonly == '1') && ($isvideo == '0')) continue;

		$thumbnailurl = $thumbnail->thumbnail_url;
		if($isvideo == 8){
			$thumbnailurl = $thumbnail->image_versions[1]->url;
		}

		if(empty($thumbnailurl)){
			$thumbnailurl = $thumbnail->image_versions2->candidates[2]->url;
		}
		// $thumbnailurl = $thumbnailurl[0]->src;
		$shortcode = $thumbnail->code;
		$postTime = $thumbnail->taken_at_ts;

		if(empty($postTime)){
			$postTime = $thumbnail->taken_at;
		}
		$like_count = $thumbnail->like_count;

		$comment_count = $thumbnail->comment_count;

		if ($isvideo == 2) {
			$mediaType = "video";
			// $views = $views;	
		} else {
			$mediaType = "image";
			// $views = 0;	
		}

		if (empty($shortcode)) continue;

		$newimgname = md5('superviralrb' . $shortcode);

		if ($_GET['rabban'] == 'true') echo 'Image name: ' . $newimgname . '<br>';
		if ($_GET['rabban'] == 'true') echo 'Thumbnail URL: ' . $thumbnailurl . '<br>';

		//CHECK IF WE ALREADY SAVED THIS THUMB
		$findpostfile = mysql_query("SELECT `shortcode`,`dnow` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
		if (mysql_num_rows($findpostfile) == 0) {

			saveThumb($thumbnailurl, $shortcode, $postTime, $mediaType, $username, $like_count, $comment_count); // New save thumb function

			if ($datatype == 'thumbs'){
				if($type == "freeTenLikes"){
					echo '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
								<div class="img-loader"></div>
								<img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '">
								<div class="icon-container">
									<img src="/imgs/white-tick.svg" alt="checked" class="check">
								</div>
							</div>';
				}else{
					echo '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
											 <div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '" /><div class="img-loader"></div></div>';
				}
			} 
			
		} else {

			mysql_query("UPDATE `ig_thumbs` SET `comment_count` = '$comment_count', `like_count` = '$like_count', thumb_url= '$thumbnailurl', `added_on_instagram` = '$postTime',`igusername` = '$username' WHERE `shortcode` = '$shortcode' LIMIT 1");

			$getthumbdatainfo = mysql_fetch_array($findpostfile);
			if ($getthumbdatainfo['dnow'] == '1') {

				if ($datatype == 'thumbs'){
					if($type == "freeTenLikes"){
						echo '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
									<div class="img-loader"></div>
									<img class="main-img" src="https://swfcaqwdq3.execute-api.us-east-2.amazonaws.com/download-thumbs-api-lambda?shortcode=' . $shortcode . '&username=' . $username . '">
									<div class="icon-container">
										<img src="/imgs/white-tick.svg" alt="checked" class="check">
									</div>
								</div>';
					}else{
						echo '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
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
						echo '  <div class="img-responsive post" data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg">
									<div class="img-loader"></div>
									<img class="main-img" src="' . $filePath . '">
									<div class="icon-container">
										<img src="/imgs/white-tick.svg" alt="checked" class="check">
									</div>
								</div>';
					}else{
						echo '<div data-media_type = "'. $mediaType .'" data-value="' . $shortcode . '###https://cdn.superviral.io/thumbs/' . $newimgname . '.jpg" class="img-responsive"><div class="tick"><img src="/imgs/img-select-tick.png"></div>
							<div class="amount">+399 ' . $packagetype . '</div><img class="main-img" src="' . $filePath . '" /><div class="img-loader"></div></div>';
					}
				} 
			}
		}

		if ($_GET['rabban'] == 'true') echo 'URL: ' . $thumbnailurl . '<br>';
	}


	$checkPostCnt = count($arrays);



	if ($datatype !== 'dp') {
		echo '<input type="hidden" id="instaUserId" value="' . $userId . '">';
		echo '<input type="hidden" id="nextPageId" value="' . $nextPageId . '">';

		if($checkPostCnt > 11) {
			if($packagetype != 'freelikes' && $type != "freeTenLikes"){
				echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center;font-weight: 600;padding: 9px 15px !important;margin: auto;margin-top: 10px;border-radius: 21px;color: white;" onclick="loadPosts(\'second\');">Load more posts</a>';
			}else if($type == "freeTenLikes"){	
				echo '<div class="btn-container"><button type="button" id="loadMoreBtnId" onclick="loadPosts(\'second\');" class="btn-transparent" style="padding: 9px;font-size: 16px;font-weight: 600;border-radius: 21px !important;">LOAD MORE POSTS</button></div>';
	
			}
		}
		
		echo '<div id="loaderLoadMoreId"></div>';
	}
} else {


	$notfound = 1;

	// if not found at all

	$checkrecentigapitstat = mysql_query("SELECT * FROM `ig_api_stats` WHERE `count` > '2' AND `igusername` = '$username' AND `added` BETWEEN '$checknow2' AND '$checknow' LIMIT 1");
	if (mysql_num_rows($checkrecentigapitstat) > 0) {
		
		//echo 'Not found at all';
		die;

	}


	if ($_GET['rabban'] == 'true') {

		echo 'Not found';
		die;
	}
}


function saveThumb($thumbnailurl, $shortcode, $postTime, $mediaType, $username, $like_count, $comment_count)
{
	$added = time();
	$sql = "INSERT INTO `ig_thumbs` SET `comment_count` = '$comment_count', `thumb_url` = '$thumbnailurl',`shortcode` ='$shortcode',`added_on_instagram` = '$postTime', `dnow` = '1', media_type = '$mediaType', igusername = '$username', `like_count` = '$like_count', `added` = '$added'";
	mysql_query($sql);

	// if($_GET['rabban']=='true')echo $sql;die;
	// echo $sql; die;
}


//IF NOT FOUND THEN SHOW A CERTAIN MESSAGE FOR THE ORDER SELECT PAGE TO REDIRECT
if ((($notfound == 1) && ($datatype == 'thumbs')) || ($isprivate == '1')){
	sendCloudwatchData('Superviral', 'error-post-unavailable', 'GetPost', 'error-post-unavailable-function', 1);
	if(!empty($lastApiStatsId)){mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:655', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");}
	echo 'Error 3420';
}
	

if ($datatype == 'thumbs') {
	if(empty($loadtime)) $loadtime = 0;
		mysql_query("UPDATE `ig_api_stats`
									SET 
									`count` = '$totalresults', 
									`loadtime` = '$loadtime', `account_status` = '$account_status'
									WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

	
}
