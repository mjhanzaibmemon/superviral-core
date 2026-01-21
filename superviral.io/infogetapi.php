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
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';


$username = strtolower(addslashes($_GET['username']));
$ordersession = addslashes($_GET['ordersession']);
$datatype = addslashes($_GET['datatype']);
$videosonly = addslashes($_GET['videosonly']);
$packagetype = addslashes($_GET['packagetype']);
if(($datatype!=='thumbs')&&($datatype!=='dp')){echo 'Incorrect Error';}

if($videosonly=='1')$videosonly='1';

if(empty($username))die('username not found');

//either dp or thumbs
//when thumbs is selected, download a DP regardless

/*if(!empty($ordersession)){
//MAKE THIS LIVE WHEN WE'VE GONE LOCAL SERVER AGAIN

}*/

$rotatingips= array('173.208.150.242:15002',
'173.208.213.170:15002',
'173.208.239.10:15002',
'173.208.136.2:15002');

$s3 = new S3($amazons3key, $amazons3password);

$totalresults = 0;

////////THUMBS REQUEST: SEARCH OUR SYSTEM IN THE LAST 12-HOURS
/*
if($datatype=='thumbs'){




$getexistingthumbsq = mysql_query("SELECT * FROM `ig_thumbs` WHERE `igusername` = '$username' AND `added` BETWEEN '$sixhoursago' AND '$now'");

while($getexistingthumbs = mysql_fetch_array($getexistingthumbsq)){

$shortcode = $getexistingthumbs['shortcode'];
$newimgname = md5('superviralrb'.$shortcode);


//PUT shortcodes into an array
$existingsimgs[$shortcode] = $shortcode;

//START ECHOING IT ALL OUT
echo '<div data-value="'.$shortcode.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
				<div class="amount">+399 '.$packagetype .'</div><img  src="https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" /></div>';

}

}
*/


	
	

	$url = 'https://www.instagram.com/'.$username.'/?__a=1';

	$PROXY_HOST = "168.81.71.11";
	$PROXY_PORT = "3199";




	//ATTEMPT TODO IT OUR WAY
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($curl, CURLOPT_COOKIE, $igsessionid);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$PROXY_USER:$PROXY_PASS");
	curl_setopt($curl, CURLOPT_PROXY, "$PROXY_HOST:$PROXY_PORT");
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36');


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);

	$get = json_decode($get);

	curl_close($curl);


	$arrays = $get -> graphql -> user -> edge_owner_to_timeline_media -> edges;
	$isprivate = $get -> graphql -> user -> is_private;

	//checkfirst its not on private


	if(isset($arrays)){//this means we've successfully requested and received $get, and the account is on public


				if(!empty($ordersession)){


				$dpimgname = md5($ordersession.$username);

				//by default acquire the thumbnail
				$finddpfile = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' AND `igusername` = '$username' LIMIT 1");
				if(mysql_num_rows($finddpfile)==0){//no thumbnailfound in database


						$dp = $get -> graphql -> user -> profile_pic_url;
						$randnum = rand(0, 3);

						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL, $dp); 
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
						curl_setopt($curl, CURLOPT_TIMEOUT, 10);
						curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);


						$get = curl_exec($curl);

						curl_close($curl);


						$putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);
						mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='$ordersession', `igusername` = '$username'");

				}

				if($datatype=='dp')echo '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/dp/'.$dpimgname.'.jpg">';

				}



				foreach($arrays as $thumbnail){



				$totalresults++;

				$isvideo = $thumbnail -> node -> is_video;

				if(($videosonly=='1')&&($isvideo=='0'))continue;

				$thumbnailurl = $thumbnail -> node -> thumbnail_resources;
				$thumbnailurl = $thumbnailurl[0] -> src;
				$shortcode = $thumbnail -> node -> shortcode;

				if(empty($shortcode))continue;

				$newimgname = md5('superviralrb'.$shortcode);

				if($_GET['rabban']=='true')echo 'Image name: '.$newimgname.'<br>';
				if($_GET['rabban']=='true')echo 'Thumbnail URL: '.$thumbnailurl.'<br>';

					//CHECK IF WE ALREADY SAVED THIS THUMB
					$findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$shortcode' LIMIT 1");
					if(mysql_num_rows($findpostfile)==0){

						$randnum = rand(0, 3);

						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL, $thumbnailurl); 
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
						curl_setopt($curl, CURLOPT_TIMEOUT, 10);
						curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);

						$get = curl_exec($curl);

						curl_close($curl);

						if($_GET['rabban']=='true'){

							echo 'GET contents: '.$get; die;

						}



						$putobject = S3::putObject($get, 'cdn.superviral.io', 'thumbs/'.$newimgname.'.jpg', S3::ACL_PUBLIC_READ);

						if(!empty($get))mysql_query("INSERT INTO `ig_thumbs` SET `shortcode` = '$shortcode', `igusername` = '$username', `added` = '$now'");

				

					}else{

						//mysql_query("UPDATE `ig_thumbs` SET `added` = '$now' WHERE `shortcode` = '$shortcode' LIMIT 1");

						if($_GET['rabban']=='true'){echo 'already in system';die;}

					}

				if($_GET['rabban']=='true')echo 'URL: '.$thumbnailurl.'<br>';

				//IF ALREADY SHOWING THEN CONTINUE
				if(array_key_exists($shortcode, $existingsimgs))continue;

				if($datatype=='thumbs')echo '<div data-value="'.$shortcode.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
				<div class="amount">+399 '.$packagetype .'</div><img  src="https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" /></div>

			';

			unset($get);


				}

				

	}else{


		$notfound = 1;


			echo 'Not found on SV feed now proceed to blast up<hr>';



	}



	if($notfound==1){//Get the feed from Blastup and we couldn't use our own feed this means

	echo 'Blast up mode is on<hr>';

	$url = 'https://blastup.com/user/media/'.$username;
	$randnum = rand(0, 3);

	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);
	curl_setopt($curl, CURLOPT_TIMEOUT, 25);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36');


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);

	$get = json_decode($get);

	curl_close($curl);


	$isprivate =  $get -> is_private;

	$msgerror = $get -> msg;

	if (strpos($msgerror, 'retrieve any user data') === false) {//CHECK BLAST UP FOR DATA AND SET THE 

	$notfound=0;

	//if order session is set, then save the DP
	if(!empty($ordersession)){

		$dpimgname = md5($ordersession.$username);

		$finddpfile = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' LIMIT 1");
		if(mysql_num_rows($finddpfile)==0){

		//by default acquire the thumbnail
		$profile_picturesrc =  $get -> profile_picture;
		$profile_picturesrc = explode(',', $profile_picturesrc);
		$profile_picturesrc = base64_decode($profile_picturesrc[1]);

		$putobject = S3::putObject($profile_picturesrc, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);
		
		mysql_query("INSERT INTO `ig_dp` SET `order_session` = '$ordersession',`ig_dp` ='$dpimgname', `igusername` = '$username'");
		}

		if($datatype=='dp')echo '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/dp/'.$dpimgname.'.jpg">';

	}


		$arrays = $get->media;

		if(is_array($arrays)){


		foreach ($arrays as $imgs) {

			$totalresults++;

			$code = $imgs->code;
			if(empty($code))continue;

			$isvideo = $imgs->is_video;
			if(($videosonly=='1')&&($isvideo=='0'))continue;



			$newimgname = md5('superviralrb'.$code);

			$findpostfile = mysql_query("SELECT `shortcode` FROM `ig_thumbs` WHERE `shortcode` = '$code' LIMIT 1");
			if(mysql_num_rows($findpostfile)==0){

			$thumbnail = $imgs->thumbnail;
			$thumbnail = explode(',', $thumbnail);
			$thumbnail = base64_decode($thumbnail[1]);

			$putobject = S3::putObject($thumbnail, 'cdn.superviral.io', 'thumbs/'.$newimgname.'.jpg', S3::ACL_PUBLIC_READ);


			mysql_query("INSERT INTO `ig_thumbs` SET `shortcode` = '$code', `igusername` = '$username', `added` = '$now'");
			
			unset($thumbnail);
			}
			else
			{

						//mysql_query("UPDATE `ig_thumbs` SET `added` = '$now' WHERE `shortcode` = '$code' LIMIT 1");

			}

			//IF ALREADY SHOWING THEN CONTINUE
			if(array_key_exists($code, $existingsimgs))continue;

			if($datatype=='thumbs')echo '<div data-value="'.$code.'###https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive"><div class="amount">+399 '.$packagetype .'</div><img  src="https://cdn.superviral.io/thumbs/'.$newimgname.'.jpg" /></div>';

	}

		} else{

			$notfound=1;

		}


	} 



	}






echo 'Is private'.$isprivate.'<hr>';
echo 'Not found'.$notfound.'<hr>';



//IF NOT FOUND THEN SHOW A CERTAIN MESSAGE FOR THE ORDER SELECT PAGE TO REDIRECT
if((($notfound==1)&&($datatype=='thumbs'))||($isprivate=='1'))echo 'Error 3420';


if($datatype=='thumbs'){
mysql_query("INSERT INTO `ig_api_stats`

	SET 
	`igusername` = '$username', 
	`count` = '$totalresults', 
	`added` = '$now', 
	`ordersession` = '$ordersession'

	");}

?>