<?php

$discountoff = 10;
$discountadded = time();
$discountexpiry = $discountadded + 172800;//ADD 2 DAYS
$discountmd5 = md5($discountoff.$discountadded.$discountexpiry);
$discountcode = 'forbes10';
$discounttitle = '10% Off All Followers, Likes and Views - Expiring in <font id="demo" color="red"></font>!<br>Celebrating our Feature on Forbes magazine!';
$discounttitlecart = '10% Off All Orders - Expiring in <font id="demo" color="red"></font>!<br>Forbes celebration!';
$discountuser = $info['id'];

mysql_query("INSERT INTO `discounts` SET `user` = '$discountuser',`discountoff` = '$discountoff',`md5`='$discountmd5',`title` = '$discounttitle',`titlecart` = '$discounttitlecart',`added` = '$discountadded',`expiry` = '$discountexpiry',`code` = '$discountcode', brand ='$brand'");


$ctalink = 'https://superviral.io/activate-discount/?id='.$discountmd5.'&expiry='.$discountexpiry;

$tpl = str_replace('{ctalink}', $ctalink, $tpl);

unset($ctalink);

?>