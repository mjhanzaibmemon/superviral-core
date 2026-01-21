<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=0;
include('header.php');

//IF ITS UK THEN SHOW STATIC PAGE
/*if(($loc=='uk')&&($_GET['rabban']!=='true')){
$tpl = file_get_contents('uk/about-us.html');
echo $tpl;
die;
}
*/

//asdaa

$tpl = file_get_contents('about-us.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'about-us') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");

echo "<!-- SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'about-us') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global')  -->";

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);

echo $tpl;

?>