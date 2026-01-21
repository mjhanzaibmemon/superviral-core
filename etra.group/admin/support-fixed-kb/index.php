<?php


$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;


require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$btnAddCat = addslashes($_POST['btnAddCat']);

if(!empty($btnAddCat)){
    $catName = addslashes($_POST['category']);

    $insert = mysql_query("INSERT INTO `ai_support_fixed_kb` SET `type` = 'category', `value` = '$catName'");
    $lastId = mysql_insert_id();

    // insert other types
    mysql_query("
    INSERT INTO `ai_support_fixed_kb` (`type`, `parent_id`) 
    VALUES 
    ('info', '$lastId'),
    ('actions', '$lastId'),
    ('api', '$lastId'),
    ('user_info', '$lastId')
");

}

$categoryQ = mysql_query("SELECT * FROM `ai_support_fixed_kb` WHERE `type` ='category'");


$categories = "";
while ($categoryData = mysql_fetch_array($categoryQ)) {

    $categories .= '<li><a href="edit.php?id='. $categoryData['id'] .'&category='. $categoryData['value'] .'">' . $categoryData['value'] . '</a></li>';
}


$tpl = str_replace('{categories}',$categories,$tpl);


output($tpl, $options);
