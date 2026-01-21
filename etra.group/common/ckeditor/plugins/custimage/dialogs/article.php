<?php

/*
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set ('Europe/London');//set timezones
include('../extdb.php');

$tpl = @file_get_contents("article.html");

$type = $_POST['name'];
$id = explode('-',(addslashes(mysql_real_escape_string($_GET['id']))));
$pid = $id[0];
$id = $id[1];
$title = trim(addslashes(mysql_real_escape_string($_POST['title'])));
$contents = addslashes(mysql_real_escape_string($_POST['contents']));
$point1 = trim(addslashes(mysql_real_escape_string($_POST['point1'])));
$point2 = trim(addslashes(mysql_real_escape_string($_POST['point2'])));
$point3 = trim(addslashes(mysql_real_escape_string($_POST['point3'])));
$shortdesc = trim(addslashes(mysql_real_escape_string($_POST['shortdesc'])));
$category = trim(addslashes(mysql_real_escape_string($_POST['category'])));

$time = time();

function ago($time)
{$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference = $now - $time;
       $tense = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
}   return "$difference $periods[$j] ago";}

$timetake = time() - 604800;
$time = time();

if((!empty($_GET['id']))||(!empty($_GET['pid']))){

$searchidq = mysql_query("SELECT * FROM `insiderarticles` WHERE `encrypt` = '$id' AND `published` = '0' LIMIT 1");
if(mysql_num_rows($searchidq)==0)die;

}else{


$insertq = mysql_query("INSERT INTO `insiderarticles` SET `added` = '$time'");
$insertid = mysql_insert_id();

$hash = md5($time.$id);
mysql_query("UPDATE `insiderarticles` SET `encrypt` = '$hash' WHERE `id` = '$insertid' LIMIT 1");

header('Location: article.php?id='.$insertid.'-'.$hash);die;}


/////////////////////////////////////////////////////////////////////

if(!empty($contents)){//echo $contents;


$url = ereg_replace("[-]+", "-", ereg_replace("[^a-z0-9-]", "",
      strtolower( str_replace(" ", "-", trim($title)) ) ) );


$contents = nl2br($contents);
$contents = str_replace('\r', '', $contents);$contents = str_replace("\r", "", $contents);
$contents = str_replace('\n', '', $contents);$contents = str_replace("\n", "", $contents);
$contents = str_replace('class="imgcaption" style="background-color:#ccc;padding:5px;font-weight:600;font-size:15px;"','class="imgcaption"',$contents);//Remove the style tag to the imgcaption tag
$contents = str_replace('\\','',$contents);

//Connect To S3
if (!class_exists('S3'))require_once('ckeditor/plugins/custimage/dialogs/S3.php');
if (!defined('awsAccessKey')) define('awsAccessKey', $amazons3key);
if (!defined('awsSecretKey')) define('awsSecretKey', $amazons3password);
$s3 = new S3(awsAccessKey, awsSecretKey);  

//echo $contents.'<br>';
preg_match_all('/<img[^>]+src\s*=\s*["\']?([^"\' ]+)[^>]*>/', $contents, $matches);

foreach($matches[1] as $src){
$srcverif= explode('/',$src);$srcverif = $srcverif[count($srcverif)-1];$srcverif = explode('-',$srcverif);
if(strpos($src,'cdn.secret3.co.uk/') !== false){$usedimgs[] = $srcverif[0].'-'.str_replace('.jpg','',$srcverif[1]).'.jpg';}}


$q = mysql_query("SELECT * FROM `insiderimgs` WHERE `pid` = '$pid' AND `encrypt` = '$id'");$i =1;
while($row = mysql_fetch_array($q)){

$fileterm = $row['pid'].'-'.$row['id'].'.jpg';

if (!in_array($fileterm, $usedimgs)) {//echo 'ITS NOT IN: '.$row['pid'].'-'.$row['id'].'.jpg.'.'<br>';

$s3->deleteObject('cdn.secret3.co.uk', $fileterm);
mysql_query("DELETE FROM `insiderimgs` WHERE `pid` = '{$row['pid']}' AND `id` = '{$row['id']}' LIMIT 1");


}else{//ITS IN

if($i==1){

include_once('ckeditor/plugins/custimage/dialogs/resizer.php');*/

$tempfile = tmpfile();

echo $tempfile;die;

/*

$ch = curl_init('http://cdn.secret3.co.uk/'.$fileterm);$fp = fopen($tempfile, 'wb');curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);curl_exec($ch);curl_close($ch);


resize_image($tempfile, $row['pid'].'small.jpg', 310, 250);
resize_image($tempfile, $row['pid'].'medium.jpg', 300, 400);


fclose($fp);

}
$i++;

}}

mysql_query("UPDATE `insiderimgs` SET `deleteit` = '0' WHERE `pid` = '$pid'");


//Change image style property into class
$contents = str_replace('style="max-width:100%;width:auto;height:auto;"','class="img"',$contents);
$contents = str_replace('style="margin-bottom:-5px;"','class="imgmarginbottom"',$contents);
$contents = str_replace('style="background-color:#ccc;padding:5px;font-weight:600;font-size:15px;"','class="imgcaption"',$contents);

$contents = addslashes($contents);


mysql_query("UPDATE `insiderarticles` SET 
`title` = '$title',
`url` = '$url',
`shortdesc` = '$shortdesc',
`category` = '$category',
`summary1` = '$point1',
`summary2` = '$point2',
`summary3` = '$point3',
`article` = '$contents',
`written` = UNIX_TIMESTAMP(),
`published` = '1'
WHERE `encrypt` = '$id' LIMIT 1");

$tpl = str_replace('{message}','<div style="color:#49C225;font-size:14px;margin:5px;text-align:center;">Thank you for submitting your article. We will review your article within 24 hours.</div>',$tpl);
$tpl = str_replace('name="submitarticle"','name="submitarticle" style="display:none;"',$tpl);
echo $tpl;

die;}

////////////////////////////////////////////////////////////////////

$tpl = str_replace('{message}','',$tpl);

echo $tpl;
*/
?>