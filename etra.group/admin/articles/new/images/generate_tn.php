<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$tpl = file_get_contents('generate_tn.html');

$idsParam = isset($_GET['ids']) ? $_GET['ids'] : 0;
$ids = array_filter(array_map('intval', explode(',', $idsParam)));
$edit = isset($_GET['edit']) ? addslashes($_GET['edit']) : '';
$iframeHtml = '';
$numberOfImages = 3; 

foreach ($ids as $articleId) {
    $imgQ = mysql_query("SELECT * FROM `articles_vector_image` 
                         WHERE `article_id` = '$articleId' 
                         ORDER BY id DESC 
                         LIMIT $numberOfImages");

    while ($vectorImg = mysql_fetch_array($imgQ)) {
        $imgId = $vectorImg['id'];
        $iframeHtml .= "<iframe src='https://anuj.etra.group/admin/articles/new/images/image_editor.php?id=$imgId&edit=$edit' 
                        style='width:100%;height:1400px;border:1px solid #ccc;margin:10px;'></iframe>";
    }
}

$tpl = str_replace('{iframes}', $iframeHtml, $tpl);
$tpl = str_replace('{numberOfImages}', $numberOfImages, $tpl);
echo $tpl;
