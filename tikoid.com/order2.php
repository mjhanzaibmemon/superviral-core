<?php

// start time
$start_time = microtime(true);

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

if($_COOKIE['discount']=='on'){

	$discounton = '<div class="summary thewidth">
                            <div class="thewidthleft"><span class="package">Exclusive discount</span></div>
                            <div class="thewidthright"><font style="font-style:italic;">-30% OFF</font></div>
                    </div>';


}

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));

$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']).' package';


if(($packageinfo['type']=='likes')||($packageinfo['type']=='views')){

		$back = '/order/select/';

		$chooseposts = explode('~~~', $info['chooseposts_image']);

		foreach($chooseposts as $posts){
		if(empty($posts))continue;

		$allposts .= '<img style="margin-left:2px;margin-top:5px;" height="99" width="66" src="'.$posts.'">';
		$profilepicturedisplay .= 'style="display:none;"';

		}


}else{

		$back = '/order/details/';

}



$discountamount = round($packageinfo['amount'] * 0.50);
$discountoriginal = number_format(round($packageinfo['price'] * 0.50,2),2);
$discountactual = number_format(round($discountoriginal * 0.75,2),2);

$discounttitle = 'Add <b>'.$discountamount .' '. $packageinfo['type'] . '</b> and <b style="color:#80bd29">save 25%</b>';
$discountbtn = '<a class="btn greenbtn" href="?add=true">+ Add for '.$currency.$discountactual.' <strike>'.$currency.$discountoriginal.'</strike></a>';



if($_GET['add']=='true'){

$upselladd = $discountamount.'###'.$discountactual;

mysql_query("UPDATE `order_session` SET `upsell` = '$upselladd' WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to' LIMIT 1");

sendCloudwatchData('Tikoid', 'upsell-'. $packageinfo['type'], 'OrderReview', 'order-review-upsell-'. $packageinfo['type'] .'-function', 1);

header('Location: /order/review/');

}

if($_GET['add']=='false'){

mysql_query("UPDATE `order_session` SET `upsell` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to' LIMIT 1");
header('Location: /order/review/');

}

if(!empty($info['upsell'])){


$discounttitle .= '<div class="tickadded"><span class="tick"></span><span style="">Added</span></div>';

$discountbtn = $currency.$discountactual.'<br><a class="remove" href="?add=false">Remove</a>';



$summaryupsell1 = '<span class="package ups" style="display: block;margin-top: 13px;color:#008000!important">Additional '.$discountamount.' '. $packageinfo['type'] . '</span>';
$summaryupsell2 = '<span class="ups" style="display: block;margin-top: 19px;color: black;">+ '.$currency.$discountactual.'</span><a href="?add=false" style="    position: absolute;right: -35px;bottom: 9px;">
<svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a>';

$setdiscount = $discountactual;
}

$totalprice = $packageinfo['price'] + $setdiscount;

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

if($loggedin==true){
	$displayaccountbtn = 'displayaccountbtn';
	$displayemailaddress = 'style="display:none;"';}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order2.html');

$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
$tpl = str_replace('{back}', $back, $tpl);
$tpl = str_replace('{profilepicture}',$profilepicture,$tpl);
$tpl = str_replace('{igusername}',$info['igusername'],$tpl);
$tpl = str_replace('{packagetitle}',$packagetitle,$tpl);


$tpl = str_replace('{profilepicturedisplay}',$profilepicturedisplay,$tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{discounttitle}',$discounttitle,$tpl);
$tpl = str_replace('{discountbtn}',$discountbtn,$tpl);
$tpl = str_replace('{summaryupsell1}',$summaryupsell1,$tpl);
$tpl = str_replace('{summaryupsell2}',$summaryupsell2,$tpl);
$tpl = str_replace('{posts}',$allposts,$tpl);

$tpl = str_replace('{packagetitle}',$packagetitle,$tpl);
$tpl = str_replace('{currency}',$currency,$tpl);
$tpl = str_replace('{discountreview}',$discountreview,$tpl);
$tpl = str_replace('{price}',$packageinfo['price'],$tpl);
$tpl = str_replace('{totalprice}',$totalprice,$tpl);
$tpl = str_replace('{discounton}',$discounton,$tpl);
$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

sendCloudwatchData('Tikoid', 'order-review', 'UserFunnel', 'user-funnel-order-review-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Tikoid', 'page-load-order-review', 'PageLoadTiming', 'page-load-order-review-function', number_format($execution_time_sec, 2));



echo $tpl;
