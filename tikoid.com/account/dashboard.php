<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');


$activelink1 = 'activelink';


include_once('../db.php');
include('auth.php');
include('header.php');
////////////////////////////////////////////////////////////////////////////////////////


$dontdisplayfreealbox = 0;

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '0' AND `brand` = 'to' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
$q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' AND `brand` = 'to' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
  `contactnumber` = '{$userinfo['freeautolikesnumber']}' OR 
  `emailaddress` = '{$userinfo['email']}' OR 
  `ipaddress` = '{$userinfo['user_ip']}' AND `brand` = 'to' LIMIT 1 ");

	if(mysql_num_rows($q)==1)$dontdisplayfreealbox = 1;



}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1 ");
if(mysql_num_rows($q)==1){$dontdisplayfreealbox = 1;}



}


if($dontdisplayfreealbox == 1){$freeautolikesdisplay = ' display:none;';}

////////////////////////////////////////////////////////////////////////////////////////



function ago($time)
{
   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
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
   }   return "$difference $periods[$j] ago";
}

function calcAccountValue($followers, $avgPostLikes){

	return round((0.64 * intval($followers)) + (1.1 * intval($avgPostLikes)));
}



if(time() > strtotime("today 06:00:00")){ // if current time greater than 06:00
	// echo "yes";
	$daycats = strtotime("today", time()) + 86400; //add +1 day
}else{
	// echo "No";
	$daycats = strtotime("today", time()); 
}

$daysago = 7;

for ($x = $daysago; $x >= 1; $x--) {

  $newdate = date('dmY', $daycats - (86400 * $x));


  $labels .= $labels[$newdate];
  $labelsbackend .= "'" . date('dS D', $daycats - (86400 * $x)) . "',";


}
$labels = rtrim($labels, ',');
/////////////////////////////////////////////////////////////

$dontdisplayfreealbox = 0;

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '0' AND `brand` = 'to' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
$q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' AND `brand` = 'to' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
  `contactnumber` = '{$userinfo['freeautolikesnumber']}' OR 
  `emailaddress` = '{$userinfo['email']}' OR 
  `ipaddress` = '{$userinfo['user_ip']}' AND `brand` = 'to' LIMIT 1 ");

	if(mysql_num_rows($q)==1)$dontdisplayfreealbox = 1;



}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1 ");
if(mysql_num_rows($q)==1){$dontdisplayfreealbox = 1;}



}


if($dontdisplayfreealbox == 1){$freeautolikesdisplay = ' display:none;';}

////////////////////////////////////////////////////////////////////////////////////////

if($_GET['passwordchange']=='true')$message1 = '<div class="emailsuccess">Password changed successfully. It\'s good to have you back!</div>';

$q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 10");

if(mysql_num_rows($q)==0){$orders = 'Once you\'ve made an order while logged in, it will show up here.';}
else{

		while($info = mysql_fetch_array($q)){


			if($info['fulfilled']=='0'){
				$class = 'pending';
				$status = 'delivery in progress';
			}else{
				
				$now = time();
				$betweendays = time() - (86400 * 2);
				 if(($betweendays <= $info['fulfilled']) && ($info['fulfilled'] <= $now)){
				 	//its between 3-days
				 	$class = 'complete complete-a';
				 }else{$class = 'complete';}

				$status = 'delivered '.date("D NS F Y",$info['fulfilled']);
			}



							if(($info['packagetype'] !== "freefollowers")&&($info['packagetype'] !== "freelikes"))	{

									$receipt ='
									<form class="mobileshidethis" method="post" action="/receipt-pdf-generator.php" >
										<input name="orderCountry" type="hidden" value="'. $info['country'] .'">
										<input name="orderAmount" type="hidden" value="'. $info['amount'] .'">
										<input name="billingName" type="hidden" value="'. $info['payment_billingname'] .'">
										<input name="billingEmail" type="hidden" value="'. $info['emailaddress'] .'">
										<input name="orderID" type="hidden" value="'. $info['id'] .'"><input name="billingCard" type="hidden" value="'. $info['lastfour'] .'">
										<input name="orderDate" type="hidden" value="'. date("l j/n/Y",$info['added']) .'">
										<input name="orderPrice" type="hidden" value="'. intval($info['price']/100) .'.00">
										<input name="packageType" type="hidden" value="'. $info['packagetype'] .'">
										<img class="receipt" onclick="$(this).closest(\'form\').submit();" src="/imgs/bill.png" style="height: 26px;">
									</form>';
								}
								



			$orders .= '
			<div class="history-container">
          <div class="history-stats '.$class.'">
            <h2>+'.$info['amount'].' '.$info['packagetype'].'
            <div class="spinholder"><img class="spinning" src="/imgs/inprogressgreen.svg"></div></h2>
            <p class="status ">'.$status.'</p>
            <p class="orderdesc">#'.$info['id'].' - @'.$info['igusername'].'</p>
          </div>
          <div class="tracking-btn-sec">
            <button onclick="location.href=\'/'.$loclinkforward.'track-my-order/'.$info['order_session'].'/'.$info['id'].'\';" class="btn-rounded">View tracking</button>
            <button onclick="location.href=\'/'.$loclinkforward.'order/choose/?setorder='.$info['order_session'].'&discounton=no\';" class="btn-rounded">Re-order</button>

						'.$receipt.'

          </div>
      </div>';

		unset($receipt);
		unset($class);
		unset($status);

		}

}




////////////////////////////


$findsubcriptonsq = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' ORDER BY `id` DESC");

if(mysql_num_rows($findsubcriptonsq)!==0){

	while($subsinfo = mysql_fetch_array($findsubcriptonsq)){

		$fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$subsinfo['igusername']}%' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 1");
		$fetchimg = mysql_fetch_array($fetchimgq);


		if(empty($fetchimg['dp'])){$dp = '/imgs/placeholder.jpg';}else{$dp = 'https://cdn.superviral.io/dp/'.$fetchimg['dp'].'.jpg';}

		if($subsinfo['disabled']=='1'){

			$status = 'Paused';
			$statuspaused = 'statuspaused';

			}else{
		
		$status = '<span class="livebox"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="livesvg"><path d="M256 0C115.4 0 0 115.4 0 256s115.4 256 256 256 256-115.4 256-256S396.6 0 256 0z"></path></svg> active</span> ';}

		if($subsinfo['cancelbilling']=='3'){$additionalstatus = ' (expires on '.date("d/m/Y",$subsinfo['expires']).')';}


$autolikesonaccount .= '
       <div class="subscriptions">
            <img class="dp" src="'.$dp.'">
            <div class="substitle subtitlemain"><b>'.$subsinfo['likes_per_post']. ' likes per post</b>
              <font class="username"> @'.$subsinfo['igusername'].'</font>
            </div>
            <div class="substitle">
              <div class="status '.$statuspaused.'">'.$status.$additionalstatus.'</div>
              <a href="/'.$loclinkforward.'account/edit/'.$subsinfo['md5'].'" class="btn btn3 savingcardbtn">Edit auto
                likes</a>
            </div>
          </div>';


		unset($cardbrandset);
		unset($makeprimary);
		unset($primaryclass);
		unset($expiredmsg);
		unset($status);
		unset($additionalstatus);
		unset($statuspaused);

		}

} else {

	
	$autolikesonaccount = 'It seems you do not have an Automatic Likes plan active. <a style="text-decoration:underline;" href="/'.$loclinkforward.'account/automatic-likes/">Click here to get automatic likes!</a><br><br>';


}

// /////////////////////// Page data display code starts


$followers = 0;
$avgPostLikes = 0;
$accountValue = 0;
$getUserid = addslashes($_GET['userid']);
$accountId = $userinfo['id'];

if($getUserid == "" || $getUserid == null) {
	$userName = $userinfo['current_ig_username'];
	if($userName == "" || $userName == null) {
		$checkUserExistQuery = mysql_query("SELECT username from account_usernames where account_id ='$accountId' AND `brand` = 'to' order by id desc limit 1");
		$checkUserExistData = mysql_fetch_array($checkUserExistQuery);
		$userName = $checkUserExistData['username'];
	}
}else{

	$checkUserExistQuery = mysql_query("SELECT username from account_usernames where id='$getUserid' AND `brand` = 'to'");
	$checkUserExistData = mysql_fetch_array($checkUserExistQuery);
	$userName = $checkUserExistData['username'];
}

$hideAll = "style='display:block'";
$proceedFurther = 1;
if($userName == "" || $userName == null){
	$hideAll = "style='display:none'";
	$proceedFurther = 0;
}else{
	
	$checkUserQuery = "SELECT * FROM account_usernames WHERE username = '$userName' and active = 1 AND `brand` = 'to'";

	$checkUserQueryRun = mysql_query($checkUserQuery);
	$checkUserExistCount = mysql_num_rows($checkUserQueryRun);

	if($checkUserExistCount > 0){
		$proceedFurther = 1;
		$hideAll = "style='display:block'";
	}else{
		$proceedFurther = 0;
		$hideAll = "style='display:none'";
	}
}

// check if exist in accountusername



if($proceedFurther ==1){

$checkViewedQuery = mysql_query("SELECT 1 from checkusers where ig_username='$userName' AND viewed = 0 AND `brand` = 'to'");
$checkViewedCount = mysql_num_rows($checkViewedQuery);

if(($checkViewedCount > 0)&&(empty($_GET['loadfreeautolikes']))){ // redirect load dashboard page
	if($getUserid == "" || $getUserid == null) {
		header('Location: /'.$loclinkforward.'account/load-dashboard/');
	}else{
		header('Location: /'.$loclinkforward.'account/load-dashboard/?userid='.$getUserid);
	}
	
}
$showHideAccAvailable = "";
$showHideAccAvailableMsg = "hideBlock";
$dp = "/imgs/placeholder.jpg";
$messageAccAvailable = "";
$profileDataQuery = mysql_query("SELECT cu.followers, 
										cu.avg_post_likes, 
										cu.ig_username,
										cu.added,
										cun.next_check,
										cun.is_private
										FROM checkusers cu 
															INNER JOIN checkusers_now cun
															ON cu.checkusers_now_id = cun.id	
															WHERE cu.ig_username = '$userName' AND cun.unavailable = 0 AND cu.brand = 'to' ORDER BY cu.`id` DESC limit 1");

$profileData = mysql_fetch_array($profileDataQuery);
if(mysql_num_rows($profileDataQuery) > 0){
	$followers = $profileData["followers"];
	$avgPostLikes = $profileData["avg_post_likes"];
	$uname = $profileData["ig_username"];
	$lastUpdated = ago($profileData["added"]);
	$accountValue = calcAccountValue($followers, $avgPostLikes);
	//$nextUpdate = $profileData['next_check'];
	$nextUpdate = strtotime('tomorrow') + 25200;
	$isPrivate = $profileData['is_private'];
	if($isPrivate == "" || $isPrivate == null) $isPrivate = 0;

	if($isPrivate > 0){
		$showHidePrvtAcc = "showBlock";
		$showHidePrvtAccChart = "hideBlock";
	}else{
		$showHidePrvtAcc = "hideBlock";
		$showHidePrvtAccChart = "showBlockFlex";
	}
}else{
	$uname = $userName;
	$nextUpdate = strtotime('tomorrow') + 25200;
	$showHideAccAvailable = "hideBlock";
	$isPrivate = 0;
	$showHideAccAvailableMsg = "showBlock";
	$messageAccAvailable = "We are retriving your data..";

}
$unavailable = 0;

$checkQuery = mysql_query("SELECT 1 from checkusers_now where ig_username='$userName' AND unavailable = 1 AND `brand` = 'to'"); //check for unavailable
$checkCount = mysql_num_rows($checkQuery);

if($checkCount > 0){
	$messageAccAvailable = "Account is unavailable, we're unable to retrieve your data. Make an order with the correct Instagram username for your data to appear on here.";
	$unavailable = 1;
}

$q1 = mysql_query("select dates, followers, avg_post_likes, acc_value
from
(
  select DATE(FROM_UNIXTIME(added)) AS dates, followers, avg_post_likes, ROUND((0.64 * followers) + (1.1 * avg_post_likes)) AS acc_value
  from checkusers
  WHERE DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND CURDATE() AND ig_username = '$userName' AND `brand` = 'to'
  group by DATE(FROM_UNIXTIME(added))
  union all
  select CURDATE(), 0, 0, 0
  union all
  select date_add(curdate(), interval -1 DAY), 0, 0, 0
  union all
  select date_add(curdate(), interval -2 DAY), 0, 0, 0
  union all
  select date_add(curdate(), interval -3 DAY), 0, 0, 0
  union all
  select date_add(curdate(), interval -4 DAY), 0, 0, 0
  union all
  select date_add(curdate(), interval -5 DAY), 0, 0, 0
  union all
  select date_add(curdate(), interval -6 DAY), 0, 0, 0
) x
group BY x.dates
order BY x.dates");

while ($info1 = mysql_fetch_array($q1)) {

  $followersData .= $info1["followers"] . ",";
  $avgPostLikesData .= $info1["avg_post_likes"]. ",";
  $accValueData .= $info1["acc_value"]. ",";
}

$followersData = rtrim($followersData, ',');
$avgPostLikesData = rtrim($avgPostLikesData, ',');
$accValueData = rtrim($accValueData, ',');



$profileDpQuery = mysql_query("SELECT dp
										FROM ig_dp	
															WHERE igusername = '$userName' AND `brand` = 'to' ORDER BY `id` DESC limit 1");

$profileDp = mysql_fetch_array($profileDpQuery);

if(mysql_num_rows($profileDpQuery) > 0){
	$dp = 'https://cdn.superviral.io/dp/'.$profileDp["dp"].'.jpg';
}

$followerUpCss = "hideBlock";
$followerDownCss = "hideBlock";
$avgPostUpCss = "hideBlock";
$avgPostDownCss = "hideBlock";
$accValueUpCss = "hideBlock";
$accValueDownCss = "hideBlock";



if($unavailable == 0){
	
$diffQuery = "SELECT
cu.added,cu.id, 
cu.followers - cu1.followers AS follower_diff , 
cu.avg_post_likes - cu1.avg_post_likes AS avg_post_likes_diff, 
ROUND((0.64 * cu.followers) + (1.1 * cu.avg_post_likes)) - ROUND((0.64 * cu1.followers) + (1.1 * cu1.avg_post_likes)) AS acc_value_diff 
FROM checkusers cu 
,checkusers cu1 
WHERE cu.ig_username = '$userName' AND cu.brand = 'to'  AND 
cu.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1) AND
cu1.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1,1)";

$diffQueryRun = mysql_query($diffQuery);

	if(mysql_num_rows($diffQueryRun) > 0){
		
		$diffQueryData = mysql_fetch_array($diffQueryRun);

		$followersDiff = $diffQueryData["follower_diff"];
		if($followersDiff > 0){
			$followerUpCss = "showBlock";
		}else if($followersDiff != 0){
			$followerDownCss = "showBlock";
		}
		$avgPostLikesDiff = $diffQueryData["avg_post_likes_diff"];
		if($avgPostLikesDiff > 0 ){
			$avgPostUpCss = "showBlock";
		}else if($avgPostLikesDiff != 0){
			$avgPostDownCss = "showBlock";
		}
		$accValueDataDiff = $diffQueryData["acc_value_diff"];
		if($accValueDataDiff > 0){
			$accValueUpCss = "showBlock";
		}else if($accValueDataDiff != 0){
			$accValueDownCss = "showBlock";
		}
}

}


$competitorsList = "";

$competitorListQuery = "SELECT * FROM account_competitors WHERE account_id = $accountId AND archive = 0 AND `brand` = 'to'";

$competitorListQueryRun = mysql_query($competitorListQuery);
$countCompetitor = mysql_num_rows($competitorListQueryRun);
if($countCompetitor > 0){

	$i =1;
	while($data = mysql_fetch_array($competitorListQueryRun)){
 	  $competitorsList .= '<tr id="tableTrComp'.$i.'">
		<td class="positionofuser">#'.($i).'</td>
		<td><img class="usernameimg" src="/imgs/placeholder.jpg" id="avatarImage'. $i .'" ><div class="loader" id="avatarLoader'.$i.'" style="margin-top: -46px; margin-left: 9px;"></div>
		</td>
		<td>
			<span class="uname" id="userProfileName'.$i.'">@'. $data['competitor'] .'</span>
			<span class="unamestat"><div class="loader" id="followersLoader'.$i.'"></div><span id="followersCount'.$i.'">Followers</span> </span>
		</td>
		<tr>';
		$i++;
		$competiorData .= "'". $data["competitor"]. "'". ",";
	}
	$competitorsList .= '<tr id="tableTrComp'.$i.'">
		<td class="positionofuser">#'.($i).'</td>
		<td><img class="usernameimg" src="/imgs/placeholder.jpg" id="avatarImage'. $i .'" ><div class="loader" id="avatarLoader'.$i.'" style="margin-top: -46px; margin-left: 9px;"></div>
		</td>
		<td>
			<span class="uname" id="userProfileName'.$i.'">@'. $data['competitor'] .'</span>
			<span class="unamestat"><div class="loader" id="followersLoader'.$i.'"></div><span id="followersCount'.$i.'">Followers</span> </span>
		</td>
		<tr>';
}else{
	$competitorsList = 'You currently have no competitors, click the "Edit" button to add competitors!';
}

$competiorData = rtrim($competiorData, ",");

$postList = "";	
$postListQuery = "SELECT 	ig.thumb_url,	
							ig.shortcode,	
							cp.likes,	
							ig.added_on_instagram,	
							ig.dnow,	
							ig.shortcode	
							FROM 	
							ig_thumbs ig	
							INNER JOIN 	
							checkposts_stats cp 	
							ON cp.checkposts_id = ig.id	
							WHERE ig.igusername ='$userName' AND cp.brand = 'to' group by ig.shortcode order by ig.added_on_instagram desc limit 12";	


$postListQueryRun = mysql_query($postListQuery);	

$countPost= mysql_num_rows($postListQueryRun);	
if($countPost > 0){	
	$i =1;	
	while($data = mysql_fetch_array($postListQueryRun)){	
		$imgSrc = '/imgs/placeholder.jpg';	
		$newimgname = md5('superviralrb'.$data['shortcode']);	
		if($data['dnow'] == 0){	
			$imgSrc = "https://cdn.superviral.io/thumbs/$newimgname.jpg";	
		}	
		$shortCode = "'".$data['shortcode']."'";
  		$postList .= ' <tr>	
		  <td style = "display:flex"><img class="usernameimg" src="'. $imgSrc .'">	
			<p style="margin-left: 15px;" class="unamestat"><span>'. $data['likes'] .' Likes</span> <br/>	
				<span class="uname" >Posted '. ago($data['added_on_instagram']) .'</span>
				<button class="btn btn-blue max768pxShow" style="width: 120px;margin: 0px;" href="#0;" onclick="viewStats('.$shortCode.')">view stats</button>

			</p>

		  </td>	
			
	  </tr>';	
		$i++;	
	}	
}else{	
	$postList = 'You currently have no post!';	
}


}


$userList ="";
$userListQuery = "SELECT * FROM account_usernames WHERE account_id = $accountId and active = 1 AND `brand` = 'to'";

$userListQueryRun = mysql_query($userListQuery);
$countUserList = mysql_num_rows($userListQueryRun);
while($data = mysql_fetch_array($userListQueryRun)){
	if($data['username'] == $userName){
		$userList .= '<a href="?userid='. $data['id'] .'" class="changeusernametab changeusernameselected">'. $data['username'] .'</a>';

	}else{
		$userList .= '<a href="?userid='. $data['id'] .'"" class="changeusernametab">'. $data['username'] .'</a>';

	}

}
$displayStyle = "style='display:none'";

if($countUserList > 1){
	$displayStyle = "style='display:block'";
}else{
	$displayStyle = "style='display:none'";
}


////////////////////////////



$tpl = file_get_contents('dashboard.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loc}', $loc, $tpl);
$tpl = str_replace('{orders}', $orders, $tpl);
$tpl = str_replace('{message1}', $message1, $tpl);
$tpl = str_replace('{freeautolikesdisplay}', $freeautolikesdisplay, $tpl);
$tpl = str_replace('{autolikesonaccount}', $autolikesonaccount, $tpl);
$tpl = str_replace("{followers}", $followers, $tpl);
$tpl = str_replace("{avgPostLikes}", $avgPostLikes, $tpl);
$tpl = str_replace("{uname}", $uname, $tpl);
$tpl = str_replace("{lastUpdated}", $lastUpdated, $tpl);
$tpl = str_replace("{currency}", $locas[$loc]['currencysign'], $tpl);
$tpl = str_replace("{accountValue}", $accountValue, $tpl);
$tpl = str_replace("{nextUpdate}", $nextUpdate, $tpl);
$tpl = str_replace("{labelsbackend}", $labelsbackend, $tpl);
$tpl = str_replace("{followersData}", $followersData, $tpl);
$tpl = str_replace("{avgPostLikesData}", $avgPostLikesData, $tpl);
$tpl = str_replace("{accountValueData}", $accValueData, $tpl);
$tpl = str_replace("{dpImage}", $dp, $tpl);
$tpl = str_replace("{followersDiff}", $followersDiff, $tpl);
$tpl = str_replace("{avgPostLikesDiff}", $avgPostLikesDiff, $tpl);
$tpl = str_replace("{accValueDiff}", $accValueDataDiff, $tpl);
$tpl = str_replace("{avgLikeUpClass}", $avgPostUpCss, $tpl);
$tpl = str_replace("{avgLikeDownClass}", $avgPostDownCss, $tpl);
$tpl = str_replace("{accValUpClass}", $accValueUpCss, $tpl);
$tpl = str_replace("{accValDownClass}", $accValueDownCss, $tpl);
$tpl = str_replace("{follwerUpClass}", $followerUpCss, $tpl);
$tpl = str_replace("{followerDownClass}", $followerDownCss, $tpl);
$tpl = str_replace("{competitorCount}", $countCompetitor, $tpl);
$tpl = str_replace("{competitorBody}", $competitorsList, $tpl);
$tpl = str_replace("{competiorData}", $competiorData, $tpl);
$tpl = str_replace("{userList}", $userList, $tpl);
$tpl = str_replace("{styledisplay}", $displayStyle, $tpl);
$tpl = str_replace("{showHidePrvtAcc}", $showHidePrvtAcc, $tpl);
$tpl = str_replace("{showHidePrvtAccChart}", $showHidePrvtAccChart, $tpl);
$tpl = str_replace("{isPrivate}", $isPrivate, $tpl);
$tpl = str_replace("{hideAll}", $hideAll, $tpl);
$tpl = str_replace("{showHideAccAvailable}", $showHideAccAvailable, $tpl);
$tpl = str_replace("{showHideAccAvailableMsg}", $showHideAccAvailableMsg, $tpl);
$tpl = str_replace("{messageAccAvailable}", $messageAccAvailable, $tpl);
$tpl = str_replace("{postList}", $postList, $tpl);



if($_GET['loadfreeautolikes']=='true'){$tpl = str_replace('<body>','<body onload="signupAl();return false;">',$tpl);}

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='to' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = 'ww' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

/*use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}*/

echo $tpl;
?>
