<?php

include('db.php');

$stime = time() - strtotime("today");

$info = mysql_fetch_array(mysql_query("SELECT * FROM `chekoutusers` WHERE `id` = '$stime' LIMIT 1"));

///

$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.','',$loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));



if(empty($loc))$loc = '';
if($loc=='superviral')$loc = '';
if($loc=='www')$loc = '';

if(!empty($loc))$loc = $loc.'.';

///


header('Access-Control-Allow-Origin: https://superviral.io');

echo $info['users'];




?>