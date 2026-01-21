<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$tpl = file_get_contents('editautoreply.html');


$did = addslashes($_GET['did']);
$id = addslashes($_GET['id']);

if ($_POST['submit'] == 'Save') {

    $title = addslashes(trim($_POST['title']));
    $autoreply = addslashes(trim($_POST['autoreply']));
    $page = addslashes(trim($_POST['page']));
    $added = time();
    $showDefault = isset($_POST['showdefault']) ? 1 : 0;

    if(!empty($showDefault)){
        mysql_query("UPDATE `email_autoreplies` 
        SET `showdefault`= 0 WHERE `page` = '$page'");
    }

    if (!empty($id)) {

        $q = mysql_query("UPDATE `email_autoreplies` 
        SET `title` = '{$title}',`autoreply`='{$autoreply}', 
        added='" . $added . "', 
        `showdefault`='$showDefault', `page` = '$page' WHERE id = '$id'");

        $newid = $id;

    }else{
        
        $q = mysql_query("INSERT INTO `email_autoreplies` 
        SET `title` = '{$title}',`autoreply`='{$autoreply}', 
        added='" . $added . "', 
        `showdefault`='$showDefault', `page` = '$page'");

        $newid = mysql_insert_id();

    }

     if ($q) {
        header('Location: editautoreply.php?id=' . $newid . '&message=3');
    } else {
        die('Error creating a new row QUERY');
    }

    die;
}

if (!empty($id)) {
    $q = mysql_query("SELECT * FROM `email_autoreplies` WHERE `id` = '$id' LIMIT 1");

    $info = mysql_fetch_array($q);
}
if ($_GET['message'] == '3') {
    $message = '<div class="emailsuccess">Saved Successfully.</div>';
}


if (!empty($did)) {

    $q = mysql_query("DELETE FROM `email_autoreplies` WHERE `id` = '$did' LIMIT 1");

    if ($q) {
        header('Location: /admin/autoreplies/?&message=5');
        die;
    }
}

$tpl = str_replace('value="'. $info['page'] .'"', 'value="'. $info['page'] .'" selected', $tpl);

$checked = !empty($info['showdefault']) ? 'checked' : "";

$tpl = str_replace('{title}', $info['title'], $tpl);
$tpl = str_replace('{autoreply}', $info['autoreply'], $tpl);
$tpl = str_replace('{checked}', $checked, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{loc}', $info['country'] == 'uk' ? '/uk' : '', $tpl);

output($tpl, $options);
