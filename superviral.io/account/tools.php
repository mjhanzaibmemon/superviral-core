<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink3 = 'activelink';


include('../db.php');
include('auth.php');
include('header.php');
// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){

    header('Location: '. $siteDomain . $uri ,TRUE,301);die;

}
$tpl = file_get_contents('tools.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            // require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            // $translate = new TranslateClient(['key' => $googletranslatekey]);

            // $result = $translate->translate($tpl, [
            //     'source' => 'en', 
            //     'target' => $locas[$loc]['sdb'],
            //     'format' => 'html'
            // ]);

            // $tpl = $result['text'];

}

echo $tpl;
?>