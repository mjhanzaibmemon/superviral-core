<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=0;
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
$tpl = file_get_contents('uk/tos.html');
echo $tpl;
die;
}


*/

//$tpl = file_get_contents('tos.html');
$tpl = file_get_contents('tos-2.html');

$domain = $_SERVER['SERVER_NAME'];
$email = strtolower($domain);


$loctos = array(
    "co.uk" => array(
             "contactnumber" => "0203 856 3786 (UK Toll-free landline)",
             "currency" => "GBP (British Pound sterling)",
             "country" => "UK ",
             "mailing" => "160 City Road, London, EC1V 2NX United Kingdom"
         ),
    "us" => array(
             "contactnumber" => "+1 646 941 7829 (US Toll-free landline)",
             "currency" => "USD (United States dollars)",
             "country" => "US ",
             "mailing" => "980 6th Avenue, New York, NY 10018"
         )
  );


$customerservice = '';

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{domain}', $domain, $tpl);
$tpl = str_replace('{email}', $email, $tpl);
$tpl = str_replace('{contactnumber}', $loctos[$loc]['contactnumber'], $tpl);
$tpl = str_replace('{mailing}', $loctos[$loc]['mailing'], $tpl);
$tpl = str_replace('{currency}', $loctos[$loc]['currency'], $tpl);
$tpl = str_replace('{country}', $loctos[$loc]['country'], $tpl);
$tpl = str_replace('{currencysign}', $locas[$loc]['currencysign'], $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'tos') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);

$tpl = str_replace('ITH Retail Group LTD', 'Etra Ventures LTD', $tpl);



echo $tpl;
?>
