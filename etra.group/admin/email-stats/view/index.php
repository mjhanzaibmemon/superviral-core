<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$id = addslashes(trim($_GET['id']));

$catQuery = "SELECT * FROM email_sent_stats WHERE id = $id ";
$queryRun = mysql_query($catQuery);
$res = mysql_fetch_array($queryRun);

$tpl = str_replace('{body}', $res['body'] , $tpl);

output($tpl, $options);
