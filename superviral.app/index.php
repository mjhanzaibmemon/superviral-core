<?php


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

$db=1;

include('header.php');





$tpl = file_get_contents('index-2.html');




$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{hsince}', "since 2012", $tpl);




echo $tpl;


?>