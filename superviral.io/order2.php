<?php
 
// start time
$start_time = microtime(true);


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

header('Content-type: text/html; charset=utf-8');



$db=1;

include('header.php');

include('ordercontrol.php');

$redis = new Redis();

try {
	$redis->connect('127.0.0.1', 6379);

	if($redis->exists("od_package_{$info['id']}")) {
		$redis->delete("od_package_{$info['id']}");
	}

	if($redis->exists("od_{$info['emailaddress']}")) {
		$redis->delete("od_{$info['emailaddress']}");
	}

} catch (Exception $e) {
	echo "Redis connection failed: " . $e->getMessage();
}

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}


if($_COOKIE['discount']=='on'){



	$discounton = '<div class="summary thewidth">

                            <div class="thewidthleft"><span class="package">{exclusivediscount}</span></div>

                            <div class="thewidthright"><font style="font-style:italic;">{exclusivediscountpercent}</font></div>

                    </div>';





}

// check if duplicate session
$checkq = mysql_query("SELECT * FROM `order_session` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' AND `packageid` = '{$info['packageid']}'");  
if(mysql_num_rows($checkq)=='2'){mysql_query("DELETE FROM `order_session` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' AND `packageid` = '{$info['packageid']}' LIMIT 1 ");}


//FIND OUT WHAT THE PACKAGE INFORMATION IS

if ($redis->exists("or_packageinfo_{$info['id']}")) {
	$packageinfo = json_decode($redis->get("or_packageinfo_{$info['id']}"), true);

} else {
		$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1"));

		// reddis set
		$redis->setex("or_packageinfo_{$info['id']}", 600, json_encode($packageinfo));

		// print_r(json_decode($redis->get('or_packageinfo'), true));
}
if($packageinfo['premium']=='1'){$premium = ' Premium';}else{$premium = '';}
$packagetitle = $packageinfo['amount']. $premium .' {'.ucwords($packageinfo['type']).' pckg}';

$upsellFollowerHtml = '';

$dpimgname = md5($info['order_session'].$info['igusername']);
// WHAT TYPE OF ORDER SESSION ISIT FOR? isit for views, likes and lastly followers?

if(($packageinfo['type']=='likes')||($packageinfo['type']=='views')||($packageinfo['type']=='comments') || ($packageinfo['type']=='freelikes')){



		$back = '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1select'].'/';


		if($packageinfo['type']=='comments'){
			$back = '/'.$locas[$loc]['order'].'/select-comments/';
		}
		
		if($packageinfo['socialmedia']=='tt'){

			$chooseposts = explode('~~~', $info['chooseposts_image']);

			foreach($chooseposts as $posts){

				if(empty($posts))continue;
	
				$profilepicture .= '<img class="dp" height="65" width="65" src="'.$posts.'">';
	
			}


		}else{
			$chooseposts = explode('~~~', $info['chooseposts']);

		
			$postURL = "";
			foreach($chooseposts as $posts){

			if(empty($posts))continue;

			//$postURL .= " ". $posts . " ";

			$posts1 = explode('###',$posts);

			$postmanual = $posts1[1];

			$profilepicture .= '<img class="dp" height="65" width="65" src="'.$posts1[1].'">';

		}

		}
		
		if (strpos($posts1[1], 'instagram') !== false) {
			$profilepicture = "";
		}else{
			$postmanual_display = "display:none;";
			
		}

		if($packageinfo['type']=='likes'){$upsellFollowerHtml = ' <div class="ordertbl discounttbl {animatedborderFollower} {displayNoneComments}" id="followerUpsellDiv">
			
			<div class="thewidth">
					<div class="thewidthleft"><span class="package" id="discounttitleFollowerDiv">Add <b>{discountamount_follower} Followers</b> and <b style="color:#80bd29">save 25%</b></span><span id="upsellReadyDeliveryMsgDiv">{upsellReadyDeliveryMsg}</span></div>

					<div class="thewidthright" id="upsellFollowerBtnDiv">{upsellFollowerBtn}</div>
					</div>

			</div>'; 
			$animatedborderFollower = "animatedborder";
		}




}else{



		$back = '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/';


		$postmanual_display = "display:none;";
		

		if($info['socialmedia']=='tt'){
			
			$searchfordpq = mysql_query("SELECT `dp` FROM `tt_dp` WHERE `dp` = '$dpimgname' and `dnow` = 0 LIMIT 1");
			$bucket = 'tt-dp/';

		}else{

			$searchfordpq = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' LIMIT 1");
			$bucket = 'dp/';

		}

		if(mysql_num_rows($searchfordpq)==1){$profilepicture = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/'. $bucket .$dpimgname.'.jpg">';}

		

		else{



			$profilepicture = '';

			if($info['socialmedia']=='tt'){
				$triggergetpost = 'getTTdp();';

			}else{
				$triggergetpost = 'getdp();';

			}



			}



		$changeusernamelink = '<a class="changeusernamelink" href="/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/">(change)</a>';



		$upsellFollowerHtml = '';



}

// COMPETITION PRICES
$buzzoid_price = $packageinfo['buzzoid_price'];
$instafollowes_price = $packageinfo['instafollowes_price'];
$goread_price = $packageinfo['goread_price'];





//THIS IS FOR STANDARD UPSELLS and make any changes to the database if needed

$discountamount = round($packageinfo['amount'] * 0.50);

$discountoriginal = number_format(round($packageinfo['price'] * 0.50,2),2);

$discountactual = number_format(round($discountoriginal * 0.75,2),2);

$discountamount_follower = round($packageinfo['amount'] * 0.50);

$discountoriginal_follower = number_format(round($packageinfo['price'] * 0.50,2),2);

$discountactual_follower = number_format(round($discountoriginal * 0.75,2),2);


$displayNoneComments = 'displayNoneComments';
if($packageinfo['type'] != 'comments'){
	$discounttitle = '{discountpopup}';

	if(empty($_GET['split'])){
		$splitParam = '';
	}

	$discountbtn = '<a class="btn-upsell btn greenbtn gtm-click" onclick="addUpsell(\'like\')" href="javascript:void(0);" data-click-name="upsell add likes">{discountbtn}</a>';
	$displayNoneComments = '';
}


$upsellFollowerBtn = '<a class="btn greenbtn gtm-click" data-click-name="upsell add followers" onclick="addUpsell(\'follower\')" href="javascript:void(0);">+ Add for '.$currency.$discountactual_follower.' <strike>$'. $discountoriginal_follower .'</strike></a>';


// hide for tiktok for above 5k qty

if($packageinfo['socialmedia'] == 'tt' && ($packageinfo['type'] == 'likes' || $packageinfo['type'] == 'views')){

	$displayNoneComments = 'displayNoneComments';
}



if(($_GET['new']=='true')&&($info['upsellalreadyadded']!=='1')){





$upselladd = $discountamount.'###'.$discountactual;



mysql_query("UPDATE `order_session` SET `upsell` = '$upselladd', `upsellalreadyadded` = '1' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");

header('Location: /'.$loclinkforward.'order/review/?new=true');



}







if($_GET['add']=='true'){



$upselladd = $discountamount.'###'.$discountactual;



mysql_query("UPDATE `order_session` SET `upsell` = '$upselladd' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");


if(empty($_GET['split'])){
	$splitParam = '';
}

header('Location: /'.$loclinkforward.'order/review/'.$splitParam);



}



if($_GET['add']=='false'){



mysql_query("UPDATE `order_session` SET `upsell` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");


if(empty($_GET['split'])){
	$splitParam = '';
}

header('Location: /'.$loclinkforward.'order/review/'. $splitParam);



}


if($_GET['addfollower']=='true'){



	$upselladdfollower = $discountamount_follower.'###'.$discountactual_follower;
	
	
	
	mysql_query("UPDATE `order_session` SET `upsell_all` = '$upselladdfollower' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
	

	if(empty($_GET['split'])){
		$splitParam = '';
	}
	
	header('Location: /'.$loclinkforward.'order/review/'.$splitParam);
	
	
	
	}
	
	
	
	if($_GET['addfollower']=='false'){
	
		if(empty($_GET['split'])){
			$splitParam = '';
		}
	
	mysql_query("UPDATE `order_session` SET `upsell_all` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
	
	header('Location: /'.$loclinkforward.'order/review/'. $splitParam);
	
	
	
	}


////////////////////////////////







//THIS IS FOR AUTO LIKES UPSELL and make add onto the database if any changes are made



$discountamount2 = $auto_likes['likes_per_post'];

$discountoriginal2 = $auto_likes['original_price'];

$discountactual2 = $auto_likes['price'];



$discounttitleautolikes = '<div class="bftag">{altag1}</div>{altag2}</b>';

$discountbtnautolikes = '<a class="btn greenbtn gtm-click" href="?addautolikes=true" data-click-name="upsell add autolikes">{aladdfor}</a>';







if($_GET['addautolikes']=='true'){



$upselladd_autolikes = $discountamount2.'###'.$discountactual2.'###'.$auto_likes['likes_per_post'].'###'.$auto_likes['max_per_day'].'###'.$auto_likes['price'].'###'.$auto_likes['original_price'].'###'.$auto_likes['save'];



mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '$upselladd_autolikes' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");

header('Location: /'.$loclinkforward.'order/review/');





}



if($_GET['addautolikes']=='false'){



mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");

header('Location: /'.$loclinkforward.'order/review/');





}



///////////////////////////////



















//DISPLAY STANDARD UPSELL

$animatedborderLikes = "animatedborder";

if(!empty($info['upsell'])){





$discounttitle .= '<div class="tickadded"><span class="tick"><img src="/imgs/check.svg" alt="check"></span><span style="">{upselladded}</span></div><div class="loaderadded"><span class="loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="30" height="30" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="39.269908169872416 39.269908169872416" stroke="#fb5343" stroke-width="10" r="25" cy="50" cx="50">
                            <animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="0.7092198581560283s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
                          </circle><g></g></g></svg></span><span>Hang tight, we\'re getting your order ready..</span></div>';


if(empty($_GET['split'])){
	$splitParam = '';
}

$discountbtn = $currency.$discountactual.$locas[$loc]['currencyend'].'<br><a class="remove" onclick="upsellRemove(\'like\')" href="javascript:void(0);">{upsellremove}</a>';



//Additional '.$discountamount.' Followers



$summaryupsell1 = '<span class="package ups" style="display: block;margin-top: 13px;color:#008000!important">{additionalfollowers}</span>';

$summaryupsell2 = '<span class="ups1" style="display: block;margin-top: 19px;color: black;">{discountactual}</span><a onclick="upsellRemove(\'like\')" href="javascript:void(0);" style="    position: absolute;right: -35px;bottom: 9px;">

<svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a>';



$summaryupsell2 = '<div class="ups1">{discountactual}<a onclick="upsellRemove(\'like\')" href="javascript:void(0);" style="    position: absolute;right: -35px;bottom: 9px;">

<svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a></div>';



$setdiscount = $discountactual;

$animatedborderLikes = "";
}





$upsellSubTotal = '';
$upsellReadyDeliveryMsg = '';
// for follower upselladd
if(!empty($info['upsell_all'])){



	$upsellReadyDeliveryMsg = '<div class="tickadded"><span class="tick"><img src="/imgs/check.svg" alt="check"></span><span style="">Added - ready for delivery</span></div><div class="loaderadded"><span class="loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="30" height="30" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="39.269908169872416 39.269908169872416" stroke="#fb5343" stroke-width="10" r="25" cy="50" cx="50">
                            <animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="0.7092198581560283s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
                          </circle><g></g></g></svg></span><span>Hang tight, we\'re getting your order ready..</span></div>';



	if(empty($_GET['split'])){
		$splitParam = '';
	}
	
	$upsellFollowerBtn = $currency.$discountactual_follower.'<br><a class="remove" onclick="upsellRemove(\'follower\')" href="javascript:void(0);">Remove</a>';
	
	$upsellSubTotal = '<div class="thewidthleft" style="padding-top: 0px;"><span class="package ups" style="display: block;color:#008000!important">Additional '. $discountamount_follower .' Followers</span></div>
       
	<div class="thewidthright" style="padding-top: 0px;">
		<div class="ups1" style="    padding-top: 0px;">+ '. $currency .$discountactual_follower.'<a onclick="upsellRemove(\'follower\')" href="javascript:void(0);"
				style="    position: absolute;right: -35px;bottom: 9px;">
	
				<svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15"
					height="15" xmlns="http://www.w3.org/2000/svg">
					<path
						d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z">
					</path>
				</svg></a>
		</div>
	</div>';
	
	//Additional '.$discountamount.' Followers
	
	$animatedborderFollower = "";
	
	// $summaryupsell1 = '<span class="package ups" style="display: block;margin-top: 13px;color:#008000!important">{additionalfollowers}</span>';
	
	// $summaryupsell2 = '<span class="ups1" style="display: block;margin-top: 19px;color: black;">{discountactual}</span><a href="?addfollower=false" style="    position: absolute;right: -35px;bottom: 9px;">
	
	// <svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a>';
	
	
	
	// $summaryupsell2 = '<div class="ups1">{discountactual}<a href="?addfollower=false" style="    position: absolute;right: -35px;bottom: 9px;">
	
	// <svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a></div>';
	
	
	
	$setdiscount2 = $discountactual_follower;
	
}






////////////////////////////////////



$checkalq = mysql_query("SELECT * FROM `automatic_likes` WHERE `brand`='sv' AND `disabled` = '0' AND `igusername` = '{$info['igusername']}' LIMIT 1");

if(mysql_num_rows($checkalq)==1){



	$nowal = time();

	$checkalq = mysql_query("SELECT * FROM `automatic_likes` WHERE `brand`='sv' AND `igusername` = '{$info['igusername']}' AND `expires` > '$nowal' LIMIT 1");

}


if ($redis->exists('al_' .$info['emailaddress'] .'_'. $info['igusername'] .'_count')) {

	$num_alfreeq = $redis->get('al_' .$info['emailaddress'] .'_'. $info['igusername'] .'_count');
	// echo 'exists' . $num_alfreeq;
}else{
	$checkalfreeq = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `brand`='sv' AND (`igusername` LIKE '%{$info['igusername']}%' OR `emailaddress` LIKE '%{$info['emailaddress']}%') LIMIT 1");//THIS NEEDS TO BE 0 to show the auto likes box
	$num_alfreeq = mysql_num_rows($checkalfreeq);
}




if($loggedin==true){

  



if($userinfo['freeautolikes']==1){//DONT DISPLAY THE AUTO LIKES

$autolikesoffer = 'style="display:none"';



if(!empty($info['upsell_autolikes'])){mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '' WHERE `id` = '{$info['id']}' LIMIT 1");}



$info['upsell_autolikes'] = '';}



$searchordersessionsforalq = mysql_query("SELECT * FROM `order_session` WHERE `brand`='sv' AND `upsell_autolikes` != '' AND `order_session` != '{$info['order_session']}' AND `account_id` = '{$userinfo['id']}' LIMIT 2");



if(mysql_num_rows($searchordersessionsforalq)==2)$autolikesoffer = 'style="display:none"';



}





if($num_alfreeq!==0){



$autolikesoffer = 'style="display:none"';



if(!empty($info['upsell_autolikes'])){mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '' WHERE `id` = '{$info['id']}' LIMIT 1");}



$info['upsell_autolikes'] = '';



}


// hide for tiktok services
if($info['socialmedia'] == 'tt'){
	
	$autolikesoffer = 'style="display:none"';
}

//DISPLAY AUTO LIKES UPSELL, once auto likes upsell is applied, the price from the order_session table is shown on here

if(!empty($info['upsell_autolikes'])){





$autolikesdesc = 'style="display:none;"';



$upsell_autolikesdb = explode('###', $info['upsell_autolikes']);

$upsell_autolikesdbprice = $upsell_autolikesdb[1];



$discounttitleautolikes .= '<div class="tickadded"><span class="tick"><img src="/imgs/check.svg" alt="check"></span><span style="">{upselladded}</span></div>';



$discountbtnautolikes = $currency.$upsell_autolikesdbprice.$locas[$loc]['currencyend'].'<br><a class="remove" href="?addautolikes=false">{upsellremove}</a>';



//LEFT OF summary table

$summaryautolikes1 = '<span class="package ups" style="display: block;margin-top: 13px;color:#008000!important">Automatic likes</span>';



//RIGHT OF summary table

$summaryautolikes2 = '<div class="ups1">+ '.$currency.$upsell_autolikesdbprice.$locas[$loc]['currencyend'].'<a href="?addautolikes=false" style="    position: absolute;right: -35px;bottom: 9px;">

<svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a></div>';



$setdiscount1 = $upsell_autolikesdbprice;









}

else{



	

}





/////////////////////////////////

// UI manipulation
function formatCount($count) {
    if ($count >= 5000) {
        return number_format($count / 1000, ($count % 1000 !== 0 ? 1 : 0)) . 'K';
    }
    return $count;
}

$socialmedia = $packageinfo['socialmedia'];
$packgtype = $packageinfo['type'];

switch ("$socialmedia-$packgtype") {
    case "ig-likes":
			
		if($_GET['split']=='b'){
			
	
			$searchfordpq = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' LIMIT 1");
			$bucket = 'dp/';
			if (mysql_num_rows($searchfordpq) == 1) {
				$profilepictureNew = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/' . $bucket . $dpimgname . '.jpg">';
				$hideplaceholder = 'style="display:none;"';
			} else {
				$profilepictureNew = '';
				$triggergetpost = 'getdp();';

			}
		}
		$chooseposts = explode('~~~', $info['chooseposts']);
		$amountOfPost = count($chooseposts);	

		$selected_htm = '';
		$first = 0;
		foreach($chooseposts as $posts){

			if(empty($posts))continue;

			$code = explode('###',$posts)[0];
			$post = explode('###',$posts)[1];
			if($first == 0){
				$first_src = $post;
			}

			$selected_htm .= '<div class="item"><img src="'. $post .'" alt="thumbnail"></div>';
                  
			$findpostfile = mysql_query("SELECT * FROM `ig_thumbs` WHERE `shortcode` = '$code' LIMIT 1");

			if(mysql_num_rows($findpostfile)=='1'){

				$PostData = mysql_fetch_array($findpostfile);
				$like_count = $PostData['like_count'];
				$comment_count = $PostData['comment_count'];
			}

			$first++;

		}
		if(empty($like_count))$like_count = 100;
		if(empty($comment_count))$comment_count = 100;
		$initialValue = $like_count;

		$headHtml = ' <div id="ig-likes-preview" class="preview-content preview-hero-wrapper">
                    <div class="preview-hero">
                        <img src="'. $first_src .'" width="196px" height="364px" alt="preview-hero">
						<span class="username">@{igusername}</span>
                        <div class="stats">
							<div id="loadpic">
								<div class="placeholder" '. $hideplaceholder .'></div>
								'. $profilepictureNew .'
							</div>
                            <div class="item special">
                                <div class="counter-container">
                                    <img class="follower-img" src="/imgs/order-preview/fol1.jpg" alt="follower-img1">
                                    <img class="follower-img" src="/imgs/order-preview/fol2.jpg" alt="follower-img2">
                                    <img class="follower-img" src="/imgs/order-preview/fol3.jpg" alt="follower-img3">
                                    <img class="follower-img" src="/imgs/order-preview/fol4.jpg" alt="follower-img4">
                                    <img class="follower-img" src="/imgs/order-preview/fol5.jpg" alt="follower-img5">
                                    <img class="follower-img" src="/imgs/order-preview/fol6.jpg" alt="follower-img6">
                                    <img class="follower-img" src="/imgs/order-preview/fol7.jpg" alt="follower-img7">
                                    <img class="follower-img" src="/imgs/order-preview/fol8.jpg" alt="follower-img8">
                                    <img class="follower-img" src="/imgs/order-preview/fol9.png" alt="follower-img9">
                                    <img class="follower-img" src="/imgs/order-preview/fol10.png" alt="follower-img10">
                                </div>
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.00001 1.84929C6.40054 -0.120541 3.7278 -0.729304 1.72376 1.07447C-0.280285 2.87824 -0.562427 5.89404 1.01136 8.02739C2.31986 9.80106 6.27983 13.542 7.5777 14.7528C7.72285 14.8883 7.79547 14.956 7.88018 14.9826C7.95405 15.0058 8.03494 15.0058 8.1089 14.9826C8.19361 14.956 8.26614 14.8883 8.41138 14.7528C9.70925 13.542 13.6692 9.80106 14.9777 8.02739C16.5515 5.89404 16.3037 2.85927 14.2653 1.07447C12.2268 -0.710329 9.59947 -0.120541 8.00001 1.84929Z" fill="url(#paint0_linear_0_58)"/>
                                        <defs>
                                            <linearGradient id="paint0_linear_0_58" x1="16" y1="1.01272e-06" x2="-2.5057" y2="4.3567" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#6A00FF"/>
                                            <stop offset="1" stop-color="#BF00FF"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="num counter">0</div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <g opacity="0.5">
                                        <path d="M3.75 12.4595V12.0696L3.43083 11.8456C1.77256 10.6819 0.75 8.92969 0.75 7C0.75 3.63964 3.89903 0.75 8 0.75C12.101 0.75 15.25 3.63964 15.25 7C15.25 10.3608 12.101 13.25 8 13.25C7.53405 13.25 7.07609 13.2077 6.62656 13.1335L6.35272 13.0883L6.11543 13.2323L3.75 14.6676V12.4595Z" stroke="white" stroke-width="1.5"/>
                                        </g>
                                    </svg>
                                </div>
                                <div class="num">'. $comment_count .'</div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path opacity="0.5" d="M17 1L1 6.33334L8.66664 9.33336M17 1L11.6666 17L8.66664 9.33336M17 1L8.66664 9.33336" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="selected-items">
                    '. $selected_htm .'
                </div>';
        
    break;
	case "ig-followers":

		if($_GET['split']=='b'){
			
	
			$searchfordpq = mysql_query("SELECT `dp` FROM `ig_dp` WHERE `dp` = '$dpimgname' LIMIT 1");
			$bucket = 'dp/';
			if (mysql_num_rows($searchfordpq) == 1) {
				$hideplaceholder = 'style="display:none;"';
				$profilepictureNew = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/' . $bucket . $dpimgname . '.jpg">';
			} else {
				$profilepictureNew = '';
				$triggergetpost = 'getdp();';

			}
		}
		$amountOfPost = 1;
    

			$fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE `ig_username` = '{$info['igusername']}' LIMIT 1");

			if (mysql_num_rows($fetchuseridq) == '1') {

				$userData = mysql_fetch_array($fetchuseridq);
				$follower_count = $userData['followers'];
				$following_count = $userData['following'];
				$media_count = $userData['media_count'];
				$media_count = formatCount($media_count);

			} else {

				$follower_count = 100;
				$following_count = 100;
				$media_count = 100;
			}
			
		
		if(empty($follower_count))$follower_count = 100;
		$initialValue = $follower_count;

		$headHtml = '<div id="ig-followers-preview" class="preview-content flex-box">
                    <div>
                        <div id="loadpic">
							<div class="placeholder" '. $hideplaceholder .'></div>
							'. $profilepictureNew .'
						</div>
                        <span class="username">@{igusername}</span>
                    </div>
                    <div class="stats">
                        <div class="num">'. $media_count .'</div>
                        <div class="label">Posts</div>
                    </div>
                    <div class="stats special">
                        <div class="counter-container">
                            <img class="follower-img" src="/imgs/order-preview/fol1.jpg" alt="follower-img1">
                            <img class="follower-img" src="/imgs/order-preview/fol2.jpg" alt="follower-img2">
                            <img class="follower-img" src="/imgs/order-preview/fol3.jpg" alt="follower-img3">
                            <img class="follower-img" src="/imgs/order-preview/fol4.jpg" alt="follower-img4">
                            <img class="follower-img" src="/imgs/order-preview/fol5.jpg" alt="follower-img5">
                            <img class="follower-img" src="/imgs/order-preview/fol6.jpg" alt="follower-img6">
                            <img class="follower-img" src="/imgs/order-preview/fol7.jpg" alt="follower-img7">
                            <img class="follower-img" src="/imgs/order-preview/fol8.jpg" alt="follower-img8">
                            <img class="follower-img" src="/imgs/order-preview/fol9.png" alt="follower-img9">
                            <img class="follower-img" src="/imgs/order-preview/fol10.png" alt="follower-img10">
                        </div>
                        <div class="num counter">'. $follower_count .'</div>
                        <div class="label">Followers</div>
                    </div>
                    <div class="stats">
                        <div class="num">'. $following_count .'</div>
                        <div class="label">Following</div>
                    </div>
                </div>';
			
	break;
	case "tt-likes":
			if($_GET['split']=='b'){
				$searchfordpq = mysql_query("SELECT `dp` FROM `tt_dp` WHERE `dp` = '$dpimgname' and `dnow` = 0 LIMIT 1");
				$bucket = 'tt-dp/';
			
				if (mysql_num_rows($searchfordpq) == 1) {
					$profilepictureNew = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/' . $bucket . $dpimgname . '.jpg">';
				} else {
				
					$profilepictureNew = '';
					$triggergetpost = 'getTTdp();';
					
				}
			}
			$selected_htm = '';
			$first = 0;

			$chooseposts = explode('~~~', $info['chooseposts_image']);

			foreach($chooseposts as $posts){

				if(empty($posts))continue;
				if($first == 0){
					$first_src = $posts;
				}
				$selected_htm .= '<div class="item"><img src="'. $posts .'" alt="thumbnail"></div>';
				$first++;
			}


			$chooseposts = explode('~~~', $info['chooseposts']);
			$amountOfPost = count($chooseposts);	

			foreach($chooseposts as $posts){

				if(empty($posts))continue;
				
				$parts = explode("/", rtrim($posts, "/"));
				$code = end($parts);
			
				$findpostfile = mysql_query("SELECT * FROM `tt_thumbs` WHERE `shortcode` = '$code' LIMIT 1");

				if(mysql_num_rows($findpostfile)=='1'){

					$PostData = mysql_fetch_array($findpostfile);
					$like_count = $PostData['like_count'];
					$comment_count = $PostData['comment_count'];
				}
				
				
			}
			if(empty($like_count))$like_count = 100;
			if(empty($comment_count))$comment_count = 100;
			$initialValue = $like_count;

			$headHtml = '<div id="tt-likes-preview" class="preview-content preview-hero-wrapper">
                    <div class="preview-hero">
                        <img src="'. $first_src .'" width="196px" height="364px" alt="preview-hero">
						<span class="username">@{igusername}</span>
                        <div class="top-label-box">
                            <div class="item">Following</div>
                            <div class="item">For you</div>
                        </div>
                        <div class="stats">
							<div id="loadpic">
								<div class="placeholder"></div>
								'. $profilepictureNew .'
							</div>
                            <div class="item special">
                                <div class="counter-container">
                                    <img class="follower-img" src="/imgs/order-preview/fol1.jpg" alt="follower-img1">
                                    <img class="follower-img" src="/imgs/order-preview/fol2.jpg" alt="follower-img2">
                                    <img class="follower-img" src="/imgs/order-preview/fol3.jpg" alt="follower-img3">
                                    <img class="follower-img" src="/imgs/order-preview/fol4.jpg" alt="follower-img4">
                                    <img class="follower-img" src="/imgs/order-preview/fol5.jpg" alt="follower-img5">
                                    <img class="follower-img" src="/imgs/order-preview/fol6.jpg" alt="follower-img6">
                                    <img class="follower-img" src="/imgs/order-preview/fol7.jpg" alt="follower-img7">
                                    <img class="follower-img" src="/imgs/order-preview/fol8.jpg" alt="follower-img8">
                                    <img class="follower-img" src="/imgs/order-preview/fol9.png" alt="follower-img9">
                                    <img class="follower-img" src="/imgs/order-preview/fol10.png" alt="follower-img10">
                                </div>
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.00001 1.84929C6.40054 -0.120541 3.7278 -0.729304 1.72376 1.07447C-0.280285 2.87824 -0.562427 5.89404 1.01136 8.02739C2.31986 9.80106 6.27983 13.542 7.5777 14.7528C7.72285 14.8883 7.79547 14.956 7.88018 14.9826C7.95405 15.0058 8.03494 15.0058 8.1089 14.9826C8.19361 14.956 8.26614 14.8883 8.41138 14.7528C9.70925 13.542 13.6692 9.80106 14.9777 8.02739C16.5515 5.89404 16.3037 2.85927 14.2653 1.07447C12.2268 -0.710329 9.59947 -0.120541 8.00001 1.84929Z" fill="url(#paint0_linear_0_58)"/>
                                        <defs>
                                            <linearGradient id="paint0_linear_0_58" x1="16" y1="1.01272e-06" x2="-2.5057" y2="4.3567" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#6A00FF"/>
                                            <stop offset="1" stop-color="#BF00FF"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="num counter">120K</div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C3.582 0 0 3.1345 0 7C0 9.2095 1.1725 11.177 3 12.4595V16L6.5045 13.8735C6.9895 13.9535 7.4885 14 8 14C12.418 14 16 10.866 16 7C16 3.1345 12.418 0 8 0Z" fill="white"/>
                                    </svg>
                                </div>
                                <div class="num">'. $comment_count .'</div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="15" viewBox="0 0 19 15" fill="none">
                                        <path d="M19.0001 7.59972L10.281 0.526611V4.37019C9.64068 4.37019 8.92992 4.37019 8.14565 4.37019C0.404982 4.37019 -1.1076 10.0643 0.672021 14.2461C1.02775 10.4202 5.94354 10.5091 9.48026 10.5091C9.74919 10.5091 10.0163 10.5091 10.281 10.5091V14.6732L19.0001 7.59972Z" fill="white"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="selected-items">
                   '. $selected_htm .'
                </div>';
	break;
	case "tt-followers":

		if($_GET['split']=='b'){
			$searchfordpq = mysql_query("SELECT `dp` FROM `tt_dp` WHERE `dp` = '$dpimgname' and `dnow` = 0 LIMIT 1");
			$bucket = 'tt-dp/';
		
			if (mysql_num_rows($searchfordpq) == 1) {
				$profilepictureNew = '<img class="dp dp2" height="65" width="65" src="https://cdn.superviral.io/' . $bucket . $dpimgname . '.jpg">';
			} else {
			
				$profilepictureNew = '';
				$triggergetpost = 'getTTdp();';
				
			}
		}
		$amountOfPost = 1;

			$fetchuseridq = mysql_query("SELECT * FROM `tt_searchbyusername` WHERE `tt_username` = '{$info['igusername']}' LIMIT 1");
			if (mysql_num_rows($fetchuseridq) == '1') {
				$userData = mysql_fetch_array($fetchuseridq);
				$follower_count = $userData['followers'];
				$following_count = $userData['following'];
				$media_count = $userData['media_count'];
				$media_count = formatCount($media_count);

			} else {

				$follower_count = 100;
				$following_count = 100;
				$media_count = 100;
			}
    
		if(empty($follower_count))$follower_count = 100;
		$initialValue = $follower_count;

		$headHtml = '<div id="tt-followers-preview" class="preview-content">
                    <div>
                        <div id="loadpic">'. $profilepictureNew .'</div>
                        <span class="username">@{igusername}</span>
                    </div>
                    <div class="flex-box">
                        <div class="stats">
                            <div class="num">'. $media_count .'</div>
                            <div class="label">Posts</div>
                        </div>
                        <div class="stats special">
                            <div class="counter-container">
                                <img class="follower-img" src="/imgs/order-preview/fol1.jpg" alt="follower-img1">
                                <img class="follower-img" src="/imgs/order-preview/fol2.jpg" alt="follower-img2">
                                <img class="follower-img" src="/imgs/order-preview/fol3.jpg" alt="follower-img3">
                                <img class="follower-img" src="/imgs/order-preview/fol4.jpg" alt="follower-img4">
                                <img class="follower-img" src="/imgs/order-preview/fol5.jpg" alt="follower-img5">
                                <img class="follower-img" src="/imgs/order-preview/fol6.jpg" alt="follower-img6">
                                <img class="follower-img" src="/imgs/order-preview/fol7.jpg" alt="follower-img7">
                                <img class="follower-img" src="/imgs/order-preview/fol8.jpg" alt="follower-img8">
                                <img class="follower-img" src="/imgs/order-preview/fol9.png" alt="follower-img9">
                                <img class="follower-img" src="/imgs/order-preview/fol10.png" alt="follower-img10">
                            </div>
                            <div class="num counter">'. $follower_count .'</div>
                            <div class="label">Followers</div>
                        </div>
                        <div class="stats">
                            <div class="num">'. $following_count .'</div>
                            <div class="label">Following</div>
                        </div>
                    </div>
                </div>';
					
	break;
    default:
		$dispNoneNewUi = 'display:none;';
        $oldHtml = ' <div class="ordertbl">



                <div class="orderheader thewidth">







                    <span class="thewidthleft">{tblitem}</span>



                    <span class="thewidthright">{tblprice}</span>







                </div>







                <div class="thewidth">











                    <div class="thewidthleft"><span class="package package-title">{packagetitle}</span><span
                            class="username">@{igusername} {changeusernamelink}</span>

                        <div id="loadpic">{profilepicture}</div>

                        <div id="loadurl">{postURL}</div>


                        <div id="postmanual" style="{postmanual_display}">
                            <a style="display:inline-block;text-decoration: underline;" href="{postmanual}"
                                rel="nofollow" target="_blank">1 post</a> selected
                        </div>

                        <div class="tickadded"><span class="tick"><img src="/imgs/check.svg"
                                    alt="check"></span><span>Ready for delivery</span></div>

                        <div class="loaderadded"><span class="loader"><svg xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="30" height="30"
                                    style="shape-rendering: auto; display: block; background: transparent;"
                                    xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <g>
                                        <circle stroke-linecap="round" fill="none"
                                            stroke-dasharray="39.269908169872416 39.269908169872416" stroke="#fb5343"
                                            stroke-width="10" r="25" cy="50" cx="50">
                                            <animateTransform values="0 50 50;360 50 50" keyTimes="0;1"
                                                dur="0.7092198581560283s" repeatCount="indefinite" type="rotate"
                                                attributeName="transform"></animateTransform>
                                        </circle>
                                        <g></g>
                                    </g>
                                </svg>
                            </span><span>Hang tight, we\'re getting your order ready..</span></div>

                    </div>



                    <div class="thewidthright">{currency}{price}{currencyend}</div>







                </div>















            </div>';
    break;
}








$totalprice = $packageinfo['price'] + $setdiscount + $setdiscount1 + $setdiscount2;

$styleSevTax = "style='display:none;'";


if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}



$locredirect = $loc.'.';

if($locredirect=='ww.')$locredirect = '';



if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}



$tpl = file_get_contents('order-template.html');

if($_GET['split'] == 'b'){
	$body = file_get_contents('split-test/order2-b.html');

}else{
	$body = file_get_contents('order2.html');

}


//if (strpos($_SERVER['REQUEST_URI'], "/us/") !== false) {$body = file_get_contents('order2-1.html');}



$tpl = str_replace('{body}', $body, $tpl);

$tpl = str_replace('{headHtml}', $headHtml, $tpl);
$tpl = str_replace('{dispNoneNewUi}', $dispNoneNewUi, $tpl);
$tpl = str_replace('{oldHtml}', $oldHtml, $tpl);

$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);

$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

$tpl = str_replace('{back}', $back, $tpl);

$tpl = str_replace('{profilepicture}',$profilepicture,$tpl);
$tpl = str_replace('{postURL}',$postURL,$tpl);

$tpl = str_replace('{triggergetpost}',$triggergetpost,$tpl);

$tpl = str_replace('{ordersession}',$info['order_session'],$tpl);

$tpl = str_replace('{igusername}',$info['igusername'],$tpl);

$tpl = str_replace('{packagetitle}',$packagetitle,$tpl);

$tpl = str_replace('{changeusernamelink}',$changeusernamelink,$tpl);

$tpl = str_replace('{styleSevTax}',$styleSevTax,$tpl);
$tpl = str_replace('{delFee}','{currency}' . $delFee,$tpl);
$tpl = str_replace('{servTax}','{currency}'. $servTax,$tpl);


if($packageinfo['type'] =='freelikes'){
	$displayNoneComments = 'displayNoneComments';
	$tpl = str_replace('{ctabtn}',"Complete Order >>",$tpl);
	$tpl = str_replace('{submitformto}','/freelikeprocess.php',$tpl); // AJ: chagne order3: cardinity, order3-new :acquired, order3-new-1: cardinity card saved

}

$tpl = str_replace('{discounttitle}',$discounttitle,$tpl);

$tpl = str_replace('{discountbtn}',$discountbtn,$tpl);

$tpl = str_replace('{summaryupsell1}',$summaryupsell1,$tpl);

$tpl = str_replace('{summaryupsell2}',$summaryupsell2,$tpl);



$tpl = str_replace('{discounttitleautolikes}',$discounttitleautolikes,$tpl);

$tpl = str_replace('{discountbtnautolikes}',$discountbtnautolikes,$tpl);

$tpl = str_replace('{summaryautolikes1}',$summaryautolikes1,$tpl);

$tpl = str_replace('{summaryautolikes2}',$summaryautolikes2,$tpl);

$tpl = str_replace('{autolikesoffer}',$autolikesoffer,$tpl);

$tpl = str_replace('{autolikesdesc}',$autolikesdesc,$tpl);



$tpl = str_replace('{packagetitle}',$packagetitle,$tpl);

$tpl = str_replace('{currency}',$currency,$tpl);

if($packageinfo['type'] != 'freelikes'){

	
	$tpl = str_replace('{currencyend}',$locas[$loc]['currencyend'],$tpl);	
	$tpl = str_replace('{price}',$packageinfo['price'],$tpl);

}else{
	$tpl = str_replace('{currency}','',$tpl);
	$tpl = str_replace('{currencyend}','',$tpl);	
	$tpl = str_replace('{price}','FREE',$tpl);
	$tpl = str_replace($currency.'FREE','<div style="display:flex;justify-content:end;gap:10px;padding:0;"><span style="color:#008000;margin:0;font-weight:bold;">FREE</span><span style="text-decoration:line-through;margin:0;">'.$currency.'1.29</span></div>',$tpl);


}


$tpl = str_replace('{discountreview}',$discountreview,$tpl);

$totalprice = sprintf('%0.2f', $totalprice);

$tpl = str_replace('{totalprice}',$totalprice,$tpl);

$tpl = str_replace('{discounton}',$discounton,$tpl);

$upsellFollowerHtml = str_replace('{upsellFollowerBtn}', $upsellFollowerBtn, $upsellFollowerHtml);
$upsellFollowerHtml = str_replace('{animatedborderFollower}', $animatedborderFollower, $upsellFollowerHtml);

$tpl = str_replace('{upsellFollowerHtml}', $upsellFollowerHtml, $tpl);
$tpl = str_replace('{upsellSubTotal}', $upsellSubTotal, $tpl);
$tpl = str_replace('{upsellReadyDeliveryMsg}', $upsellReadyDeliveryMsg, $tpl);
$tpl = str_replace('{discountamount_follower}', $discountamount_follower, $tpl);


$tpl = str_replace('{buzzoid_price}',$buzzoid_price,$tpl);
$tpl = str_replace('{instafollowers_price}',$instafollowes_price,$tpl);
$tpl = str_replace('{goread_price}',$goread_price,$tpl);
$tpl = str_replace('{animatedborderLikes}',$animatedborderLikes,$tpl);



$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$tpl = str_replace('{loclink}', $loclink, $tpl);

$splitParam = "?split=b";
if(empty($_GET['split'])){
	$splitParam = '';
}

$tpl = str_replace('{submitformto}','/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3'].'/'. $splitParam,$tpl); // AJ: chagne order3 to order3-new

$tpl = str_replace('{displayNoneComments}', $displayNoneComments, $tpl);
$tpl = str_replace('{postmanual}', $postmanual, $tpl);
$tpl = str_replace('{postmanual_display}', $postmanual_display, $tpl);

$tpl = str_replace('{increaseValue}', intval($packageinfo['amount']), $tpl);

$tpl = str_replace('{initialValue}', $initialValue, $tpl);


$req_uri = $_SERVER['REQUEST_URI'];
$afterDomain = substr($req_uri,0,strrpos($req_uri,'/'));
$tpl = str_replace('{page_url}', $afterDomain.'/', $tpl);
$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order2') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){



$foundcontent=0;



if($cinfo['name']=='additionalfollowers')



	{



		$cinfo['content'] = str_replace('ucwords($packagetype)',ucwords($packageinfo['type']),$cinfo['content']);

		$cinfo['content'] = str_replace('$discountamount',$discountamount,$cinfo['content']);

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

		$foundcontent = 1;



	}



if($cinfo['name']=='discountactual')



	{



		$cinfo['content'] = str_replace('$discountactual',$discountactual,$cinfo['content']);

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

		$foundcontent = 1;

	}





if($cinfo['name']=='discountpopup')



	{



		$cinfo['content'] = str_replace('ucwords($packagetype)',ucwords($packageinfo['type']),$cinfo['content']);

		$cinfo['content'] = str_replace('$discountamount',$discountamount,$cinfo['content']);

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

		$foundcontent = 1;

	}



if($cinfo['name']=='discountbtn')



	{



		$cinfo['content'] = str_replace('$currency',$currency,$cinfo['content']);

		$cinfo['content'] = str_replace('$locas[$loc][\'currencyend\']',$locas[$loc]['currencyend'],$cinfo['content']);

		$cinfo['content'] = str_replace('$discountactual',$discountactual,$cinfo['content']);

		$cinfo['content'] = str_replace('$discountoriginal',$discountoriginal,$cinfo['content']);

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

		$foundcontent = 1;

	}





if($cinfo['name']=='aladdfor')



	{







		$cinfo['content'] = str_replace('$currency',$currency,$cinfo['content']);

		$cinfo['content'] = str_replace('$locas[$loc][\'currencyend\']',$locas[$loc]['currencyend'],$cinfo['content']);

		$cinfo['content'] = str_replace('$discountactual',number_format(round($discountactual2,2),2),$cinfo['content']);

		$cinfo['content'] = str_replace('$discountoriginal',number_format(round($discountoriginal2,2),2),$cinfo['content']);

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

		$foundcontent = 1;

	}





if($foundcontent==0)



	{



		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);



	}



}


$tpl = str_replace('{Freelikes pckg}', ' Free Likes', $tpl);
sendCloudwatchData('Superviral', 'order-review', 'UserFunnel', 'user-funnel-order-review-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Superviral', 'page-load-order-review', 'PageLoadTiming', 'page-load-order-review-function', number_format($execution_time_sec, 2));


echo $tpl;

?>
