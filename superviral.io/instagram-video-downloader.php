<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=0;
include('header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

if($locas[$loc]['sdb'] == 'uk'){
	$tpl = file_get_contents('uk/instagram-video-downloader.html');
}else{
	$tpl = file_get_contents('us/instagram-video-downloader.html');
}

$statsQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `type` = 'free_tools_service' LIMIT 1");   
$statsData = mysql_fetch_array($statsQuery);
$metricCount = $statsData['metric'];
$secondPointContext = "2. Tap the button below";
$thirdPointContext = "";
if($metricCount > 30){
    
    // $recaptchaUrl = '<script src="https://www.google.com/recaptcha/api.js?render='.$googleV3ClientKey.'"></script>';	
                        
    $stylePoint3Text = "";
    $onClickEvent = "onSubmitData(event);";
}
else{
    // $recaptchaUrl = "";
    $stylePoint3Text = "style='display:none;'";
    $onClickEvent = "getUserInfo('', event);";

}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{banner-instruction-2}', $secondPointContext, $tpl);
$tpl = str_replace('{banner-instruction-3}', $thirdPointContext, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{stylePoint3Text}', $stylePoint3Text, $tpl);
$tpl = str_replace('{googlev3recaptchakey}', $googleV3ClientKey, $tpl);
$tpl = str_replace('{onClickEvent}', $onClickEvent, $tpl);
$tpl = str_replace('{recaptchaUrl}', $recaptchaUrl, $tpl);


$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('instagram-video-downloader','global')");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}



echo $tpl;

?>