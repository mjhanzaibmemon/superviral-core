<?php



// start time
$start_time = microtime(true);



if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();



header('Content-type: text/html; charset=utf-8');







$db=1;



include('header.php');



include('ordercontrol.php');







//IF SUBMITTED











if(!empty($_POST['posts_selected'])){



  $submitted_values = json_decode($_POST['posts_selected'],true);







foreach($submitted_values as $value){







$values .= addslashes($value).'~~~';







}





$submitted_values_image = json_decode($_POST['posts_selected_image'],true);







foreach($submitted_values_image as $value1){







$values1 .= addslashes($value1).'~~~';







}









mysql_query("UPDATE `order_session` SET 



 			`chooseposts` = '{$values}',

			 `chooseposts_image` = '{$values1}'



			WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");











header('Location: /order/review/');







die;







}







if(!empty($info['chooseposts'])){







$chooseposts = explode('~~~', $info['chooseposts']);



foreach($chooseposts as $posts1){

if(empty($posts1))continue;

$selectedlist .= '"'.$posts1.'":"'.$posts1.'"'.',';

}


$selectedlist = rtrim($selectedlist,',');


$selectedlist = '{'.$selectedlist.'}';


$chooseposts_image = explode('~~~', $info['chooseposts_image']);



foreach($chooseposts_image as $posts2){


if(empty($posts2))continue;


$selectedlist_image .= '"'.$posts2.'":"'.$posts2.'"'.',';

}


$selectedlist_image = rtrim($selectedlist_image,',');







$selectedlist_image = '{'.$selectedlist_image.'}';

}else{


$selectedlist = '{}';

$selectedlist_image = '{}';





}







$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));



$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);











$maxamount = $packageinfo['amount'];



$postlimit = $packageinfo['postlimit'];



$packagedesc = ucwords($packageinfo['amount'].' '.$packageinfo['type'].' package');



/////////







$userDetail = mysql_fetch_array(mysql_query("SELECT * FROM `order_session` WHERE order_session = '$ordersession' AND `brand` = 'to' ORDER BY id DESC LIMIT 1"));



$userName = $userDetail['igusername'];







if($userName !=null && $userName !=""){



	$uName = "<input type='hidden' id='UserNameID' value='". $userName ."'>";



}else{



	$uName = "";



}



if($loggedin==true){
	$displayaccountbtn = 'displayaccountbtn';
	$displayemailaddress = 'style="display:none;"';}



/////////

$loadingtimelimit = 15;

$findloadingtimeq = mysql_query("SELECT `id`,`loadtime` FROM `tt_api_stats` WHERE `loadtime` != '0' ORDER BY `id` DESC LIMIT $loadingtimelimit");

while($findloadingtime = mysql_fetch_array($findloadingtimeq)){$loadingtimes[] = $findloadingtime['loadtime'];}

$loadingtimes = array_sum($loadingtimes)/$loadingtimelimit;
$loadingtimes = $loadingtimes + 0.9;




if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}







$tpl = file_get_contents('order-template.html');



$body = file_get_contents('order2-select.html');











$tpl = str_replace('{body}', $body, $tpl);



$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);



$tpl = str_replace('{back}', '/order/details/', $tpl);



$tpl = str_replace('{imgs}', $imgs, $tpl);



$tpl = str_replace('{postlimit}', $postlimit, $tpl);



$tpl = str_replace('{maxamount}', $maxamount, $tpl);



$tpl = str_replace('{selectedlist}', $selectedlist, $tpl);



$tpl = str_replace('{selectedlist_image}', $selectedlist_image, $tpl);



$tpl = str_replace('{packagedesc}', $packagedesc, $tpl);



$tpl = str_replace('{packages}', $packages, $tpl);



$tpl = str_replace('{errorstyle}', $errorstyle, $tpl);



$tpl = str_replace('{userNameTextBox}', $uName, $tpl);

$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);

$tpl = str_replace('{packageType}', $packageinfo['type'], $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$tpl = str_replace('{loadtime}', $loadingtimes, $tpl);

sendCloudwatchData('Tikoid', 'order-select', 'UserFunnel', 'user-funnel-order-select-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Tikoid', 'page-load-order-select', 'PageLoadTiming', 'page-load-order-select-function', number_format($execution_time_sec, 2));

echo $tpl;
?>