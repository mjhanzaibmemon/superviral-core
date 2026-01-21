<?php


$fetchdiscountq = mysql_query("SELECT * FROM `discounts` WHERE `user` = '{$info['id']}' AND brand ='$brand' LIMIT 1");
$fetchdiscountinfo = mysql_fetch_array($fetchdiscountq);

$ctalink = 'https://superviral.io/activate-discount/?id='.$fetchdiscountinfo['md5'].'&expiry='.$fetchdiscountinfo['expiry'];

$tpl = str_replace('{ctalink}', $ctalink, $tpl);

unset($fetchdiscountq);
unset($fetchdiscountinfo);
unset($ctalink);

?>