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

$tpl = file_get_contents('index.html');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit = isset($_GET['edit']) ? addslashes($_GET['edit']) : '';

if(!empty($id)) {
    $imageQ = mysql_query("SELECT * FROM `articles_vector_image` WHERE id = $id LIMIT 1");

    if ($row = mysql_fetch_array($imageQ)) {
        $vector_image = $row['blob']; 
        $article_id = $row['article_id'];

        $articleQ = mysql_query("SELECT * FROM `articles` WHERE id = $article_id LIMIT 1");

        $row = mysql_fetch_array($articleQ);
        $article_title = $row['title'];

    } 
} 

$tpl = str_replace('{vector_image}', $vector_image, $tpl);
$tpl = str_replace('{article_id}', $article_id, $tpl);
$tpl = str_replace('{article_title}', $article_title, $tpl);
$tpl = str_replace('{edit}', $edit, $tpl);

echo $tpl;
