<?php
// start time
$start_time = microtime(true);

use function GuzzleHttp\Psr7\str;

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}


function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

//IF SUBMITTED

// check for post detection system

$postDetectionFlag = addslashes($_GET["postdetection"]);

if($postDetectionFlag == "true"){
	include "order2-select-postdetect.php";
	die;
}

if($_GET['free_likes'] == 1){
	$style_free_likes = '.mobilenext{background-color: rgba(0, 0, 0, 0)!important;bottom: 100px!important;}
	@media (max-width:425px){
	.mobilenext{bottom: 350px!important;}
	.image_checkboxes .img-responsive .tick img{margin:30px auto 0!important;}
	}

.mobilenext .selectedposts{color: #000 !important;}

.mobilenext .selectedposts .likes_per_post, .mobilenext .selectedposts .amount_of_posts{color: #000!important}';
}


$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand`='sv' LIMIT 1"));
$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);

$hidevideos = $packageinfo['type'];
$packagetype = $packageinfo['type'];
if($packagetype=='views'){$videosonly = '&videosonly=1';}



if(!empty($_POST['instaCombinedPosts'])){
	$submittedValues = addslashes($_POST['instaCombinedPosts']);
  
	$saveValues = str_replace(",","~~~",$submittedValues);
  
  mysql_query("UPDATE `order_session` SET `chooseposts` = '{$saveValues}' WHERE `order_session` = '$ordersession' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");
  
  
  header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/');
  
  die;
  
  }

if(!empty($_POST['posts_selected'])){
	$submitted_values = json_decode($_POST['posts_selected'],true);

	foreach($submitted_values as $value){

		if (strpos($value, 'instagram') !== false) {
			continue;
		}

		$values .= addslashes($value).'~~~';

	}

	$values_array = explode('~~~', trim($values, '~~~'));

	// free views
	$submitted_values_mt = json_decode($_POST['posts_selected_mt'],true);

	foreach($submitted_values_mt as $value_mt){
		if (in_array($value_mt, $values_array)) {
			$values_mt .= addslashes($value_mt) . '~~~';
		}
	}

	if($packagetype=='likes'){

		mysql_query("UPDATE `order_session` SET 
				 `freeviewsposts` = '{$values_mt}'
				WHERE `order_session` = '$ordersession' LIMIT 1");
	}

	
	$submitted_values_image = json_decode($_POST['posts_selected_image'],true);

	foreach($submitted_values_image as $value1){

	$values1 .= addslashes($value1).'~~~';

	}




	mysql_query("UPDATE `order_session` SET `chooseposts` = '{$values}', `chooseposts_image` = '{$values1}' WHERE `order_session` = '$ordersession' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

	if($packagetype == 'comments'){
		
		header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/select-comments/');
		die;
	}

	if($packagetype=='freelikes'){
		// header('Location: /freelikeprocess.php');
		header('Location: /free-package-process/');		
		die;
	}

	header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/');

	die;

}

if(!empty($info['chooseposts'])){

$chooseposts = explode('~~~', $info['chooseposts']);
$postURL = "";
foreach($chooseposts as $posts1){

$postURL .= $posts1 . ",";

if(empty($posts1))continue;


$posts2 = explode('###', $posts1);

$selectedlist .= '"'.$posts2[0].'###'.$posts2[1].'":"'.$posts2[0].'###'.$posts2[1].'"'.',';}

if($packageinfo['socialmedia'] == 'tt'){
	$selectedlist = str_replace('###', "",$selectedlist);
}

$selectedlist = rtrim($selectedlist,',');

$selectedlist = '{'.$selectedlist.'}';

$postURL = rtrim($postURL,',');

$chooseposts_image = explode('~~~', $info['chooseposts_image']);



foreach($chooseposts_image as $posts2){


if(empty($posts2))continue;


$selectedlist_image .= '"'.$posts2.'":"'.$posts2.'"'.',';

}


$selectedlist_image = rtrim($selectedlist_image,',');







$selectedlist_image = '{'.$selectedlist_image.'}';


}else{

$selectedlist = '{}';
$selectedlist_image = '{}';

}


if(!empty($info['freeviewsposts'])){

	$freeviewsposts = explode('~~~', $info['freeviewsposts']);
	$postURL = "";
	foreach($freeviewsposts as $posts3){
	
	$postURL .= $posts3 . ",";
	
	if(empty($posts3))continue;
	
	
	$posts23 = explode('###', $posts3);
	
	$selectedlistmt .= '"'.$posts23[0].'###'.$posts23[1].'":"'.$posts23[0].'###'.$posts23[1].'"'.',';}
	
	
	$selectedlistmt = rtrim($selectedlistmt,',');
	
	$selectedlistmt = '{'.$selectedlistmt.'}';
	
	$postURL = rtrim($postURL,',');
	
	
}else{
	
	$selectedlistmt = '{}';
	
}



$maxamount = $packageinfo['amount'];
$postlimit = $packageinfo['postlimit'];
if($packageinfo['premium']==1){$premium = ' premium ';}else{$premium = ' ';}
$packagedesc = ucwords($packageinfo['amount']. $premium .'{'.$packageinfo['type'].' package}');
/////////


//require('order2-getposts.php');//NOT IN USE

/////////

$loadingtimelimit = 15;

$findloadingtimeq = mysql_query("SELECT `id`,`loadtime` FROM `ig_api_stats` WHERE `loadtime` != '0' ORDER BY `id` DESC LIMIT $loadingtimelimit");

while($findloadingtime = mysql_fetch_array($findloadingtimeq)){$loadingtimes[] = $findloadingtime['loadtime'];}

$loadingtimes = array_sum($loadingtimes)/$loadingtimelimit;
$loadingtimes = $loadingtimes + 0.9;

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

//THIS ISNT IN USE AS EVERYTHINGS NOT AUTOMATED THROUGH JQUERY
//if($nopostfound==1){$dontshowselectctn='style="display:none;"';$shownopostmsg = '';}else{$dontshowselectctn='';$shownopostmsg = 'style="display:none;"';}


if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order2-select-5.html');

//if($_SERVER['HTTP_X_FORWARDED_FOR'] == '212.159.178.222'){$body = file_get_contents('order2-select-5b.html');}
//if($_GET['rabban']=='true')$body = file_get_contents('order2-select-3.html');

$tpl = str_replace('{body}', $body, $tpl);
//$tpl = str_replace('{dontshowselectctn}', $dontshowselectctn, $tpl); //NOT IN USE
//$tpl = str_replace('{shownopostmsg}', $shownopostmsg, $tpl); //NOT IN USE
$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
if($packagetype=='freelikes')
	$tpl = str_replace('{back}', '/'.$loclinkforward.'freelikes/', $tpl);
else{
	$tpl = str_replace('{back}', '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/', $tpl);
}
//$tpl = str_replace('{imgs}', $imgs, $tpl);//NOT IN USE
$tpl = str_replace('{postlimit}', $postlimit, $tpl);
$tpl = str_replace('{maxamount}', $maxamount, $tpl);
$tpl = str_replace('{selectedlistmt}', $selectedlistmt, $tpl);  	
$tpl = str_replace('{selectedlist}', $selectedlist, $tpl);
$tpl = str_replace('{selectedlist_image}', $selectedlist_image, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{packagetype}', $packagetype, $tpl);  	
$tpl = str_replace('{username}', $info['igusername'], $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);
$tpl = str_replace('{videosonly}', $videosonly, $tpl);
$tpl = str_replace('{errorstyle}', $errorstyle, $tpl);
$tpl = str_replace('{packagedesc}', $packagedesc, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{loadtime}', $loadingtimes, $tpl);
$tpl = str_replace('{changepackagehref}', '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/', $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);
$tpl = str_replace('{style_free_likes}', $style_free_likes, $tpl);

if($packagetype=='likes' || $packagetype=='freelikes')$tpl = str_replace('{divboxpckg}', '{divboxlikespckg}', $tpl);
if($packagetype=='views')$tpl = str_replace('{divboxpckg}', '{divboxviewspckg}', $tpl);
if($packagetype=='comments'){
	$tpl = str_replace('{divboxpckg}', '{divboxcommentspckg}', $tpl);
	$tpl = str_replace('{selectedposts}', '<span class="amount_of_posts">0</span> <b>posts</b> Selected / <span class="likes_per_post">0</span> <b>comments</b> per post', $tpl);
}
$tpl = str_replace('{postURL}', $postURL, $tpl);


$styleNoneForIframe = '';

if($packagetype =='freelikes'){

	$tpl = str_replace('{nextbtn}',"Continue",$tpl);
	$styleNoneForIframe = 'display:none;';
	$styleblockForIframe = "display:block !important";
}

// from admin test post grabber
if(!empty($_GET['type']) && $_GET['type'] == 'testPG'){

    $stylePGIframe = 'display:none;';
	
}

$tpl = str_replace('{styleblockForIframe}', $styleblockForIframe, $tpl);
$tpl = str_replace('{styleNoneForIframe}', $styleNoneForIframe, $tpl);
$tpl = str_replace('{stylePGIframe}', $stylePGIframe, $tpl);
$tpl = str_replace('{paramGet}', $_GET['type'], $tpl);

$req_uri = $_SERVER['REQUEST_URI'];
$afterDomain = substr($req_uri,0,strrpos($req_uri,'/'));
$tpl = str_replace('{page_url}', $afterDomain.'/', $tpl);
$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order1-select') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){

if($packagetype =='freelikes'){

		if($cinfo['name']=='maxlimitreached'){
	
			$tpl = str_replace('{'.$cinfo['name'].'}',"Maximum of 1 post reached",$tpl);
	
		}
}	

$foundcontent=0;


if($cinfo['name']=='packagedesc')

	{

		$cinfo['content'] = str_replace('$packageinfo[\'amount\']',$packageinfo['amount'],$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;

	}



if($foundcontent==0)$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);


}

$tpl = str_replace('{freelikes Package}', ' Free Likes', $tpl);

if($packageinfo['socialmedia'] == 'tt'){
	$tpl = str_ireplace("Instagram", "Tiktok", $tpl);
}
$tpl = str_ireplace("{socialMediaType}", $packageinfo['socialmedia'], $tpl);

sendCloudwatchData('Superviral', 'order-select', 'UserFunnel', 'user-funnel-order-select-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Superviral', 'page-load-order-select', 'PageLoadTiming', 'page-load-order-select-function', number_format($execution_time_sec, 2));


echo $tpl;
?>
