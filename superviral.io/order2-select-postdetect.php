<?php

use function GuzzleHttp\Psr7\str;

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;



include('header.php');
include('ordercontrol.php');

$postDetectionFlag = addslashes($_GET["postdetection"]);

$pointerCss = "";
$changePackageDropDown = "";
if(!empty($postDetectionFlag)){
	$pointerCss = "pointer-events: none;";
	$changePackageDropDown = '<select id="get_value">
									{dropDownPackage}
							  </select>';
	$countPost = count(explode('~~~', $info['chooseposts'])); 
	
	// get suitable package
	if(addslashes($_GET['changepackage']) == ""){
		$suitablePackage = mysql_fetch_array(mysql_query("SELECT id FROM packages WHERE postlimit >= $countPost AND `type`='likes' ORDER BY postlimit asc LIMIT 1 "));
		$suitablePackageId = $suitablePackage['id'];
	
		$res =  mysql_query("UPDATE `order_session` SET 
								  `packageid` = '{$suitablePackageId}'
								 WHERE `order_session` = '$ordersession' LIMIT 1");
	
		$checkq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$ordersession}' AND `packageid` = '$suitablePackageId' LIMIT 1");
		$info = mysql_fetch_array($checkq);	
	}
	
}

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));
$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);

$hidevideos = $packageinfo['type'];
$packagetype = $packageinfo['type'];

$allpackagesq = mysql_query("SELECT * FROM `packages` WHERE `type` = '{$packageinfo['type']}' AND `premium` = '0' ORDER BY `amount` ASC");

$dropDownPackage = "";
while($allpackages = mysql_fetch_array($allpackagesq)){


	$dropDownPackage .= '<option name="packages" value="'.$allpackages['id'].'">'.$allpackages['amount'].' '.$allpackages['type'].' - '.$locas[$loc]['currencysign'].$allpackages['price'].$locas[$loc]['currencyend'].'</option>';
	// $ptype = $allpackages['type'];

}

$dropDownPackage = str_replace('value="'.$info['packageid'].'"', 'value="'.$info['packageid'].'" selected = "selected"', $dropDownPackage);

if($packagetype=='views'){$videosonly = '&videosonly=1';}



if(!empty($_POST['instaCombinedPosts'])){
	$submittedValues = addslashes($_POST['instaCombinedPosts']);
  
	$saveValues = str_replace(",","~~~",$submittedValues);
  
  mysql_query("UPDATE `order_session` SET 
			   `chooseposts` = '{$saveValues}'
			  WHERE `order_session` = '$ordersession' LIMIT 1");
  
  
  header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/');
  
  die;
  
  }

if(!empty($_POST['posts_selected'])){
  $submitted_values = json_decode($_POST['posts_selected'],true);

foreach($submitted_values as $value){

$values .= addslashes($value).'~~~';

}

echo $values;


mysql_query("UPDATE `order_session` SET 
 			`chooseposts` = '{$values}'
			WHERE `order_session` = '$ordersession' LIMIT 1");


header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order2'].'/');

die;

}

if(!empty($info['chooseposts'])){

$chooseposts = explode('~~~', $info['chooseposts']);
$postURL = "";
foreach($chooseposts as $posts1){

$postURL .= $posts1 . ",";

if(empty($posts1))continue;


$posts2 = explode('###', $posts1);

$selectedlist .= '"'.$posts2[0].'###'.$posts2[1].'":"'.$posts2[0].'###'.$posts2[1].'"'.',';}


$selectedlist = rtrim($selectedlist,',');

$selectedlist = '{'.$selectedlist.'}';

$postURL = rtrim($postURL,',');


}else{

$selectedlist = '{}';

}


$maxamount = $packageinfo['amount'];
$postlimit = $packageinfo['postlimit'];
$packagedesc = ucwords($packageinfo['amount'].'{'.$packageinfo['type'].' package}');
/////////


//require('order2-getposts.php');//NOT IN USE

/////////



if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

//THIS ISNT IN USE AS EVERYTHINGS NOT AUTOMATED THROUGH JQUERY
//if($nopostfound==1){$dontshowselectctn='style="display:none;"';$shownopostmsg = '';}else{$dontshowselectctn='';$shownopostmsg = 'style="display:none;"';}

$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';

if($loggedin==true){$displayaccountbtn = 'displayaccountbtn';}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order2-select-postdetect.html');

//if($_GET['rabban']=='true')$body = file_get_contents('order2-select-3.html');

$tpl = str_replace('{body}', $body, $tpl);
$tpl = str_replace('{sdblivecheckout}', $locredirect, $tpl);
//$tpl = str_replace('{dontshowselectctn}', $dontshowselectctn, $tpl);//NOT IN USE
//$tpl = str_replace('{shownopostmsg}', $shownopostmsg, $tpl);//NOT IN USE
$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
$tpl = str_replace('{back}', '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/', $tpl);
//$tpl = str_replace('{imgs}', $imgs, $tpl);//NOT IN USE
$tpl = str_replace('{postlimit}', $postlimit, $tpl);
$tpl = str_replace('{maxamount}', $maxamount, $tpl);
$tpl = str_replace('{selectedlist}', $selectedlist, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{username}', $info['igusername'], $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{videosonly}', $videosonly, $tpl);
$tpl = str_replace('{errorstyle}', $errorstyle, $tpl);
$tpl = str_replace('{packagedesc}', $packagedesc, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{changepackagehref}', '/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/', $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);
$tpl = str_replace('{pointerCss}', $pointerCss, $tpl);
$tpl = str_replace('{changePackageDropDown}', $changePackageDropDown, $tpl);
$tpl = str_replace('{dropDownPackage}', $dropDownPackage, $tpl);


if($packagetype=='likes')$tpl = str_replace('{divboxpckg}', '{divboxlikespckg}', $tpl);
if($packagetype=='views')$tpl = str_replace('{divboxpckg}', '{divboxviewspckg}', $tpl);
$tpl = str_replace('{postURL}', $postURL, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order1-select') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){

$foundcontent=0;


if($cinfo['name']=='packagedesc')

	{

		$cinfo['content'] = str_replace('$packageinfo[\'amount\']',$packageinfo['amount'],$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;

	}



if($foundcontent==0)$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);


}


echo $tpl;
?>