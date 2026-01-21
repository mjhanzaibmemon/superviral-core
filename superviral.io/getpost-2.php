<?php
 

$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.','',$loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));

if(empty($loc))$loc = '';
if($loc=='superviral')$loc = '';
if($loc=='www')$loc = '';
if(!empty($loc))$loc = $loc.'.';

header('Access-Control-Allow-Origin: https://'.$loc.'superviral.io');

$now=time();
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


include('db.php');

$postDetectionFlag = addslashes($_GET["postdetection"]);



$username = strtolower(addslashes($_GET['username']));
$ordersession = addslashes($_GET['ordersession']);
$datatype = addslashes($_GET['datatype']);
$videosonly = addslashes($_GET['videosonly']);
$packagetype = addslashes($_GET['packagetype']);
if(($datatype!=='thumbs')&&($datatype!=='dp')){echo 'Incorrect Error';}

if($videosonly=='1')$videosonly='1';

if(empty($username))die('1');

// Added code : to get data from DB insead lamadava

if(!empty($postDetectionFlag)){

	$Query = "SELECT id, shortcode, thumb_url FROM ig_thumbs WHERE igusername = '" . $username . "'
				 ORDER BY added_on_instagram DESC LIMIT 12";
	$runQuery = mysql_query($Query);
	


	while($data = mysql_fetch_array($runQuery)){
		$shortcode = $data['shortcode'];

		$newimgname = md5('superviralrb'.$shortcode);

			//CHECK IF WE ALREADY SAVED THIS THUMB
			$filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
			
			echo '<div data-value="'.$shortcode.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
					<div class="amount">+399 '.$packagetype .'</div><img  src="'. $filePath .'" /></div>';


	}

	$userQuery = "SELECT instagram_user_id FROM checkusers_now WHERE ig_username = '" . $username . "'";
	$runUserQuery = mysql_query($userQuery);
	$userData = mysql_fetch_array($runUserQuery);
	
	echo '<input type="hidden" id="instaUserId" value="'.$userData['instagram_user_id'].'">';
	echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center; padding: 4px 15px !important;margin: auto;margin-top: 10px;border-radius: 30px;color: white;" onclick="loadPosts(\'second\');">Load more post</a>';
	echo '<div id="loaderLoadMoreId"></div>';
	die;
}

//either dp or thumbs
//when thumbs is selected, download a DP regardless

/*if(!empty($ordersession)){
//MAKE THIS LIVE WHEN WE'VE GONE LOCAL SERVER AGAIN

}*/

$rotatingips= array('173.208.150.242:15002',
'173.208.213.170:15002',
'173.208.239.10:15002',
'173.208.136.2:15002');



$totalresults = 0;



	
	
// new API //////////////////////////////////////////////////////////////////////////////////////////////////

	 $starttime = microtime(true);

	 $url = 'https://api.lamadava.com/a1/user?username='.$username;
	 

	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);


	$get = json_decode($get);


	curl_close($curl);

	$arrays = $get -> graphql -> user -> edge_owner_to_timeline_media -> edges;
	// print_r($arrays);  die;
	$isprivate = $get -> graphql -> user -> is_private;
	$userId = $get -> graphql -> user -> id;	


if(empty($userId)){

	 $url = 'https://api.datalama.io/a1/user?username='.$username;
	 

	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $datalamaaccess" ));
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, '');

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	$get = curl_exec($curl);

	$get = json_decode($get);


	$arrays = $get -> graphql -> user -> edge_owner_to_timeline_media -> edges;
	// print_r($arrays);  die;
	$isprivate = $get -> graphql -> user -> is_private;
	$userId = $get -> graphql -> user -> id;	


}

	
	$endtime = microtime(true);	

	$loadtime = $endtime - $starttime;	


	//checkfirst its not on private


	if(isset($arrays)){//this means we've successfully requested and received $get, and the account is on public


//////////////////////


				foreach($arrays as $thumbnail){



				$totalresults++;

				$isvideo = $thumbnail -> node -> is_video;

				if(($videosonly=='1')&&($isvideo=='0'))continue;

				$thumbnailurl = $thumbnail -> node -> thumbnail_resources;
				$thumbnailurl = $thumbnailurl[0] -> src;
				$shortcode = $thumbnail -> node -> shortcode;
				$postTime = $thumbnail -> node-> taken_at_timestamp;
// echo $shortcode;die;
				if(empty($shortcode))continue;

				$newimgname = md5('superviralrb'.$shortcode);

				if($_GET['rabban']=='true')echo 'Image name: '.$newimgname.'<br>';
				if($_GET['rabban']=='true')echo 'Thumbnail URL: '.$thumbnailurl.'<br>';

					//CHECK IF WE ALREADY SAVED THIS THUMB
					$findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
					if(mysql_num_rows($findpostfile)==0){

						saveThumb($thumbnailurl, $shortcode,$postTime); // New save thumb function

						if($datatype=='thumbs')echo '<div data-value="'.$shortcode.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
											 <div class="amount">+399 '.$packagetype .'</div><img  src="/thumb/'.$shortcode.'?username='.$username.'" /></div>';

					}else{

						mysql_query("UPDATE `ig_thumbs` SET `added_on_instagram` = '$postTime' WHERE `shortcode` = '$shortcode' LIMIT 1");

						if($_GET['rabban']=='true'){echo 'already in system';die;}
						$filePath = "https://cdn.superviral.io/thumbs/$newimgname.jpg";
						if($datatype=='thumbs')echo '<div data-value="'.$shortcode.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
							<div class="amount">+399 '.$packagetype .'</div><img  src="'. $filePath .'" /></div>';
					}

				if($_GET['rabban']=='true')echo 'URL: '.$thumbnailurl.'<br>';

				//IF ALREADY SHOWING THEN CONTINUE
				// if(array_key_exists($shortcode, $existingsimgs))continue;

				

				

				}


				if(!empty($ordersession)){


				$dpimgname = md5($ordersession.$username);

				//by default acquire the thumbnail
				$finddpfile = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' AND `igusername` = '$username' LIMIT 1");
				if(mysql_num_rows($finddpfile)==0){//no thumbnailfound in database


						require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';
						
						$s3 = new S3($amazons3key, $amazons3password);



						$dp = $get -> graphql -> user -> profile_pic_url;

						$randnum = rand(0, 3);

						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL, $dp); 
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
						curl_setopt($curl, CURLOPT_TIMEOUT, 10);
						curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
						curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
						curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
						curl_setopt($curl, CURLOPT_ENCODING, '');


						$get = curl_exec($curl);

						curl_close($curl);


						$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);
						mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username'");

				}


				if($datatype=='dp')echo '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/dp/'.$dpimgname.'.jpg">';

				}









				if($datatype!=='dp'){
						echo '<input type="hidden" id="instaUserId" value="'.$userId.'">';
						echo '<a href="#0" class="color4 btnnxt" id="loadMoreBtnId" style="text-align: center; padding: 4px 15px !important;margin: auto;margin-top: 10px;border-radius: 30px;color: white;" onclick="loadPosts(\'second\');">Load more post</a>';
						echo '<div id="loaderLoadMoreId"></div>';
				}	

	}else{


		$notfound = 1;

		if($_GET['rabban']=='true'){

			echo 'Not found';die;

		}


	}





	function saveThumb($thumbnailurl, $shortcode, $postTime)
	{
		$sql = "INSERT INTO `ig_thumbs` SET `thumb_url` = '$thumbnailurl',`shortcode` ='$shortcode', `added_on_instagram` = '$postTime'";
		mysql_query($sql);
		// echo $sql; die;
	}







//IF NOT FOUND THEN SHOW A CERTAIN MESSAGE FOR THE ORDER SELECT PAGE TO REDIRECT
if((($notfound==1)&&($datatype=='thumbs'))||($isprivate=='1'))echo 'Error 3420';


if($datatype=='thumbs'){
mysql_query("INSERT INTO `ig_api_stats`

	SET 
	`igusername` = '$username', 
	`count` = '$totalresults', 
	`added` = '$now', 
	`ordersession` = '$ordersession',
	`loadtime` = '$loadtime'

	");}

?>