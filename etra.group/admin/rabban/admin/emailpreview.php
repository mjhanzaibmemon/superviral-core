<?php


include('adminheader.php');

//////////////////////////// AGO
function ago($time)
{$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
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
   ////////////////////////////////////

$id = addslashes($_GET['id']);

$q = mysql_query("SELECT * FROM `email_funnels` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($q)=='0'){exit('DOES NOT EXIST');}

$funnelinfo = mysql_fetch_array($q);

if($funnelinfo['type']=='cold')$theactualtype = 'freetrial';
if($funnelinfo['type']=='warm')$theactualtype = 'cart';
if($funnelinfo['type']=='hot')$theactualtype = 'order';

$thefunnelstate = $funnelinfo['type'].'sequence';

$previousfunnel = $funnelinfo[$thefunnelstate] - 1;

$finduser = mysql_query("SELECT * FROM `users` WHERE `source` = '$theactualtype' AND `funnelstate` = '{$previousfunnel}' ORDER BY `id` DESC LIMIT 1");
if(mysql_num_rows($finduser)=='0')die('FOUND NO USER');
$info = mysql_fetch_array($finduser);//FETCH USER

/////////////////////// ABOVE IS SAME AS emailmain.php

$subject = $funnelinfo['subject'];

////////////////////////////////////////////

////////////////////// BELOW IS SAME AS emailmain.php

$tpl = @file_get_contents('../emailtemplate/emailtemplate.html');

$tpl = str_replace('{md5unsub}', $info['md5'], $tpl);
$tpl = str_replace('{body}', $funnelinfo['body'], $tpl);

include('../crons/main'.$funnelinfo['id'].'.php');

$tpl = str_replace('{subject}', $subject, $tpl);

echo $tpl;

if($_GET['sendtestemail']=='true'){

include('../crons/emailer.php');


emailnow('r.faruqui@live.co.uk',$funnelinfo['name'],'support@superviral.io',$subject,$tpl);

}

?>