<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include_once('header.php');

$articleid = $_GET['id'];
$sortby = $_GET['sortby'];
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$url= $_GET['url'];


$q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' LIMIT 1");

if(mysql_num_rows($q)==0)exit('404');

$info = mysql_fetch_array($q);

$url = $info['category'];$_GET['url'] = $info['category'];

//$update = mysql_query("UPDATE `articles` SET `shared` = `shared` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");

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

$word = str_word_count(strip_tags($info['article']));
$m = floor($word / 500);
$s = floor($word % 200 / (200 / 60));
$est = $m . ' minute' . ($m == 1 ? '' : 's');


$tpl = @file_get_contents("article.html");

$tpl = str_replace('{header}', $header,$tpl);
$tpl = str_replace('{footer}', $footer,$tpl);
$tpl = str_replace('{headerscript}', $headerscript,$tpl);

$tpl = str_replace('{sharelink}', 'https://tikoid.com/blog/'.$info['url'],$tpl);
$tpl = str_replace('{shares}', $info['shared'],$tpl);
$tpl = str_replace('{id}', ucwords(stripslashes($info['id'])),$tpl);
$tpl = str_replace('{title}', stripslashes($info['title']),$tpl);
$tpl = str_replace('{description}', ucfirst(stripslashes($info['shortdesc'])),$tpl);
$tpl = str_replace('{summary1}', ucfirst(stripslashes($info['summary1'])),$tpl);
$tpl = str_replace('{summary2}', ucfirst(stripslashes($info['summary2'])),$tpl);
$tpl = str_replace('{summary3}', ucfirst(stripslashes($info['summary3'])),$tpl);
$tpl = str_replace('{written}', ucwords(gmdate('H:i l jS F Y', $info['written'])),$tpl);
$tpl = str_replace('{readtime}', $est.' read',$tpl);
$tpl = str_replace('{article}', stripslashes($info['article']),$tpl);

echo $tpl;

?>