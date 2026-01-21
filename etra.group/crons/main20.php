<?php

mysql_query("UPDATE `freetrial` SET `md5` = '',`done` = '0' WHERE `emailaddress` = '{$info['emailaddress']}' AND brand ='$brand'");

$freetrialmd5 = md5($info['emailaddress'].time());

$insertq = mysql_query("INSERT INTO `freetrial` SET `md5` = '{$freetrialmd5}',`emailaddress`='{$info['emailaddress']}',`type`='1', brand ='$brand'");

if(!$insertq)die('Not inserted free trial');

$ctalink = 'https://superviral.io/free-followers/?id='.$freetrialmd5;

$tpl = str_replace('{ctalink}', $ctalink, $tpl);

unset($freetrialmd5);
unset($ctalink);
unset($insertq);

?>