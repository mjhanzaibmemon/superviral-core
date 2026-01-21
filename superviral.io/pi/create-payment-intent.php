<?php

require_once 'shared.php';

$db=1;
include('../db.php');

$session = addslashes($_GET['session']);

$q = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$session' LIMIT 1");
if(mysql_num_rows($q)=='0')die;

$info = mysql_fetch_array($q);

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



if(!empty($_COOKIE['discount'])){include('../detectdiscount.php');}

setlocale(LC_MONETARY, 'en_UK');

$finalprice = money_format('%.2n', $finalprice);
$finalprice = str_replace('.', '', $finalprice);

$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);
$packagetitle = 'SV CV Services';

$ordersessioncountry = $info['country'];
$countrycurrency = $locas[$ordersessioncountry]['currencypp'];

$paymentIntent = \Stripe\PaymentIntent::create([
	'amount' => $finalprice,
	'currency' => $countrycurrency,
	'description' => $packagetitle,
	'metadata' => ["order_id" => $session]
]);

$output = [
	'publishableKey' => $stripepublishablekey,
	'clientSecret' => $paymentIntent->client_secret,
];

echo json_encode($output);
