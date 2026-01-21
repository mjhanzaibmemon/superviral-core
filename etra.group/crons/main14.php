<?php

$q1 = mysql_query("SELECT * FROM `order_session` WHERE `emailaddress` = '{$info['emailaddress']}' AND `igusername` !='' AND brand ='$brand' ORDER BY `id` DESC LIMIT 1");

$qinfo = mysql_fetch_array($q1);

$username = $qinfo['igusername'];


$subject = str_replace('{igusername}', '@'.ucfirst($username), $subject);
$tpl = str_replace('{igusername}', '@'.ucfirst($username), $tpl);
$tpl = str_replace('{ctalink}', 'https://superviral.io/buy-instagram-followers/', $tpl);

?>