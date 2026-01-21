<?php

$q1 = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` = '{$info['emailaddress']}' AND `amount` != '0.00' AND brand ='$brand'  ORDER BY `id` DESC LIMIT 1");

$qinfo = mysql_fetch_array($q1);

$tpl = str_replace('{ordernum}', $qinfo['id'], $tpl);
$tpl = str_replace('{fullservice}', $qinfo['amount'].' '.$qinfo['packagetype'], $tpl);

$subject = str_replace('{ordernum}', $qinfo['id'], $subject);

unset($q1);
unset($qinfo);

?>