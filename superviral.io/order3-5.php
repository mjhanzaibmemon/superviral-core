<?php

include('order3-cardinity.php');die;

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

require_once('stripe-php-master/init.php');


\Stripe\Stripe::setApiKey($stripeapikey2);

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));

if(!empty($info['upsell'])){

$upsellprice = explode('###',$info['upsell']);

$upsellamount = $upsellprice[0];
$upsellprice = $upsellprice[1];

$finalprice = $packageinfo['price'] + $upsellprice;
$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

}else{

$finalprice = $packageinfo['price'];

}

//UPSELL AUTO LIKES
if(!empty($info['upsell_autolikes'])){

$upsellpriceautolikes = explode('###',$info['upsell_autolikes']);

$upsellpriceal = $upsellpriceautolikes[1];

$finalprice = $finalprice + $upsellpriceal;

}

$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);


if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

$priceamount = $finalprice;
$finalprice = str_replace('.', '', $finalprice);


///PREVIOUSLY USED FULFILLING CODE WAS HERE

$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order3-5.html');

$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{discounturl}', $discounturl, $tpl);
$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
$tpl = str_replace('{back}','/'.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/', $tpl);
$tpl = str_replace('{redirect}', 'https://'.$locredirect.'superviral.io/'.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/', $tpl);
$tpl = str_replace('{price}', $priceamount, $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){

if($cinfo['name']=='maincta'){$cinfo['content'] = str_replace('$price',$priceamount,$cinfo['content']);}

	$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

}

echo $tpl;
?>