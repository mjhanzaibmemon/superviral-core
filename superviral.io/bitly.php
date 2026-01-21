<?php

include('db.php');

$id = addslashes($_GET['id']);

$q = mysql_query("SELECT * FROM `bitly` WHERE `hash` = '$id' LIMIT 1");
if(mysql_num_rows($q)=='0'){header("Location: https://superviral.io/buy-instagram-followers/");die;}


$info = mysql_fetch_array($q);

header('Location: '.$info['href']);

?>
