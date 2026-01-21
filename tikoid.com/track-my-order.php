<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

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

if(empty($id)){include('track-my-order-main.php');die;}

$tpl = file_get_contents('track-my-order.html');
if(empty($_GET['rabban']=='true')){$tpl = file_get_contents('track-my-order-2.html');}

$q = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$id' AND `brand` = 'to' LIMIT 1");
if(mysql_num_rows($q)=='0'){

  sendCloudwatchData('Tikoid', 'track-order-failure', 'TrackOrder', 'track-order-load-failure-function', 1);
  die('No order found');
}

$info = mysql_fetch_array($q);

$info['packagetype'] = str_replace('freefollowers','followers',$info['packagetype']);

if(empty($info['order_response'])){

echo 'Added: '.$info['added'].'<br>';

$added = $info['added'];


$dif = time() - $added;


$time = $added + rand(0, 10);
$text = 'Tikoid algorithm scanning @'.$info['igusername'].' account statistics age, usage, current followers and post engagement for best delivery method';
$duration = '0.'.rand(1,9);
$update1 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(50, 200);
$algo = rand(40, 470);
$text = 'Using algorithm #'.$algo.' out of 477 to deliver the safest way to @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update2 = '~~~'.$time.'###'.$text.'###'.$duration;



$time = $added + rand(201, 320);
$text = 'Choosing '.$info['amount'].' '.$info['packagetype'].' out of 3.4 million super high quality '.$info['packagetype'].' to follow @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update3 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(321, 600);
$text = 'Sending TikTok followers to @'.$info['igusername'].' with Tikoid RapidDelivery™';
$duration = '0.'.rand(1,9);
$update4 = '~~~'.$time.'###'.$text.'###'.$duration;

$order_response = addslashes($update1.$update2.$update3.$update4);

mysql_query("UPDATE `orders` SET `order_response` = '$order_response' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");

header('Location: https://tikoid.com/track-my-order/'.$id);

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

$timeremaining = $info['added'] + 13320;

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
$deliverystatus = 'Delivered '.date("l j/n/Y",$info['added']).' at '.date("G:i a",$info['added']);

}

if($i==1)$i = 1;
if($i==2)$i = 2;
if($i==3)$i = 2;
if($i==4)$i = 2;
if($i==5)$i = 3;
$d = 100 / 3;$i = $i * $d; 
$percentbar = $i;
$tickbar = $i - 3;


if(empty($info['contactnumber'])){$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">Add phone number for free text notifications</a>';$textnotifsbtn = '<a onclick="signup();return false;" href="#" class="btn color3 dshadow" style="margin-top:15px;">Get Free Order Notifications</a>';}
  else{$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">'.$info['contactnumber'].' (change)</a>';}

sendCloudwatchData('Tikoid', 'track-order-success', 'TrackOrder', 'track-order-load-success-function', 1);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{textnotifsbtn}', $textnotifsbtn, $tpl);
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
$tpl = str_replace('{footer}', $footer, $tpl);

echo $tpl;
?>