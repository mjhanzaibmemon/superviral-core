<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');

$db = 0;
include('../header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us", "", $_SERVER['REQUEST_URI']);
if ($queryLoc == 'us') {
    // echo $queryLoc;
    header('Location: ' . $siteDomain . $uri, TRUE, 301);
    die;
}

$id = addslashes($_GET['id']);

if (!empty($id)) {

    $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' and `brand` = 'sv' LIMIT 1");

    if (mysql_num_rows($validq) == '1') {
        $res = mysql_fetch_array($validq);

        if($res['amount'] > 0){
            header('Location: /free-followers-reels-form/?id='.$id);
            die;
        }

        if($res['done'] > 0){
            header('Location: /buy-instagram-followers/?freefollowers='.$id.'&em_split=b');
            die;
        }
    }

} else {


    die('Try clicking on the link from the email again.');
}

$tpl = file_get_contents('freefollowers_reels.html');



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('instagram-story-downloader','global')");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}



echo $tpl;
