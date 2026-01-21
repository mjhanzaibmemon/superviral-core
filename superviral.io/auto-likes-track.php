<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

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
$order_id = addslashes($_POST['order_id']);
$refill_session_id = addslashes($_POST['refill_session_id']);
$refill = addslashes($_POST['changestatus']);

if(!empty($refill)){

  if((empty($order_id))||(empty($refill_session_id)))die('Error 40392: Please contact support team with this error.');

  if($refill=='on'){$refillsqlchange = "0";}
  if($refill=='off'){$refillsqlchange = "1";}

  mysql_query("UPDATE `automatic_likes` SET `disabled` = '$refillsqlchange' WHERE `id` = '$order_id' AND `md5` = '$refill_session_id' LIMIT 1");

}


if(empty($id)){die;}

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `md5` = '$id' LIMIT 1");
if(mysql_num_rows($q)=='0')die('No order found');

$info = mysql_fetch_array($q);





$status = '<font color="green">Automatic likes is active and on-going</font>';

if($info['refunded']=='1'){$status .= ' (refunded)';}

if($info['last_updated']!=='0')$lastupdated = '<tr><td>Last updated:</td><td>'.ago($info['last_updated']).'</td></tr>';

$packageexpiry = $info['expires'];
$now = time();

if($now > $info['added'] && $now < $packageexpiry){$status = '<font color="green">Automatic likes is active and on-going</font>';

if($info['disabled']=='1'){$status = '<font color="orange">Automatic Likes have been paused</font>';$refillbtn = '<input type="hidden" name="changestatus" value="on">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Enable Automatic Likes">';}else{

$refillbtn = '<input type="hidden" name="changestatus" value="off">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Disable Automatic Likes">';
  
  }

$status .= '<br><form method="POST" action="#refill"><input type="hidden" name="refill_session_id" value="'.$info['md5'].'"><input type="hidden" name="order_id" value="'.$info['id'].'">
'.$refillbtn.'</form>';

  $lastchecked = '<tr><td>Last checked:</td><td>@{igusername} was checked '.ago($lastchecked).'</td></tr>';}
  else{
    $status = '30-day automatic likes have finished';
    unset($lastchecked);
  }








$tpl = file_get_contents('auto-likes-track.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{ordernum}',$info['id'],$tpl);
$tpl = str_replace('{orderdesc}',$info['likes_per_post'].' Automatic Likes<br>Per Post',$tpl);

$tpl = str_replace('{igusername}','@'.$info['igusername'],$tpl);
$tpl = str_replace('{postperday}',$info['max_post_per_day'].' posts per day (once you\'ve published 4-posts, our system will renew your limit tomorrow at 00:00)',$tpl);
$tpl = str_replace('{lastupdated}',$lastupdated,$tpl);
$tpl = str_replace('{expires}',date("l j/n/Y",$info['expires']),$tpl);
$tpl = str_replace('{status}',$status,$tpl);
$tpl = str_replace('{activate}',$activate,$tpl);

$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = '') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>