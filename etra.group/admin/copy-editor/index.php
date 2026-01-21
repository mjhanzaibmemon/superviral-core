<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$comp = addslashes($_GET['comp']);

if(!empty($comp)) $brand = $comp;
// country
$country = mysql_query("SELECT id,country FROM `content` WHERE brand = '$brand' AND country != 'ww' group by `country`");


$countryHtml = "";
while ($countryData = mysql_fetch_array($country)) {

    $countryHtml .= '<option value="' . $countryData['country'] . '">' . $countryData['country'] . '</option>';
}

// page

$page = mysql_query("SELECT id,`page` FROM `content` WHERE brand = '$brand' group BY `page`;");

$pageHtml = "";
while ($pageData = mysql_fetch_array($page)) {

    $pageHtml .= '<option value="' . $pageData['page'] . '">' . $pageData['page'] . '</option>';
}

// name

$name = mysql_query("SELECT id,`name` FROM `content` WHERE brand = '$brand' group BY `name`;");

$nameHtml = "";
while ($nameData = mysql_fetch_array($name)) {

    $nameHtml .= '<option value="' . $nameData['name'] . '">' . $nameData['name'] . '</option>';
}


$tpl = str_replace('{countryHtml}',$countryHtml,$tpl);
$tpl = str_replace('{pageHtml}',$pageHtml,$tpl);
$tpl = str_replace('{nameHtml}',$nameHtml,$tpl);


output($tpl, $options);
