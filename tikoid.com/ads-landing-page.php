<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=0;
include('header.php');

$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'followers' ORDER BY `amount` ASC");

while($info = mysql_fetch_array($q)){


	$otherpackages .= '<div onclick="window.location.href = \'https://tikoid.com/order/choose/'.$info['id'].'\'; return false;">'.$info['amount'].' followers / $'.$info['price'].'</div>';

}

$tpl = file_get_contents('ads-landing-page.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{otherpackages}', $otherpackages, $tpl);

echo $tpl;
?>