<?php


$q1 = mysql_query("SELECT * FROM `order_session` WHERE `emailaddress` = '{$info['emailaddress']}' AND brand ='$brand' ORDER BY `id` DESC LIMIT 1");

$qinfo = mysql_fetch_array($q1);



$pq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$qinfo['packageid']}' AND brand ='$brand' LIMIT 1");
$packageinfo = mysql_fetch_array($pq);


$username = $qinfo['igusername'];
$payment  = $packageinfo['price'];
$service = $packageinfo['amount'].' Real '.ucwords($packageinfo['type']);


$tpl = str_replace('{username}', $username, $tpl);
$subject = str_replace('{username}', $username, $subject);
$tpl = str_replace('{payment}', $payment, $tpl);
$tpl = str_replace('{service}', $service, $tpl);
$tpl = str_replace('{ctalink}', 'https://superviral.io/order/choose/?setorder='.$qinfo['order_session'].'&discount=on', $tpl);

$tpl = str_replace('{body}', $funnelinfo['body'], $tpl);

echo $info['packageid'].' - '.$brand.'<br>';

print_r($qinfo);

unset($pq);
unset($packageinfo);
unset($q1);
unset($qinfo);

?>