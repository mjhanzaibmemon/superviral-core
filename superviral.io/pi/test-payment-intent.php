<?php

$db=1;
include('../db.php');

$session = addslashes($_GET['session']);

$q = mysql_query("SELECT `id`,`order_session`,`added`,`packageid`,`upsell` FROM `order_session` WHERE `order_session` = '2f6fbcff0cc73104fb164d2dc24a55a2' LIMIT 1");
if(mysql_num_rows($q)=='0')die;

$info = mysql_fetch_array($q);

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));

if(!empty($info['upsell'])){

$upsellprice = explode('###',$info['upsell']);

$upsellamount = $upsellprice[0];
$upsellprice = $upsellprice[1];

$finalprice = $packageinfo['price'] + $upsellprice;
$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

echo $upsellprice.'<br>';
echo $packageinfo['price'].'<br>';

}else{

$finalprice = $packageinfo['price'];

}

if(!empty($_COOKIE['discount'])){include('../detectdiscount.php');}

echo 'Before: '.$finalprice.'<br>';

setlocale(LC_MONETARY, 'en_UK');


$finalprice = money_format('%.2n', $finalprice);
//$finalprice = str_replace('.', '', $finalprice);

echo 'After'.$finalprice.'<br>';

$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);


?>