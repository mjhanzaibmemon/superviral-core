<?php

header('Access-Control-Allow-Origin: https://superviral.io');

$now=time();
$sixhoursago = $now - 43200;

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/



include('db.php');



$username = strtolower(addslashes($_POST['username']));
$ordersession = addslashes($_POST['ordersession']);
$ordersession_id = addslashes($_POST['ordersession_id']);
$datatype = addslashes($_POST['datatype']);
$videosonly = addslashes($_POST['videosonly']);
$packagetype = addslashes($_POST['packagetype']);
if(($datatype!=='thumbs')&&($datatype!=='dp')){echo 'Incorrect Error';}

if($videosonly=='1')$videosonly='1';

$username = str_replace('@', '', $username);

$username = str_replace('@','',$username);
$username = str_replace(' ','',$username);
$username = str_replace('https://instagram.com/','',$username);
$username = str_replace('instagram.com/','',$username);
$username = str_replace('?utm_medium=copy_link','',$username);
$username = str_replace('?r=nametag','',$username);
$username = str_replace('https://www.','',$username);
$username = str_replace('?hl=en.','',$username);

$usernameerror = array('Error' => 'No Username');
if(empty($username))die(json_encode($usernameerror));

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


if(mysql_num_rows($checkrecentigapitstat)==1){

sendCloudwatchData('Superviral', 'ig-api-stats-success', 'PreloadPost2', 'ig-api-stats-success-function', 1);

 //$alreadydone = array('Already done' => 'Max limit reached for this');
 //die(json_encode($alreadydone));

}




// $fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE `ig_username` = '$username' LIMIT 1");

// if(mysql_num_rows($fetchuseridq)=='1'){

	// $fetchuserinfoq = mysql_fetch_array($fetchuseridq);
	// $userId = $fetchuserinfoq['ig_id'];
	// $is_private = $fetchuserinfoq['is_private'];
	//echo 'Found: '.$userId.'<hr>';

// } else{



		$igrequestidurl = 'https://www.instagram.com/web/search/topsearch/?query='.$username;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $igrequestidurl);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
		
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

		curl_setopt($curl, CURLOPT_ENCODING, '');

		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
		$getuserid = curl_exec($curl);

		curl_close($curl);


		$getuserid = json_decode($getuserid);
		$users = $getuserid -> users[0];
		$userId = $users -> user -> pk;
		$is_private = $users -> user -> is_private;

		if (empty($is_private)) {
			$is_private = 'Public';
		} else {
			$is_private = 'Private';
		}

		$follower_count = $users -> user -> follower_count;
		$following_count = $users -> user -> following_count;
		$media_count = $users -> user -> media_count;

		if(empty($userId)){

			// mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_preloadpost_userid' AND `brand` = 'sv' LIMIT 1");
			sendCloudwatchData('Superviral', 'supernova-api-preloadpost-userid', 'PreloadPost2', 'supernova-api-preloadpost-userid-function', 1);
			
			$url = 'https://i.supernova-493.workers.dev/api/v3/userId?username='.$username;
					
			$curl = curl_init(); 
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "X-API-KEY: $superviralsocialscrapekey" ));
			curl_setopt($curl, CURLOPT_URL, $url); 
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					
					
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					
			$get = curl_exec($curl);
			$resp = $get;

			$get = json_decode($get,true);
			
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

			if(empty($userId)){

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
					$is_private = $users -> user -> is_private;

					if (empty($is_private)) {
						$is_private = 'Public';
					} else {
						$is_private = 'Private';
					}
					
					$follower_count = $users->user->follower_count;
					$following_count = $users->user->following_count;
					$media_count = $users->user->media_count;

			}
		}

		if(is_numeric($userId)){
		
		$account_status = $is_private;
		
		sendCloudwatchData('Superviral', 'ig-user-id-success', 'PreloadPost2', 'ig-user-id-success-function', 1);

		$fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE `ig_username` = '$username' LIMIT 1");

		if(mysql_num_rows($fetchuseridq)=='1'){

			mysql_query("UPDATE `searchbyusername`

			SET
			`ig_id` = '$userId',
			`is_private` = '$is_private',
			`followers` = '$follower_count',
			`following` = '$following_count',
			`media_count` = '$media_count' WHERE `ig_username` = '$username'  LIMIT 1
			");

		}else{

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

	

		$useridfound = array('Success' => 'User ID found', 'is_private' => $is_private);
    	echo json_encode($useridfound);



	} else{

		sendCloudwatchData('Superviral', 'ig-user-id-failure', 'PreloadPost2', 'ig-user-id-failure-function', 1);
		$account_status = 'User Not Found';


		$useriderror = array('Error' => 'No User ID found');
		if(empty($userId))die(json_encode($useriderror));

	}



//}


//////////////////////////////////////////////////



		$useriderror = array('Error' => 'No User ID found');
		if(empty($userId))
		{
			sendCloudwatchData('Superviral', 'preloadpost-failure', 'PreloadPost2', 'preloadpost-failure-function', 1);  
			die(json_encode($useriderror));
		}

		sendCloudwatchData('Superviral', 'supernova-api-preloadpost-getposts', 'PreloadPost2', 'supernova-api-preloadpost-getposts-function', 1);

		$starttime = microtime(true);

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


	$endtime = microtime(true);	

	$loadtime = $endtime - $starttime;	

	//echo 'Load time: '.$loadtime.'<hr>';

	$arrays = $get->data->response->items;

	$dp = $get->data->response->user->profile_pic_url;







	if(isset($arrays)){//this means we've successfully requested and received $get, and the account is on public

		sendCloudwatchData('Superviral', 'preloadpost-success', 'PreloadPost2', 'preloadpost-success-function', 1);  
//////////////////////


				foreach($arrays as $thumbnail){

				$totalresults++;

				$isvideo = $thumbnail->media_type;

				if (($videosonly == '1') && ($isvideo == '0')) continue;

				$thumbnailurl = $thumbnail->thumbnail_url;
				if($isvideo == 8){
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
				


				if(empty($shortcode))continue;

				$newimgname = md5('superviralrb'.$shortcode);


					//CHECK IF WE ALREADY SAVED THIS THUMB

					$findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
					if(mysql_num_rows($findpostfile)==0){

						$downloadnowornot = 'Download now';
						saveThumb($thumbnailurl, $shortcode,$postTime,$username, $mediaType); // New save thumb function


					}else{

						$downloadnowornot = 'DONT Download';
						mysql_query("UPDATE `ig_thumbs` SET `added_on_instagram` = '$postTime',`igusername` = '$username' WHERE `shortcode` = '$shortcode' LIMIT 1");


					}

					/*
						 echo $shortcode.'<br>';
						 echo $thumbnailurl.'<br>';
						 echo $postTime.'<br>';
						 echo $isvideo.'<br>';
						 echo $downloadnowornot.'<br>';
						 echo '<hr>';
       					*/
				

				}

				  // ig dp

				  if(!empty($dp) && $dp != null){
					$dpimgname = md5($ordersession.$username);

					require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

					$s3 = new S3($amazons3key, $amazons3password);


					$dp = $get->data->response->user->profile_pic_url;

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

					curl_close($curl);

					$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
					if($putobject){
						mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");
		
						sendCloudwatchData('Superviral', 's3-image-upload-success', 'PreloadPost2', 's3-image-upload-success-function', 1);
					}else{
						sendCloudwatchData('Superviral', 's3-image-upload-failure', 'PreloadPost2', 's3-image-upload-failure-function', 1);
		
					}
					

				  }



				if(!empty($ordersession)){


				$dpimgname = md5($ordersession.$username);


				}








				/*
				 if($datatype!=='dp'){
				 		echo '<input type="hidden" id="instaUserId" value="'.$userId.'">';
				 		echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center; padding: 4px 15px !important;margin: auto;margin-top: 10px;border-radius: 30px;color: white;" onclick="loadPosts(\'second\');">Load more post</a>';
				 		echo '<div id="loaderLoadMoreId"></div>';
				 }
     				*/	

	}else{


		$notfound = 1;


		mysql_query("UPDATE `ig_api_stats` SET `error` = 'getpost:655', `account_status` = '' WHERE id = '$lastApiStatsId' LIMIT 1;");

		if($_GET['rabban']=='true'){

			echo 'Not found';die;

		}


	}





	function saveThumb($thumbnailurl, $shortcode,$postTime,$igusername, $mediaType)
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
if(($notfound==1)&&($datatype=='thumbs')){

		$usernameerror = array('Error' => 'No Username');
		if(empty($username))die(json_encode($usernameerror));

		
			mysql_query("UPDATE `ig_api_stats`
											SET 
											`count` = '0', 
											`loadtime` = '$loadtime', `account_status` = '$account_status'
											WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

	}

?>
