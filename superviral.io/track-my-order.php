<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');

date_default_timezone_set('Europe/London');

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


$id = addslashes($_GET['id']);
$uid = addslashes($_GET['uid']);
$order_id = addslashes($_POST['order_id']);
$refill_session_id = addslashes($_POST['refill_session_id']);
$refill = addslashes($_POST['refill']);

if(!empty($refill)){

  if((empty($order_id))||(empty($refill_session_id)))die('Error 40392: Please contact support team with this error.');

  if($refill=='on'){$refillsqlchange = "0";}
  if($refill=='off'){$refillsqlchange = "1";}

  mysql_query("UPDATE `orders` SET `norefill` = '$refillsqlchange' WHERE `id` = '$order_id' AND `order_session` = '$refill_session_id' LIMIT 1");

}


if(empty($id)){include('track-my-order-main.php');die;}

//$tpl = file_get_contents('track-my-order.html');
$tpl = file_get_contents('track-my-order-2.html');

if(!empty($uid)){$uidwhere = "AND `id` = '$uid' ";}

$q = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$id' $uidwhere ORDER BY `id` DESC LIMIT 1");
if(mysql_num_rows($q)=='0')die('No order found');

$info = mysql_fetch_array($q);

$info['packagetype'] = str_replace('freefollowers','followers',$info['packagetype']);

if(empty($info['order_response'])){

echo 'Added: '.$info['added'].'<br>';

$added = $info['added'];


$dif = time() - $added;


$time = $added + rand(0, 10);
$text = 'Superviral algorithm scanning @'.$info['igusername'].' account statistics age, usage, current followers and post engagement for best delivery method';
$duration = '0.'.rand(1,9);
$update1 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(50, 200);
$algo = rand(40, 470);
$text = 'Using algorithm #'.$algo.' out of 477 to deliver the safest way to @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update2 = '~~~'.$time.'###'.$text.'###'.$duration;



$time = $added + rand(201, 320);
$text = 'Choosing '.$info['amount'].' followers out of 3.4 million super high quality followers to follow @'.$info['igusername'];
if($info['packagetype']=='likes')$text = 'Choosing '.$info['amount'].' of the highest quality accounts to like post(s) at @'.$info['igusername'];
if($info['packagetype']=='views')$text = 'Choosing '.$info['amount'].' of the highest quality accounts to watch video post(s) at @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update3 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(321, 600);
$text = 'Sending Instagram followers to @'.$info['igusername'].' with Superviral RapidDelivery™';
if($info['packagetype']=='likes')$text = 'Sending Instagram likes to @'.$info['igusername'].' with Superviral RapidDelivery™';
if($info['packagetype']=='views')$text = 'Sending Instagram views to @'.$info['igusername'].' with Superviral RapidDelivery™';
$duration = '0.'.rand(1,9);
$update4 = '~~~'.$time.'###'.$text.'###'.$duration;

$order_response = addslashes($update1.$update2.$update3.$update4);

mysql_query("UPDATE `orders` SET `order_response` = '$order_response' WHERE `id` = '{$info['id']}' LIMIT 1");

$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

header('Location: https://superviral.io/'.$loclinkforward.'track-my-order/'.$id);

die;

}

	$i = 0;

  if(!empty($info['order_response_finish'])){$info['order_response'] = $info['order_response'].$info['order_response_finish'];}
	$trackinghistory1 = explode('~~~', $info['order_response']);

	$trackinghistory1 = array_reverse($trackinghistory1);

	foreach($trackinghistory1 as $trackingdate){

		if(empty($trackingdate))continue;

		$trackinginfod = explode('###',$trackingdate);

		if($trackinginfod[0] > time())continue;

		$i++;

		$trackingtable .= '<tr><td>'.ago($trackinginfod[0]).':</td><td>'.$trackinginfod[1].' ('.$trackinginfod[2].' seconds)</td></tr>';

	}


if($info['fulfilled']=='0'){



  /////////////////

//$timeremaining = $info['added'] + 13320;
$timeremaining = $info['added'] + 72000;

if($timeremaining > time()){

//Calculate difference    
$diff=$timeremaining-time();//time returns current time in seconds
$days=floor($diff/(60*60*24));//seconds/minute*minutes/hour*hours/day)
$hours=round(($diff-$days*60*60*24)/(60*60));
$minutes=round($diff/60);

if($hours=='1'||$hours=='0'){$time= $minutes.' minutes';}else{$time = $hours.' hours';}

    $deliverystatus = 'Estimated delivery in '.$time;


    }else{

   $deliverystatus = 'Estimated delivery today';//GONE PAST THE TIME

 }

$status = 'In progress <a href="#trackinghistory" style="color:#2e00f4;text-decoration:underline;">(check tracking history)</a>';

}else{

$status = 'Delivered';
$deliverystatus = 'Delivered '.date("l j/n/Y",$info['fulfilled']).' at '.date("G:i a",$info['fulfilled']);

}

if($i==1)$i = 1;
if($i==2)$i = 2;
if($i==3)$i = 2;
if($i==4)$i = 2;
if($i==5)$i = 3;
$d = 100 / 3;$i = $i * $d; 
$percentbar = $i;
$tickbar = $i - 3;


if(empty($info['contactnumber'])){$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">Add phone number for free order update</a>';$textnotifsbtn = '<a onclick="signup();return false;" id="addphonenumber" href="#" class="btn color4 dshadow" style="margin-top:15px;">Get Free Order Notifications</a>';}
  else{$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">'.$info['contactnumber'].' (change)</a>';}

if(($info['freelikes']=='0')&&($info['packagetype']=='followers'))$freelikesbtn = '<a onclick="signup2();return false;" href="#" class="btn color4 dshadow" style="margin-top:10px;">Get Free Instagram Likes</a>';


if((($info['packagetype']=='followers')||($info['packagetype']=='freefollowers'))&&($info['fulfilled']!=='0')){

if($info['lastrefilled']=='0'){$lastchecked = $info['fulfilled'];}else{$lastchecked = $info['lastrefilled'];}

$lastrefilldate = $info['added'] + 2592000;
$now = time();

if($now > $info['added'] && $now < $lastrefilldate){$refillstatus = '<font color="green">Refills are on-going</font>';

if($info['norefill']=='1'){$refillstatus = '<font color="orange">Refills have been paused</font>';$refillbtn = '<input type="hidden" name="refill" value="on">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Enable Refills">';}else{

$refillbtn = '<input type="hidden" name="refill" value="off">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Disable Refills">';
  
  }

$refillstatus .= '<br><form method="POST" action="#refill"><input type="hidden" name="refill_session_id" value="'.$info['order_session'].'"><input type="hidden" name="order_id" value="'.$info['id'].'">
'.$refillbtn.'</form>';

  $lastchecked = '<tr><td>Last checked:</td><td>@{igusername} was checked '.ago($lastchecked).'</td></tr>';}
  else{
    $refillstatus = '30-day refill period for your followers have finished';
    unset($lastchecked);
  }



$refilltable = '<div class="box dshadow">
              <div class="boxheading" id="refill">♻️ 30-Day Auto-Refill Guarantee</div>
              <div class="refillnotice">Now that we\'ve delivered your Instagram followers, we will monitor your Instagram account for 30-days after placing your order. This is to ensure that the followers you\'ve received - remains on your account.<br><br>If the followers you\'ve ordered drops, don\'t worry - we\'ll refill your account to the amount you\'ve ordered. Our systems monitor and check your account every 12-24 hours. At Superviral - the customers always comes first. ❤️</div>
              <div class="contents">
            <table>
              <tr><td>Service:</td><td>Superviral AutoRefill™ ({amount} Followers)</td></tr>
              <tr><td>Order first made:</td><td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td></tr>
              <tr><td>Username:</td><td>{igusername}</td></tr>
              '.$lastchecked.'
              <tr><td>Status:</td><td>'.$refillstatus.'</td></tr>
            </table>
          </div>
        </div>';
      }

$askednumber = '';

if($info['askednumber']=='1')$askednumber = '<div id="askednumber" style="display:none;"></div>';

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'track-my-order-inside') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{textnotifsbtn}', $textnotifsbtn, $tpl);
$tpl = str_replace('{freelikesbtn}', $freelikesbtn, $tpl);
$tpl = str_replace('{refilltable}', $refilltable, $tpl);
$tpl = str_replace('{trackingtable}', $trackingtable, $tpl);
$tpl = str_replace('{percentbar}', $percentbar, $tpl);
$tpl = str_replace('{deliverystatus}', $deliverystatus, $tpl);
$tpl = str_replace('{tickbar}', $tickbar, $tpl);
$tpl = str_replace('{trackingnumber}', substr($info['order_session'],0,15), $tpl);
$tpl = str_replace('{hash}', $info['order_session'], $tpl);
$tpl = str_replace('{ordernum}', $info['id'], $tpl);
$tpl = str_replace('{igusername}', $info['igusername'], $tpl);
$tpl = str_replace('{amount}', $info['amount'], $tpl);
$tpl = str_replace('{service}', ucfirst($info['packagetype']), $tpl);
$tpl = str_replace('{status}', $status, $tpl);
$tpl = str_replace('{askednumber}', $askednumber, $tpl);


echo $tpl;
?>