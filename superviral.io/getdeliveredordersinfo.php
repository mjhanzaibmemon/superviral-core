<?php

$loc = 'uk.';

header('Access-Control-Allow-Origin: https://'.$loc.'superviral.io');

include('db.php');


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


$randomago = rand(15, 420);
//$randomago = rand(15, 720);
$ago = ago(time() - $randomago);
$type = addslashes($_GET['type']);

/*
$smallestId = mysql_fetch_array(mysql_query("SELECT id, packagetype FROM orders WHERE `id` > '1022145' ORDER BY id ASC LIMIT 1"));

$biggestId = mysql_fetch_array(mysql_query("SELECT id, packagetype FROM orders WHERE `id` > '1022145' ORDER BY id DESC LIMIT 1"));

$randomId = mt_rand($smallestId['id'], $biggestId['id']);

$randomSql = mysql_query("SELECT * FROM orders WHERE id='$randomId' AND (`packagetype` != 'freefollowers' OR `packagetype` != 'freelikes') LIMIT 1 ");

$info = mysql_fetch_array($randomSql);
*/


$q = mysql_query("SELECT `amount`,`packagetype`,`id` FROM `orders` WHERE `packagetype` = '$type' ORDER BY `id` DESC LIMIT 50");

$i = 1;

while($info = mysql_fetch_array($q)){


$loadedinfo[$i] = $info;

$i++;

}

$therandomnumber = rand(1,50);

echo '<b>'.$loadedinfo[$therandomnumber]['amount'].' '.$loadedinfo[$therandomnumber]['packagetype'].'</b> delivered <span class="delivago">'.$ago.'</span>';



?>