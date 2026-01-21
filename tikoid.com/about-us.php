<?php



if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

header('Content-type: text/html; charset=utf-8');



$db=0;

include('header.php');





$tpl = file_get_contents('about-us.html');



$tpl = str_replace('{header}', $header, $tpl);

$tpl = str_replace('{headerscript}', $headerscript, $tpl);

$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('about-us', 'global') AND brand = 'to' ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

echo $tpl;

// test anuj local

?>