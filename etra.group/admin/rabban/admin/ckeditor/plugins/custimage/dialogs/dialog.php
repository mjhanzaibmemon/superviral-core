<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set ('Europe/London');//set timezones
include('../../../../../db.php');

$id = explode('-',(addslashes($_GET['id'])));
$pid = $id[0];
$id = $id[1];

$tpl = @file_get_contents("dialog.html");

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


if(!empty($_GET['delete'])){


    $deleteid = addslashes($_GET['delete']);
    $firstid = $_GET['pid'];
    $secondid = $_GET['id'];

    $checkvalidq = mysql_query("SELECT * FROM `articles_imgs` WHERE `id` = '$secondid' AND `pid` = '$firstid' LIMIT 1");
    if(mysql_num_rows($checkvalidq)=='0'){exit('None Found');}

    else{

    mysql_query("UPDATE `articles_imgs` SET `deleteit` = '1' WHERE `id` = '$secondid' AND `pid` = '$firstid' LIMIT 1");

    if (!class_exists('S3'))require_once('S3.php');

    //AWS access info
    if (!defined('awsAccessKey')) define('awsAccessKey', $amazons3key);
    if (!defined('awsSecretKey')) define('awsSecretKey', $amazons3password);  
    $s3 = new S3(awsAccessKey, awsSecretKey);  

    $s3->deleteObject('svstorage', "$deleteid.jpg");

    }

    header('Location: dialog.php');

}

$q = mysql_query("SELECT * FROM `articles_imgs` WHERE `encrypt` = '$id' AND `deleteit` = '0'");
while($row = mysql_fetch_array($q)){

  $pid = $row['pid'];

  $uploadedimgs .= '<div class="eachimage"><a onclick="return onClose(\'https://svstorage.s3.amazonaws.com/'.$pid.'-'.$row['id'].'.jpg\')" href="#">IMG00'.$row['id'].'</a> <font style="font-style:italic;font-size:15px;"> ('.ago($row['added']).')</font><a style="float:right;font-size:15px;" href="?id='.$row['id'].'&delete='.$pid.'-'.$row['id'].'&pid='.$pid.'">Delete Image</a></div>';

}


$tpl = str_replace('{id}',$id,$tpl);
$tpl = str_replace('{pid}',$pid,$tpl);
$tpl = str_replace('{uploaded}',$uploadedimgs,$tpl);

echo $tpl;

?>