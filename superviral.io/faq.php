<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
include('header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}



/*//IF ITS UK THEN SHOW STATIC PAGE
if(($loc=='uk')&&($_GET['rabban']!=='true')){


if($_GET['id']=='payments'){

		$tpl = file_get_contents('uk/faq-2.html');

	}else{

		$tpl = file_get_contents('uk/faq.html');

}

echo $tpl;
die;
}*/


if($locas[$loc]['sdb'] == 'uk'){
	$tpl = file_get_contents('uk/faq.html');
}else{
	$tpl = file_get_contents('us/faq.html');
}


$_GET['id'] = addslashes($_GET['id']);


if($_GET['id']=='payments'){
	$faq1btn = 'color4';
	$faq2btn = 'faqactive';
	
	$faq = file_get_contents($locas[$loc]['sdb']."/faq-payment.html");
}else{
	$faq1btn = 'faqactive';
	$faq2btn = 'color4';
	$faq = file_get_contents($locas[$loc]['sdb'].'/faq-service.html');
}



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{faq}', $faq, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{faq1btn}', $faq1btn, $tpl);
$tpl = str_replace('{faq2btn}', $faq2btn, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'faq') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");


while($cinfo = mysql_fetch_array($contentq)){
	if($_GET['id'] && $cinfo['name']=='metadesc')$tpl = str_replace('{metadesc}','{metadesc_payment}',$tpl);
	if($_GET['id'] && $cinfo['name']=='metadesc')$tpl = str_replace('{metadesc}','{metadesc_payment}',$tpl);
	if($_GET['id'] && $cinfo['name']=='h1')str_replace('{h1}','{h1_payment}',$tpl);

	$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
	if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];
}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);

echo $tpl;
?>