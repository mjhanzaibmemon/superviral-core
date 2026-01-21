<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

$q = addslashes($_GET['q']);
$res = mysql_query("
    SELECT id, business_name, tiktok_profile, instagram_profile, ubereats_profile, justeat_profile, website 
    FROM de_restaurant 
    WHERE business_name LIKE '%$q%' 
    LIMIT 1
");

$row = mysql_fetch_array($res);

header('Content-Type: application/json');
echo json_encode($row ?: []);
